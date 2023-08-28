<?php

namespace app\common\model\common;

use app\common\model\Base;
use think\model\concern\SoftDelete;

/**
 * SlideCategory
 */
class SlideCategory extends Base
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_slide_category';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 隐藏字段
    protected $hidden = [
        'update_time',
        'delete_time'
    ];

}
