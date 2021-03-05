<?php declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * 是否验证
     *
     * @access public
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 验证规则
     *
     * @access public
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'taskId'  => 'required',
            'taskNo'  => 'required',
            'runtime' => 'nullable|date',
            'content' => 'required',
        ];
    }

    /**
     * 字段名称
     *
     * @access public
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'taskId'  => '任务ID',
            'taskNo'  => '任务编号',
            'runtime' => '运行时间',
            'content' => '任务内容',
        ];
    }
}
