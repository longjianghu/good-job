<?php declare(strict_types=1);

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Date;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Required;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * 其它验证
 *
 * @package App\Validator
 * @Validator(name="OtherValidator")
 */
class OtherValidator
{
    /**
     * 任务ID
     *
     * @NotEmpty(message="taskId.NotEmpty")
     * @IsString(message="taskId.IsString")
     * @Required()
     */
    protected $taskId;
}