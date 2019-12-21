<?php declare(strict_types=1);

return [
    'retryNum' => env('RETRY_NUMBER', 3), // 重试次数
    'queue'    => [
        'worker' => 'worker', // 任务队列
        'retry'  => 'retry', // 重试队列
        'log'    => 'log', // 日志队列
        'task'   => 'task', // 任务详情
        'delay'  => 'delay', // 延迟队列
        'notify' => 'notify', // 预警提醒
    ], // 消息队列
    'smtp'     => [
        'host'     => env('SMTP_HOST'),
        'username' => env('SMTP_USERNAME'),
        'password' => env('SMTP_PASSWORD'),
        'fromName' => env('SMTP_FROM_NAME'),
    ], // SMTP 设置
    'notify'   => [
        'mobile' => env('NOTIFY_MOBILE'),
        'email'  => env('NOTIFY_EMAIL')
    ],// 提醒配置
];