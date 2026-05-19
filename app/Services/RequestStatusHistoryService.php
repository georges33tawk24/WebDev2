<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;

class RequestStatusHistoryService
{
    public function recordPayment(Payment $payment): void
    {
        $payment->loadMissing('serviceRequest');

        $serviceRequest = $payment->serviceRequest;

        if (! $serviceRequest instanceof ServiceRequest) {
            return;
        }

        $marker = '[payment:'.$payment->id.']';

        $alreadyRecorded = RequestStatusHistory::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('to_status', 'paid')
            ->where('comment', 'like', '%'.$marker.'%')
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        $amount = number_format((float) $payment->amount, 2).' '.$payment->currency;

        RequestStatusHistory::query()->create([
            'service_request_id' => $serviceRequest->id,
            'changed_by' => $payment->user_id,
            'from_status' => $serviceRequest->status,
            'to_status' => 'paid',
            'comment' => __('ui.history.payment_paid', [
                'amount' => $amount,
                'method' => __('ui.payments.method_'.$payment->method),
            ]).' '.$marker,
            'changed_at' => $payment->paid_at ?? now(),
        ]);
    }
}
