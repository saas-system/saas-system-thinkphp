<?php

namespace app\common\model\common;

use app\common\model\Base;
use think\model\concern\SoftDelete;

/**
 * Pages
 */
class Pages extends Base
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_pages';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 字段类型转换
    protected $type = [];


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}
