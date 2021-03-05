<?php declare(strict_types=1);

namespace App\Controller;

use App\Data\ApplicationData;
use App\Middleware\AuthMiddleware;
use App\Request\ApplicationRequest;

use Hyperf\Utils\Arr;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class IndexController
 *
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * @Inject()
     * @var ApplicationData
     */
    private $_applicationData;

    /**
     * @Inject()
     * @var ApplicationRequest
     */
    private $_applicationRequest;

    /**
     * 发送短信
     *
     * @access
     * @RequestMapping(path="",methods="get")
     * @return mixed
     */
    public function index()
    {
        $status = ['code' => 200, 'data' => ['good.job'], 'message' => ''];

        return withJson($status);
    }

    /**
     * 创建应用
     *
     * @access public
     * @Middleware(AuthMiddleware::class)
     * @RequestMapping(path="/application",methods="post")
     * @return object
     */
    public function application(RequestInterface $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $validator = validator($this->_applicationRequest);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $result = $this->_applicationData->create($request->all());

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => Arr::get($result, 'data'),
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }
}
