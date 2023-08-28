<?php
declare (strict_types=1);

namespace app\common\command;

use app\tenant\model\TenantUser as User;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class DataTruncate extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('data:truncate')
            ->setDescription('清空数据表');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->truncateData($output);
    }

    protected function truncateData($output)
    {
        // 日志类
        Db::query('truncate table platform_admin_log');
        Db::query('truncate table platform_crud_log');
        Db::query('truncate table security_data_recycle_log');
        Db::query('truncate table security_sensitive_data_log');

        // 公共token => 平台端和租户端
        Db::query('truncate table token');

        // 租户相关
        Db::query('truncate table tenant_printer_log');

        // 商品
        // Db::query('truncate table goods');
        // Db::query('truncate table goods_category');

        // 赛事
        Db::query('truncate table `tenant_match`');
        // Db::query('truncate table match_blind');
        // Db::query('truncate table match_blind_level');
        // Db::query('truncate table match_level');
        // Db::query('truncate table match_level_detail');
        Db::query('truncate table tenant_match_result');
        Db::query('truncate table tenant_match_signup_record');
        Db::query('truncate table tenant_match_timer');
        Db::query('truncate table tenant_match_timer_level');

        // 订单
        Db::query('truncate table tenant_number_generate_record');
        Db::query('truncate table `tenant_order`');

        // 用户
        Db::query('truncate table tenant_user_package');

        // 钱包
        Db::query('truncate table tenant_wallet');
        Db::query('truncate table tenant_wallet_flow');

        // 初始化用户分冗余数据
        $this->initUserScoreData();

        // todo 清除缓存数据

        $output->writeln("已清空相应的数据表");
    }

    protected function initUserScoreData()
    {
        User::where('id', '<>', 0)->update(['integral' => 0, 'competitive_point' => 0, 'master_score' => 0, 'coupon_num' => 0]);
    }
}
