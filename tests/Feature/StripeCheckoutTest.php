<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureCitizenIdDocument;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureCitizenIdDocument::class);
    }

    public function test_checkout_redirects_to_stripe_when_configured(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $this->mock(StripePaymentService::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->andReturn([
                    'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
                    'session_id' => 'cs_test_123',
                ]);
        });

        $this->actingAs($citizen)
            ->get(route('citizen.payments.checkout', $request))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        $this->assertDatabaseHas('payments', [
            'service_request_id' => $request->id,
            'method' => 'card',
            'status' => 'pending',
            'gateway_reference' => 'cs_test_123',
        ]);
    }

    public function test_checkout_errors_when_stripe_not_configured(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        config(['services.stripe.secret' => null]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.checkout', $request))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_mark_paid_sends_notification_once(): void
    {
        [, , $citizen, $request] = $this->seedCitizenWithRequest();

        $payment = Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $service = app(StripePaymentService::class);
        $service->markPaid($payment, 'pi_test_123');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'gateway_reference' => 'pi_test_123',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $citizen->id,
            'title' => __('ui.notifications.payment_confirmed'),
        ]);
    }

    /**
     * @return array{0: Office, 1: Role, 2: User, 3: ServiceRequest}
     */
    private function seedCitizenWithRequest(): array
    {
        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake-id');

        $office = Office::query()->create([
            'name' => 'Pay Office',
            'municipality' => 'Beirut',
        ]);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $citizen = User::query()->create([
            'name' => 'Payer',
            'email' => 'stripe-payer@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
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
