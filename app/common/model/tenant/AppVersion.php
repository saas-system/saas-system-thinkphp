<?php

namespace app\common\model\tenant;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * Version
 */
class AppVersion extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_app_version';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 隐藏字段
    protected $hidden = ['update_time', 'delete_time'];


    protected static function onBeforeWrite($model)
    {
        if ($model->range == 0) {
            $model->user_ids = '';
        }
    }

    public function getUserIdsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setUserIdsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
