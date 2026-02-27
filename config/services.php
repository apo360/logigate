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

    'appypay' => [

        // OAuth (GPO)
        'oauth_url' => env('APPYPAY_OAUTH_URL'),
        'client_id' => env('APPYPAY_CLIENT_ID'),
        'client_secret' => env('APPYPAY_CLIENT_SECRET'),
        'resource' => env('APPYPAY_RESOURCE'),

        // Gateway
        'gwy_url' => env('APPYPAY_GWY_URL'),

        // Métodos de pagamento
        'methods' => [
            'gpo' => env('APPYPAY_GPO_METHOD'),
            'ref' => env('APPYPAY_REF_METHOD'),
        ],

        // Webhook
        'webhook_secret' => env('APPYPAY_WEBHOOK_SECRET'),
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

];
