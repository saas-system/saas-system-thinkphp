<?php

namespace app\tenant\model;

use think\Model;

/**
 * MenuRule 模型
 * @controllerUrl 'authMenu'
 */
class AdminRule extends BaseModel
{
    protected $table = 'tenant_admin_rule';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}
