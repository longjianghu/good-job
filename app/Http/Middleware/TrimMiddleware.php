<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Swoft\Http\Message\Request;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Server\Contract\MiddlewareInterface;

/**
 * TrimMiddleware
 *
 * @Bean()
 */
class TrimMiddleware implements MiddlewareInterface
{
    /**
     * 自定义处理方法.
     *
     * @access public
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface        $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body  = $request->getParsedBody();
        $query = $request->getQueryParams();

        $body  = ( ! empty($body)) ? $this->_trim($body) : [];
        $query = ( ! empty($query)) ? $this->_trim($query) : [];

        $request = $request->withParsedBody($body)->withQueryParams($query);

        return $handler->handle($request);
    }

    /**
     * 过滤空格
     *
     * @access privatee
     * @param mixed $input 过滤内容
     * @return array|string
     */
    private function _trim($input)
    {
        if ( ! is_array($input)) {
            return trim($input);
        }

        return array_map([$this, '_trim'], $input);
    }
}
