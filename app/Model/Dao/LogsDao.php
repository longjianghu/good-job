<?php declare(strict_types=1);

namespace App\Model\Dao;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\DB;

/**
 * 任务
 *
 * @package App\Model\Dao
 * @Bean()
 */
class LogsDao
{
    const TABLE = 'logs';
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
     * 查找日志记录
     *
     * @access public
     * @param string $taskId 任务ID
     * @return mixed
     */
    public function findAllByTaskId(string $taskId)
    {
        return Db::query(self::POOL)->from(self::TABLE)->where(['task_id' => $taskId])->get();
    }
}