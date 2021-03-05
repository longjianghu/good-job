<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int            $id
 * @property int            $is_deleted
 * @property string         $app_name
 * @property string         $app_key
 * @property string         $secret_key
 * @property int            $step
 * @property int            $retry_total
 * @property string         $mobile
 * @property string         $email
 * @property string         $link_url
 * @property string         $remark
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ApplicationModel extends Model
{
    /**
     * 自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 日期格式
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * 数据表名.
     *
     * @var string
     */
    protected $table = 'application';

    /**
     * 允许批量赋值.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * 数据格式化.
     *
     * @var array
     */
    protected $casts
        = [
            'id'          => 'integer',
            'is_deleted'  => 'integer',
            'step'        => 'integer',
            'retry_total' => 'integer',
            'created_at'  => 'integer',
            'updated_at'  => 'integer'
        ];

    /**
     * 查找记录
     *
     * @access public
     * @param string $appKey APP KEY
     * @return mixed
     */
    public function findByAppKey(string $appKey)
    {
        $where = ['is_deleted' => 0, 'app_key' => $appKey, 'status' => 1];

        return Db::table($this->table)->where($where)->first();
    }
}