<?php declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;

return [
    'default' => [
        'handler'   => [
            'class'       => Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH.'/runtime/logs/hyperf.log',
                'level'    => Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class'       => Formatter\LineFormatter::class,
            'constructor' => [
                'format'                => null,
                'dateFormat'            => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
