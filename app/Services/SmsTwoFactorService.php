<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class SmsTwoFactorService
{
    public function send(string $toPhone, string $code): bool
    {
        $sid = (string) config('services.twilio.sid', '');
        $token = (string) config('services.twilio.token', '');
        $from = (string) config('services.twilio.from', '');

        if ($sid === '' || $token === '' || $from === '') {
            return false;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post($url, [
                    'From' => $from,
                    'To' => $toPhone,
                    'Body' => "Your verification code is: {$code}",
                ]);

            return $response->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
