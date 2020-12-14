<?php declare(strict_types=1);

namespace App\Model\Data;

use App\Model\Dao\LogsDao;
use App\Model\Dao\TaskDao;
use App\Model\Dao\AbortDao;
use App\Model\Dao\ApplicationDao;

use Swoft\Db\DB;
use Swoft\Redis\Pool;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * 任务处理
 *
 * @package App\Model\Data
 * @Bean()
 */
class TaskData
{
    const POOL = 'dbJobPool';

    /**
     * @Config("app.queue.delay")
     */
    private $_delayQueue;

    /**
     * @Config("app.queue.task")
     */
    private $_taskQueue;

    /**
     * @Config("app.queue.worker")
     */
    private $_workerQueue;

    /**
     * @Config("app.retryTotal")
     */
    private $_retryTotal = 0;

    /**
     * @Inject()
     * @var AbortDao
     */
    private $_abortDao;

    /**
     * @Inject()
     * @var ApplicationDao
     */
    private $_applicationDao;

    /**
     * @Inject()
     * @var LogsDao
     */
    private $_logsDao;

    /**
     * @Inject()
     * @var TaskDao
     */
    private $_taskDao;

    /**
     * @Inject("redisPool")
     * @var Pool
     */
    private $_redis;

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

        DB::connection(self::POOL)->beginTransaction();

        try {
            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空!');
            }

            $result = $this->_taskDao->findById($taskId);

            if (empty($result)) {
                throw new \Exception('任务信息获取失败!');
            }

            if (Arr::get($result, 'status') != 0) {
                throw new \Exception('任务已执行拦截失败!');
            }

            $exists = $this->_redis->get($taskId);

            if ( ! empty($exists)) {
                throw new \Exception('请勿重复提交!');
            }

            $state   = 0;
            $runtime = Arr::get($result, 'runtime');
            // 如果大于一小时直接更新任务状态为已取消
            if ($runtime - time() > 3600) {
                $state = 1;
                $query = $this->_taskDao->updateTaskStatus($taskId, 3);

                if (empty($query)) {
                    throw new \Exception('任务取消失败!');
                }
            } else {
                $this->_redis->set($taskId, 1, 3600);
            }

            $data = [
                'task_id'    => $taskId,
                'status'     => $state,
                'created_at' => time(),
                'updated_at' => 0
            ];

            $query = $this->_abortDao->create($data);

            if (empty($query)) {
                throw new \Exception('拦截任务添加失败!');
            }

