<?php declare(strict_types=1);

namespace App\Model\Logic;

use App\Model\Dao\LogsDao;
use App\Model\Dao\NotifyDao;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Swoft\Redis\Pool;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Stdlib\Helper\ArrayHelper;

/**
 * 异常预警
 *
 * @package App\Model\Logic
 * @Bean()
 */
class NotifyLogic
{
    /**
     * @Inject("redisPool")
     * @var Pool
     */
    private $_redis;

    /**
     * @Inject()
     * @var LogsDao
     */
    private $_logsDao;

    /**
     * @Inject()
     * @var NotifyDao
     */
    private $_notifyDao;

    /**
     * 监听通知队列
     *
     * @access public
     * @return array
     */
    public function monitor()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $taskId = $this->_redis->lPop(config('app.queue.notify'));

            if (empty($taskId)) {
                throw new \Exception('没有需要提醒的记录!');
            }

            $queueName = config('app.queue.task');
            $taskInfo  = $this->_redis->hGet($queueName, $taskId);

            if (empty($taskInfo)) {
                throw new \Exception('任务信息获取失败!');
            }

            $taskInfo = json_decode($taskInfo, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception('任务数据解析失败!');
            }

            $taskNo = ArrayHelper::getValue($taskInfo, 'taskNo');

            if (empty($taskNo)) {
                throw new \Exception('数据格式不正确!');
            }

            $this->_redis->hDel($queueName, $taskId);

            // 邮件提醒
            $email = config('app.notify.email');
            $email = explode(',', $email);

            if ( ! empty($email)) {
                $logs = $this->_logsDao->findByTaskId($taskId);

                $subject = sprintf('GoodJob提醒你:%s任务执行失败!', $taskNo);
                $content = sprintf('<p>执行时间:%s</p><p>提示信息：%s</p>', date('Y-m-d H:i:s', ArrayHelper::getValue($logs, 'created_at')), ArrayHelper::getValue($logs, 'remark'));

                sgo(function () use ($email, $subject, $content) {
                    $this->_sendEmail($email, $subject, $content);
                });
            }

            // 短信提醒
            $mobile = config('app.notify.mobile');
            $mobile = explode(',', $mobile);

            if ( ! empty($mobile)) {
                $message = sprintf('GoodJob提醒你 %s 执行失败!', ArrayHelper::getValue($taskInfo, 'task_no'));

                sgo(function () use ($mobile, $message) {
                    $this->_sendSms($mobile, $message);
                });
            }

            $reciver = array_merge($email, $mobile);
            $reciver = array_filter($reciver);

            sgo(function () use ($reciver, $taskId, $taskNo) {
                $this->_addNotifyLog($reciver, $taskId, $taskNo);
            });

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 发送邮件
     *
     * @access public
     * @param array  $email   邮件地址
     * @param string $subject 邮件主题
     * @param string $content 邮件内容
     * @return array
     */
    private function _sendEmail(array $email, string $subject, string $content)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $mailer = new SmtpMailer([
                                         'host'     => config('app.smtp.host'),
                                         'username' => config('app.smtp.username'),
                                         'password' => config('app.smtp.password'),
                                         'secure'   => 'ssl',
                                     ]);

            $mail = new Message();
            $mail->setFrom(config('app.smtp.username'), config('app.smtp.fromName'));

            foreach ($email as $k => $v) {
                $mail->addTo($v);
            }

            $mail->setSubject($subject);
            $mail->setHTMLBody($content);
            $mailer->send($mail);

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 发送短信
     *
     * @access public
     * @param array  $mobile  手机号码
     * @param string $message 消息内容
     * @return array
     */
    private function _sendSms(array $mobile, string $message)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            // todo

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 添加提醒日志
     *
     * @access private
     * @param array  $reciver 收件人
     * @param string $taskId  任务ID
     * @param string $taskNo  任务编号
     * @return array
     */
    private function _addNotifyLog(array $reciver, string $taskId, string $taskNo)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($reciver)) {
                throw new \Exception('收件人不能为空!');
            }

            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空!');
            }

            $data = [];

            foreach ($reciver as $k => $v) {
                $data[] = [
                    'task_id'    => $taskId,
                    'receiver'   => $v,
                    'retry'      => config('app.retryNum'),
                    'task_no'    => $taskNo,
                    'created_at' => time(),
                ];
            }

            $query = $this->_notifyDao->create($data);

            if (empty($query)) {
                throw new \Exception('提醒日志写入失败!');
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}