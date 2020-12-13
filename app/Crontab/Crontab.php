<?php declare(strict_types=1);

namespace App\Crontab;

use App\Model\Data\TaskData;
use App\Model\Logic\TaskLogic;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Crontab\Annotaion\Mapping\Cron;
use Swoft\Config\Annotation\Mapping\Config;
use Swoft\Crontab\Annotaion\Mapping\Scheduled;

/**
 * 定时任务
 *
 * @package App\Crontab
 * @Scheduled()
 */
class Crontab
{
    /**
     * @Config("app.queue.delay")
     */
    private $_delayQueue;

    /**
     * @Config("app.queue.retry")
     */
    private $_retryQueue;

    /**
     * @Inject()
     * @var TaskLogic
     */
    private $_taskLogic;

    /**
     * @Inject()
     * @var TaskData
     */
    private $_taskData;

    /**
     * 延迟/重试任务
     *
     * @Cron("* * * * * *")
     */
    public function monitor()
    {
        $this->_taskLogic->watch($this->_delayQueue);
        $this->_taskLogic->watch($this->_retryQueue);
    }

    /**
     * 定时任务
     *
     * @Cron("0 * * * * *")
     */
    public function scheduled()
    {
        $this->_taskData->scheduled();
    }
}