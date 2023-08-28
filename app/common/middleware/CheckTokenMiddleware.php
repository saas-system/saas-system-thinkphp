<?php
declare (strict_types=1);

namespace app\common\middleware;

use app\common\services\api\AuthService;
use Closure;
use think\Request;

/**
 * 接口登录验证中间件
 * Class CheckTokenMiddleware
 * @package app\middleware
 */
class CheckTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->method(true) == 'OPTIONS') {
            return $next($request);
        }

        $userInfo      = (new AuthService())->checkToken();
        $request->user = $userInfo;

        return $next($request);

    }
}

