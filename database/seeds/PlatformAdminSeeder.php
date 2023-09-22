<?php

use think\migration\Seeder;

class PlatformAdminSeeder extends Seeder
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
        $this->truncateData();
        $this->createAdminData();
        $this->createAdminGroup();
        $this->createAdminGroupAccess();
    }

    protected function truncateData()
    {
        $sqlList = [
            'truncate table platform_admin',
            'truncate table platform_admin_group',
            'truncate table platform_admin_group_access'
        ];

        foreach ($sqlList as $sql) {
            \think\facade\Db::execute($sql);
        }
    }

    protected function createAdminData()
    {
        $data = [
            [
                'id'              => 1,
                'username'        => 'admin',
                'nickname'        => 'Admin',
                'avatar'          => '',
                'email'           => 'admin@buildadmin.com',
                'mobile'          => '18888888888',
                'login_failure'   => 0,
                'last_login_time' => 1680347634,
                'last_login_ip'   => '127.0.0.1',
                'password'        => '792fe3eb2a0ce2f415961f0426cc2478',
                'salt'            => 'tDzJKid1yqCvj56I',
                'motto'           => '',
                'create_time'     => 1645876529,
                'update_time'     => 1680347634,
                'status'          => '1',
            ],
        ];

        $table = $this->table('platform_admin');
        $table->insert($data)->save();
    }

    protected function createAdminGroup()
    {
        $data = [
            [
                'id'          => 1,
                'pid'         => 0,
                'name'        => '超级管理组',
                'rules'       => '*',
                'create_time' => 1645876529,
                'update_time' => 1647805864,
                'status'      => '1',
            ]
        ];

        $table = $this->table('platform_admin_group');
        $table->insert($data)->save();
    }

    protected function createAdminGroupAccess()
    {
        $data = [
            [
                'uid'      => 1,
                'group_id' => 1,
            ]
        ];

        $table = $this->table('platform_admin_group_access');
        $table->insert($data)->save();
    }
}
