<?php

namespace App\Services;

use App\Models\Payment;

class PaymentCompletionService
{
    public function markAsPaid(Payment $payment, string $gatewayReference): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status' => 'paid',
            'gateway_reference' => $gatewayReference,
            'paid_at' => now(),
        ]);

        $payment->loadMissing('serviceRequest.service', 'serviceRequest.citizen');

        app(RequestStatusHistoryService::class)->recordPayment($payment);
        app(NotificationService::class)->paymentCompleted($payment->serviceRequest, $payment);
        app(PaymentDocumentService::class)->sendPaidDocuments($payment);
    }

    public function markFailed(Payment $payment): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        $payment->update(['status' => 'failed']);
    }
}
