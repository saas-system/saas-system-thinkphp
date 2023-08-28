<?php

namespace app\tenant\model;

use app\common\model\tenant\TenantConfig;
use think\facade\Config;
use think\Model;
use think\model\concern\SoftDelete;

class Tenant extends BaseModel
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant';

    protected $autoWriteTimestamp = 'int';

    protected $append = [
        'expire_time_text'
    ];

    public function getExpireTimeTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['expire_time']) ? $data['expire_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogoAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    /**
     * 获取租户名称
     *
     * @param $tenantId
     * @return mixed
     */
    public static function getTenantNameByTenantId($tenantId)
    {
        return Tenant::where('id', $tenantId)->value('name');
    }


    /**
     * 验证租户端状态信息
     *
     * @param $tenantId
     * @return bool|string
     */
    public static function checkTenantStatusInfo($tenantId)
    {
        $tenantModel = Tenant::where('id', $tenantId)->find();

        if (!$tenantModel) {
            return '请传入有效的租户ID';
        }

        if ($tenantModel->status == 0) {
            return '系统已禁用，请联系平台管理员';
        }
        if ($tenantModel->expire_time && $tenantModel->expire_time < time()) {
            return ('系统已到期，请联系平台续费');
        }

        return true;
    }

    /**
     * 租户配置
     *
     * @return \think\model\relation\HasOne
     */
    public function config()
    {
        return $this->hasOne(TenantConfig::class, 'tenant_id');
    }
}
