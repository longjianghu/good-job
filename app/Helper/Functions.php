<?php declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

use Hyperf\Utils\Arr;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Contract\IdGeneratorInterface;

/**
 * 控制台日志
 *
 * @access public
 * @return StdoutLoggerInterface|mixed
 */
if ( ! function_exists('console')) {
    function console()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

/**
 * 获取容器
 *
 * @access public
 * @return \Psr\Container\ContainerInterface
 */
if ( ! function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}

/**
 * 校验手机号码
 *
 * @access public
 * @param string $mobile 手机号码
 * @return bool
 */
if ( ! function_exists('isMobile')) {
    function isMobile(string $mobile)
    {
        return (preg_match('/^(0|86|17951)?1[3456789](\d){9}$/', $mobile)) ? true : false;
    }
}

/**
 * 日志
 *
 * @access public
 * @return LoggerFactory|mixed
 */
if ( ! function_exists('logger')) {
    function logger(string $name = '', string $group = 'default')
    {
        return container()->get(LoggerFactory::class)->get($name, $group);
    }
}

/**
 * 获取 Redis 客户端
 *
 * @access public
 * @param string $poolName 连接池名称
 * @return \Hyperf\Redis\RedisProxy
 */
if ( ! function_exists('redis')) {
    function redis(string $poolName = 'default')
    {
        return container()->get(RedisFactory::class)->get($poolName);
    }
}

/**
 * 数据请示
 *
 * @access public
 * @retrun mixed
 */
if ( ! function_exists('request')) {
    function request()
    {
        return container()->get(RequestInterface::class);
    }
}

/**
 * 响应方法
 *
 * @access public
 * @retrun mixed
 */
if ( ! function_exists('response')) {
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}

/**
 * 发送请求(表单提交)
 *
 * @access public
 * @param string $url     URL
 * @param array  $args    提交参数
 * @param array  $headers HEAD信息
 * @param string $method  请求方法
 * @return array
 */
if ( ! function_exists('sendRequest')) {
    function sendRequest(string $url, array $args = [], array $headers = [], $method = 'GET')
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $options = [];
            $method  = ( ! empty($method)) ? $method : null;

            if ( ! empty($args)) {
                $options = (strtoupper($method) == 'POST') ? ['form_params' => $args] : ['query' => $args];
            }

            if ( ! empty($headers)) {
                $options['headers'] = $headers;
            }

            $response = (new Client(['verify' => false]))->request($method, $url, $options);

            $status = [
                'code'    => 200,
                'data'    => $response->getBody()->getContents(),
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 并发请求
 *
 * $args格式：[['url'=>'','method'=>'get/post','query'=>[],'header'=>[]],url]
 *
 * @access public
 * @param array $args 提交参数
 * @return array
 */
if ( ! function_exists('sendMultiRequest')) {
    function sendMultiRequest(array $args)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $data = $promises = [];

            $client = new Client(['verify' => false]);

            foreach ($args as $k => $v) {
                $url = Arr::get($v, 'url');
                $url = ( ! empty($url)) ? $url : $v;

                $method = Arr::get($v, 'method', 'GET');
                $method = strtoupper($method);

                $query  = Arr::get($v, 'query');
                $header = Arr::get($v, 'header');

                $options = [];

                if ( ! empty($query)) {
                    $field = ($method == 'POST') ? 'form_params' : 'query';

                    $options[$field] = $query;
                }

                if ( ! empty($header)) {
                    $options['headers'] = $header;
                }

                $promises[] = ($method == 'POST') ? $client->postAsync($url, $options) : $client->getAsync($url, $options);
            }

            $result = Promise\unwrap($promises);

            foreach ($result as $k => $v) {
                $data[$k] = ($v->getStatusCode() == 200) ? $v->getBody()->getContents() : $v->getReasonPhrase();
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 雪花算法
 *
 * @access public
 * @return IdGeneratorInterface|mixed
 */
if (function_exists('snowflake')) {
    function snowflake()
    {
        return container()->get(IdGeneratorInterface::class);
    }
}

/**
 * 单站点并发请求
 *
 * $args格式：[['uri'=>'','method'=>'get/post','query'=>[],'header'=>[]],uri]
 *
 * @access public
 * @param string $baseUrl 基础URL
 * @param array  $args    提交参数
 * @return array
 */
if ( ! function_exists('singleMultiRequest')) {
    function singleMultiRequest(string $baseUrl, array $args)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $data   = $promises = [];
            $client = new Client(['base_uri' => $baseUrl, 'verify' => false]);

            foreach ($args as $k => $v) {
                $uri = Arr::get($v, 'uri');
                $uri = ( ! empty($uri)) ? $uri : $v;

                $method = Arr::get($v, 'method', 'GET');
                $method = strtoupper($method);

                $query  = Arr::get($v, 'query');
                $header = Arr::get($v, 'header');

                $options = [];

                if ( ! empty($query)) {
                    $field = ($method == 'POST') ? 'form_params' : 'query';

                    $options[$field] = $query;
                }

                if ( ! empty($header)) {
                    $options['headers'] = $header;
                }

                $promises[] = ($method == 'POST') ? $client->postAsync($uri, $options) : $client->getAsync($uri, $options);
            }

            $result = Promise\unwrap($promises);

            foreach ($result as $k => $v) {
                $data[$k] = ($v->getStatusCode() == 200) ? $v->getBody()->getContents() : $v->getReasonPhrase();
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 上传文件
 *
 * $options格式：['name' => $field, 'contents' => fopen($filename, 'r'), 'headers' => $header]
 *
 * @access public
 * @param string $url     上传地址
 * @param array  $options 上传选项
 * @return array
 */
if ( ! function_exists('uploadFile')) {
    function uploadFile(string $url, array $options = [])
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($url)) {
                throw new \Exception('上传地址不能为空！');
            }

            if (empty($options)) {
                throw new \Exception('上传选项不能为空！');
            }

            $response = (new Client(['verify' => false]))->request('POST', $url, ['multipart' => $options]);

            if ($response->getStatusCode() != 200) {
                throw new \Exception($response->getReasonPhrase());
            }

            $status = [
                'code'    => 200,
                'data'    => $response->getBody()->getContents(),
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 验证器
 *
 * @access public
 * @param FormRequest $formRequest 验证规则
 * @parm   string  $keys  校验字段
 * @return \Hyperf\Contract\ValidatorInterface
 */
if ( ! function_exists('validator')) {
    function validator(FormRequest $formRequest, string $keys = '')
    {
        $rules = $formRequest->rules();

        if ( ! empty($keys)) {
            $keys  = explode(',', $keys);
            $rules = Arr::only($rules, $keys);
        }

        return container()->get(ValidatorFactoryInterface::class)->make(request()->all(), $rules, $formRequest->messages(), $formRequest->attributes());
    }
}

/**
 * 格式化JSON
 *
 * @access public
 * @param mixed $data 待处理数据
 * @return mixed
 */
if ( ! function_exists('withJson')) {
    function withJson(array $data)
    {
        if (isset($data['data'])) {
            if (empty($data['data']) && is_array($data['data'])) {
                $data['data'] = new \stdClass();
            }
        }

        return response()->json($data);
    }
}