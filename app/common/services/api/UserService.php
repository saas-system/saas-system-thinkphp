<?php

namespace app\common\services\api;

use app\api\validate\UserValidate;
use app\common\exceptions\UserException;
use app\common\model\user\User as UserModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
use function validate;

class UserService
{
    /**
     * 更新用户信息
     * @param $userId
     * @param $data
     * @return mixed
     */
    public function changeUserInfo($userId, $data)
    {
        // 1. 验证数据合法性
        try {
            validate(UserValidate::class)->check($data);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            throw new UserException($e->getError());
        }

        $user = UserModel::where('id', $userId)
            ->where('status', 1)
            ->find();

        $user->hidden(['extra', 'platform', 'openid', 'register_ip', 'register_ip_addr', 'register_origin', 'last_login_ip', 'last_login_time', 'last_login_ip_addr']);

        if (!$user) {
            throw new UserException('用户不存在');
        }

        Db::startTrans();

        try {
            // 更新用户数据
            $user->nickname  = $data['nickname'] ?? '';
            $user->real_name = $data['real_name'] ?? '';
            $user->gender    = $data['gender'] ?? 0;
            $user->id_card   = $data['id_card'] ?? '';
            $user->avatar    = $data['avatar'] ?? '';
            $user->address   = $data['address'] ?? '';
            $user->save();

            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            Log::critical("更新用户信息失败：" . $e->__toString(), $data);
            throw new UserException('更新用户信息失败，请联系管理员');
        }

        return $user;
    }

}
