<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\LogsData;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Process\Annotation\Mapping\Process;
use Swoole\Process\Pool;

/**
 * 日志处理
 *
 * @package App\Process
 * @Process(workerId=0)
 */
class LogsProcess implements ProcessInterface
{
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
            $this->_logsData->create();
        }
    }
}