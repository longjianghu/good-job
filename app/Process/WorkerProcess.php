<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\LogsData;
use App\Model\Logic\TaskLogic;

use Swoole\Process\Pool;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Process\Annotation\Mapping\Process;

/**
 * 任务处理
 *
 * @package App\Process
 * @Process(workerId={0,1,2})
 */
class WorkerProcess implements ProcessInterface
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
     * @param Pool $pool
     * @param int  $workerId
     */
    public function run(Pool $pool, int $workerId): void
    {
        while (true) {
            $this->_taskLogic->worker();
            $this->_logsData->monitor();
        }
    }
}
