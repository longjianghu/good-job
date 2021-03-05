<?php declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrimMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * 初始化方法.
     *
     * @access public
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 处理方法.
     *
     * @access public
     * @param ServerRequestInterface  $request 数据请求
     * @param RequestHandlerInterface $handler 处理方法
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