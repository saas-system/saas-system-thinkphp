<?php

namespace app\admin\validate;

use think\Validate;

class Tenant extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'name'        => 'require',
        'mobile'      => 'mobile|unique:tenant',
        'expire_time' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name', 'mobile', 'expire_time'],
        'edit' => [],
    ];

    public function __construct()
    {
        $this->field = [
            'name'        => '代理商名称',
            'mobile'      => '手机号',
            'expire_time' => '过期时间',
        ];
        parent::__construct();
    }
}
