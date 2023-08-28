<?php

namespace app\common\model\user;

use app\common\model\Base;
use think\model\concern\SoftDelete;

class User extends Base
{
    use SoftDelete;

    /**
     * 与模型关联的表名
     * @var string
     */
    protected $name = 'tenant_user';

    /**
     * 不可批量赋值的属性。当 $guarded 为空数组时则所有属性都可以被批量赋值。
     * @var array
     */
    protected $guarded = [];

    /**
     * 隐藏属性
     * @var array
     */
    protected $hidden = ['delete_time'];

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['gender_text'];

    public function getAvatarAttr($value)
    {
        if (empty($value)) {
            $value = '/static/images/avatar.png';
        }
        return full_url($value);
    }

    public function setAvatarAttr($value)
    {
        if (!empty($value)) {
            $oss    = \config('alioss');//alioss 配置参数
            $cdnUrl = $oss['Cdnurl'] ?? '';
            $value  = str_replace($cdnUrl, '', $value);
            // 更新
            return $value;
        }

        return $value;
    }

    public function getGenderTextAttr($value, $data)
    {
        $gender = $data['gender'] ?? 0;
        $arr    = ['保密', '男', '女'];

        return $arr[$gender] ?? $arr[0];
    }

    /**
     * 验证用户是否已完善信息
     *
     * @param $userModel
     * @return bool
     */
    public static function checkUserIsFullInfo($userModel)
    {
        $nickname = $userModel->nickname;
        $mobile   = $userModel->mobile;
        // $idCard   = $userModel->id_card;

        if (!$nickname || !$mobile) {
            return false;
        }

        return true;
    }
}
