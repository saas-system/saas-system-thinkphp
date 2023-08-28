<?php

namespace app\common\model;

use ba\Random;
use think\Model;
use think\facade\Config;

/**
 * 会员公共模型
 * @property int    $id              会员ID
 * @property string $password        密码密文
 * @property string $salt            密码盐
 * @property int    $login_failure   登录失败次数
 * @property string $last_login_time 上次登录时间
 * @property string $last_login_ip   上次登录IP
 * @property string $email           会员邮箱
 * @property string $mobile          会员手机号
 */
class User extends Base
{
    protected $autoWriteTimestamp = true;

    public function getAvatarAttr($value): string
    {
        return full_url(htmlspecialchars_decode($value), true, Config::get('buildadmin.default_avatar'));
    }

    public function resetPassword($uid, $newPassword): int|User
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }


}
