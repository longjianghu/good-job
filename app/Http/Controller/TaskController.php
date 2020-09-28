<?php declare(strict_types=1);

namespace App\Http\Controller;

use App\Model\Data\TaskData;
use App\Http\Middleware\ApiMiddleware;

use Swoft\Stdlib\Helper\Arr;
use Swoft\Http\Message\Request;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Validator\Annotation\Mapping\Validate;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * 任务处理
 *
 * @Controller(prefix="task")
 */
class TaskController
{
    /**
     * @Inject()
     * @var TaskData
     */
    private $_taskData;

    /**
     * 取消任务
     *
     * @RequestMapping("abort",method={RequestMethod::POST})
     * @Validate(validator="TaskValidator",fields={"taskId"})
     * @Middleware(ApiMiddleware::class)
     * @return Object
     */
    public function abort(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $taskId = $request->post('taskId');
            $result = $this->_taskData->abort($taskId);

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => formatData(Arr::get($result, 'data')),
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }

    /**
     * 创建任务
     *
     * @RequestMapping("create",method={RequestMethod::POST})
     * @Validate(validator="TaskValidator",fields={"appName","step","retryTotal","linkUrl","remark"})
     * @return Object
     */
    public function create(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $post   = $request->post();
            $result = $this->_taskData->create($post);

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => formatData(Arr::get($result, 'data')),
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
     * @RequestMapping("detail",method={RequestMethod::POST})
     * @Validate(validator="TaskValidator",fields={"taskId"})
     * @Middleware(ApiMiddleware::class)
     * @return Object
     */
    public function detail(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $taskId = $request->post('taskId');
            $result = $this->_taskData->detail($taskId);

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => formatData(Arr::get($result, 'data')),
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
     * @RequestMapping("push",method={RequestMethod::POST})
     * @Validate(validator="PushValidator")
     * @Middleware(ApiMiddleware::class)
     * @return Object
     */
    public function push(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $appKey = $request->getHeaderLine('app-key');
            $result = $this->_taskData->push($appKey, $request->post());

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => formatData(Arr::get($result, 'data')),
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
     * @RequestMapping("retry",method={RequestMethod::POST})
     * @Validate(validator="TaskValidator",fields={"taskId"})
     * @Middleware(ApiMiddleware::class)
     * @return Object
     */
    public function retry(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $appKey = $request->getHeaderLine('app-key');
            $taskId = $request->post('taskId');

            $result = $this->_taskData->retry($appKey, $taskId);

            if (Arr::get($result, 'code') != 200) {
                throw new \Exception(Arr::get($result, 'message'));
            }

            $status = [
                'code'    => 200,
                'data'    => formatData(Arr::get($result, 'data')),
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }
}
