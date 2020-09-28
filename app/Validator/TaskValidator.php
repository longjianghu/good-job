<?php declare(strict_types=1);

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Max;
use Swoft\Validator\Annotation\Mapping\Min;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Required;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * 任务验证器
 *
 * @package App\Validator
 * @Validator(name="TaskValidator")
 */
class TaskValidator
{
    /**
     * 任务名称
     *
     * @NotEmpty(message="appName.NotEmpty")
     * @IsString(message="appName.IsString")
     * @Required()
     */
    protected $appName;

    /**
     * 重试间隔
     *
     * @IsInt(message="step.IsInt")
     * @Min(value=0,message="step.Min")
     * @Max(value=3600,message="step.Max")
     * @var int
     */
    protected $step = 0;

    /**
     * 重试间隔
     *
     * @IsInt(message="retryTotal.IsInt")
     * @Min(value=0,message="retryTotal.Min")
     * @Max(value=10,message="retryTotal.Max")
     * @var int
     */
    protected $retryTotal = 0;

    /**
     * 接口地址
     *
     * @NotEmpty(message="linkUrl.NotEmpty")
     * @IsString(message="linkUrl.IsString")
     * @Required()
     */
    protected $linkUrl;

    /**
     * 备注信息
     *
     * @IsString(message="remark.IsString")
     * @var string
     */
    protected $remark = '';

    /**
     * 任务ID
     *
     * @NotEmpty(message="taskId.NotEmpty")
     * @IsString(message="taskId.IsString")
     * @Required()
     */
    protected $taskId;
}