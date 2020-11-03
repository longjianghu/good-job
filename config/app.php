<?php declare(strict_types=1);

return [
    'retryTotal' => env('RETRY_TOTAL', 3), // 重试次数
    'queue'      => [
        'worker' => 'worker', // 任务队列
        'retry'  => 'retry', // 重试队列
        'log'    => 'log', // 日志队列
        'task'   => 'task', // 任务详情
        'delay'  => 'delay', // 延迟队列
    ], // 消息队列
];