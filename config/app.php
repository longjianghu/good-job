<?php declare(strict_types=1);

return [
    'minWorkerNum' => swoole_cpu_num(), // 最小 Worker 数量
    'maxWorkerNum' => swoole_cpu_num() * 4, // 最大 Worker 数量
    'retryTotal'   => env('RETRY_TOTAL', 3), // 重试次数
    'queue'        => [
        'worker' => 'worker', // 任务队列
        'retry'  => 'retry', // 重试队列
        'task'   => 'task', // 任务详情
        'delay'  => 'delay', // 延迟队列
    ], // 消息队列
];