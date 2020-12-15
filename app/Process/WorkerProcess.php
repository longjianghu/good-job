<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Logic\TaskLogic;

use Swoft\Co;
use Swoole\Process\Pool;
use Swoft\Process\Annotation\Mapping\Process;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * WorkerProcess
 *
 * @since 2.0
 * @Process()
 */
class WorkerProcess implements ProcessInterface
{
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
            $this->_taskLogic->worker();

            Co::sleep(1);
        }
    }
}