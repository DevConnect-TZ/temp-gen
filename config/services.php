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

    'sonicpesa' => [
        'api_key' => env('SONICPESA_API_KEY'),
    ],

    'snippe' => [
        'api_key' => env('SNIPPE_API_KEY'),
        'base_url' => 'https://api.snippe.sh/v1',
        'webhook_url' => env('SNIPPE_WEBHOOK_URL', 'https://example.com/webhook'),
    ],

    'fastlipa' => [
        'api_token' => env('FASTLIPA_API_TOKEN'),
        'base_url' => 'https://api.fastlipa.com/api',
    ],

    'mobilipa' => [
        'api_key' => env('MOBILIPA_API_KEY', 'sk_live_YN0RUW4o2EQ3SxLNOiyyj25Ldfs37KyBHk1GSbda'),
        'base_url' => 'https://api.mobilipa.store',
    ],

    'pesalink' => [
        'api_token' => env('PESALINK_API_KEY'),
        'base_url' => 'https://pesalink.online/api',
    ],

];
