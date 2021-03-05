<?php declare(strict_types=1);

namespace App\Service;

use App\Model\ApplicationModel;

use Hyperf\Di\Annotation\Inject;

/**
 * Class ApplicationService
 *
 * @package App\Service
 */
class ApplicationService
{
    /**
     * @Inject()
     * @var ApplicationModel
     */
    private $_applicationModel;

    /**
     * 获取应用信息
     *
     * @access public
     * @param string $appKey APP KEY
     * @return array
     */
    public function getApplicationInfo(string $appKey)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($appKey)) {
                throw new \Exception('APP KEY不能为空!');
            }

            $data = redis()->get($appKey);

            if (empty($data)) {
                $data = $this->_applicationModel->findByAppKey($appKey);

                if (empty($data)) {
                    throw new \Exception('APP KEY 输入有误!');
                }

                redis()->set($appKey, $data, 300);
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}