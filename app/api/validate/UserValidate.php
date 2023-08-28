<?php


namespace app\api\validate;

use think\Validate;;

class UserValidate extends Validate
{
    protected $rule = [
        // 'real_name' => 'require',
        'nickname' => 'require',
        'gender'   => 'number|between:0,2',
        // 'id_card'  => 'require|idCard',
    ];

    // 定义信息
    protected $message = [
        // 'real_name'     => '真实姓名不能为空',
        'nickname'      => '昵称不能为空',
        'gender.number' => '性别格式错误',
        // 'id_card'        => '有效证件不能为空',
        // 'id_card.idCard' => '有效证件格式错误',
    ];
}
