<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    /**
     * @return array{url: string, session_id: string}
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(ServiceRequest $serviceRequest, Payment $payment, User $user): array
    {
        $this->bootstrap();

        $serviceRequest->loadMissing('service');
        $amountCents = $this->amountInCents((float) $payment->amount);

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower((string) $payment->currency),
                    'unit_amount' => $amountCents,
                    'product_data' => [
                        'name' => $serviceRequest->service?->localized('name') ?? __('ui.payments.municipal_service_fee'),
                        'description' => __('ui.payments.stripe_line_description', [
                            'ref' => $serviceRequest->reference_number,
                        ]),
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('citizen.payments.success', $serviceRequest).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('citizen.payments.cancel', $serviceRequest),
            'client_reference_id' => (string) $payment->id,
            'customer_email' => $user->email,
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'service_request_id' => (string) $serviceRequest->id,
                'user_id' => (string) $user->id,
            ],
        ]);

        return [
            'url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    /**
     * @throws ApiErrorException
     */
    public function retrieveSession(string $sessionId): Session
    {
        $this->bootstrap();

        return Session::retrieve($sessionId, ['expand' => ['payment_intent']]);
    }

    public function completeFromSession(Session $session): ?Payment
    {
        if ($session->payment_status !== 'paid') {
            return null;
        }

        $payment = $this->resolvePaymentFromSession($session);

        if (! $payment) {
            return null;
        }

        $this->markPaid(
            $payment,
            (string) ($session->payment_intent ?? $session->id),
        );

        return $payment->fresh();
    }

    public function handleWebhook(string $payload, ?string $signatureHeader): bool
    {
        $secret = config('services.stripe.webhook_secret');

        if (! filled($secret)) {
            Log::warning('Stripe webhook received but STRIPE_WEBHOOK_SECRET is not set.');

            return false;
        }

        try {
            $event = Webhook::constructEvent($payload, (string) $signatureHeader, $secret);
        } catch (\Throwable $exception) {
            Log::warning('Stripe webhook signature verification failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        if ($event->type !== 'checkout.session.completed') {
            return true;
        }

        /** @var Session $session */
        $session = $event->data->object;
        $this->completeFromSession($session);

        return true;
    }

    public function markPaid(Payment $payment, string $gatewayReference): void
    {
        app(PaymentCompletionService::class)->markAsPaid($payment, $gatewayReference);
    }

    public function markFailed(Payment $payment): void
    {
        app(PaymentCompletionService::class)->markFailed($payment);
    }

    private function resolvePaymentFromSession(Session $session): ?Payment
    {
        $paymentId = $session->client_reference_id
            ?? $session->metadata['payment_id'] ?? null;

        if (! $paymentId) {
            return null;
        }

        return Payment::query()->find($paymentId);
    }

    private function amountInCents(float $amount): int
    {
        return max(50, (int) round($amount * 100));
    }

    private function bootstrap(): void
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));
    }
}
