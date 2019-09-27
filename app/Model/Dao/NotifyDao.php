<?php declare(strict_types=1);

namespace App\Model\Dao;

use Swoft\Db\DB;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 任务提醒
 *
 * @package App\Model\Dao
 * @Bean()
 */
class NotifyDao
{
    const TABLE = 'notify';
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
        return Db::query(self::POOL)->from(self::TABLE)->insert($data);
    }
}