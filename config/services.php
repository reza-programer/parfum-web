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

    'rajaongkir' => [
        'key' => env('RAJAONGKIR_API_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        // ID kecamatan (district) gudang asal pengiriman.
        'origin' => (int) env('RAJAONGKIR_ORIGIN', 0),
        'couriers' => env('RAJAONGKIR_COURIERS', 'jne:jnt:sicepat:pos:tiki'),
        // Layanan yang disembunyikan di checkout (trucking, kirim motor, kargo, barang khusus).
        'exclude_services' => array_filter(array_map('trim', explode(',', (string) env(
            'RAJAONGKIR_EXCLUDE_SERVICES',
            'JTR,JTR<130,JTR>130,JTR>200,TRC,T15,T25,T60,GOKIL,POS KARGO,PAKETPOS DANGEROUS GOODS,PAKETPOS VALUABLE GOODS',
        )))),
        // Hanya berlaku di APP_ENV=local: pakai data contoh tanpa memanggil API asli.
        'fake' => (bool) env('RAJAONGKIR_FAKE', false),
    ],

    'komerce' => [
        'payment_key' => env('KOMERCE_PAYMENT_API_KEY'),
        // Kunci HMAC-SHA256 untuk memverifikasi header X-Callback-Api-Key.
        'callback_key' => env('KOMERCE_CALLBACK_KEY'),
        // false = sandbox (api-sandbox.collaborator.komerce.id). PaymentService memilih base URL dari sini.
        'production' => (bool) env('KOMERCE_IS_PRODUCTION', false),
        // Opsional: timpa base URL (dipakai test).
        'base_url' => env('KOMERCE_PAYMENT_BASE_URL'),
        // Opsional: URL callback publik (misalnya https://xxxx.ngrok-free.app/pembayaran/komerce/callback) saat uji di laptop.
        'callback_url' => env('KOMERCE_CALLBACK_URL'),
    ],

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

];
