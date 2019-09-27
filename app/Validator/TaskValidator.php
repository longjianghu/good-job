<?php declare(strict_types=1);

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Max;
use Swoft\Validator\Annotation\Mapping\Min;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * 创建任务
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
     * @var string
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
     * 接口地址
     *
     * @NotEmpty(message="linkUrl.NotEmpty")
     * @IsString(message="linkUrl.IsString")
     * @var string
     */
    protected $linkUrl;

    /**
     * 备注信息
     *
     * @IsString(message="remark.IsString")
     * @var string
     */
    protected $remark = '';
}