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

    /**
     * 执行任务
     *
     * @access public
     * @return void
     */
    public function handle(): void
    {
        console()->notice('=== 自定义进程启动 ===');

        while (true) {
            go(function () {
                $this->_sendData->send();
            });

            usleep(100000);
        }
    }
}
