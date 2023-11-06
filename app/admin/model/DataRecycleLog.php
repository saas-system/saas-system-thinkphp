<?php

namespace app\admin\model;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * DataRecycleLog 模型
 */
class DataRecycleLog extends Model
{
    protected $name = 'security_data_recycle_log';

    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function recycle(): BelongsTo
    {
        return $this->belongsTo(DataRecycle::class, 'recycle_id');
    }

    // public function admin(): BelongsTo
    // {
    //     return $this->belongsTo(Admin::class, 'admin_id');
    // }

    /**
     * 多态关联模型
     *
     * @return \think\model\relation\MorphTo
     */
    public function admin()
    {
        return $this->morphTo('admin');
    }

    public function tadmin()
    {
        return $this->belongsTo(TenantAdmin::class, 'admin_id')->visible(['nickname', 'id']);
    }

    public function pmadmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id')->visible(['nickname', 'id']);
    }

    public function tenant(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
