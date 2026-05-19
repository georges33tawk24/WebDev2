<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Services\ExchangeRateService;
use App\Services\NowPaymentsService;
use App\Services\PaymentCompletionService;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Throwable;

class PaymentController extends Controller
{
    public function show(ServiceRequest $serviceRequest, ExchangeRateService $exchangeRateService): View|RedirectResponse
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        if ($this->hasPaidPayment($serviceRequest)) {
            return redirect()
                ->route('citizen.payments')
                ->with('success', __('ui.flash.payment_already_completed'));
        }

        $priceUsd = (float) ($serviceRequest->service->price ?? 0);
        $dualPrice = $exchangeRateService->formatDualPrice($priceUsd);
        $lbpRate = $exchangeRateService->usdLbpRate();
        $stripeConfigured = app(StripePaymentService::class)->isConfigured();
        $nowPayments = app(NowPaymentsService::class);
        $cryptoConfigured = $nowPayments->isConfigured();
        $cryptoSandbox = $nowPayments->isSandbox();
        $cryptoMinUsd = $cryptoConfigured ? $nowPayments->minimumPriceAmount('usd') : null;
        $cryptoAmountTooLow = $cryptoMinUsd !== null && $priceUsd < $cryptoMinUsd;

        return view('citizen.payment-show', compact(
            'serviceRequest',
            'dualPrice',
            'lbpRate',
            'priceUsd',
            'stripeConfigured',
            'cryptoConfigured',
            'cryptoSandbox',
            'cryptoMinUsd',
            'cryptoAmountTooLow',
        ));
    }

    public function checkout(ServiceRequest $serviceRequest, StripePaymentService $stripe): RedirectResponse
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        if ($this->hasPaidPayment($serviceRequest)) {
            return redirect()
                ->route('citizen.payments')
                ->with('success', __('ui.flash.payment_already_completed'));
        }

        if (! $stripe->isConfigured()) {
            return back()->withErrors([
                'payment' => __('ui.flash.stripe_not_configured'),
            ]);
        }

        $amount = (float) ($serviceRequest->service->price ?? 0);

        if ($amount <= 0) {
            return back()->withErrors([
                'payment' => __('ui.flash.payment_amount_invalid'),
            ]);
        }

        $payment = Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => Auth::id(),
            'method' => 'card',
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        try {
            $checkout = $stripe->createCheckoutSession($serviceRequest, $payment, Auth::user());
        } catch (ApiErrorException $exception) {
            $payment->update(['status' => 'failed']);
            Log::warning('Stripe checkout session failed', ['message' => $exception->getMessage()]);

            $message = app()->environment('local')
                ? __('ui.flash.stripe_checkout_failed').' ('.$exception->getMessage().')'
                : __('ui.flash.stripe_checkout_failed');

            return back()->withErrors(['payment' => $message]);
        } catch (Throwable $exception) {
            $payment->update(['status' => 'failed']);
            Log::error('Stripe checkout unexpected error', ['message' => $exception->getMessage()]);

            return back()->withErrors(['payment' => __('ui.flash.stripe_checkout_failed')]);
        }

        if (empty($checkout['url'])) {
            $payment->update(['status' => 'failed']);

            return back()->withErrors(['payment' => __('ui.flash.stripe_checkout_failed')]);
        }

        $payment->update(['gateway_reference' => $checkout['session_id']]);

        return redirect()->away($checkout['url']);
    }

    public function success(Request $request, ServiceRequest $serviceRequest, StripePaymentService $stripe): RedirectResponse
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $sessionId = (string) $request->query('session_id', '');

        if ($sessionId === '') {
            return redirect()
                ->route('citizen.payments')
                ->withErrors(['payment' => __('ui.flash.stripe_session_missing')]);
        }

        try {
            $session = $stripe->retrieveSession($sessionId);
        } catch (ApiErrorException) {
            return redirect()
                ->route('citizen.payments')
                ->withErrors(['payment' => __('ui.flash.stripe_verify_failed')]);
        }

        $payment = $stripe->completeFromSession($session);

        if (! $payment || (int) $payment->service_request_id !== (int) $serviceRequest->id) {
            return redirect()
                ->route('citizen.payments')
                ->withErrors(['payment' => __('ui.flash.stripe_verify_failed')]);
        }

        return redirect()
            ->route('citizen.payments')
            ->with('success', __('ui.flash.payment_completed'));
    }

    public function cancel(ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        Payment::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where('method', 'card')
            ->latest()
            ->limit(1)
            ->get()
            ->each(fn (Payment $payment) => app(StripePaymentService::class)->markFailed($payment));

        return redirect()
            ->route('citizen.payments')
            ->with('status', __('ui.flash.payment_cancelled'));
    }

    public function cryptoCheckout(
        ServiceRequest $serviceRequest,
        NowPaymentsService $nowPayments,
    ): RedirectResponse {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        if ($this->hasPaidPayment($serviceRequest)) {
            return redirect()
                ->route('citizen.payments')
                ->with('success', __('ui.flash.payment_already_completed'));
        }

        if (! $nowPayments->isConfigured()) {
            return back()->withErrors([
                'payment' => __('ui.flash.crypto_not_configured'),
            ]);
        }

        $amount = (float) ($serviceRequest->service->price ?? 0);

        if ($amount <= 0) {
            return back()->withErrors([
                'payment' => __('ui.flash.payment_amount_invalid'),
            ]);
        }

        if (! $nowPayments->amountMeetsMinimum($amount, 'usd')) {
            $min = $nowPayments->minimumPriceAmount('usd');

            return back()->withErrors([
                'payment' => __('ui.flash.crypto_amount_below_minimum', [
                    'min' => $min !== null ? localized_money($min) : __('ui.na'),
                ]),
            ]);
        }

        $payment = Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => Auth::id(),
            'method' => 'crypto',
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        try {
            $gateway = $nowPayments->createInvoice($serviceRequest, $payment);
        } catch (\Throwable $exception) {
            $payment->update(['status' => 'failed']);
            Log::warning('NOWPayments create payment failed', ['message' => $exception->getMessage()]);

            $message = $nowPayments->userFacingErrorMessage($exception);

            if (app()->environment('local') && $message === __('ui.flash.crypto_checkout_failed')) {
                $message .= ' ('.$exception->getMessage().')';
            }

            return back()->withErrors(['payment' => $message]);
        }

        $invoiceId = (string) ($gateway['id'] ?? $gateway['invoice_id'] ?? '');
        $invoiceUrl = (string) ($gateway['invoice_url'] ?? '');

        if ($invoiceId === '' || $invoiceUrl === '') {
            $payment->update(['status' => 'failed']);

            return back()->withErrors(['payment' => __('ui.flash.crypto_checkout_failed')]);
        }

        $invoicePayment = $nowPayments->createInvoicePayment($invoiceId);
        $gatewayPaymentId = (string) ($invoicePayment['payment_id'] ?? '');

        if ($gatewayPaymentId === '') {
            $payment->update(['status' => 'failed']);

            return back()->withErrors(['payment' => __('ui.flash.crypto_checkout_failed')]);
        }

        $payment->update([
            'gateway_reference' => $gatewayPaymentId,
            'gateway_meta' => [
                'invoice_url' => $invoiceUrl,
                'invoice_id' => $invoiceId,
                'payment_id' => $gatewayPaymentId,
                'pay_address' => $invoicePayment['pay_address'] ?? null,
                'pay_amount' => $invoicePayment['pay_amount'] ?? null,
                'pay_currency' => $invoicePayment['pay_currency'] ?? $nowPayments->payCurrency(),
                'payment_status' => $invoicePayment['payment_status'] ?? 'waiting',
                'sandbox' => $nowPayments->isSandbox(),
            ],
        ]);

        return redirect()->away($invoiceUrl);
    }

    public function cryptoPay(ServiceRequest $serviceRequest, Payment $payment): View|RedirectResponse
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);
        $this->authorizeCryptoPayment($serviceRequest, $payment);

        if ($payment->status === 'paid' || $this->hasPaidPayment($serviceRequest)) {
            return redirect()
                ->route('citizen.payments')
                ->with('success', __('ui.flash.payment_already_completed'));
        }

        return $this->cryptoPayView($serviceRequest, $payment);
    }

    public function cryptoStatus(
        ServiceRequest $serviceRequest,
        Payment $payment,
        NowPaymentsService $nowPayments,
    ): JsonResponse {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);
        $this->authorizeCryptoPayment($serviceRequest, $payment);

        $result = $nowPayments->syncPayment($payment);

        $payload = [
            'state' => $result['state'],
            'gateway_status' => $result['gateway_status'],
            'message' => $result['message'],
        ];

        if ($result['state'] === 'paid') {
            $payload['redirect_url'] = route('citizen.payments');
        }

        return response()->json($payload);
    }

    public function cryptoSuccess(
        ServiceRequest $serviceRequest,
        NowPaymentsService $nowPayments,
    ): RedirectResponse|View {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $payment = Payment::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('user_id', Auth::id())
            ->where('method', 'crypto')
            ->latest()
            ->first();

        if (! $payment || ! $payment->gateway_reference) {
            return redirect()
                ->route('citizen.payments')
                ->withErrors(['payment' => __('ui.flash.crypto_verify_failed')]);
        }

        $result = $nowPayments->syncPayment($payment);

        if ($result['state'] === 'paid') {
            return redirect()
                ->route('citizen.payments')
                ->with('success', __('ui.flash.payment_completed'));
        }

        if ($result['state'] === 'failed') {
            return redirect()
                ->route('citizen.payments.show', $serviceRequest)
                ->withErrors(['payment' => $result['message'] ?? __('ui.flash.crypto_payment_failed')]);
        }

        return $this->cryptoPayView($serviceRequest, $payment, returnedFromGateway: true);
    }

    private function cryptoPayView(
        ServiceRequest $serviceRequest,
        Payment $payment,
        bool $returnedFromGateway = false,
    ): View {
        $meta = $payment->gateway_meta ?? [];

        return view('citizen.payment-crypto', [
            'serviceRequest' => $serviceRequest,
            'payment' => $payment,
            'invoiceUrl' => $meta['invoice_url'] ?? null,
            'payAddress' => $meta['pay_address'] ?? null,
            'payAmount' => $meta['pay_amount'] ?? null,
            'payCurrency' => $meta['pay_currency'] ?? config('services.nowpayments.pay_currency'),
            'isSandbox' => (bool) ($meta['sandbox'] ?? true),
            'pollConfig' => [
                'pollUrl' => route('citizen.payments.crypto.status', [$serviceRequest, $payment]),
                'paymentsUrl' => route('citizen.payments'),
                'returnedFromGateway' => $returnedFromGateway,
                'pendingLabel' => __('ui.citizen.crypto_poll_pending'),
                'confirmingLabel' => __('ui.citizen.crypto_confirming'),
                'paidLabel' => __('ui.citizen.crypto_poll_paid'),
                'failedLabel' => __('ui.flash.crypto_payment_failed'),
                'timeoutLabel' => __('ui.citizen.crypto_poll_timeout'),
                'returnedLabel' => __('ui.citizen.crypto_poll_returned'),
            ],
        ]);
    }

    private function authorizeCryptoPayment(ServiceRequest $serviceRequest, Payment $payment): void
    {
        abort_unless(
            (int) $payment->service_request_id === (int) $serviceRequest->id
            && (int) $payment->user_id === (int) Auth::id()
            && $payment->method === 'crypto',
            404,
        );
    }

    public function cryptoCancel(
        ServiceRequest $serviceRequest,
        PaymentCompletionService $completion,
    ): RedirectResponse {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        Payment::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where('method', 'crypto')
            ->latest()
            ->limit(1)
            ->get()
            ->each(fn (Payment $pending) => $completion->markFailed($pending));

        return redirect()
            ->route('citizen.payments')
            ->with('status', __('ui.flash.payment_cancelled'));
    }

    private function hasPaidPayment(ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->payments()
            ->where('status', 'paid')
            ->exists();
    }
}
