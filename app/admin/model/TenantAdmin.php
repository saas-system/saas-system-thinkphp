<?php

namespace app\admin\model;

use app\common\model\tenant\Tenant;
use think\facade\Config;
use think\facade\Db;
use think\Model;
use ba\Random;

/**
 * TenantAdmin
 */
class TenantAdmin extends Model
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

    public function getGroupArrAttr($value, $row)
    {
        return Db::name('platform_admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
    }

    public function getGroupNameArrAttr($value, $row)
    {
        $groupAccess = Db::name('platform_admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        return TenantAdminGroup::whereIn('id', $groupAccess)->column('name');
    }

    public function getAvatarAttr($value)
    {
        return full_url($value, true, Config::get('buildadmin.default_avatar'));
    }

    public function getLastlogintimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : 'none';
    }

    /**
     * 重置用户密码
     * @param int $uid 管理员ID
     * @param string $newPassword 新密码
     */
    public function resetPassword($uid, $newPassword)
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
