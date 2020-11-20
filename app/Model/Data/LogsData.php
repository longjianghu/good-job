<?php declare(strict_types=1);

namespace App\Model\Data;

use App\Model\Dao\LogsDao;
use App\Model\Dao\TaskDao;

use Swoft\Redis\Pool;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * 日志处理
 *
 * @package App\Model\Data
 * @Bean()
 */
class LogsData
{
    /**
     * @Config("app.queue.log")
     */
    private $_logQueue;

    /**
     * @Inject()
     * @var TaskDao
     */
    private $_taskDao;

    /**
     * @Inject()
     * @var LogsDao
     */
    private $_logsDao;

    /**
     * @Inject("redisPool")
     * @var Pool
     */
    private $_redis;

    /**
     * 创建日志
     *
     * @access public
     * @return array
     */
    public function monitor()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $logs = $this->_redis->lPop($this->_logQueue);

            if (empty($logs)) {
                throw new \Exception('日志数据不能为空!');
            }

            $logs = json_decode($logs, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception('数据解析失败!');
            }

            $taskId = Arr::get($logs, 'taskId');

            $data = [
                'task_id'    => $taskId,
                'retry'      => (int)Arr::get($logs, 'retry', 0),
                'remark'     => (string)Arr::get($logs, 'remark'),
                'created_at' => time(),
                'updated_at' => 0
            ];

            $query = $this->_logsDao->create($data);

            if (empty($query)) {
                throw new \Exception('日志添加失败!');
            }

            $query = $this->_taskDao->updateTaskStatus($taskId, 2);

            if (empty($query)) {
                throw new \Exception('任务状态更新失败!');
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}