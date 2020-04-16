<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\LogsData;
use App\Model\Logic\TaskLogic;
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
class WorkerProcess extends UserProcess
{
    /**
     * @Inject()
     * @var TaskLogic
     */
    private $_taskLogic;

    /**
     * @Inject()
     * @var LogsData
     */
    private $_logsData;

    /**
     * @param Process $process
     */
    public function run(Process $process): void
    {
        while (true) {
            $this->_taskLogic->worker();
            $this->_logsData->monitor();
        }
    }
}
