<?php

namespace app\common\model\tenant;


use think\Model;

/**
 * AdminGroup模型
 * @controllerUrl 'authGroup'
 */
class AdminGroup extends Model
{
    // 表名
    protected $name = 'tenant_admin_group';

    protected $autoWriteTimestamp = true;

}
