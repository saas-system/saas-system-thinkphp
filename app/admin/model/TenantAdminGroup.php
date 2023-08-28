<?php

namespace app\admin\model;

use think\Model;

/**
 * AdminGroup模型
 * @controllerUrl 'tenantAuthGroup'
 */
class TenantAdminGroup extends Model
{
    // 表名
    protected $name = 'tenant_admin_group';

    protected $autoWriteTimestamp = true;
}
