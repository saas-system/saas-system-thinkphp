<?php

use think\migration\Seeder;

class InitWalletAccountSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        // 清空数据
        $this->truncateData();

        $this->createWalletRole();
        $this->createAccountTypeData();
        $this->createAccountData();
    }

    protected function truncateData(): void
    {
        $sqlList = [
            'truncate table tenant_wallet_role',
            'truncate table tenant_wallet_account_type',
            'truncate table tenant_wallet_account',
        ];

        foreach ($sqlList as $sql) {
            \think\facade\Db::execute($sql);
        }
    }

    public function createWalletRole(): void
    {
        $table = $this->table('tenant_wallet_role');
        $list  = [
            ['name' => '平台', 'short_name' => 'P'],
            ['name' => '用户', 'short_name' => 'U'],
            ['name' => '租户', 'short_name' => 'A'],
        ];

        $table->insert($list)
            ->saveData();
    }


    public function createAccountTypeData(): void
    {
        $table = $this->table('tenant_wallet_account_type');
        $list  = [
            ['name' => '现金', 'memo' => ''],
            ['name' => '信用卡', 'memo' => ''],
            ['name' => '储蓄卡/借记卡', 'memo' => ''],
            ['name' => '网络账户', 'memo' => '票务账户，包邮账户，微信账户'],
            ['name' => '投资账户', 'memo' => '证券账户，基金账户'],
            ['name' => '应收/应付', 'memo' => ''],
            ['name' => '储值卡', 'memo' => '购物卡，公交卡，饭卡'],
            ['name' => '虚拟账户', 'memo' => '积分，Q币，点卡，分销账户'],
        ];
        $table->insert($list)
            ->saveData();
    }

    public function createAccountData(): bool
    {
        $table = $this->table('tenant_wallet_account');
        $list  = [
            [
                'id'                => 1,
                'name'              => '平台交易账户',
                'account_type_id'   => 4,
                'account_type_name' => '网络账户',
            ],

            // 租户
            [
                'id'                => 11,
                'name'              => '租户交易账户',
                'account_type_id'   => 4,
                'account_type_name' => '网络账户',
            ],

            // 用户相关账户
            [
                'id'                => 21,
                'name'              => '用户交易账户',
                'account_type_id'   => 4,
                'account_type_name' => '网络账户',
            ],

            // todo 新增
        ];

        $table->insert($list)
            ->saveData();
        return true;
    }
}
