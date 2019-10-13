<?php declare(strict_types=1);

return [
    'charset' => env('DB_CHARSET'),
    'config'  => [
        'collation' => env('DB_COLLATION'),
        'strict'    => false,
        'timezone'  => '+8:00',
        'modes'     => 'NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES',
        'fetchMode' => PDO::FETCH_ASSOC,
    ],
    'options' => []
];
