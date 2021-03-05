<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int            $id
 * @property int            $is_deleted
 * @property int            $task_id
 * @property int            $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TaskAbortModel extends Model
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
    protected $table = 'task_abort';

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
            'status'     => 'integer',
            'created_at' => 'integer',
            'updated_at' => 'integer'
        ];

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

        return Db::table($this->table)->where(['task_id' => $taskId])->update($data);
    }
}