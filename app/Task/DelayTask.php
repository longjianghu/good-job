<?php declare(strict_types=1);

namespace App\Task;

use App\Data\SendData;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Config\Annotation\Value;
use Hyperf\Crontab\Annotation\Crontab;

/**
 * 延迟任务
 *
 * @access  public
 * @Crontab(name="DelayTask", rule="* * * * * *", callback="execute")
 * @package App\Task
 */
class DelayTask
{
    /**
     * @Value("app.delayQueue")
     */
    private $_delayQueue;

    /**
     * @Value("app.retryQueue")
     */
    private $_retryQueue;

    /**
     * @Inject()
     * @var SendData
     */
    private $_sendData;

    /**
     * 每秒钟一次
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $this->_sendData->monitor($this->_delayQueue);
        $this->_sendData->monitor($this->_retryQueue);
    }
}
