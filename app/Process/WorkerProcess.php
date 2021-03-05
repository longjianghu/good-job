<?php declare(strict_types=1);

namespace App\Process;

use App\Data\SendData;

use Hyperf\Utils\Arr;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;

class WorkerProcess extends AbstractProcess
{
    /**
     * @Inject()
     * @var SendData
     */
    private $_sendData;

    /**
     * 初始化.
     *
     * @access public
     * @param ContainerInterface $container 容器接口
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->nums = config('app.workerNum', swoole_cpu_num());
    }

    public function handle(): void
    {
        console()->notice('=== 自定义进程启动 ===');

        while (true) {
            go(function () {
                $result = $this->_sendData->worker();
                $code   = Arr::get($result, 'code');

                if ($code == 100) {
                    logger('WorkerProcess')->info(Arr::get($result, 'message'));
                } elseif ($code == 200) {
                    logger('WorkerProcess')->info(sprintf('%s处理完成！', Arr::get($result, 'data.taskId')));
                }
            });

            usleep(100000);
        }
    }
}
