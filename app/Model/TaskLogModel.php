<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int            $id
 * @property int            $is_deleted
 * @property int            $task_id
 * @property int            $retry
 * @property string         $remark
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TaskLogModel extends Model
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
    protected $table = 'task_log';

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
            'id'         => 'integer',
            'is_deleted' => 'integer',
            'task_id'    => 'integer',
            'retry'      => 'integer',
            'created_at' => 'integer',
            'updated_at' => 'integer'
        ];

    /**
     * 查找日志记录
     *
     * @access public
     * @param string $taskId 任务ID
     * @return mixed
     */
    public function findAllByTaskId(string $taskId)
    {
        return Db::table($this->table)->where(['task_id' => $taskId])->get();
    }
}