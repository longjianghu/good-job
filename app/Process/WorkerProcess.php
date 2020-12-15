<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Logic\TaskLogic;

use Swoft\Co;
use Swoole\Process\Pool;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Process\Annotation\Mapping\Process;

/**
 * WorkerProcess
 *
 * @since 2.0
 * @Process()
 */
class WorkerProcess implements ProcessInterface
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
     * @param Pool $pool
     * @param int  $workerId
     */
    public function run(Pool $pool, int $workerId): void
    {
        while (true) {
            for ($i = 0; $i < $this->_workerNum; $i++) {
                $this->_taskLogic->worker();
            }

            Co::sleep(1);
        }
    }
}