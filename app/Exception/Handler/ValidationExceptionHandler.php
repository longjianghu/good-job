<?php declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $e, ResponseInterface $response)
    {
        $this->stopPropagation();

        /**
         * @var \Hyperf\Validation\ValidationException $e
         */
        $body = $e->validator->errors()->first();

        return withJson(['code' => $e->getCode(), 'data' => [], 'message' => $body]);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}