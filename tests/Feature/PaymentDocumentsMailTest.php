<?php

namespace Tests\Feature;

use App\Mail\PaymentDocumentsMail;
use App\Models\Office;
use App\Models\Payment;
use App\Models\RequestStatusHistory;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\PaymentDocumentService;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentDocumentsMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_paid_sends_invoice_and_receipt_email(): void
    {
        Mail::fake();

        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        app(StripePaymentService::class)->markPaid($payment, 'pi_test_email');

        Mail::assertSent(PaymentDocumentsMail::class, function (PaymentDocumentsMail $mail) use ($citizen, $request): bool {
            return $mail->hasTo($citizen->email)
                && (int) $mail->payment->service_request_id === (int) $request->id
                && count($mail->attachments()) === 2;
        });

        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $request->id,
            'to_status' => 'paid',
        ]);
    }

    public function test_payment_documents_mail_has_invoice_and_receipt_attachments(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'gateway_reference' => 'CRYPTO-TEST',
        ]);

        $mail = new PaymentDocumentsMail($payment);
        $names = array_map(fn ($attachment) => $attachment->as, $mail->attachments());

        $this->assertContains('invoice-'.$request->reference_number.'.pdf', $names);
        $this->assertContains('receipt-'.$request->reference_number.'.pdf', $names);
    }

    public function test_mark_paid_does_not_resend_email_when_already_paid(): void
    {
        Mail::fake();

        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(StripePaymentService::class)->markPaid($payment, 'pi_dup');

        Mail::assertNothingSent();
    }

    public function test_send_skips_invalid_email(): void
    {
        Mail::fake();

        $office = Office::query()->create(['name' => 'O', 'municipality' => 'Beirut']);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $citizen = User::query()->create([
            'name' => 'No Mail',
            'email' => 'not-an-email',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'S',
            'price' => 10,
            'is_active' => true,
        ]);
        $request = ServiceRequest::query()->create([
            'reference_number' => (string) Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(PaymentDocumentService::class)->sendPaidDocuments($payment);

        Mail::assertNothingSent();
    }

    /**
     * @return array{0: Office, 1: Role, 2: User, 3: ServiceRequest}
     */
    private function seedCitizenWithRequest(): array
    {
        $office = Office::query()->create([
            'name' => 'Mail Office',
            'municipality' => 'Beirut',
        ]);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $citizen = User::query()->create([
            'name' => 'Payer',
            'email' => 'payer-docs@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Fee Service',
            'price' => 25,
            'is_active' => true,
        ]);
        $request = ServiceRequest::query()->create([
            'reference_number' => (string) Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return [$office, $citizenRole, $citizen, $request];
    }
}
