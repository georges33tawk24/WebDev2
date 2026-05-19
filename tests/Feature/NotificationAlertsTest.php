<?php

namespace Tests\Feature;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\PushSubscription;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_twilio_sms_sends_when_configured(): void
    {
        config([
            'services.sms.driver' => 'twilio',
            'services.twilio.sid' => 'ACtest',
            'services.twilio.token' => 'test-token',
            'services.twilio.from' => '+15005550006',
        ]);

        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201),
        ]);

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'SMS User',
            'email' => 'sms@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'phone' => '+96170123456',
        ]);

        $sent = app(SmsService::class)->send($user, 'Test SMS body');

        $this->assertTrue($sent);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.twilio.com')
            && $request['Body'] === 'Test SMS body');
    }

    public function test_textbee_sms_sends_when_configured(): void
    {
        config([
            'services.sms.driver' => 'textbee',
            'services.textbee.api_key' => 'tb_test_key',
            'services.textbee.device_id' => 'device-abc',
            'services.textbee.api_url' => 'https://api.textbee.dev',
        ]);

        Http::fake([
            'api.textbee.dev/*' => Http::response(['success' => true], 200),
        ]);

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Textbee User',
            'email' => 'textbee@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'phone' => '+96170123456',
        ]);

        $sent = app(SmsService::class)->send($user, 'Appointment reminder');

        $this->assertTrue($sent);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.textbee.dev/api/v1/gateway/devices/device-abc/send-sms')
                && $request->hasHeader('x-api-key', 'tb_test_key')
                && $request['recipients'] === ['+96170123456']
                && $request['message'] === 'Appointment reminder';
        });
    }

    public function test_brevo_sms_sends_when_configured(): void
    {
        config([
            'services.sms.driver' => 'brevo',
            'services.brevo.api_key' => 'brevo-test-key',
            'services.brevo.sms_sender' => 'EServices',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 1], 201),
        ]);

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Brevo User',
            'email' => 'brevo@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'phone' => '+96170999888',
        ]);

        $this->assertTrue(app(SmsService::class)->send($user, 'Hello'));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.brevo.com/v3/transactionalSMS/send')
            && $request->hasHeader('api-key', 'brevo-test-key')
            && $request['sender'] === 'EServices'
            && $request['recipient'] === '96170999888'
            && $request['content'] === 'Hello');
    }

    public function test_vonage_sms_sends_when_configured(): void
    {
        config([
            'services.sms.driver' => 'vonage',
            'services.vonage.api_key' => 'vonage-key',
            'services.vonage.api_secret' => 'vonage-secret',
            'services.vonage.from' => 'EServices',
        ]);

        Http::fake([
            'rest.nexmo.com/*' => Http::response([
                'messages' => [['status' => '0', 'message-id' => 'abc']],
            ], 200),
        ]);

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Vonage User',
            'email' => 'vonage@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'phone' => '+96170111222',
        ]);

        $this->assertTrue(app(SmsService::class)->send($user, 'Reminder'));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'rest.nexmo.com/sms/json')
            && $request['api_key'] === 'vonage-key'
            && $request['text'] === 'Reminder');
    }

    public function test_sms_auto_driver_prefers_brevo_over_twilio(): void
    {
        config([
            'services.sms.driver' => 'auto',
            'services.brevo.api_key' => 'brevo-key',
            'services.brevo.sms_sender' => 'EServices',
            'services.twilio.sid' => 'ACtest',
            'services.twilio.token' => 'token',
            'services.twilio.from' => '+15005550006',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 1], 201),
            'api.twilio.com/*' => Http::response([], 201),
        ]);

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Auto SMS',
            'email' => 'auto-sms@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'phone' => '+96170111222',
        ]);

        app(SmsService::class)->send($user, 'Hi');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'brevo.com'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'twilio.com'));
    }

    public function test_push_subscription_api_stores_subscription(): void
    {
        Storage::fake('public');

        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Push User',
            'email' => 'push@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
        ]);
        Storage::disk('public')->put('ids/test.png', 'x');

        $this->actingAs($user)
            ->postJson(route('api.push.subscribe'), [
                'endpoint' => 'https://push.example/sub/1',
                'keys' => [
                    'p256dh' => 'key',
                    'auth' => 'token',
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://push.example/sub/1',
        ]);
    }

    public function test_notify_creates_db_notification(): void
    {
        $role = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Notify User',
            'email' => 'notify@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        app(NotificationService::class)->notify(
            $user,
            'ui.notifications.payment_confirmed',
            [],
            'ui.notifications.payment_confirmed_body',
            ['ref' => 'REF-1', 'amount' => '10.00 USD'],
            ['type' => 'payment'],
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => __('ui.notifications.payment_confirmed'),
        ]);
    }

    public function test_appointment_reminder_command_sends_email_and_marks_sent(): void
    {
        Mail::fake();

        $office = Office::query()->create(['name' => 'Beirut Office', 'municipality' => 'Beirut']);
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $citizen = User::query()->create([
            'name' => 'Citizen',
            'email' => 'citizen-reminder@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $citizenRole->id,
        ]);

        $appointment = Appointment::query()->create([
            'office_id' => $office->id,
            'citizen_id' => $citizen->id,
            'starts_at' => now()->addHours(24),
            'ends_at' => now()->addHours(25),
            'status' => 'scheduled',
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSent(AppointmentReminderMail::class, function (AppointmentReminderMail $mail) use ($citizen) {
            return $mail->hasTo($citizen->email) && $mail->hoursBefore === 24;
        });

        $this->assertNotNull($appointment->fresh()->reminder_24h_sent_at);
    }
}
