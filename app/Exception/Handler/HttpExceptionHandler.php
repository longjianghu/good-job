<?php declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger    = $logger;
        $this->formatter = $formatter;
    }

    /**
     * Handle the exception, and return the specified result.
     *
     * @param HttpException $throwable
     */
    public function handle(Throwable $e, ResponseInterface $response)
    {
        $this->logger->debug($this->formatter->format($e));

        $this->stopPropagation();

        return withJson(['code' => $e->getCode(), 'data' => [], 'message' => $e->getMessage()]);
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
