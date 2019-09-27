<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Logic\TaskLogic;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Process\Annotation\Mapping\Process;
use Swoole\Process\Pool;

/**
 * 任务处理
 *
 * @package App\Process
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
        }
    }
}
