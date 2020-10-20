<?php declare(strict_types=1);

namespace App\Model\Logic;

use App\Model\Dao\AbortDao;

use Swoft\Redis\Pool;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * 任务处理
 *
 * @package App\Model\Logic
 * @Bean()
 */
class TaskLogic
{

    /**
     * @Config("app.queue")
     */
    private $_queue;

    /**
     * @Inject()
     * @var AbortDao
     */
    private $_abortDao;

    /**
     * @Inject("redisPool")
     * @var Pool
     */
    private $_redis;

    /**
     * 任务执行Worker
     *
     * @access public
     * @return array
     */
    public function worker()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $taskId = $this->_redis->lPop(Arr::get($this->_queue, 'worker'));

            if (empty($taskId)) {
                throw new \Exception('没有需要执行的任务!');
            }

            $queueName = Arr::get($this->_queue, 'task');
            $task      = $this->_redis->hGet($queueName, $taskId);

            if (empty($task)) {
                throw new \Exception('任务信息获取失败!');
            }

            $task = json_decode($task, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception('任务数据解析失败!');
            }

            $appKey    = Arr::get($task, 'appKey');
            $secretKey = Arr::get($task, 'secretKey');
            $taskNo    = Arr::get($task, 'taskNo');
            $linkUrl   = Arr::get($task, 'linkUrl');
            $content   = Arr::get($task, 'content');

            $step       = (int)Arr::get($task, 'step');
            $retryNum   = (int)Arr::get($task, 'retryNum');
            $retryTotal = (int)Arr::get($task, 'retryTotal');

            $logs  = ['taskId' => $taskId, 'retry' => $retryNum, 'remark' => '任务执行成功!', 'created_at' => time()];
            $abort = $this->_redis->get($taskId);

            // 是否删除任务数据
            $remove = true;

            if ( ! empty($abort)) { // 系统拦截
                $logs['remark'] = '系统拦截';

                $this->_abortDao->updateTaskStatus($taskId, 1);
            } else { // 未被拦截
                $header = [
                    'app-key'   => $appKey,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'nonce-str' => random(10),
                    'signature' => '',
                    'version'   => '1.0',
                ];

                $data = ['data' => $content];

                // 生成签名信息
                $temp = array_merge($header, $data);
                $temp = array_filter($temp);

                ksort($temp);

                $signature = [];

                foreach ($temp as $k => $v) {
                    $signature[] = sprintf('%s=%s', $k, $v);
                }

                $str = implode('&', $signature);

                $header['signature'] = md5($str.$secretKey);

                // 发送请求
                $query = send($linkUrl, $data, $header);
                $data  = (Arr::get($query, 'code') == 200) ? Arr::get($query, 'data') : 'API接口异常,数据请求失败!';

                if (strtolower($data) != 'success') {
                    $logs['remark'] = (is_string($data)) ? $data : json_encode($data);

                    if ($retryNum < $retryTotal) {
                        $retryNum += 1;
                        $remove   = false;

                        $data = [
                            'appKey'     => $appKey,
                            'secretKey'  => $secretKey,
                            'taskNo'     => $taskNo,
                            'linkUrl'    => $linkUrl,
                            'retryNum'   => $retryNum,
                            'retryTotal' => $retryTotal,
                            'step'       => $step,
                            'content'    => $content,
                        ];

                        // 更新任务信息
                        $this->_redis->hSet(Arr::get($this->_queue, 'task'), $taskId, json_encode($data));

                        // 提交到重试队列
                        $step *= $retryNum;
                        $this->_redis->zAdd(Arr::get($this->_queue, 'retry'), [$taskId => time() + $step]);
                    }
                }
            }

            // 任务执行成功删除任务
            if ( ! empty($remove)) {
                $this->_redis->hDel($queueName, $taskId);
            }

            $this->_redis->lPush(Arr::get($this->_queue, 'log'), json_encode($logs));

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 延迟/重试任务
     *
     * @access public
     * @param string $queueName 队列名称
     * @return array
     */
    public function watch($queueName)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($queueName)) {
                throw new \Exception('任务队列名称不能为空!');
            }

            $min = (string)0;
            $max = (string)time();

            $taskIds = $this->_redis->zRangeByScore($queueName, $min, $max);

            if (empty($taskIds)) {
                throw new \Exception('没有待执行的任务!');
            }

            $this->_redis->zRemRangeByScore($queueName, $min, $max);

            foreach ($taskIds as $k => $v) {
                $this->_redis->lPush(Arr::get($this->_queue, 'worker'), $v);
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}