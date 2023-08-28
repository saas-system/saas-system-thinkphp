<?php

namespace app\tenant\model;

use think\Model;
use app\tenant\library\traits\PermissionTrait;

/**
 * 基础Model
 */
class BaseModel extends Model
{
    // 引入权限过滤类
    use PermissionTrait;
}
