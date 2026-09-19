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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'fedapay' => [
        'secret_key' => env('FEDAPAY_SECRET_KEY'),
        'environment' => env('FEDAPAY_ENVIRONMENT', 'sandbox'),
        'webhook_secret' => env('FEDAPAY_WEBHOOK_SECRET'),
    ],

    'azohub' => [
        'support_email' => env('SUPPORT_EMAIL', 'support@azohub.bj'),
        // Pas de valeur par défaut : tant que ce n'est pas réglé dans .env, les vues
        // masquent le numéro plutôt que d'afficher un faux "+229 XX XX XX XX".
        'support_phone' => env('SUPPORT_PHONE'),
        'support_whatsapp' => env('SUPPORT_WHATSAPP'),
    ],

];
