<?php declare(strict_types=1);

namespace App\Data;

use App\Model\TaskModel;
use App\Model\TaskLogModel;
use App\Model\TaskAbortModel;
use App\Service\ApplicationService;

use Hyperf\Utils\Arr;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Config\Annotation\Value;

/**
 * Class TaskData
 *
 * @package App\Data
 */
class TaskData
{
    /**
     * @Value("app.retryTotal")
     */
    private $_retryTotal;

    /**
     * @Value("app.taskQueue")
     */
    private $_taskQueue;

    /**
     * @Value("app.workerQueue")
     */
    private $_workerQueue;

    /**
     * @Value("app.delayQueue")
     */
    private $_delayQueue;

    /**
     * @Inject()
     * @var ApplicationService
     */
    private $_applicationService;

    /**
     * @Inject()
     * @var TaskModel
     */
    private $_taskModel;

    /**
     * @Inject()
     * @var TaskAbortModel
     */
    private $_taskAbortDao;

    /**
     * @Inject()
     * @var TaskLogModel
     */
    private $_taskLogModel;

    /**
     * 取消任务
     *
     * @access public
     * @param string $taskId 任务ID
     * @return array
     */
    public function abort(string $taskId)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        Db::beginTransaction();

        try {
            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空!');
            }

            $result = $this->_taskModel->findById($taskId);

            if (empty($result)) {
                throw new \Exception('任务信息获取失败!');
            }

            if (Arr::get($result, 'status') != 0) {
                throw new \Exception('任务已执行拦截失败!');
            }

            $exists = redis()->get($taskId);

            if ( ! empty($exists)) {
                throw new \Exception('请勿重复提交!');
            }

            $state   = 0;
            $runtime = Arr::get($result, 'runtime');
            // 如果大于一小时直接更新任务状态为已取消
            if ($runtime - time() > 3600) {
                $state = 1;
                $query = $this->_taskModel->updateTaskStatus($taskId, 3);

                if (empty($query)) {
                    throw new \Exception('任务取消失败!');
                }
            } else {
                redis()->set($taskId, 1, 3600);
            }

            $data = [
                'task_id'    => $taskId,
                'status'     => $state,
                'created_at' => time(),
                'updated_at' => 0
            ];

            $query = $this->_taskAbortDao->insertGetId($data);

            if (empty($query)) {
                throw new \Exception('拦截任务添加失败!');
            }

            Db::commit();

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();

            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 创建任务
     *
     * @access public
     * @param string $appKey  APP KEY
     * @param array  $request 用户请求
     * @return array
     */
    public function create(string $appKey, array $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($appKey)) {
                throw new \Exception('APP KEY 不能为空!');
            }

            if (empty($request)) {
                throw new \Exception('提交数据不能为空!');
            }

            $application = $this->_applicationService->getApplicationInfo($appKey);

            if (Arr::get($application, 'code') != 200) {
                throw new \Exception(Arr::get($application, 'message'));
            }

            $application = Arr::get($application, 'data');

            $taskNo  = Arr::get($request, 'taskNo');
            $content = Arr::get($request, 'content');

            $runtime = Arr::get($request, 'runtime');
            $runtime = ( ! empty($runtime)) ? strtotime($runtime) : time();

            $count = $this->_taskModel->findNumByTaskNo($appKey, $taskNo);

            if ($count > 0) {
                throw new \Exception('请勿重复提交！');
            }

            $appKey = Arr::get($application, 'app_key');
            $runing = ($runtime <= time()) ? 1 : 0;

            $taskId = snowflake()->generate();

            $data = [
                'id'         => $taskId,
                'app_key'    => $appKey,
                'task_no'    => $taskNo,
                'status'     => $runing,
                'step'       => Arr::get($application, 'step'),
                'runtime'    => $runtime,
                'content'    => $content,
                'created_at' => time()
            ];

            $query = $this->_taskModel->insert($data);

