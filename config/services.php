<?php

declare(strict_types=1);

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

    'huggingface' => [
        'api_key' => env('HUGGINGFACE_API_KEY'),

        // Inference API модель
        'model' => env('HUGGINGFACE_MODEL', 'stabilityai/stable-diffusion-3-medium-diffusers'),

        // Параметры генерации
        'timeout' => env('HUGGINGFACE_TIMEOUT', 120),
        'image_width' => env('HUGGINGFACE_IMAGE_WIDTH', 512),
        'image_height' => env('HUGGINGFACE_IMAGE_HEIGHT', 512),
        'guidance_scale' => env('HUGGINGFACE_GUIDANCE_SCALE', 7.0),
        'inference_steps' => env('HUGGINGFACE_INFERENCE_STEPS', 28),
        'negative_prompt' => env('HUGGINGFACE_NEGATIVE_PROMPT', 'blurry, low quality, distorted, deformed, ugly, bad anatomy'),
    ],

];