            DB::connection(self::POOL)->commit();

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            DB::connection(self::POOL)->rollBack();

            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 创建任务
     *
     * @access public
     * @param array $post POST数据
     * @return array
     */
    public function create(array $post)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($post)) {
                throw new \Exception('提交数据不能为空!');
            }

            $appKey    = random(16, true);
            $secretKey = random(32);

            $data = [
                'app_key'     => $appKey,
                'app_name'    => Arr::get($post, 'appName'),
                'secret_key'  => $secretKey,
                'step'        => (int)Arr::get($post, 'step', 0),
                'retry_total' => (int)Arr::get($post, 'retryTotal', $this->_retryTotal),
                'link_url'    => Arr::get($post, 'linkUrl'),
                'remark'      => Arr::get($post, 'remark'),
                'created_at'  => time(),
                'updated_at'  => 0
            ];

            $query = $this->_applicationDao->create($data);

            if (empty($query)) {
                throw new \Exception('任务添加失败!');
            }

            $status = [
                'code'    => 200,
                'data'    => ['appKey' => $appKey, 'secretKey' => $secretKey],
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

            $result = $this->_taskDao->findById($taskId);

            if (empty($result)) {
                throw new \Exception('没有找到相任务记录!');
            }

            $logs = $this->_logsDao->findAllByTaskId($taskId);

            $data = [
                'taskId'    => Arr::get($result, 'task_id'),
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
     * 投递任务
     *
     * @access public
     * @param string $appKey APP KEY
     * @param array  $post   POST数据
     * @return array
     */
    public function push(string $appKey, array $post)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($appKey)) {
                throw new \Exception('APP KEY 不能为空!');
            }

            if (empty($post)) {
                throw new \Exception('提交数据不能为空!');
            }

            $application = $this->getApplicationInfo($appKey);

            if (Arr::get($application, 'code') != 200) {
                throw new \Exception(Arr::get($application, 'message'));
            }

            $application = Arr::get($application, 'data');

            $taskNo = Arr::get($post, 'taskNo');

            $runtime = Arr::get($post, 'runtime');
            $runtime = ( ! empty($runtime)) ? strtotime($runtime) : time();

            $content = Arr::get($post, 'content');

            if (empty($taskNo)) {
                throw new \Exception('任务编号不能为空!');
            }

            if (empty($runtime)) {
                throw new \Exception('日期格式输入有误!');
            }

            if (empty($content)) {
                throw new \Exception('任务内容不能为空!');
            }

            $appKey = Arr::get($application, 'app_key');
            $runing = ($runtime <= time()) ? 1 : 0;

            $data = [
                'app_key'    => $appKey,
                'task_no'    => $taskNo,
                'status'     => $runing,
                'step'       => Arr::get($application, 'step'),
                'runtime'    => $runtime,
                'content'    => $content,
                'created_at' => time()
            ];

            $taskId = (string)$this->_taskDao->create($data);

            if (empty($taskId)) {
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

                $exists = $this->_redis->hGet($this->_taskQueue, $taskId);

                if ( ! empty($exists)) {
                    $exists = json_decode($exists, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('数据解析异常！');
                    }

                    $data['retryNum'] = Arr::get($exists, 'retryNum');
                }

                $this->_redis->hSet($this->_taskQueue, $taskId, json_encode($data));

                if ($delay > 0) { // 延迟任务
                    $this->_redis->zAdd($this->_delayQueue, [$taskId => $runtime]);
                } else { // 立即执行
                    $this->_redis->lPush($this->_workerQueue, $taskId);
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

            $result = $this->_taskDao->findById($taskId);

            if (empty($result)) {
                throw new \Exception('任务ID输入有误!');
            }

            $application = $this->getApplicationInfo($appKey);

            if (Arr::get($application, 'code') != 200) {
                throw new \Exception(Arr::get($application, 'message'));
            }

            $application = Arr::get($application, 'data');

            $data = [
                'appKey'     => Arr::get($result, 'app_key'),
                'secretKey'  => Arr::get($application, 'secret_key'),
                'taskNo'     => Arr::get($result, 'task_no'),
                'linkUrl'    => Arr::get($application, 'link_url'),
                'mobile'     => Arr::get($application, 'mobile'),
                'email'      => Arr::get($application, 'email'),
                'retryTotal' => Arr::get($application, 'retry_total', $this->_retryTotal),
                'retryNum'   => 0,
                'step'       => Arr::get($result, 'step'),
                'content'    => Arr::get($result, 'content'),
            ];

            $this->_redis->hSetNx($this->_taskQueue, $taskId, json_encode($data));
            $this->_redis->lPush($this->_workerQueue, $taskId);

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 定时任务
     *
     * @access public
     * @return array
     */
    public function scheduled()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $result = $this->_taskDao->findPendingTask();

            if (empty($result)) {
                throw new \Exception('一小时内没有需要执行的任务!');
            }

            foreach ($result as $k => $v) {
                $taskId  = (string)Arr::get($v, 'id');
                $runtime = Arr::get($v, 'runtime');
                $appKey  = Arr::get($v, 'app_key');

                $application = $this->getApplicationInfo($appKey);

                if (Arr::get($application, 'code') != 200) {
                    throw new \Exception(Arr::get($application, 'message'));
                }

                $application = Arr::get($application, 'data');

                $data = [
                    'appKey'     => $appKey,
                    'secretKey'  => Arr::get($application, 'secret_key'),
                    'taskNo'     => Arr::get($v, 'task_no'),
                    'linkUrl'    => Arr::get($application, 'link_url'),
                    'retryNum'   => 0,
                    'retryTotal' => (int)Arr::get($application, 'retry_total', $this->_retryTotal),
                    'step'       => (int)Arr::get($v, 'step', 0),
                    'content'    => Arr::get($v, 'content'),
                ];

                $this->_redis->hSetNx($this->_taskQueue, $taskId, json_encode($data));
                $delay = $runtime - time();

                if ($delay > 0) { // 延迟任务
                    $this->_redis->zAdd($this->_delayQueue, [$taskId => $runtime]);
                } else { // 立即执行
                    $this->_redis->lPush($this->_workerQueue, $taskId);
                }

                // 更新任务状态为处理中
                $this->_taskDao->updateTaskStatus($taskId, 1);
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 获取应用信息
     *
     * @access public
     * @param string $appKey APP KEY
     * @return array
     */
    public function getApplicationInfo(string $appKey)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($appKey)) {
                throw new \Exception('APP KEY不能为空!');
            }

            $data = $this->_redis->get($appKey);

            if (empty($data)) {
                $data = $this->_applicationDao->findByAppKey($appKey);

                if (empty($data)) {
                    throw new \Exception('APP KEY 输入有误!');
                }

                $data = json_encode($data);
                $this->_redis->set($appKey, $data, 300);
            }

            $status = ['code' => 200, 'data' => json_decode($data, true), 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}