<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    /** @var list<string> */
    private const PAID_STATUSES = ['finished', 'confirmed'];

    /** @var list<string> */
    private const FAILED_STATUSES = ['failed', 'expired', 'refunded'];

    /** @var list<string> */
    private const CONFIRMING_STATUSES = ['confirming', 'sending', 'partially_paid'];

    public function isConfigured(): bool
    {
        return filled(config('services.nowpayments.api_key'));
    }

    public function isSandbox(): bool
    {
        return (bool) config('services.nowpayments.sandbox', true);
    }

    public function payCurrency(): string
    {
        return strtolower((string) config('services.nowpayments.pay_currency', 'usdttrc20'));
    }

    public function minimumPriceAmount(string $priceCurrency = 'usd', ?string $payCurrency = null): ?float
    {
        $payCurrency ??= $this->payCurrency();

        try {
            $response = $this->client()->get('/v1/min-amount', [
                'currency_from' => strtolower($priceCurrency),
                'currency_to' => $payCurrency,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $min = $response->json('min_amount');

            return is_numeric($min) ? (float) $min : null;
        } catch (\Throwable $exception) {
            Log::warning('NOWPayments min-amount fetch failed', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function amountMeetsMinimum(float $priceAmount, string $priceCurrency = 'usd'): bool
    {
        $min = $this->minimumPriceAmount($priceCurrency);

        if ($min === null) {
            return true;
        }

        return $priceAmount >= $min;
    }

    public function userFacingErrorMessage(\Throwable $exception): string
    {
        if ($exception instanceof RequestException) {
            /** @var array<string, mixed>|null $body */
            $body = $exception->response?->json();

            if (is_array($body)) {
                $code = (string) ($body['code'] ?? '');

                if ($code === 'AMOUNT_MINIMAL_ERROR') {
                    $min = $this->minimumPriceAmount();

                    return __('ui.flash.crypto_amount_below_minimum', [
                        'min' => $min !== null ? localized_money($min) : __('ui.na'),
                    ]);
                }

                if (filled($body['message'] ?? null)) {
                    return (string) $body['message'];
                }
            }
        }

        return __('ui.flash.crypto_checkout_failed');
    }

    /**
     * Hosted checkout (browser wallet UI) — preferred for laptop/desktop payers.
     *
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function createInvoice(
        ServiceRequest $serviceRequest,
        Payment $payment,
    ): array {
        $serviceRequest->loadMissing('service');

        $response = $this->client()
            ->post('/v1/invoice', $this->checkoutPayload($serviceRequest, $payment))
            ->throw()
            ->json();

        return is_array($response) ? $response : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPayload(ServiceRequest $serviceRequest, Payment $payment): array
    {
        return [
            'price_amount' => (float) $payment->amount,
            'price_currency' => strtolower((string) $payment->currency),
            'pay_currency' => $this->payCurrency(),
            'order_id' => (string) $payment->id,
            'order_description' => __('ui.payments.crypto_line_description', [
                'ref' => $serviceRequest->reference_number,
                'service' => $serviceRequest->service?->localized('name') ?? __('ui.na'),
            ]),
            'ipn_callback_url' => route('webhooks.nowpayments'),
            'success_url' => route('citizen.payments.crypto.success', $serviceRequest),
            'cancel_url' => route('citizen.payments.crypto.cancel', $serviceRequest),
        ];
    }

    /**
     * Creates the API payment record linked to a hosted invoice (required for status polling).
     *
     * @return array<string, mixed>|null
     */
    public function createInvoicePayment(string $invoiceId): ?array
    {
        try {
            $response = $this->client()
                ->post('/v1/invoice-payment', [
                    'iid' => $invoiceId,
                    'pay_currency' => $this->payCurrency(),
                ])
                ->throw()
                ->json();

            return is_array($response) ? $response : null;
        } catch (\Throwable $exception) {
            Log::warning('NOWPayments invoice-payment failed', [
                'invoice_id' => $invoiceId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve NOWPayments payment id for polling (invoice id alone cannot be polled).
     */
    public function resolveGatewayPaymentId(Payment $payment): ?string
    {
        $meta = $payment->gateway_meta ?? [];
        $paymentId = $meta['payment_id'] ?? null;

        if (filled($paymentId)) {
            return (string) $paymentId;
        }

        $reference = (string) ($payment->gateway_reference ?? '');

        if ($reference !== '' && $this->fetchPaymentStatus($reference) !== null) {
            $payment->update([
                'gateway_meta' => array_merge($meta, ['payment_id' => $reference]),
            ]);

            return $reference;
        }

        $invoiceId = (string) ($meta['invoice_id'] ?? '');

        if ($invoiceId === '') {
            return null;
        }

        $created = $this->createInvoicePayment($invoiceId);

        if (! $created) {
            return null;
        }

        $resolvedId = (string) ($created['payment_id'] ?? '');

        if ($resolvedId === '') {
            return null;
        }

        $payment->update([
            'gateway_reference' => $resolvedId,
            'gateway_meta' => array_merge($meta, [
                'invoice_id' => $invoiceId,
                'payment_id' => $resolvedId,
                'pay_address' => $created['pay_address'] ?? $meta['pay_address'] ?? null,
                'pay_amount' => $created['pay_amount'] ?? $meta['pay_amount'] ?? null,
                'pay_currency' => $created['pay_currency'] ?? $meta['pay_currency'] ?? null,
                'payment_status' => $created['payment_status'] ?? $meta['payment_status'] ?? null,
            ]),
        ]);

        return $resolvedId;
    }

    /**
     * Poll linked payment after hosted invoice checkout / IPN.
     *
     * @return array<string, mixed>|null
     */
    public function fetchGatewayStatus(Payment $payment): ?array
    {
        $paymentId = $this->resolveGatewayPaymentId($payment);

        if (! filled($paymentId)) {
            return null;
        }

        return $this->fetchPaymentStatus($paymentId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchPaymentStatus(string $gatewayPaymentId): ?array
    {
        try {
            $response = $this->client()
                ->get('/v1/payment/'.urlencode($gatewayPaymentId))
                ->throw()
                ->json();

            return is_array($response) ? $response : null;
        } catch (\Throwable $exception) {
            Log::warning('NOWPayments status fetch failed', [
                'payment_id' => $gatewayPaymentId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Poll NOWPayments and update local payment when gateway status is final.
     *
     * @return array{state: string, gateway_status: ?string, message: ?string}
     */
    public function syncPayment(Payment $payment): array
    {
        $payment->refresh();

        if ($payment->status === 'paid') {
            return [
                'state' => 'paid',
                'gateway_status' => null,
                'message' => null,
            ];
        }

        if ($payment->status === 'failed') {
            return [
                'state' => 'failed',
                'gateway_status' => null,
                'message' => __('ui.flash.crypto_payment_failed'),
            ];
        }

        $gateway = $this->fetchGatewayStatus($payment);
        $this->completeFromGatewayStatus($payment, $gateway);
        $payment->refresh();

        if ($payment->status === 'paid') {
            return [
                'state' => 'paid',
                'gateway_status' => null,
                'message' => null,
            ];
        }

        $gatewayStatus = strtolower((string) ($gateway['payment_status'] ?? ''));

        if (in_array($gatewayStatus, self::FAILED_STATUSES, true)) {
            app(PaymentCompletionService::class)->markFailed($payment);

            return [
                'state' => 'failed',
                'gateway_status' => $gatewayStatus,
                'message' => __('ui.flash.crypto_payment_failed'),
            ];
        }

        if (in_array($gatewayStatus, self::CONFIRMING_STATUSES, true)) {
            return [
                'state' => 'confirming',
                'gateway_status' => $gatewayStatus,
                'message' => __('ui.citizen.crypto_confirming'),
            ];
        }

        $message = null;

        if ($gateway === null) {
            $message = __('ui.citizen.crypto_poll_gateway_unreachable');
        } elseif ($gatewayStatus === 'waiting' || $gatewayStatus === '') {
            $message = $this->isSandbox()
                ? __('ui.citizen.crypto_poll_sandbox_waiting')
                : __('ui.citizen.crypto_poll_pending');
        }

        return [
            'state' => 'pending',
            'gateway_status' => $gatewayStatus !== '' ? $gatewayStatus : null,
            'message' => $message,
        ];
    }

    public function completeFromGatewayStatus(Payment $payment, ?array $gatewayPayload): ?Payment
    {
        if (! $gatewayPayload) {
            return null;
        }

        $status = strtolower((string) ($gatewayPayload['payment_status'] ?? ''));

        if (! in_array($status, self::PAID_STATUSES, true)) {
            return null;
        }

        $gatewayId = (string) ($gatewayPayload['payment_id'] ?? $payment->gateway_reference ?? '');

        app(PaymentCompletionService::class)->markAsPaid($payment, $gatewayId);

        return $payment->fresh();
    }

    public function handleIpn(string $rawPayload, ?string $signatureHeader): bool
    {
        $secret = config('services.nowpayments.ipn_secret');

        if (! filled($secret)) {
            Log::warning('NOWPayments IPN received but NOWPAYMENTS_IPN_SECRET is not set.');

            return false;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return false;
        }

        if (! $this->verifyIpnSignature($payload, (string) $signatureHeader, (string) $secret)) {
            Log::warning('NOWPayments IPN signature verification failed.');

            return false;
        }

        $payment = $this->resolvePaymentFromPayload($payload);

        if (! $payment) {
            return false;
        }

        $this->completeFromGatewayStatus($payment, $payload);

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyIpnSignature(array $payload, string $signatureHeader, string $secret): bool
    {
        if ($signatureHeader === '') {
            return false;
        }

        $sorted = $this->sortKeysRecursively($payload);
        $expected = hash_hmac('sha512', (string) json_encode($sorted, JSON_UNESCAPED_SLASHES), $secret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolvePaymentFromPayload(array $payload): ?Payment
    {
        $orderId = $payload['order_id'] ?? null;

        if ($orderId) {
            $payment = Payment::query()->find($orderId);

            if ($payment) {
                return $payment;
            }
        }

        $gatewayId = $payload['payment_id'] ?? null;

        if ($gatewayId) {
            return Payment::query()
                ->where('method', 'crypto')
                ->where('gateway_reference', (string) $gatewayId)
                ->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sortKeysRecursively(array $data): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sortKeysRecursively($value);
            }
        }

        return $data;
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $baseUrl = $this->isSandbox()
            ? 'https://api-sandbox.nowpayments.io'
            : 'https://api.nowpayments.io';

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => (string) config('services.nowpayments.api_key'),
            ])
            ->timeout(30);
    }
}
