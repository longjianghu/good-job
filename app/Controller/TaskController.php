<?php declare(strict_types=1);

namespace App\Controller;

use App\Data\TaskData;
use App\Request\TaskRequest;
use App\Middleware\AuthMiddleware;

use Hyperf\Utils\Arr;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class IndexController
 *
 * @Controller()
 * @Middleware(AuthMiddleware::class)
 * @package App\Controller
 */
class TaskController extends AbstractController
{
    /**
     * @Inject()
     * @var TaskData
     */
    private $_taskData;

    /**
     * @Inject()
     * @var TaskRequest
     */
    private $_taskRequest;

    /**
     * 任务拦截
     *
     * @access public
     * @RequestMapping(path="abort",methods="post")
     * @return object
     */
    public function abort(RequestInterface $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $validator = validator($this->_taskRequest, 'taskId');

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $taskId = $request->post('taskId');
            $result = $this->_taskData->abort($taskId);

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

    /**
     * 投递任务
     *
     * @access public
     * @RequestMapping(path="create",methods="post")
     * @return object
     */
    public function create(RequestInterface $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $validator = validator($this->_taskRequest, 'taskNo,runtime,content');

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $appKey = $request->getHeaderLine('app_key');
            $result = $this->_taskData->create($appKey, $request->all());

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

    /**
     * 任务详情
     *
     * @access public
     * @RequestMapping(path="detail",methods="post")
     * @return object
     */
    public function detail(RequestInterface $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $validator = validator($this->_taskRequest, 'taskId');

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $taskId = $request->post('taskId');
            $result = $this->_taskData->detail($taskId);

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

    /**
     * 任务重试
     *
     * @access public
     * @Middleware(AuthMiddleware::class)
     * @RequestMapping(path="retry",methods="post")
     * @return object
     */
    public function retry(RequestInterface $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $validator = validator($this->_taskRequest, 'taskId');

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $appKey = $request->getHeaderLine('app_key');
            $taskId = $request->post('taskId');
            $result = $this->_taskData->retry($appKey, $taskId);

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
