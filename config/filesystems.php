<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Upload Disk
    |--------------------------------------------------------------------------
    |
    | This disk will be used by the FileUploadService for all file uploads.
    | For production, this should typically be set to 'do_spaces'.
    |
    */

    'default_upload_disk' => env('UPLOAD_DISK', 'do_spaces'),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */

    'max_upload_size' => env('UPLOAD_MAX_SIZE', 10485760), // 10MB

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

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | DigitalOcean Spaces
        |--------------------------------------------------------------------------
        |
        | DigitalOcean Spaces is an S3-compatible object storage service.
        | Configure the following environment variables:
        |
        | DO_SPACES_KEY=your-spaces-key
        | DO_SPACES_SECRET=your-spaces-secret
        | DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
        | DO_SPACES_REGION=nyc3
        | DO_SPACES_BUCKET=your-bucket-name
        | DO_SPACES_CDN_ENDPOINT=https://your-bucket.nyc3.cdn.digitaloceanspaces.com
        |
        */

        'do_spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION', 'nyc3'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT', 'https://nyc3.digitaloceanspaces.com'),
            'url' => env('DO_SPACES_CDN_ENDPOINT'), // Use CDN endpoint for public URLs
            'use_path_style_endpoint' => false,
            'visibility' => 'private',
            'throw' => true,
            'report' => true,
            'options' => [
                'CacheControl' => 'max-age=31536000', // 1 year cache for static assets
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
