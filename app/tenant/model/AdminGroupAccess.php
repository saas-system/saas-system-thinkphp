<?php

namespace app\tenant\model;

use think\Model;

/**
 * AdminGroupAccess模型
 * @controllerUrl 'authGroupAccess'
 */
class AdminGroupAccess extends BaseModel
{
    // 表名
    protected $name = 'tenant_admin_group_access';

    protected $createTime = false;
    protected $updateTime = false;
}
