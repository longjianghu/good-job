<?php declare(strict_types=1);

namespace App\Migration;

use Swoft\Db\Schema\Blueprint;
use Swoft\Devtool\Annotation\Mapping\Migration;
use Swoft\Devtool\Migration\Migration as BaseMigration;

/**
 * 拦截记录
 *
 * @package App\Migration
 * @Migration(time=20190901204056,pool="dbJobPool")
 */
class Abort extends BaseMigration
{
    const TABLE = 'abort';

    /**
     * @return void
     */
    public function up(): void
    {
        $this->schema->createIfNotExists(self::TABLE, function (Blueprint $blueprint) {
            $blueprint->increments('id')->comment('自增ID');
            $blueprint->unsignedTinyInteger('is_deleted', false)->default(0)->comment('是否删除');
            $blueprint->char('task_id', 32)->default('')->comment('任务ID');
            $blueprint->unsignedTinyInteger('status', false)->default(0)->comment('拦截状态 0:未知 1:拦截成功');
            $blueprint->unsignedInteger('created_at', false)->default(0)->comment('创建时间');
            $blueprint->unsignedInteger('updated_at', false)->default(0)->comment('更新时间');
            $blueprint->index('task_id', 'task_id');
            $blueprint->engine = 'InnoDB';
            $blueprint->comment('拦截记录');
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
