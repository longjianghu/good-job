<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Contract\MiddlewareInterface;
use function context;

/**
 * Favicon.ico 处理
 *
 * @package App\Http\Middleware
 * @Bean()
 */
class FavIconMiddleware implements MiddlewareInterface
{
    /**
     * 请求处理.
     *
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface        $handler
     * @return ResponseInterface
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUriPath() === '/favicon.ico') {
            return context()->getResponse()->withStatus(404);
        }

        return $handler->handle($request);
    }
}
