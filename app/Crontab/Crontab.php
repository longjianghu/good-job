<?php declare(strict_types=1);

namespace App\Crontab;

use App\Model\Data\TaskData;
use App\Model\Logic\TaskLogic;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Crontab\Annotaion\Mapping\Cron;
use Swoft\Crontab\Annotaion\Mapping\Scheduled;
use Swoft\Log\Helper\CLog;

/**
 * 定时任务
 *
 * @package App\Crontab
 * @Scheduled()
 */
class Crontab
{
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
    public function watchTask()
    {
        $this->_taskLogic->monitor(config('app.queue.delay'));
        $this->_taskLogic->monitor(config('app.queue.retry'));
    }

    /**
     * 定时任务
     *
     * @Cron("0 0 * * * *")
     */
    public function pullTask()
    {
        $this->_taskData->addTask();
    }
}