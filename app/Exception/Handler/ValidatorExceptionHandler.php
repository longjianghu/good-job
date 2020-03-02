<?php declare(strict_types=1);

namespace App\Exception\Handler;

use Swoft\Validator\Exception\ValidatorException;
use Swoft\Error\Annotation\Mapping\ExceptionHandler;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Exception\Handler\AbstractHttpErrorHandler;
use Swoft\Log\Helper\CLog;
use Throwable;

/**
 * ValidatorExceptionHandler
 *
 * @ExceptionHandler(ValidatorException::class)
 */
class ValidatorExceptionHandler extends AbstractHttpErrorHandler
{
    /**
     * @param Throwable $e
     * @param Response  $response
     * @return Response
     */
    public function handle(Throwable $e, Response $response): Response
    {
        $status = [
            'code'    => $e->getCode(),
            'data'    => [],
            'message' => \Swoft::t(sprintf('validator.%s', $e->getMessage()), [])
        ];

        if ( ! empty(APP_DEBUG)) {
            $status['data'] = [
                'error' => sprintf('(%s) %s', get_class($e), $e->getMessage()),
                'file'  => sprintf('At %s line %d', $e->getFile(), $e->getLine()),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return $response->withData($status);
    }
}