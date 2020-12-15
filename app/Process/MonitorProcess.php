<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Logic\TaskLogic;

use Swoft\Co;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * MonitorProcess
 *
 * @since 2.0
 * @Bean()
 */
class MonitorProcess extends UserProcess
{
    /**
     * @Config("app.minWorkerNum")
     */
    private $_workerNum;

    /**
     * @Inject()
     * @var TaskLogic
     */
    private $_taskLogic;

    /**
     * @param Process $process
     */
    public function run(Process $process): void
    {
        while (true) {
            for ($i = 0; $i < $this->_workerNum; $i++) {
                $this->_taskLogic->worker();
            }

            Co::sleep(1);
        }
    }
}