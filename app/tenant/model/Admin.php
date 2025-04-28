<?php

namespace app\tenant\model;

use think\facade\Config;
use think\facade\Db;
use think\Model;

/**
 * Admin模型
 * @controllerUrl 'authAdmin'
 */
class Admin extends BaseModel
{
    // 表名
    protected $name = 'tenant_admin';

    /**
     * @var string 自动写入时间戳
     */
    protected $autoWriteTimestamp = true;

    /**
     * 追加属性
     */
    protected $append = [
        'group_arr',
        'group_name_arr',
    ];

    public function getGroupArrAttr($value, $row)
    {
        return Db::name('tenant_admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
    }

    public function getGroupNameArrAttr($value, $row)
    {
        $groupAccess = Db::name('tenant_admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        return AdminGroup::whereIn('id', $groupAccess)->column('name');
    }

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastLoginTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : 'none';
    }

    /**
     * 重置用户密码
     * @param int $uid 管理员ID
     * @param string $newPassword 新密码
     */
    public function resetPassword(int $uid, string $newPassword): Admin
    {
        return $this->where(['id' => $uid])->update(['password' => hash_password($newPassword), 'salt' => '']);
    }
}
