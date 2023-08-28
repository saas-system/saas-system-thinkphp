<?php


namespace app\api\validate;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'pay_type' => 'require',
        'buy_num'  => 'require|number',
        'goods_id' => 'require',
    ];

    // 定义信息
    protected $message = [
        'pay_type.require' => '支付类型不能为空',
        'buy_num.require'  => '商品数量不能为空',
        'buy_num.number'   => '商品数量格式错误',
        'goods_id.require' => '商品ID不能为空',
    ];
}
