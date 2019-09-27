<?php declare(strict_types=1);

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Date;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * 投递任务
 *
 * @package App\Validator
 * @Validator(name="PushValidator")
 */
class PushValidator
{
    /**
     * 任务编号
     *
     * @NotEmpty(message="taskNo.NotEmpty")
     * @IsString(message="taskNo.IsString")
     * @var string
     */
    protected $taskNo;

    /**
     * 执行时间
     *
     * @IsString(message="runtime.IsString")
     * @Date(message="runtime.Date")
     * @var string
     */
    protected $runtime;

    /**
     * 任务内容
     *
     * @NotEmpty(message="content.NotEmpty")
     * @IsString(message="content.IsString")
     * @var string
     */
    protected $content;

    /**
     * 初始化.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->runtime = ( ! empty($this->runtime)) ? $this->runtime : date('Y-m-d H:i:s');
    }
}