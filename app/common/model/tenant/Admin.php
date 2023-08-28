<?php

namespace app\common\model\tenant;

use think\facade\Config;
use think\facade\Db;
use think\Model;

/**
 * Admin模型
 * @controllerUrl 'authAdmin'
 */
class Admin extends Model
{
    // 表名
    protected $name = 'tenant_admin';

    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = 'int';

    /**
     * @var string 自动写入创建时间
     */
    protected $createTime = 'create_time';
    /**
     * @var string 自动写入更新时间
     */
    protected $updateTime = 'update_time';

    /**
     * 追加属性
     */
    protected $append = [
        'group_arr',
        'group_name_arr',
    ];

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastlogintimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : 'none';
    }
}
