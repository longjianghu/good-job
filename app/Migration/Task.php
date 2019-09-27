<?php declare(strict_types=1);

namespace App\Migration;

use Swoft\Db\Schema\Blueprint;
use Swoft\Devtool\Annotation\Mapping\Migration;
use Swoft\Devtool\Migration\Migration as BaseMigration;

/**
 * 任务列表
 *
 * @package App\Migration
 * @Migration(time=20190901204056,pool="dbJobPool")
 */
class Task extends BaseMigration
{
    const TABLE = 'task';

    /**
     * @return void
     */
    public function up(): void
    {
        $this->schema->createIfNotExists(self::TABLE, function (Blueprint $blueprint) {
            $blueprint->increments('id')->comment('自增ID');
            $blueprint->unsignedTinyInteger('is_deleted', false)->default(0)->comment('是否删除');
            $blueprint->char('task_id', 32)->default('')->comment('任务ID');
            $blueprint->char('app_key', 32)->default('')->comment('APP KEY');
            $blueprint->string('task_no', 50)->default('')->comment('任务编号');
            $blueprint->unsignedTinyInteger('status', false)->default(0)->comment('任务状态 0:待处理 1:处理中 2:已处理 3:已取消');
            $blueprint->unsignedTinyInteger('step', false, true)->default(0)->comment('重试间隔(秒)');
            $blueprint->integer('runtime', false, true)->default(0)->comment('执行时间');
            $blueprint->longText('content')->comment('任务内容');
            $blueprint->unsignedInteger('created_at', false)->default(0)->comment('创建时间');
            $blueprint->unsignedInteger('updated_at', false)->default(0)->comment('更新时间');
            $blueprint->unique('task_id', 'task_id');
            $blueprint->index(['app_key', 'task_no'], 'task_no');
            $blueprint->engine = 'InnoDB';
            $blueprint->comment('任务列表');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->schema->dropIfExists(self::TABLE);
    }
}
