<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureCitizenIdDocument;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\NowPaymentsService;
use App\Services\PaymentCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NowPaymentsCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureCitizenIdDocument::class);

        config([
            'services.nowpayments.api_key' => 'test-nowpayments-key',
            'services.nowpayments.ipn_secret' => 'test-ipn-secret',
            'services.nowpayments.sandbox' => true,
            'services.nowpayments.pay_currency' => 'usdttrc20',
        ]);
    }

    public function test_crypto_checkout_creates_payment_and_redirects_to_hosted_invoice(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $invoiceUrl = 'https://sandbox.nowpayments.io/payment/?iid=inv_test_999';

        Http::fake([
            'api-sandbox.nowpayments.io/v1/min-amount*' => Http::response([
                'currency_from' => 'usd',
                'currency_to' => 'usdttrc20',
                'min_amount' => 10,
            ], 200),
            'api-sandbox.nowpayments.io/v1/invoice' => Http::response([
                'id' => 'inv_test_999',
                'invoice_url' => $invoiceUrl,
                'payment_status' => 'waiting',
                'pay_currency' => 'usdttrc20',
            ], 200),
            'api-sandbox.nowpayments.io/v1/invoice-payment' => Http::response([
                'payment_id' => 'np_pay_999',
                'payment_status' => 'waiting',
                'pay_address' => 'TTestWalletAddress123',
                'pay_amount' => 25.5,
                'pay_currency' => 'usdttrc20',
            ], 201),
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.crypto.checkout', $request))
            ->assertRedirect($invoiceUrl);

        $payment = Payment::query()->where('service_request_id', $request->id)->first();

        $this->assertNotNull($payment);
        $this->assertSame('crypto', $payment->method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('np_pay_999', $payment->gateway_reference);
        $this->assertSame('inv_test_999', $payment->gateway_meta['invoice_id'] ?? null);
        $this->assertSame($invoiceUrl, $payment->gateway_meta['invoice_url'] ?? null);
    }

    public function test_crypto_status_endpoint_marks_paid_when_gateway_finished(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => 'inv_poll',
            'gateway_meta' => ['invoice_id' => 'inv_poll', 'payment_id' => 'np_pay_poll'],
        ]);

        Http::fake([
            'api-sandbox.nowpayments.io/v1/payment/np_pay_poll' => Http::response([
                'payment_id' => 'np_pay_poll',
                'payment_status' => 'finished',
            ], 200),
        ]);

        $this->actingAs($citizen)
            ->getJson(route('citizen.payments.crypto.status', [$request, $payment]))
            ->assertOk()
            ->assertJson([
                'state' => 'paid',
            ])
            ->assertJsonPath('redirect_url', route('citizen.payments'));

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_crypto_success_shows_poll_page_when_still_pending(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => '5509082628',
            'gateway_meta' => [
                'invoice_url' => 'https://sandbox.nowpayments.io/payment/?iid=inv_wait',
                'invoice_id' => 'inv_wait',
            ],
        ]);

        Http::fake([
            'api-sandbox.nowpayments.io/v1/invoice-payment' => Http::response([
                'payment_id' => 'np_pay_wait',
                'payment_status' => 'waiting',
            ], 201),
            'api-sandbox.nowpayments.io/v1/payment/np_pay_wait' => Http::response([
                'payment_id' => 'np_pay_wait',
                'payment_status' => 'waiting',
            ], 200),
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.crypto.success', $request))
            ->assertOk()
            ->assertSee('crypto-payment-poll-root', false)
            ->assertSee('"returnedFromGateway":true', false);
    }

    public function test_crypto_pay_page_offers_browser_checkout_link(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $invoiceUrl = 'https://sandbox.nowpayments.io/payment/?iid=inv_resume';

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => 'inv_resume',
            'gateway_meta' => [
                'invoice_url' => $invoiceUrl,
                'invoice_id' => 'inv_resume',
            ],
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.crypto.pay', [$request, $payment]))
            ->assertOk()
            ->assertSee($invoiceUrl, false)
            ->assertSee(__('ui.citizen.crypto_open_checkout'), false);
    }

    public function test_crypto_success_marks_paid_when_gateway_reports_finished(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => 'np_pay_999',
            'gateway_meta' => [
                'invoice_url' => 'https://sandbox.nowpayments.io/payment/?iid=inv_test_999',
                'invoice_id' => 'inv_test_999',
                'payment_id' => 'np_pay_999',
            ],
        ]);

        Http::fake([
            'api-sandbox.nowpayments.io/v1/payment/np_pay_999' => Http::response([
                'payment_id' => 'np_pay_999',
                'payment_status' => 'finished',
                'order_id' => (string) $payment->id,
            ], 200),
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.crypto.success', $request))
            ->assertRedirect(route('citizen.payments'))
            ->assertSessionHas('success');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $request->id,
            'to_status' => 'paid',
        ]);
    }

    public function test_ipn_webhook_marks_payment_paid_with_valid_signature(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => 'np_test_888',
        ]);

        $payload = [
            'payment_id' => 'np_test_888',
            'payment_status' => 'finished',
            'order_id' => (string) $payment->id,
        ];

        $sorted = app(NowPaymentsService::class);
        $signature = hash_hmac(
            'sha512',
            (string) json_encode($this->invokeSort($payload), JSON_UNESCAPED_SLASHES),
            'test-ipn-secret',
        );

        $this->postJson(route('webhooks.nowpayments'), $payload, [
            'x-nowpayments-sig' => $signature,
        ])->assertNoContent();

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_checkout_rejects_amount_below_nowpayments_minimum(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $request->service->update(['price' => 15]);
        $request->load('service');

        Http::fake([
            'api-sandbox.nowpayments.io/v1/min-amount*' => Http::response([
                'currency_from' => 'usd',
                'currency_to' => 'usdttrc20',
                'min_amount' => 19.22,
            ], 200),
        ]);

        $this->actingAs($citizen)
            ->from(route('citizen.payments.show', $request))
            ->get(route('citizen.payments.crypto.checkout', $request))
            ->assertRedirect(route('citizen.payments.show', $request))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseMissing('payments', [
            'service_request_id' => $request->id,
            'method' => 'crypto',
            'status' => 'pending',
        ]);
    }

    public function test_checkout_errors_when_nowpayments_not_configured(): void
    {
        config(['services.nowpayments.api_key' => null]);

        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $this->actingAs($citizen)
            ->from(route('citizen.payments.show', $request))
            ->get(route('citizen.payments.crypto.checkout', $request))
            ->assertRedirect(route('citizen.payments.show', $request))
            ->assertSessionHasErrors('payment');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function invokeSort(array $payload): array
    {
        $method = new \ReflectionMethod(NowPaymentsService::class, 'sortKeysRecursively');
        $method->setAccessible(true);

        return $method->invoke(app(NowPaymentsService::class), $payload);
    }

    /**
     * @return array{0: Office, 1: Role, 2: User, 3: ServiceRequest}
     */
    private function seedCitizenWithRequest(): array
    {
        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake-id');

        $office = Office::query()->create([
            'name' => 'Crypto Office',
            'municipality' => 'Beirut',
        ]);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $citizen = User::query()->create([
            'name' => 'Crypto Payer',
            'email' => 'crypto-payer@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Crypto Service',
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
