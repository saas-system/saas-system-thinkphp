<?php

namespace app\common\middleware;

use Closure;
use Throwable;
use think\facade\Config;
use app\admin\model\AdminLog as AdminLogModel;
use app\tenant\model\AdminLog as TenantAdminLogModel;

class AdminLog
{
    /**
     * 写入管理日志
     * @throws Throwable
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (($request->isPost() || $request->isDelete()) && Config::get('buildadmin.auto_write_admin_log')) {
            $root = $request->rootUrl();
            if ($root == '/admin') {
                AdminLogModel::instance()->record();
            } elseif ($root == '/tenant') {
                TenantAdminLogModel::instance()->record();
            }
        }

        return $response;
    }
}
