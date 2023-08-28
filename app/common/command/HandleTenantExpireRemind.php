<?php
declare (strict_types=1);

namespace app\common\command;

use app\common\services\tenant\TenantService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class HandleTenantExpireRemind extends Command
{
    protected function configure()
    {
        // 指令配置 => 每天8点提醒
        $this->setName('handle:tenant_expire_remind')
            ->setDescription('操作租户过期提醒');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->handleData();
    }

    protected function handleData()
    {
        TenantService::handleTenantExpireRemind();
    }

}
