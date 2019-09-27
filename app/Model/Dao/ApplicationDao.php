<?php declare(strict_types=1);

namespace App\Model\Dao;

use Swoft\Db\DB;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 应用管理
 *
 * @package App\Model\Dao
 * @Bean()
 */
class ApplicationDao
{
    const TABLE = 'application';
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
     * @param string $appKey APP KEY
     * @return array
     */
    public function findByAppKey(string $appKey)
    {
        return Db::query(self::POOL)->from(self::TABLE)->where(['is_deleted' => 0, 'app_key' => $appKey])->first();
    }
}