<?php declare(strict_types=1);

namespace App\Model\Logic;

use App\Model\Dao\AbortDao;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Stdlib\Helper\ArrayHelper;
use Swoft\Redis\Pool;

/**
 * 任务处理
 *
 * @package App\Model\Logic
 * @Bean()
 */
class TaskLogic
{
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
     * 执行任务
     *
     * @access public
     * @return array
     */
    public function worker()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $taskId = $this->_redis->lPop(config('queue.worker'));

            if (empty($taskId)) {
                throw new \Exception('没有需要执行的任务！');
            }

            $task = $this->_redis->hGet(config('queue.task'), $taskId);

            if (empty($task)) {
                throw new \Exception('任务信息获取失败！');
            }

            $task = json_decode($task, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception('任务数据解析失败！');
            }

            sgo(function () use ($task, $taskId) {
                $appKey    = ArrayHelper::getValue($task, 'appKey');
                $secretKey = ArrayHelper::getValue($task, 'secretKey');
                $linkUrl   = ArrayHelper::getValue($task, 'linkUrl');
                $step      = (int)ArrayHelper::getValue($task, 'step');
                $content   = ArrayHelper::getValue($task, 'content');

                $retry = (int)ArrayHelper::getValue($task, 'retry');
                $retry += 1;

                $logs  = ['taskId' => $taskId, 'retry' => $retry, 'remark' => '任务执行成功!', 'created_at' => time()];
                $abort = $this->_redis->get($taskId);

                if ( ! empty($abort)) { // 系统拦截
                    $logs['remark'] = '系统拦截';

                    $this->_abortDao->updateTaskStatus($taskId, 1);
                    $this->_redis->set($taskId, 1, 0);
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
                    $signature = array_merge($header, $data);
                    $signature = array_filter($signature);

                    ksort($signature);

                    $str = http_build_query($signature, '', '&');
                    $str = urldecode($str);

                    $header['signature'] = md5(md5($str).ArrayHelper::getValue($task, 'secretKey'));

                    // 发送请求
                    $query = send($linkUrl, $data, $header);
                    $data  = (ArrayHelper::getValue($query, 'code') == 200) ? ArrayHelper::getValue($query, 'data') : 'APP接口异常,数据请求失败！';

                    if (strtolower($data) != 'sucess') {
                        $logs['remark'] = (is_string($data)) ? $data : json_encode($data);

                        $retryNum = config('app.retryNum');

                        if ($retry < $retryNum) {
                            $data = [
                                'appKey'    => $appKey,
                                'secretKey' => $secretKey,
                                'taskNo'    => ArrayHelper::getValue($task, 'taskNo'),
                                'linkUrl'   => $linkUrl,
                                'retry'     => $retry,
                                'step'      => $step,
                                'content'   => $content,
                            ];

                            // 更新任务信息
                            $this->_redis->hSet(config('queue.task'), $taskId, json_encode($data));

                            // 提交到重试队列
                            $step *= $retry;
                            $this->_redis->zAdd(config('queue.retry'), [$taskId => time() + $step]);
                        } else {
                            // 重试次数为0时提交预警信息
                            $this->_redis->lPush(config('queue.notify'), $taskId);
                        }
                    } else {
                        // 任务执行成功删除任务
                        $this->_redis->hDel(config('queue.task'), $taskId);
                    }
                }

                $this->_redis->lPush(config('queue.log'), json_encode($logs));
            });

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
    public function monitor($queueName)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($queueName)) {
                throw new \Exception('任务队列名称不能为空！');
            }

            $min = (string)0;
            $max = (string)time();

            $taskIds = $this->_redis->zRangeByScore($queueName, $min, $max);

            if (empty($taskIds)) {
                throw new \Exception('没有待执行的任务！');
            }

            $this->_redis->zRemRangeByScore($queueName, $min, $max);

            foreach ($taskIds as $k => $v) {
                $this->_redis->lPush(config('queue.worker'), $v);
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}