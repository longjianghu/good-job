<?php declare(strict_types=1);

namespace App\Model\Dao;

use Swoft\Db\DB;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 取消任务
 *
 * @package App\Model\Dao
 * @Bean()
 */
class AbortDao
{
    const TABLE = 'abort';
    const POOL  = 'dbJobPool';

    /**
     * 写入记录
     *
     * @access public
     * @param array $data 写入数据
     * @return int
     */
    public function create(array $data)
    {
        return Db::query(self::POOL)->from(self::TABLE)->insertGetId($data);
    }

    /**
     * 查找记录详情
     *
     * @access public
     * @param string $taskId 任务ID
     * @return mixed
     */
    public function findByTaskId(string $taskId)
    {
        return Db::query(self::POOL)->from(self::TABLE)->where(['task_id' => $taskId])->orderBy('id', 'desc')->first();
    }

    /**
     * 更新任务状态
     *
     * @access public
     * @param string $taskId 任务ID
     * @param int    $status 任务状态
     * @return mixed
     */
    public function updateTaskStatus(string $taskId, int $status)
    {
        $data = ['status' => $status, 'updated_at' => time()];

        return Db::query(self::POOL)->from(self::TABLE)->where(['task_id' => $taskId])->update($data);
    }
}