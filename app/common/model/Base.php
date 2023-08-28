<?php

namespace app\common\model;

use app\tenant\library\traits\PermissionTrait;
use think\Model;

class Base extends Model
{
    // 引入权限过滤类
    use PermissionTrait;

    // 支付类型
    const PAY_TYPE_INTEGRAL          = 1; // 参赛积分
    const PAY_TYPE_COMPETITIVE_POINT = 2; // 竞技点
    const PAY_TYPE_COUPON            = 3; // 卡券
    const PAY_TYPE_MASTER_SCORE      = 4; // 大师分
    const PAY_TYPE_BALANCE           = 5; // 余额
    const PAY_TYPE_CASH              = 6; // 现金
    const PAY_TYPE_BANK_CARD         = 7; // 银行卡
    const PAY_TYPE_WECHAT            = 8; // 微信支付
    const PAY_TYPE_ALIPAY            = 9; // 支付宝


    // 平台类型
    const PLATFORM_WX_OFFICIAL_ACCOUNT = 'wxOfficialAccount'; // 微信公众号
    const PLATFORM_WX_MINI_PROGRAM     = 'wxMiniProgram'; // 微信小程序

    // 服务提供者
    const PROVIDER_WECHAT = 'wechat'; // 微信
    const PROVIDER_ALIPAY = 'alipay'; // 支付宝

    /**
     * 获取支付类型text
     *
     * @param $payType
     * @return string
     */
    public static function getPayTypeText($payType): string
    {
        $arr = [
            static::PAY_TYPE_INTEGRAL          => '参赛积分',
            static::PAY_TYPE_COMPETITIVE_POINT => '竞技点',
            static::PAY_TYPE_COUPON            => '卡券',
            static::PAY_TYPE_MASTER_SCORE      => '大师分',
            static::PAY_TYPE_BALANCE           => '余额',
            static::PAY_TYPE_CASH              => '现金',
        ];

        return $arr[$payType] ?? '';
    }


}
