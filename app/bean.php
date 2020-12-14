<?php declare(strict_types=1);

use App\Process\MonitorProcess;

use Swoft\Db\Pool;
use Swoft\Db\Database;
use Swoft\Redis\RedisDb;
use Swoft\Server\SwooleEvent;
use Swoft\Http\Server\HttpServer;
use Swoft\Log\Handler\FileHandler;
use Swoft\Task\Swoole\TaskListener;
use Swoft\Task\Swoole\FinishListener;
use Swoft\Crontab\Process\CrontabProcess;

return [
    'httpServer'         => [
        'class'    => HttpServer::class,
        'port'     => 18306,
        'listener' => [],
        'process'  => [
            'crontab' => \bean(CrontabProcess::class),
            'monitor' => \bean(MonitorProcess::class)
        ],
        'on'       => [
            SwooleEvent::TASK   => \bean(TaskListener::class),
            SwooleEvent::FINISH => \bean(FinishListener::class)
        ],
        'setting'  => [
            'worker_num'            => env('WORKER_NUM', 3),
            'dispatch_mode'         => 3,
            'task_enable_coroutine' => true
        ]
    ],
    'i18n'               => [
        'resoucePath'     => '@resource/language/',
        'defaultLanguage' => 'zh',
        'defualtCategory' => 'default',
    ],
    'httpDispatcher'     => [
        'middlewares'      => [
            App\Http\Middleware\FavIconMiddleware::class,
            App\Http\Middleware\TrimMiddleware::class,
        ],
        'afterMiddlewares' => [
            Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],
    'migrationManager'   => ['migrationPath' => '@app/Migration'],
    'lineFormatter'      => [
        'format'     => '%datetime% [%level_name%] [%channel%] [%event%] [tid:%tid%] [cid:%cid%] %messages%',
        'dateFormat' => 'Y-m-d H:i:s',
    ],
    'noticeHandler'      => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/sys-%d{Y-m-d}.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'notice,error,warning,trace',
    ],
    'applicationHandler' => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/app-%d{Y-m-d}.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'info,debug',
    ],
    'logger'             => [
        'flushRequest' => false,
        'enable'       => true,
        'handlers'     => [
            'application' => \bean('applicationHandler'),
            'notice'      => \bean('noticeHandler'),
        ],
    ],
    'redis'              => [
        'class'         => RedisDb::class,
        'host'          => env('REDIS_HOST'),
        'port'          => env('REDIS_PORT'),
        'database'      => env('REDIS_DB'),
        'retryInterval' => 10,
        'readTimeout'   => 0,
        'timeout'       => 2,
        'password'      => env('REDIS_PASSWORD'),
        'option'        => [
            'prefix'     => 'job_',
            'serializer' => Redis::SERIALIZER_PHP
        ],
    ],
    'redisPool'          => [
        'class'       => \Swoft\Redis\Pool::class,
        'redisDb'     => \bean('redis'),
        'minActive'   => 1,
        'maxActive'   => 20,
        'maxWait'     => 3,
        'maxWaitTime' => 3,
        'maxIdleTime' => 60,
    ],
    'dbJob'              => [
        'class'   => Database::class,
        'charset' => config('db.charset'),
        'options' => config('db.options'),
        'config'  => config('db.config'),
        'writes'  => [
            [
                'dsn'      => env('JOB_MASTER_DSN'),
                'username' => env('JOB_USERNAME'),
                'password' => env('JOB_PASSWORD'),
            ]
        ],
        'reads'   => [
            [
                'dsn'      => env('JOB_SLAVE_DSN'),
                'username' => env('JOB_USERNAME'),
                'password' => env('JOB_PASSWORD'),
            ]
        ]
    ],
    'dbJobPool'          => [
        'class'       => Pool::class,
        'database'    => \bean('dbJob'),
        'minActive'   => 5,
        'maxActive'   => 10,
        'maxWait'     => 3,
        'maxWaitTime' => 3,
        'maxIdleTime' => 60,
    ],
];
