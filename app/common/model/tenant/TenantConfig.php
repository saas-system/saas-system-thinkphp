<?php

namespace app\common\model\tenant;


use app\common\model\Base;

class TenantConfig extends Base
{
    /**
     * 与模型关联的表名
     * @var string
     */
    protected $name = 'tenant_config';

    /**
     * 隐藏属性
     * @var array
     */
    protected $hidden = ['update_time'];

    /**
     * 追加字段
     * @var array
     */
    protected $append = [];

    public function setRemindAdminIdsAttr($val, $data)
    {
        return $val ? implode(',', $val) : '';
    }

    public function getRemindAdminIdsAttr($val, $data)
    {
        return $val ? explode(',', $val) : [];
    }

    /**
     * 获取租户ID
     *
     * @param $miniAppId
     * @param $unlockCode
     * @return mixed|null
     */
    public static function getTenantIdByAppId($miniAppId = '', $unlockCode = '')
    {
        $tenantId = null;
        if ($miniAppId) {
            $tenantId = TenantConfig::getTenantIdByMiniAppId($miniAppId);
        } elseif ($unlockCode) {
            $tenantId = TenantConfig::getTenantIdByTvUnlockCode($unlockCode);
        }
        return $tenantId;
    }

    /**
     * 通过小程序ID获取租户ID
     *
     * @param $miniAppId
     * @return mixed
     */
    public static function getTenantIdByMiniAppId($miniAppId)
    {
        return TenantConfig::where('mini_app_id', $miniAppId)->value('tenant_id');
    }

    /**
     * 获取租户前缀名称
     *
     * @param $tenantId
     * @return mixed
     */
    public static function getTenantPreByTenantId($tenantId)
    {
        return TenantConfig::where('tenant_id', $tenantId)->value('tenant_pre');
    }

    /**
     * 通过uniappId 获取租户ID
     *
     * @param $uniAppId
     * @return mixed
     */
    public static function getTenantIdByTvUnlockCode($uniAppId)
    {
        return TenantConfig::where('unlock_code', $uniAppId)->value('tenant_id');
    }

    /**
     * 关联的租户ID
     *
     * @return \think\model\relation\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

}
