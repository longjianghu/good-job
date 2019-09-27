<?php declare(strict_types=1);

namespace App\Http\Controller;

use App\Model\Data\TaskData;
use App\Http\Middleware\ApiMiddleware;

use Swoft;
use Swoft\Http\Message\Request;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Validator\Annotation\Mapping\Validate;
use Swoft\Stdlib\Helper\ArrayHelper;
use Throwable;

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
     * 首页
     *
     * @RequestMapping("index")
     * @throws Throwable
     */
    public function index()
    {
        return withJson(['code' => 200, 'data' => [], 'message' => '']);
    }

    /**
     * 取消任务
     *
     * @RequestMapping("abort",method={RequestMethod::POST})
     * @Validate(validator="OtherValidator")
     * @Middleware(ApiMiddleware::class)
     * @throws Throwable
     */
    public function abort(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $taskId = $request->post('taskId');
            $result = $this->_taskData->abort($taskId);

            if (ArrayHelper::getValue($result, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($result, 'message'));
            }

            $data   = ArrayHelper::getValue($result, 'data');
            $status = ['code' => 200, 'data' => formatData($data), 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }

    /**
     * 创建任务
     *
     * @RequestMapping("create",method={RequestMethod::POST})
     * @Validate(validator="TaskValidator")
     * @throws Throwable
     */
    public function create(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $result = $this->_taskData->create($request->post());

            if (ArrayHelper::getValue($result, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($result, 'message'));
            }

            $data   = ArrayHelper::getValue($result, 'data');
            $status = ['code' => 200, 'data' => formatData($data), 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }

    /**
     * 任务详情
     *
     * @RequestMapping("detail",method={RequestMethod::POST})
     * @Validate(validator="OtherValidator")
     * @Middleware(ApiMiddleware::class)
     * @throws Throwable
     */
    public function detail(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $taskId = $request->post('taskId');
            $result = $this->_taskData->detail($taskId);

            if (ArrayHelper::getValue($result, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($result, 'message'));
            }

            $data   = ArrayHelper::getValue($result, 'data');
            $status = ['code' => 200, 'data' => formatData($data), 'message' => ''];
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
     * @throws Throwable
     */
    public function push(Request $request)
    {
        $status = ['code' => 500, 'data' => [], 'message' => ''];

        try {
            $appKey      = $request->getHeaderLine('app-key');
            $application = $this->_taskData->getApplicationInfo($appKey);

            if (ArrayHelper::getValue($application, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($application, 'message'));
            }

            $application = ArrayHelper::getValue($application, 'data');
            $result      = $this->_taskData->push($application, $request->post());

            if (ArrayHelper::getValue($result, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($result, 'message'));
            }

            $data   = ArrayHelper::getValue($result, 'data');
            $status = ['code' => 200, 'data' => formatData($data), 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return withJson($status);
    }
}
