<?php declare(strict_types=1);

namespace App\Model\Data;

use App\Model\Dao\AbortDao;
use App\Model\Dao\LogsDao;
use App\Model\Dao\TaskDao;
use App\Model\Dao\ApplicationDao;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Stdlib\Helper\ArrayHelper;
use Swoft\Redis\Pool;

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
     * 添加任务
     *
     * @access public
     * @return array
     */
    public function addTask()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $result = $this->_taskDao->findPendingTask();

            if (empty($result)) {
                throw new \Exception('一小时内没有需要执行的任务!');
            }

            foreach ($result as $k => $v) {
                $taskId  = ArrayHelper::getValue($v, 'task_id');
                $runtime = ArrayHelper::getValue($v, 'runtime');
                $appKey  = ArrayHelper::getValue($v, 'app_key');

                $application = $this->getApplicationInfo($appKey);

                $task = [
                    'appKey'    => $appKey,
                    'secretKey' => ArrayHelper::getValue($application, 'data.secret_key'),
                    'linkUrl'   => ArrayHelper::getValue($v, 'link_url'),
                    'retry'     => 0,
                    'step'      => ArrayHelper::getValue($v, 'step'),
                    'content'   => ArrayHelper::getValue($v, 'content'),
                ];

                $this->_redis->hSetNx(config('queue.task'), $taskId, json_encode($task));
                $delay = $runtime - time();

                if ($delay > 0) { // 延迟任务
                    $this->_redis->zAdd(config('queue.delay'), [$taskId => $runtime]);
                } else { // 立即执行
                    $this->_redis->lPush(config('queue.worker'), $taskId);
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
                throw new \Exception('任务ID不能为空！');
            }

            $result = $this->_taskDao->findByTaskId($taskId);

            if (empty($result)) {
                throw new \Exception('任务信息获取失败！');
            }

            if (ArrayHelper::getValue($result, 'status') != 0) {
                throw new \Exception('任务已执行拦截失败！');
            }

            $exists = $this->_redis->get($taskId);

            if ( ! empty($exists)) {
                throw new \Exception('请勿重复提交！');
            }

            $state   = 0;
            $runtime = ArrayHelper::getValue($result, 'runtime');
            // 如果大于一小时直接更新任务状态为已取消
            if ($runtime - time() > 3600) {
                $state = 1;
                $query = $this->_taskDao->updateTaskStatus($taskId, 3);

                if (empty($query)) {
                    throw new \Exception('任务取消失败！');
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
                throw new \Exception('拦截任务添加失败！');
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
                throw new \Exception('提交数据不能为空！');
            }

            $appKey    = random(16, true);
            $secretKey = random(32);

            $data = [
                'app_name'   => ArrayHelper::getValue($post, 'appName'),
                'app_key'    => $appKey,
                'secret_key' => $secretKey,
                'step'       => ArrayHelper::getValue($post, 'step', 0),
                'link_url'   => ArrayHelper::getValue($post, 'linkUrl'),
                'remark'     => ArrayHelper::getValue($post, 'remark'),
                'created_at' => time(),
                'updated_at' => 0
            ];

            $query = $this->_applicationDao->create($data);

            if (empty($query)) {
                throw new \Exception('任务添加失败！');
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
                throw new \Exception('任务ID不能为空！');
            }

            $data = $this->_taskDao->findByTaskId($taskId);

            if (empty($data)) {
                throw new \Exception('没有找到相任务记录！');
            }

            $data['logs'] = $this->_logsDao->findAllByTaskId($taskId);

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
                throw new \Exception('APP KEY 不能为空！');
            }

            if (empty($post)) {
                throw new \Exception('提交数据不能为空！');
            }

            $application = $this->getApplicationInfo($appKey);

            if (ArrayHelper::getValue($application, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($application, 'message'));
            }

            $application = ArrayHelper::getValue($application, 'data');

            $taskNo  = ArrayHelper::getValue($post, 'taskNo');
            $content = ArrayHelper::getValue($post, 'content');

            $runtime = ArrayHelper::getValue($post, 'runtime');
            $runtime = ( ! empty($runtime)) ? strtotime($runtime) : time();

            if (empty($taskNo)) {
                throw new \Exception('任务编号不能为空！');
            }

            if (empty($content)) {
                throw new \Exception('任务内容不能为空！');
            }

            $appKey = ArrayHelper::getValue($application, 'app_key');
            $taskId = md5(sprintf('%s%s', $appKey, $taskNo));

            $runing = ($runtime <= time()) ? 1 : 0;

            // 检测任务是否已经存在
            $count = $this->_taskDao->findByTaskId($taskId);

            if ($count > 0) {
                throw new \Exception('提交的任务已存在！');
            }

            $data = [
                'task_id'    => $taskId,
                'app_key'    => $appKey,
                'task_no'    => $taskNo,
                'status'     => $runing,
                'step'       => ArrayHelper::getValue($application, 'step'),
                'runtime'    => $runtime,
                'content'    => $content,
                'created_at' => time(),
            ];

            $query = $this->_taskDao->create($data);

            if (empty($query)) {
                throw new \Exception('任务记录写入失败！');
            }

            $delay = $runtime - time();

            // 一小时以内存入Redis,大于一小时由定时器处理
            if ($delay <= 3600) {
                // 把数据存放到 Redis
                $data = [
                    'appKey'    => $appKey,
                    'secretKey' => ArrayHelper::getValue($application, 'secret_key'),
                    'taskNo'    => $taskNo,
                    'linkUrl'   => ArrayHelper::getValue($application, 'link_url'),
                    'retry'     => 0,
                    'step'      => ArrayHelper::getValue($application, 'step'),
                    'content'   => $content,
                ];

                $this->_redis->hSetNx(config('queue.task'), $taskId, json_encode($data));

                if ($delay > 0) { // 延迟任务
                    $this->_redis->zAdd(config('queue.delay'), [$taskId => $runtime]);
                } else { // 立即执行
                    $this->_redis->lPush(config('queue.worker'), $taskId);
                }
            }

            $status = ['code' => 200, 'data' => ['taskId' => $taskId], 'message' => ''];
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
                throw new \Exception('APP KEY 不能为空！');
            }

            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空！');
            }

            $result = $this->_taskDao->findByTaskId($taskId);

            if (empty($result)) {
                throw new \Exception('任务ID输入有误！');
            }

            $application = $this->getApplicationInfo($appKey);

            if (ArrayHelper::getValue($application, 'code') != 200) {
                throw new \Exception(ArrayHelper::getValue($application, 'message'));
            }

            $application = ArrayHelper::getValue($application, 'data');

            $data = [
                'appKey'    => ArrayHelper::getValue($result, 'app_key'),
                'secretKey' => ArrayHelper::getValue($application, 'secret_key'),
                'taskNo'    => ArrayHelper::getValue($result, 'task_no'),
                'linkUrl'   => ArrayHelper::getValue($application, 'link_url'),
                'retry'     => 0,
                'step'      => ArrayHelper::getValue($result, 'step'),
                'content'   => ArrayHelper::getValue($result, 'content'),
            ];

            $this->_redis->hSetNx(config('queue.task'), $taskId, json_encode($data));
            $this->_redis->lPush(config('queue.worker'), $taskId);

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
                throw new \Exception('APP KEY不能为空！');
            }

            $application = $this->_redis->get($appKey);
            $application = ( ! empty($application)) ? $application : $this->_applicationDao->findByAppKey($appKey);

            if (empty($application)) {
                throw new \Exception('没有找到相关记录！');
            }

            $this->_redis->set($appKey, $application, 300);

            $status = ['code' => 200, 'data' => $application, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}