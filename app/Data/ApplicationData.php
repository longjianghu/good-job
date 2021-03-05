<?php declare(strict_types=1);

namespace App\Data;

use App\Model\ApplicationModel;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Config\Annotation\Value;

/**
 * Class ApplicationData
 *
 * @package App\Data
 */
class ApplicationData
{
    /**
     * @Value("app.retryTotal")
     */
    private $_retryTotal;

    /**
     * @Inject()
     * @var ApplicationModel
     */
    private $_applicationModel;

    /**
     * 创建任务
     *
     * @access public
     * @param array $request POST数据
     * @return array
     */
    public function create(array $request)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($request)) {
                throw new \Exception('提交数据不能为空!');
            }

            $appKey    = Str::random(16);
            $secretKey = Str::random(32);

            $data = [
                'status'      => 1,
                'app_key'     => $appKey,
                'app_name'    => Arr::get($request, 'appName'),
                'secret_key'  => $secretKey,
                'step'        => (int)Arr::get($request, 'step', 0),
                'retry_total' => (int)Arr::get($request, 'retryTotal', $this->_retryTotal),
                'link_url'    => Arr::get($request, 'linkUrl'),
                'remark'      => Arr::get($request, 'remark'),
                'created_at'  => time()
            ];

            $query = $this->_applicationModel->insertGetId($data);

            if (empty($query)) {
                throw new \Exception('任务添加失败!');
            }

            $status = [
                'code'    => 200,
                'data'    => ['appKey' => $appKey, 'secretKey' => $secretKey],
                'message' => ''
            ];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}