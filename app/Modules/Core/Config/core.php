<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'default_disk' => env('UPLOAD_DISK', 'do_spaces'),

        'max_size' => env('UPLOAD_MAX_SIZE', 10485760), // 10MB

        'allowed_mime_types' => [
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',

            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            // Archives
            'application/zip',
            'application/x-rar-compressed',

            // Text
            'text/plain',
            'text/csv',
            'application/json',
        ],

        'image_max_size' => env('UPLOAD_IMAGE_MAX_SIZE', 5242880), // 5MB
        'document_max_size' => env('UPLOAD_DOCUMENT_MAX_SIZE', 20971520), // 20MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Configuration
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'cipher' => 'aes-256-gcm',
        'hash_algo' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'password' => [
            'min_length' => 12,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => true,
            'special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
        ],

        'two_factor' => [
            'enabled' => env('TWO_FACTOR_ENABLED', true),
            'issuer' => env('TWO_FACTOR_ISSUER', config('app.name')),
            'digits' => 6,
            'window' => 1,
            'algorithm' => 'sha1',
        ],

        'session' => [
            'single_device' => env('SINGLE_DEVICE_SESSION', false),
            'timeout_minutes' => env('SESSION_TIMEOUT', 120),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Defaults
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],
];
