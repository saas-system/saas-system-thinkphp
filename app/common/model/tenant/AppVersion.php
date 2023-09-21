<?php

namespace app\common\model\tenant;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * Version
 */
class AppVersion extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_app_version';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 隐藏字段
    protected $hidden = ['update_time', 'delete_time'];

    public function getUrlAttr($value): string
    {
        return $value ? full_url($value) : '';
    }
}
