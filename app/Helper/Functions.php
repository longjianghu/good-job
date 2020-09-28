<?php declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
 * 发送请求
 *
 * @access public
 * @param string $url     URL
 * @param array  $args    提交参数
 * @param array  $headers HEAD信息
 * @param string $method  提交方法
 * @return array
 * @throws
 */
if ( ! function_exists('send')) {
    function send(string $url, array $args = [], array $headers = [], string $method = 'POST')
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $options = ( ! empty($args)) ? ['form_params' => $args] : [];

            if ( ! empty($headers)) {
                $options['headers'] = $headers;
            }

            $response = (new Client())->request($method, $url, $options);
            $content  = $response->getBody()->getContents();

            if (empty($content)) {
                throw new \Exception('没有返回相关数据！');
            }

            $status = ['code' => 200, 'data' => $content, 'message' => ''];
        } catch (RequestException $e) {
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