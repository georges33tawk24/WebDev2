<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'scope' => env('FACEBOOK_LOGIN_SCOPE', 'public_profile'),
    ],

    'id_ocr' => [
        'url' => env('ID_OCR_API_URL'),
        'token' => env('ID_OCR_API_TOKEN'),
        'language' => env('ID_OCR_LANGUAGE', 'eng'),
    ],

    'oauth' => [
        'verify_ssl' => env('OAUTH_VERIFY_SSL', true),
    ],

    'exchange' => [
        'usd_lbp_fallback' => env('EXCHANGE_USD_LBP_RATE', 89500),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'nowpayments' => [
        'api_key' => env('NOWPAYMENTS_API_KEY'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
        'public_key' => env('NOWPAYMENTS_PUBLIC_KEY'),
        'sandbox' => env('NOWPAYMENTS_SANDBOX', true),
        'pay_currency' => env('NOWPAYMENTS_PAY_CURRENCY', 'usdttrc20'),
    ],

    'sms' => [
        // auto | brevo | vonage | twilio | textbee | log
        // auto: first configured cloud provider (brevo → vonage → twilio → textbee), else log only
        'driver' => env('SMS_DRIVER', 'auto'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'sms_sender' => env('BREVO_SMS_SENDER'),
    ],

    'vonage' => [
        'api_key' => env('VONAGE_API_KEY'),
        'api_secret' => env('VONAGE_API_SECRET'),
        'from' => env('VONAGE_SMS_FROM'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],

    'textbee' => [
        'api_url' => env('TEXTBEE_API_URL', 'https://api.textbee.dev'),
        'api_key' => env('TEXTBEE_API_KEY'),
        'device_id' => env('TEXTBEE_DEVICE_ID'),
    ],

    'webpush' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    'appointments' => [
        'reminder_windows' => [
            ['hours' => 24, 'column' => 'reminder_24h_sent_at'],
            ['hours' => 1, 'column' => 'reminder_1h_sent_at'],
        ],
        'reminder_tolerance_minutes' => (int) env('APPOINTMENT_REMINDER_TOLERANCE_MINUTES', 10),
    ],

    /*
    | SSE (/api/live/stream) holds a PHP worker open ~30s per browser tab.
    | php artisan serve handles one request at a time — SSE makes the site feel frozen.
    | Default: off in local, on in production (use php-fpm / Octane / Sail there).
    */
    'live_updates' => [
        'sse_enabled' => filter_var(
            env('LIVE_UPDATES_SSE', env('APP_ENV', 'local') === 'production'),
            FILTER_VALIDATE_BOOL,
        ),
        'poll_seconds' => (int) env('LIVE_UPDATES_POLL_SECONDS', 5),
    ],

];
