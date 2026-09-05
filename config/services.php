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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // দ্বিতীয় মতামতের পেমেন্ট গেটওয়ে — সবগুলো সবসময় sandbox মোডে থাকবে যতক্ষণ না
    // *_SANDBOX=false সেট করা হয়।
    'payments' => [
        'default' => env('PAYMENT_DEFAULT_GATEWAY', 'sslcommerz'),
    ],

    'bkash' => [
        'sandbox' => env('BKASH_SANDBOX', true),
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
    ],

    'nagad' => [
        'sandbox' => env('NAGAD_SANDBOX', true),
        'merchant_id' => env('NAGAD_MERCHANT_ID'),
        'merchant_private_key' => env('NAGAD_MERCHANT_PRIVATE_KEY'),
        'pg_public_key' => env('NAGAD_PG_PUBLIC_KEY'),
    ],

    'sslcommerz' => [
        'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    ],

];
