<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Logic\NotifyLogic;

use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * 异常预警
 *
 * @package App\Process
 * @Bean()
 */
class NotifyProcess extends UserProcess
{
    /**
     * @Inject()
     * @var NotifyLogic
     */
    private $_notifylogic;

    /**
     * @param Process $process
     */
    public function run(Process $process): void
    {
        while (true) {
            $this->_notifylogic->monitor();
        }
    }
}
