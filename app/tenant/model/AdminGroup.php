<?php

namespace app\tenant\model;


/**
 * AdminGroup模型
 * @controllerUrl 'authGroup'
 */
class AdminGroup extends BaseModel
{
    // 表名
    protected $name = 'tenant_admin_group';

    protected $autoWriteTimestamp = true;

}
