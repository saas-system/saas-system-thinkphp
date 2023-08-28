<?php

namespace app\common\services\api;

use app\common\exceptions\AuthException;
use app\common\model\tenant\Tenant;
use app\tenant\model\TenantUser as User;
use function request;

/**
 * 权限验证业务层
 *
 * Class AuthService
 * @package app\service
 */
class AuthService
{
    protected $header = 'authorization';
    protected $prefix = 'bearer';

    /**
     * 验证TOKEN状态
     * @return array
     */
    public function checkToken()
    {
        $token = $this->getToken();
        if (empty($token)) {
            throw new AuthException('请传入有效的token');
        }

        $info     = (new JwtService())->checkToken($token);
        $userId   = $info->data->user_id;
        $userInfo = User::where('id', $userId)->field(['id', 'nickname', 'tenant_id', 'status'])->find();
        // 1. 验证用户是否存在
        if (!$userInfo) {
            throw new AuthException('用户不存在', 401);
        }

        // 2. 验证用户状态是否异常
        if ($userInfo->status == 0) {
            throw new AuthException('用户账户异常，请联系管理员');
        }

        // 3. 验证租户状态
        $tenantStatusResult = Tenant::checkTenantStatus($userInfo->tenant_id);
        if ($tenantStatusResult !== true) {
            throw new AuthException($tenantStatusResult);
        }

        return $userInfo;
    }

    /**
     * 获取token
     */
    public function getToken()
    {
        $header = Request()->header($this->header);
        if ($header && preg_match('/' . $this->prefix . '\s*(\S+)\b/i', $header, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * 通过token获取用户ID
     * @return false
     */
    public function getUserIdByToken()
    {
        $token = $this->getToken();
        if (empty($token)) {
            return false;
        }
        try {
            $info     = (new JwtService())->decode($token);
            $userId   = $info->data->user_id;
            $userInfo = User::field('id,nickname,status')->where('id', $userId)->find();

            // 1. 验证用户是否存在
            if (!$userInfo) {
                return false;
            }
            // 2. 验证用户状态是否异常
            if ($userInfo->status == 0) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $userId;
    }

}
