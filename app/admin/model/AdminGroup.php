<?php

namespace app\admin\model;

use think\Model;

/**
 * AdminGroup模型
 */
class AdminGroup extends Model
{
    // 表名
    protected $name = 'platform_admin_group';

    protected $autoWriteTimestamp = true;
}
