<?php

namespace app\tenant\validate;

use think\Validate;

class MenuRule extends Validate
{
    protected $failException = true;

    protected $rule = [
        'type'  => 'require',
        'title' => 'require',
        'name'  => 'require|unique:tenant_menu_rule',
    ];

    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['type', 'title', 'name'],
        'edit' => ['type', 'title', 'name'],
    ];

    public function __construct()
    {
        $this->field = [
            'type'  => __('type'),
            'title' => __('title'),
            'name'  => __('name'),
        ];
        parent::__construct();
    }
}
