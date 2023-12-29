<?php

namespace app\admin\model;

use think\Model;

/**
 * MenuRule 模型
 */
class MenuRule extends Model
{
    protected $table = 'platform_menu_rule';

    protected $autoWriteTimestamp = true;

    public function setComponentAttr($value)
    {
        if ($value) $value = str_replace('\\', '/', $value);
        return $value;
    }

}
