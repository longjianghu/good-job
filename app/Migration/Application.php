<?php declare(strict_types=1);

namespace App\Migration;

use Swoft\Db\Schema\Blueprint;
use Swoft\Devtool\Annotation\Mapping\Migration;
use Swoft\Devtool\Migration\Migration as BaseMigration;

/**
 * 应用管理
 *
 * @package App\Migration
 * @Migration(time=20190901204056,pool="dbJobPool")
 */
class Application extends BaseMigration
{
    const TABLE = 'application';

    /**
     * @return void
     */
    public function up(): void
    {
        $this->schema->createIfNotExists(self::TABLE, function (Blueprint $blueprint) {
            $blueprint->increments('id')->comment('自增ID');
            $blueprint->unsignedTinyInteger('is_deleted', false)->default(0)->comment('是否删除');
            $blueprint->string('app_name', 100)->default('')->comment('应用名称');
            $blueprint->char('app_key', 16)->default('')->comment('APP KEY');
            $blueprint->char('secret_key', 32)->default('')->comment('SECRET KEY');
            $blueprint->unsignedTinyInteger('step', false, true)->default(0)->comment('重试间隔(秒)');
            $blueprint->unsignedTinyInteger('retry_total', false, true)->default(0)->comment('重试次数');
            $blueprint->string('mobile', 20)->default('')->comment('手机号码');
            $blueprint->string('email', 100)->default('')->comment('Email');
            $blueprint->string('link_url', 200)->default('')->comment('接口地址');
            $blueprint->string('remark', 255)->default('')->comment('备注信息');
            $blueprint->unsignedInteger('created_at', false)->default(0)->comment('创建时间');
            $blueprint->unsignedInteger('updated_at', false)->default(0)->comment('更新时间');
            $blueprint->unique('app_key', 'app_key');
            $blueprint->engine = 'InnoDB';
            $blueprint->comment('工作任务');
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
