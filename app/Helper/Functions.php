<?php declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * 数据格式化
 *
 * @access public
 * @param mixed $args 待处理数据
 * @return mixed
 */
if ( ! function_exists('formatData')) {
    function formatData($args)
    {
        $data = [];

        foreach ($args as $key => $val) {
            $key = lineToHump((string)$key);

            if (is_array($val) || is_object($val)) {
                $val = formatData($val);
            }

            $data[$key] = $val;
        }

        return $data;
    }
}

/**
 * 驼峰转下划线
 *
 * @access public
 * @param string $str 字符串
 * @return string
 */
if ( ! function_exists('lineToHump')) {
    function humpToLine(string $str)
    {
        return preg_replace_callback('/([A-Z])/', function ($match) {
            return '_'.lcfirst($match[0]);
        }, $str);
    }
}

/**
 * 下划线转驼峰
 *
 * @access public
 * @param string $str 字符串
 * @return string
 */
if ( ! function_exists('lineToHump')) {
    function lineToHump(string $str)
    {
        return preg_replace_callback('/(_[a-z])/', function ($match) {
            return ucfirst(trim($match[0], '_'));
        }, $str);
    }
}

/**
 * 随机字符串
 *
 * @access public
 * @param integer $len 字符长度
 * @param boolean $int 纯数字
 * @return string
 */
if ( ! function_exists('random')) {
    function random($len = 10, $int = false)
    {
        $str = '';
        $len = (is_numeric($len)) ? $len : 10;

        $seed = base_convert(md5(microtime(true).uniqid((string)mt_rand(), true)), 16, ( ! empty($int)) ? 10 : 35);
        $seed = ( ! empty($int)) ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));

        if ( ! empty($int)) {
            $str = '';
        } else {
            $str = chr(mt_rand(1, 26) + mt_rand(0, 1) * 32 + 64);
            --$len;
        }

        $max = strlen($seed) - 1;

        for ($i = 0; $i < $len; ++$i) {
            $str .= $seed{mt_rand(0, $max)};
        }

        return $str;
    }
}

/**
 * 发送请求(表单提交)
 *
 * @access public
 * @param string $url     URL
 * @param mixed  $args    提交参数
 * @param array  $headers HEAD信息
 * @param string $method  请求方法
 * @return array
 */
if ( ! function_exists('sendRequest')) {
    function sendRequest(string $url, $args = [], array $headers = [], string $method = 'GET')
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $options = [];
            $method  = ( ! empty($method)) ? $method : null;

            if ( ! empty($args)) {
                if (strtoupper($method) == 'POST') {
                    $options = (is_array($args)) ? ['form_params' => $args] : ['body' => \GuzzleHttp\Psr7\stream_for($args)];
                } else {
                    $options = ['query' => $args];
                }
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

        return $data;
    }
}