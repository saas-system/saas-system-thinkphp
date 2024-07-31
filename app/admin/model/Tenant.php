<?php

namespace app\admin\model;

use app\common\model\Area;
use app\common\model\tenant\BusinessAdmin;
use app\common\model\tenant\TenantConfig;
use think\Model;

/**
 * Tenant
 */
class Tenant extends Model
{
    // 表名
    protected $name = 'tenant';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 字段类型转换
    protected $type = [
        'expire_time' => 'timestamp:Y-m-d H:i:s',
    ];

    protected $append = [
        'business_admin',
        'area_ids'
    ];

    public static function onBeforeWrite(Model $model): void
    {
        $areaIds = $model->getData('area_ids');
        if (!empty($areaIds)) {
            $model->province_id = $areaIds[0];
            $model->city_id     = $areaIds[1];
            $model->district_id = $areaIds[2];
        }
    }

    public function setBusinessAdminIdsAttr($val, $data)
    {
        return $val ? implode(',', $val) : '';
    }


    public function getBusinessAdminIdsAttr($val, $data)
    {
        return $val ? explode(',', $val) : [];
    }

    public function getBusinessAdminAttr($val, $data)
    {
        if (!empty($data['business_admin_ids'])) {
            return (new BusinessAdmin())->where('id', 'in', $data['business_admin_ids'])->column('name');
        }
        return [];
    }

    public function getAreaIdsAttr($val, $data)
    {
        $provinceId = $data['province_id'];
        $cityId     = $data['city_id'];
        $districtId = $data['district_id'];

        if ($provinceId && $cityId && $districtId) {
            return [$provinceId, $cityId, $districtId];
        }
        return [];
    }

    public function province()
    {
        return $this->belongsTo(Area::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(Area::class, 'city_id');
    }

    public function district()
    {
        return $this->belongsTo(Area::class, 'district_id');
    }

    public function config()
    {
        return $this->hasOne(TenantConfig::class, 'tenant_id', 'id');
    }

}
