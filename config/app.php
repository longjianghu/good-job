<?php declare(strict_types=1);

return [
    'retryNum' => env('RETRY_NUMBER', 3), // 重试次数
    'smtp'     => [
        'host'     => env('SMTP_HOST'),
        'username' => env('SMTP_USERNAME'),
        'password' => env('SMTP_PASSWORD'),
        'fromName' => env('SMTP_FROM_NAME'),
    ], // SMTP 设置
    'notify'   => [
        'mobile' => env('NOTIFY_MOBILE'),
        'email'  => env('NOTIFY_EMAIL')
    ], // 提醒配置
];