<?php

namespace app\common\model;

use app\tenant\library\traits\PermissionTrait;
use think\Model;

class Base extends Model
{
    // 引入权限过滤类
    use PermissionTrait;

    // 平台类型
    const PLATFORM_WX_OFFICIAL_ACCOUNT = 'wxOfficialAccount'; // 微信公众号
    const PLATFORM_WX_MINI_PROGRAM     = 'wxMiniProgram'; // 微信小程序

    // 服务提供者
    const PROVIDER_WECHAT = 'wechat'; // 微信
    const PROVIDER_ALIPAY = 'alipay'; // 支付宝

}