            if (empty($query)) {
                throw new \Exception('任务记录写入失败!');
            }

            $delay = $runtime - time();

            // 一小时以内存入Redis,大于一小时由定时器处理
            if ($delay <= 3600) {
                // 把数据存放到 Redis
                $data = [
                    'appKey'     => $appKey,
                    'secretKey'  => Arr::get($application, 'secret_key'),
                    'taskNo'     => $taskNo,
                    'linkUrl'    => Arr::get($application, 'link_url'),
                    'retryNum'   => 0,
                    'retryTotal' => Arr::get($application, 'retry_total', $this->_retryTotal),
                    'step'       => Arr::get($application, 'step'),
                    'content'    => $content,
                ];

                $exists = redis()->hGet($this->_taskQueue, $taskId);

                if ( ! empty($exists)) {
                    $exists = json_decode($exists, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('数据解析异常！');
                    }

                    $data['retryNum'] = Arr::get($exists, 'retryNum');
                }

                redis()->hSet($this->_taskQueue, $taskId, json_encode($data));

                if ($delay > 0) { // 延迟任务
                    redis()->zAdd($this->_delayQueue, [$taskId => $runtime]);
                } else { // 立即执行
                    redis()->lPush($this->_workerQueue, $taskId);
                }
            }

            $status = [
                'code'    => 200,
                'data'    => ['taskId' => $taskId],
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 任务详情
     *
     * @access public
     * @param string $taskId 任务ID
     * @return array
     */
    public function detail($taskId)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空!');
            }

            $result = $this->_taskModel->findById($taskId);

            if (empty($result)) {
                throw new \Exception('没有找到相任务记录!');
            }

            $logs = $this->_taskLogModel->findAllByTaskId($taskId);

            $data = [
                'taskId'    => Arr::get($result, 'id'),
                'taskNo'    => Arr::get($result, 'task_no'),
                'status'    => Arr::get($result, 'status'),
                'step'      => Arr::get($result, 'step'),
                'runtime'   => Arr::get($result, 'runtime'),
                'content'   => Arr::get($result, 'content'),
                'createdAt' => Arr::get($result, 'created_at'),
                'updatedAt' => Arr::get($result, 'updated_at'),
                'logs'      => []
            ];

            foreach ($logs as $k => $v) {
                $data['logs'][] = [
                    'retry'     => Arr::get($v, 'retry'),
                    'remark'    => Arr::get($v, 'remark'),
                    'createdAt' => Arr::get($v, 'created_at'),
                    'updatedAt' => Arr::get($v, 'updated_at'),
                ];
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 任务重试
     *
     * @access public
     * @param string $appKey APP KEY
     * @param string $taskId 任务ID
     * @return array
     */
    public function retry(string $appKey, string $taskId)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($appKey)) {
                throw new \Exception('APP KEY 不能为空!');
            }

            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空!');
            }

            $task = $this->_taskModel->findById($taskId);

            if (empty($task)) {
                throw new \Exception('任务ID输入有误!');
            }

            $application = $this->_applicationService->getApplicationInfo($appKey);

            if (Arr::get($application, 'code') != 200) {
                throw new \Exception(Arr::get($application, 'message'));
            }

            $application = Arr::get($application, 'data');

            $data = [
                'appKey'     => Arr::get($task, 'app_key'),
                'secretKey'  => Arr::get($application, 'secret_key'),
                'taskNo'     => Arr::get($task, 'task_no'),
                'linkUrl'    => Arr::get($application, 'link_url'),
                'retryTotal' => Arr::get($application, 'retry_total', $this->_retryTotal),
                'retryNum'   => 0,
                'step'       => Arr::get($task, 'step'),
                'content'    => Arr::get($task, 'content'),
            ];

            redis()->hSetNx($this->_taskQueue, $taskId, json_encode($data));
            redis()->lPush($this->_workerQueue, $taskId);

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}