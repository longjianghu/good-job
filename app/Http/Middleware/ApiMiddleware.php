<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Model\Data\TaskData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Swoft\Http\Message\Request;
use Swoft\Stdlib\Helper\ArrayHelper;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Contract\MiddlewareInterface;

/**
 * API 接口
 *
 * @package App\Http\Middleware
 * @Bean()
 */
class ApiMiddleware implements MiddlewareInterface
{
    /**
     * @Inject()
     * @var TaskData
     */
    private $_taskData;

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
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $appKey    = $request->getHeaderLine('app-key');
            $timestamp = $request->getHeaderLine('timestamp');
            $nonceStr  = $request->getHeaderLine('nonce-str');
            $signature = $request->getHeaderLine('signature');
            $version   = $request->getHeaderLine('version');

            if (empty($appKey)) {
                throw new \Exception('APP KEY不能为空！');
            }

            if (empty($timestamp)) {
                throw new \Exception('请求时间不能为空！');
            }

            $now = strtotime($timestamp);

            if (empty($now)) {
                throw new \Exception('时间格式输入有误！');
            }

            $now = time() - $now;

            if ($now > 180 || $now < -180) {
                throw new \Exception(sprintf('请求时间与系统偏差过大,系统当前时间:%s！', date('Y-m-d H:i:s')));
            }

            if (empty($nonceStr) || strlen($nonceStr) < 6) {
                throw new \Exception('随机字符串不能小于6位！');
            }

            if ($version != '1.0') {
                throw new \Exception('版本号输入有误！');
            }

            if (empty($signature)) {
                throw new \Exception('用户签名不能为空！');
            }

            $data = $request->post();

            if (empty($data)) {
                throw new \Exception('提交数据有误！');
            }

            $data['app-key']   = $appKey;
            $data['timestamp'] = $timestamp;
            $data['nonce-str'] = $nonceStr;
            $data['version']   = $version;

            $validator = $this->_checkUserSignature($data, $signature);

            if (ArrayHelper::getValue($validator, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($validator, 'message'));
            }
        } catch (\Exception $e) {
            $status['message'] = $e->getMessage();

            return context()->getResponse()->withData($status);
        }

        return $handler->handle($request);
    }

    /**
     * 校验用户签名
     *
     * @access private
     * @param array  $data      签名数据
     * @param string $signature 用户签名
     * @return string
     */
    private function _checkUserSignature(array $data, string $signature)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $data = array_filter($data);

            if (empty($data)) {
                throw new \Exception('签名数据不能为空！');
            }

            if (empty($signature)) {
                throw new \Exception('用户签名不能为空！');
            }

            $appKey = ArrayHelper::getValue($data, 'app-key');
            $result = $this->_taskData->getApplicationInfo($appKey);

            if (ArrayHelper::getValue($result, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($result, 'message'));
            }

            $secretKey = ArrayHelper::getValue($result, 'data.secret_key');

            ksort($data);

            $str = http_build_query($data, '', '&');
            $str = urldecode($str);
            $str = md5(md5($str).$secretKey);

            if ($str != $signature && APP_DEBUG == 0) {
                throw new \Exception('用户签名不正确！');
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Exception $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}
