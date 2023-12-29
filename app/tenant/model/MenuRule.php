<?php

namespace app\tenant\model;

use think\Model;

/**
 * MenuRule 模型
 * @controllerUrl 'authMenu'
 */
class MenuRule extends BaseModel
{
    protected $table = 'tenant_menu_rule';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}
