<?php

namespace App\Services;

use App\Mail\PaymentDocumentsMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentDocumentService
{
    public function sendPaidDocuments(Payment $payment): void
    {
        $payment->loadMissing('serviceRequest.citizen');

        $citizen = $payment->serviceRequest?->citizen;

        if (! $citizen instanceof User || ! $this->hasDeliverableEmail($citizen)) {
            return;
        }

        try {
            Mail::to($citizen->email)->send(new PaymentDocumentsMail($payment));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send payment documents email.', [
                'payment_id' => $payment->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function hasDeliverableEmail(User $user): bool
    {
        $email = $user->email;

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));

        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        return ! in_array($domain, ['example.com', 'example.org', 'example.net'], true);
    }
}
