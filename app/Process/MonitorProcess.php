<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\LogsData;
use App\Model\Logic\TaskLogic;

use Swoft\Co;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * MonitorProcess
 *
 * @since 2.0
 * @Bean()
 */
class MonitorProcess extends UserProcess
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
        $cpuNum = swoole_cpu_num();

        while (true) {
            for ($i = 0; $i < $cpuNum; $i++) {
                $this->_taskLogic->worker();
                $this->_logsData->monitor();
            }

            Co::sleep(1);
        }
    }
}