<?php declare(strict_types=1);

namespace App\Data;

use App\Model\TaskModel;
use App\Model\TaskLogModel;
use App\Model\TaskAbortModel;
use App\Service\ApplicationService;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Config\Annotation\Value;

/**
 * Class SendData
 *
 * @package App\Data
 */
class SendData
{
    /**
     * @Value("app.worketQueue")
     */
    private $_workerQueue;

    /**
     * @Value("app.taskQueue")
     */
    private $_taskQueue;

    /**
     * @Value("app.retryQueue")
     */
    private $_retryQueue;

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
    private $_taskAbortModel;

    /**
     * @Inject()
     * @var TaskLogModel
     */
    private $_taskLogModel;

    /**
     * 定时任务
     *
     * @access public
     * @return array
     */
    public function schedule()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $result = $this->_taskModel->findPendingTask();

            if (empty($result)) {
                throw new \Exception('一小时内没有需要执行的任务!');
            }

            foreach ($result as $k => $v) {
                $taskId  = (string)Arr::get($v, 'id');
                $runtime = Arr::get($v, 'runtime');
                $appKey  = Arr::get($v, 'app_key');

                $application = $this->_applicationService->getApplicationInfo($appKey);

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

                redis()->hSetNx($this->_taskQueue, $taskId, json_encode($data));
                $delay = $runtime - time();

                if ($delay > 0) { // 延迟任务
                    redis()->zAdd($this->_delayQueue, [$taskId => $runtime]);
                } else { // 立即执行
                    redis()->lPush($this->_workerQueue, $taskId);
                }

                // 更新任务状态为处理中
                $this->_taskModel->updateTaskStatus($taskId, 1);
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 任务执行Worker
     *
     * @access public
     * @return array
     */
    public function send()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $taskId = redis()->lPop($this->_workerQueue);

            if (empty($taskId)) {
                throw new \Exception('没有需要执行的任务!');
            }

            $taskId = (string)$taskId;
            $task   = redis()->hGet($this->_taskQueue, $taskId);

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

            $logs = [
                'task_id'    => $taskId,
                'retry'      => $retryNum,
                'remark'     => 'success',
                'created_at' => time()
            ];

            $abort = redis()->get($taskId);

            // 是否删除任务数据
            $remove = true;

            if ( ! empty($abort)) { // 系统拦截
                $logs['remark'] = '系统拦截';

                $this->_taskAbortModel->updateTaskStatus($taskId, 1);
            } else { // 未被拦截
                $header = [
                    'app-key'   => $appKey,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'nonce-str' => Str::random(10),
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

                $header['signature'] = md5($str.$secretKey);

                // 发送请求
                $query = sendRequest($linkUrl, $data, $header, 'POST');
                $query = (Arr::get($query, 'code') == 200) ? Arr::get($query, 'data') : 'API接口异常,数据请求失败!';

                $logs['remark'] = (is_string($query)) ? $query : json_encode($query);

                if (strtolower($query) != 'success') {
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
                        redis()->hSet($this->_taskQueue, $taskId, json_encode($data));

                        // 提交到重试队列
                        $step *= $retryNum;
                        redis()->zAdd($this->_retryQueue, [$taskId => time() + $step]);
                    }
                }
            }

            // 任务执行成功删除任务
            if ( ! empty($remove)) {
                redis()->hDel($this->_taskQueue, $taskId);

                $query = $this->_taskModel->updateTaskStatus($taskId, 2);

                if (empty($query)) {
                    throw new \Exception('任务状态更新失败!');
                }
            }

            // 添加日志
            $query = $this->_taskLogModel->insertGetId($logs);

            if (empty($query)) {
                throw new \Exception('日志添加失败!');
            }

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

            $start = (string)0;
            $end   = (string)time();

            $taskIds = redis()->zRangeByScore($queueName, $start, $end);

            if (empty($taskIds)) {
                throw new \Exception('没有待执行的任务!');
            }

            foreach ($taskIds as $k => $v) {
                redis()->lPush($this->_workerQueue, $v);
            }

            // 移除对应的任务
            redis()->zRemRangeByScore($queueName, $start, $end);

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}