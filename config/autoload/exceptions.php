<?php declare(strict_types=1);

return [
    'handler' => [
        'http' => [
            App\Exception\Handler\HttpExceptionHandler::class,
            Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
            App\Exception\Handler\ValidationExceptionHandler::class
        ],
    ],
];
