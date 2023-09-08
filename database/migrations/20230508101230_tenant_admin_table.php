<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantAdminTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('tenant_admin', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 管理员表']);
        $table->addColumn('username', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'comment' => '用户名'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('nickname', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '昵称'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '头像'])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '邮箱'])
            ->addColumn('mobile', 'string', ['limit' => 11, 'null' => false, 'default' => '', 'comment' => '手机'])
            ->addColumn('login_failure', 'integer', ['limit' => 1, 'null' => false, 'default' => 0, 'comment' => '登录失败次数'])
            ->addColumn('last_login_time', 'integer', ['null' => true, 'comment' => '登录时间'])
            ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '登录IP'])
            ->addColumn('password', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => '密码'])
            ->addColumn('salt', 'string', ['limit' => 30, 'null' => false, 'default' => '', 'comment' => '密码盐'])
            ->addColumn('motto', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '签名'])
            ->addColumn('status', 'enum', ['values' => ['1', '0'], 'null' => false, 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->addIndex(['username'], ['unique' => true, 'name' => 'username'])
            ->addIndex(['mobile'])
            ->addIndex('tenant_id')
            ->create();

        $table = $this->table('tenant_admin_log', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 管理员日志表']);
        $table->addColumn('admin_id', 'integer', ['limit' => 10, 'null' => false, 'default' => 0, 'comment' => '管理员ID'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('username', 'string', ['limit' => 30, 'null' => false, 'default' => '', 'comment' => '管理员用户名'])
            ->addColumn('url', 'string', ['limit' => 1500, 'null' => false, 'default' => '', 'comment' => '操作Url'])
            ->addColumn('title', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '日志标题'])
            ->addColumn('data', 'text', ['null' => false, 'comment' => '请求数据'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => 'IP'])
            ->addColumn('useragent', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => 'User-Agent'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addIndex(['username'], ['name' => 'name'])
            ->addIndex('tenant_id')
            ->create();
    }
}
