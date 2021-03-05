<?php declare(strict_types=1);

return [
    'workerNum'   => env('WORKER_NUM', 1),
    'retryTotal'  => env('RETRY_TOTAL', 3),
    'taskQueue'   => env('TASK_QUEUE', 'task'),
    'workerQueue' => env('WORKER_QUEUE', 'worker'),
    'delayQueue'  => env('DELAY_QUEUE', 'delay'),
    'retryQueue'  => env('RETRY_QUEUE', 'retry'),
];