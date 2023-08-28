<?php

namespace app\admin\validate;

use think\Validate;

class TenantAdmin extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'nickname' => 'require',
        // 'mobile'   => 'mobile|unique:tenant_admin',
        'username' => 'require|unique:tenant_admin',
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
        'add' => [],
        // 'edit' => ['nickname', 'mobile'],
    ];

    /**
     * 验证场景-编辑资料
     */
    public function sceneEdit()
    {
        return $this->only(['nickname', 'username']);
    }

    public function __construct()
    {
        $this->field   = [
            'nickname' => __('Nickname'),
            'username' => __('Username'),
            'password'  => __('Password'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password')
        ]);
        parent::__construct();
    }
}
