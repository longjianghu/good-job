<?php declare(strict_types=1);

namespace App\Exception\Handler;

use Swoft\Error\Annotation\Mapping\ExceptionHandler;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Exception\Handler\AbstractHttpErrorHandler;
use Swoft\Log\Helper\CLog;
use Throwable;

/**
 * HttpExceptionHandler
 *
 * @ExceptionHandler(\Throwable::class)
 */
class HttpExceptionHandler extends AbstractHttpErrorHandler
{
    /**
     * @param Throwable $e
     * @param Response  $response
     * @return Response
     */
    public function handle(Throwable $e, Response $response): Response
    {
        CLog::error($e->getMessage());

        if ( ! APP_DEBUG) {
            return $response->withStatus(500)->withContent(sprintf(' %s At %s line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
        }

        $data = [
            'code'  => $e->getCode(),
            'error' => sprintf('(%s) %s', get_class($e), $e->getMessage()),
            'file'  => sprintf('At %s line %d', $e->getFile(), $e->getLine()),
            'trace' => $e->getTraceAsString(),
        ];

        return $response->withData($data);
    }
}