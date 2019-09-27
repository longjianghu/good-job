<?php
/**
 * 表单验证语言包
 */

use Swoft\Stdlib\Helper\ArrayHelper;

$erroMessage = [
    'NotEmpty' => '%s不能为空',
    'IsString' => '%s不能为空',
    'IsInt'    => '%s必须是数字',
    'Min'      => '%s最小值为%s',
    'Max'      => '%s最大值为%s',
    'Date'     => '%s格式不正确',
];

$labelName = [
    'appName' => '应用名称',
    'retry'   => '重试次数',
    'step'    => '重新间隔',
    'linkUrl' => '接口地址',
    'remark'  => '备注信息',
    'taskNo'  => '任务编号',
    'runtime' => '运行时间',
    'content' => '任务内容',
    'taskId'  => '任务ID',
];

return [
    'appName' => [
        'NotEmpty' => sprintf(ArrayHelper::getValue($erroMessage, 'NotEmpty'), ArrayHelper::getValue($labelName, 'appName')),
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'appName')),
    ],
    'retry'   => [
        'IsInt' => sprintf(ArrayHelper::getValue($erroMessage, 'step'), ArrayHelper::getValue($labelName, 'retry')),
        'Min'   => sprintf(ArrayHelper::getValue($erroMessage, 'Min'), ArrayHelper::getValue($labelName, 'retry'), 0),
        'Max'   => sprintf(ArrayHelper::getValue($erroMessage, 'Max'), ArrayHelper::getValue($labelName, 'retry'), 10),
    ],
    'step'    => [
        'IsInt' => sprintf(ArrayHelper::getValue($erroMessage, 'step'), ArrayHelper::getValue($labelName, 'step')),
        'Min'   => sprintf(ArrayHelper::getValue($erroMessage, 'Min'), ArrayHelper::getValue($labelName, 'step'), 0),
        'Max'   => sprintf(ArrayHelper::getValue($erroMessage, 'Max'), ArrayHelper::getValue($labelName, 'step'), 3600),
    ],
    'linkUrl' => [
        'NotEmpty' => sprintf(ArrayHelper::getValue($erroMessage, 'NotEmpty'), ArrayHelper::getValue($labelName, 'linkUrl')),
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'linkUrl')),
    ],
    'remark'  => [
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'remark')),
    ],
    'taskNo'  => [
        'NotEmpty' => sprintf(ArrayHelper::getValue($erroMessage, 'NotEmpty'), ArrayHelper::getValue($labelName, 'taskNo')),
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'taskNo')),
    ],
    'runtime' => [
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'runtime')),
        'Date'     => sprintf(ArrayHelper::getValue($erroMessage, 'Date'), ArrayHelper::getValue($labelName, 'runtime')),
    ],
    'content' => [
        'NotEmpty' => sprintf(ArrayHelper::getValue($erroMessage, 'NotEmpty'), ArrayHelper::getValue($labelName, 'content')),
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'content')),
    ],
    'content' => [
        'NotEmpty' => sprintf(ArrayHelper::getValue($erroMessage, 'NotEmpty'), ArrayHelper::getValue($labelName, 'taskId')),
        'IsString' => sprintf(ArrayHelper::getValue($erroMessage, 'IsString'), ArrayHelper::getValue($labelName, 'taskId')),
    ],
];

