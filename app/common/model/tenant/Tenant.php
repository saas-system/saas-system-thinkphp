<?php

namespace app\common\model\tenant;

use app\common\model\Base;
use think\model\concern\SoftDelete;

class Tenant extends Base
{
    use SoftDelete;

    /**
     * 与模型关联的表名
     * @var string
     */
    protected $name = 'tenant';

    /**
     * 隐藏属性
     * @var array
     */
    protected $hidden = ['delete_time', 'update_time'];

    /**
     * 追加字段
     * @var array
     */
    protected $append = [];

    /**
     * @param $tenantId
     * @return boolean|string
     */
    public static function checkTenantStatus($tenantId)
    {
        $info = Tenant::where('id', $tenantId)->field('id,status,expire_time')->find();

        if (!$info) {
            return '租户不存在';
        }

        if ($info->status != 1) {
            return '租户已禁用';
        }

        if ($info->expire_time && $info->expire_time < time()) {
            return '租户已过期';
        }

        return true;
    }

}
