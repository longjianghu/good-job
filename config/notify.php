<?php declare(strict_types=1);

return [
    'smtp' => [
        'host'     => env('SMTP_HOST'),
        'username' => env('SMTP_USERNAME'),
        'password' => env('SMTP_PASSWORD'),
        'fromName' => env('SMTP_FROM_NAME'),
    ],
    'user' => [
        'mobile' => env('NOTIFY_MOBILE'),
        'email'  => env('NOTIFY_EMAIL')
    ]
];
