<?php declare(strict_types=1);

namespace App\Model\Dao;

use Swoft\Db\DB;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 任务详情
 *
 * @package App\Model\Dao
 * @Bean()
 */
class TaskDao
{
    const TABLE = 'task';
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
     * 查询记录
     *
     * @access public
     * @param string $id 任务ID
     * @return array
     */
    public function findById(string $id)
    {
        return Db::query(self::POOL)->from(self::TABLE)->where(['is_deleted' => 0, 'id' => $id])->first();
    }

    /**
     * 查询待执行的任务
     *
     * @access public
     * @return array
     */
    public function findPendingTask()
    {
        $where = [
            'is_deleted' => 0,
            'status'     => 0,
            ['runtime', '<=', time()]
        ];

        return Db::query(self::POOL)->from(self::TABLE)->where($where)->get();
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

        return Db::query(self::POOL)->from(self::TABLE)->where(['id' => $taskId])->update($data);
    }
}