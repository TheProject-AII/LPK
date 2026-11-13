<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'contract_attachment' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/contract_attachment'),
            'url' => env('APP_URL').'/storage/uploads/contract_attachment',
            'visibility' => 'public',
        ],

        'student_attachment' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/student_attachment'),
            'url' => env('APP_URL').'/storage/uploads/student_attachment',
            'visibility' => 'public',
        ],

        'student_attachment_photo' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/student_attachment/photo'),
            'url' => env('APP_URL').'/storage/uploads/student_attachment/photo',
            'visibility' => 'public',
        ],

        'student_required_docs' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/student_attachment/required_docs'),
            'url' => env('APP_URL').'/storage/uploads/student_attachment/required_docs',
            'visibility' => 'public',
        ],

        'student_attachment_identity' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/student_attachment/identity'),
            'url' => env('APP_URL').'/storage/uploads/student_attachment/identity',
            'visibility' => 'public',
        ],

        'student_attachment_license' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/student_attachment/license'),
            'url' => env('APP_URL').'/storage/uploads/student_attachment/license',
            'visibility' => 'public',
        ],

        'vehicle_attachment_document' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/vehicle_attachment/document'),
            'url' => env('APP_URL').'/storage/uploads/vehicle_attachment/document',
            'visibility' => 'public',
        ],

        'notification_attachment' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/notification_attachment'),
            'url' => env('APP_URL').'/storage/uploads/notification_attachment',
            'visibility' => 'public',
        ],

        'notification_image' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads/notification_image'),
            'url' => env('APP_URL').'/storage/uploads/notification_image',
            'visibility' => 'public',
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
