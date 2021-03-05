<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class ApplicationRequest extends FormRequest
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
            'appName'    => 'required',
            'step'       => 'required|numeric|between:1,100',
            'retryTotal' => 'required|numeric|between:1,100',
            'linkUrl'    => 'required|url',
            'remark'     => 'required',
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
            'appName'    => '应用名称',
            'step'       => '步长值',
            'retryTotal' => '重试次数',
            'linkUrl'    => '接口地址',
            'remark'     => '应用说明',
        ];
    }
}
