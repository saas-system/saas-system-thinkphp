<?php

namespace app\common\model\tenant;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * BusinessAdmin
 */
class BusinessAdmin extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'business_admin';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 字段类型转换
    protected $append = ['title'];

    public function getTitleAttr($val, $data)
    {
        return $data['name'] . ' - ' . $data['mobile'];
    }
}
