<?php

namespace app\common\services\tenant;

use app\admin\model\sms\Log as SmsLog;
use app\admin\model\TenantAdmin;
use app\common\exceptions\UserException;
use app\common\library\SpreadsheetUtil;
use app\common\model\tenant\Tenant;
use app\tenant\model\AdminGroup;
use app\tenant\model\AdminGroupAccess;
use ba\Random;
use modules\sms\Sms as smsLib;
use think\Exception;
use think\facade\Log;

class TenantService
{
    /**
     * 初始化租户管理员相关数据
     *
     * @param $tenant
     * @param $ip
     * @return bool
     * @throws Exception
     */
    public static function initTenantAdminData($tenant, $ip, $userName = 'admin')
    {
        try {
            $tenantId = $tenant->id;
            $salt     = Random::build('alnum', 16);
            $passwd   = encrypt_password(123123, $salt);
            $data     = [
                'tenant_id'     => $tenantId,
                'username'      => $userName,
                'nickname'      => $tenant->contact_name ?? 'Admin',
                'avatar'        => '',
                'email'         => '',
                'mobile'        => $tenant->mobile,
                'login_failure'  => 0,
                'last_login_time' => time(),
                'last_login_ip'   => $ip,
                'password'      => $passwd,
                'salt'          => $salt,
                'motto'         => '',
                'status'        => '1',
            ];

            $adminModel = TenantAdmin::create($data);

            $data       = [
                'tenant_id' => $tenantId,
                'pid'       => 0,
                'name'      => '超级管理组',
                'rules'     => '*',
                'status'    => '1',
            ];
            $groupModel = AdminGroup::create($data);

            $data = [
                'uid'       => $adminModel->id,
                'group_id'  => $groupModel->id,
                'tenant_id' => $tenantId,
            ];

            AdminGroupAccess::create($data);
            return true;

        } catch (\Exception $e) {
            Log::critical('初始化租户端账号信息失败，失败原因：' . $e->getMessage() . '-' . $e->getLine() . $e->getTraceAsString());
            throw new Exception('初始化租户端账号信息失败');
        }
    }

    public static function initTenantAdminGroup($tenant)
    {
        try {
            $tenantId = $tenant->id;
            $data     = [
                'tenant_id' => $tenantId,
                'pid'       => 0,
                'name'      => '超级管理组',
                'rules'     => '*',
                'status'    => '1',
            ];

            $groupModel = AdminGroup::create($data);

            return true;

        } catch (\Exception $e) {
            Log::critical('初始化租户端管理员信息失败，失败原因：' . $e->getMessage() . '-' . $e->getLine() . $e->getTraceAsString());
            throw new Exception('初始化租户端管理员信息失败');
        }
    }

    /**
     * 操作租户提醒功能
     *
     * @return void
     */
    public static function handleTenantExpireRemind()
    {
        $remindConfig = get_sys_config('', 'remind');
        $isRemind     = $remindConfig['is_remind'] ?? '';
        if (empty($isRemind)) {
            Log::critical('未开启消息提醒功能');
            return;
        }

        $remindMethods              = $remindConfig['remind_method'];
        $remindMethod               = $remindMethods['0'] ?? 1; // 默认短信提醒
        $remindPlatformAdminMobiles = $remindConfig['remind_platform_admin'] ?: [];
        $remindTimePeriods          = $remindConfig['remind_time_period'] ?? '';

        if (empty($remindTimePeriods)) {
            Log::critical('未开启提醒时间段');
            return;
        }


        if (!empty($remindPlatformAdminMobiles)) {
            $remindPlatformAdminMobiles = explode(',', $remindPlatformAdminMobiles);
        }

        try {
            $nowTime    = time();
            $tenantList = Tenant::with('config')->where('status', 1)
                ->where('expire_time', '>', $nowTime)->select();

            $logs  = [];
            $count = 0;
            foreach ($tenantList as $tenant) {
                $expireTime   = $tenant->expire_time;
                $isWillExpire = Tenant::checkIsWillExipre($expireTime, $remindTimePeriods);

                if (!$isWillExpire) {
                    continue;
                }

                $tenantName     = $tenant->name;
                $expireTimeText = date('Y-m-d H:i:s', $expireTime);
                $config         = $tenant->config;
                $remindAdminIds = $config->remind_admin_ids;

                $tenantMobiles = [];
                if (!empty($remindAdminIds)) {
                    $tenantMobiles = TenantAdmin::whereIn('id', $remindAdminIds)->column('mobile');
                }

                $sendMobiles  = array_unique(array_merge($remindPlatformAdminMobiles, $tenantMobiles));
                $templateCode = 'tenant_expire_remind';

                foreach ($sendMobiles as $mobile) {
                    $res = smsLib::send($templateCode, $mobile, ['name' => $tenantName, 'time' => $expireTimeText]);

                    // 发送日志写入
                    SmsLog::addSendLog($mobile, $res);
                }

                $logs  = [
                    'tenant_name'      => $tenantName,
                    'expire_time_text' => $expireTimeText,
                    'send_mobiles'     => $sendMobiles,
                ];
                $count += 1;
            }
            Log::info('检测租户是否过期完成，数量为' . $count, $logs);

        } catch (\Exception $e) {
            Log::critical('发送消息提醒失败，失败原因：' . $e->getMessage() . '-' . $e->getLine() . '-' . $e->getTraceAsString());
        }

    }

    /**
     * 导出比赛报名记录
     * @param array $queryBuilder
     * @return string
     */
    public function exportTenant(array $queryBuilder)
    {
        try {
            $items           = [];
            $spreadsheetUtil = new SpreadsheetUtil();
            $headers         = [
                'A' => ['title' => '代理商名称', 'width' => 'auto'],
                'B' => ['title' => '联系人', 'width' => 'auto'],
                'C' => ['title' => '联系电话', 'width' => 'auto'],
                'D' => ['title' => '过期时间', 'width' => 'auto']
            ];
            $titles          = [];
            foreach ($headers as $key => $item) {
                $titles[] = $item['title'];
                if ($item['width'] == 'auto') {
                    $spreadsheetUtil->getActiveSheet()->getColumnDimension($key)->setAutoSize(true);
                } else {
                    $spreadsheetUtil->getActiveSheet()->getColumnDimension($key)->setWidth($item['width']);
                }
            }
            $spreadsheetUtil->setRowValue($titles, 1); // 头部

            list($where, $alias, $limit, $order) = $queryBuilder;
            $tenantList = (new Tenant())->withJoin(['province', 'city', 'district', 'config'], 'LEFT')
                ->alias($alias)
                ->where($where)
                ->order('expire_time asc')
                ->select();

            // 格式化数据
            foreach ($tenantList as $tenant) {
                $items[] = [
                    $tenant->name,
                    $tenant->contact_name,
                    $tenant->mobile,
                    $tenant->expire_time_text
                ];
            }

            $spreadsheetUtil->setRowValues($items, 2); // 内容

            // 文件名
            $fileName  = "租户导出" . time();

            return $spreadsheetUtil->save('', $fileName, false); // 浏览
        } catch (\Exception $e) {
            Log::critical("导出租户错误：" . $e->__toString());
            throw new UserException("导出错误");
        }
    }

}
