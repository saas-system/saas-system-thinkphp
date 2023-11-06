<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * SensitiveDataLog 模型
 */
class SensitiveDataLog extends Model
{
    protected $name = 'security_sensitive_data_log';

    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function sensitive(): BelongsTo
    {
        return $this->belongsTo(SensitiveData::class, 'sensitive_id');
    }

    public function tadmin()
    {
        return $this->belongsTo(TenantAdmin::class, 'admin_id')->visible(['nickname', 'id']);
    }

    public function pmadmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id')->visible(['nickname', 'id']);
    }

    /**
     * 多态关联模型
     *
     * @return \think\model\relation\MorphTo
     */
    public function admin()
    {
        return $this->morphTo('admin');
    }

    public function tenant(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
