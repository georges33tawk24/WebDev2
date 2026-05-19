<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /** @var list<string> */
    private const CLOUD_DRIVERS = ['brevo', 'vonage', 'twilio', 'textbee'];

    public function driver(): string
    {
        $configured = (string) config('services.sms.driver', '');

        if ($configured !== '' && $configured !== 'auto') {
            return $configured;
        }

        foreach (['brevo', 'vonage', 'twilio', 'textbee'] as $candidate) {
            if ($this->isDriverConfigured($candidate)) {
                return $candidate;
            }
        }

        return 'log';
    }

    public function isConfigured(): bool
    {
        return in_array($this->driver(), self::CLOUD_DRIVERS, true);
    }

    public function send(User $user, string $message): bool
    {
        $phone = $this->normalizePhone($user->phone);

        if ($phone === null) {
            return false;
        }

        return match ($this->driver()) {
            'brevo' => $this->sendViaBrevo($phone, $message),
            'vonage' => $this->sendViaVonage($phone, $message),
            'twilio' => $this->sendViaTwilio($phone, $message),
            'textbee' => $this->sendViaTextbee($phone, $message),
            default => $this->logOnly($phone, $message),
        };
    }

    private function isDriverConfigured(string $driver): bool
    {
        return match ($driver) {
            'brevo' => $this->brevoConfigured(),
            'vonage' => $this->vonageConfigured(),
            'twilio' => $this->twilioConfigured(),
            'textbee' => $this->textbeeConfigured(),
            default => false,
        };
    }

    private function brevoConfigured(): bool
    {
        return filled(config('services.brevo.api_key'))
            && filled(config('services.brevo.sms_sender'));
    }

    private function vonageConfigured(): bool
    {
        return filled(config('services.vonage.api_key'))
            && filled(config('services.vonage.api_secret'))
            && filled(config('services.vonage.from'));
    }

    private function twilioConfigured(): bool
    {
        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.from'));
    }

    private function textbeeConfigured(): bool
    {
        return filled(config('services.textbee.api_key'))
            && filled(config('services.textbee.device_id'));
    }

    private function sendViaBrevo(string $phone, string $message): bool
    {
        if (! $this->brevoConfigured()) {
            return $this->logOnly($phone, $message, 'Brevo not configured');
        }

        $response = Http::withHeaders([
            'api-key' => (string) config('services.brevo.api_key'),
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/transactionalSMS/send', [
            'sender' => (string) config('services.brevo.sms_sender'),
            'recipient' => ltrim($phone, '+'),
            'content' => $message,
            'type' => 'transactional',
        ]);

        if (! $response->successful()) {
            Log::warning('Brevo SMS failed', [
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function sendViaVonage(string $phone, string $message): bool
    {
        if (! $this->vonageConfigured()) {
            return $this->logOnly($phone, $message, 'Vonage not configured');
        }

        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => (string) config('services.vonage.api_key'),
            'api_secret' => (string) config('services.vonage.api_secret'),
            'from' => (string) config('services.vonage.from'),
            'to' => ltrim($phone, '+'),
            'text' => $message,
        ]);

        if (! $response->successful()) {
            Log::warning('Vonage SMS failed', [
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $messages = $response->json('messages', []);

        if (is_array($messages) && isset($messages[0]['status']) && $messages[0]['status'] !== '0') {
            Log::warning('Vonage SMS rejected', [
                'to' => $phone,
                'error' => $messages[0]['error-text'] ?? $messages[0]['status'],
            ]);

            return false;
        }

        return true;
    }

    private function sendViaTwilio(string $phone, string $message): bool
    {
        if (! $this->twilioConfigured()) {
            return $this->logOnly($phone, $message, 'Twilio not configured');
        }

        $sid = (string) config('services.twilio.sid');
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::withBasicAuth($sid, (string) config('services.twilio.token'))
            ->asForm()
            ->post($url, [
                'To' => $phone,
                'From' => (string) config('services.twilio.from'),
                'Body' => $message,
            ]);

        if (! $response->successful()) {
            Log::warning('Twilio SMS failed', [
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function sendViaTextbee(string $phone, string $message): bool
    {
        if (! $this->textbeeConfigured()) {
            return $this->logOnly($phone, $message, 'Textbee not configured');
        }

        $deviceId = (string) config('services.textbee.device_id');
        $url = rtrim((string) config('services.textbee.api_url'), '/')
            ."/api/v1/gateway/devices/{$deviceId}/send-sms";

        $response = Http::withHeaders([
            'x-api-key' => (string) config('services.textbee.api_key'),
            'Accept' => 'application/json',
        ])->post($url, [
            'recipients' => [$phone],
            'message' => $message,
        ]);

        if (! $response->successful()) {
            Log::warning('Textbee SMS failed', [
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function logOnly(string $phone, string $message, ?string $reason = null): bool
    {
        Log::channel('single')->info('SMS (not sent — provider not configured)', [
            'driver' => $this->driver(),
            'reason' => $reason,
            'to' => $phone,
            'message' => $message,
        ]);

        return false;
    }

    private function normalizePhone(mixed $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', (string) $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (! str_starts_with($digits, '+')) {
            $digits = '+961'.ltrim($digits, '0');
        }

        return $digits;
    }
}
