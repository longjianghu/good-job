<?php declare(strict_types=1);

namespace App\Task;

use App\Data\SendData;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Crontab\Annotation\Crontab;

/**
 * 定时任务
 *
 * @access  public
 * @Crontab(name="ScheduleTask", rule="0\/1 * * * *", callback="execute")
 * @package App\Task
 */
class ScheduleTask
{
    /**
     * @Inject()
     * @var SendData
     */
    private $_sendData;

    /**
     * 每分钟一次
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $this->_sendData->schedule();
    }
}
