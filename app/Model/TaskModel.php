<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int            $id
 * @property int            $is_deleted
 * @property int            $status
 * @property string         $app_key
 * @property string         $task_no
 * @property int            $step
 * @property int            $runtime
 * @property string         $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TaskModel extends Model
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
    protected $table = 'task';

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
            'status'     => 'integer',
            'step'       => 'integer',
            'runtime'    => 'integer',
            'created_at' => 'integer',
            'updated_at' => 'integer'
        ];

    /**
     * 查询记录
     *
     * @access public
     * @param string $id 任务ID
     * @return array
     */
    public function findById(string $id)
    {
        return Db::table($this->table)->where(['is_deleted' => 0, 'id' => $id])->first();
    }

    /**
     * 查询记录
     *
     * @access public
     * @param string $appKey APP KEY
     * @param string $taskNo 任务编号
     * @return array
     */
    public function findNumByTaskNo(string $appKey, string $taskNo)
    {
        $where = [
            'is_deleted' => 0,
            'app_key'    => $appKey,
            'task_no'    => $taskNo,
            'status'     => 0
        ];

        return Db::table($this->table)->where($where)->count();
    }

    /**
     * 查询待执行的任务
     *
     * @access public
     * @param int $limit 记录数
     * @return array
     */
    public function findPendingTask(int $limit = 100)
    {
        $where = [
            'is_deleted' => 0,
            'status'     => 0,
            ['runtime', '<=', time()]
        ];

        return Db::table($this->table)->where($where)->orderBy('id', 'desc')->limit($limit)->get();
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

        return Db::table($this->table)->where(['id' => $taskId])->update($data);
    }
}