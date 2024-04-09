<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantConfigTable extends Migrator
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
    public function change(): void
    {
        $table = $this->table('tenant_config', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 配置表']);
        $table->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('mini_app_id', 'string', ['limit' => 255, 'default' => '', 'comment' => '微信小程序ID'])
            ->addColumn('mini_secret_id', 'string', ['limit' => 255, 'default' => '', 'comment' => '微信小程序秘钥'])
            ->addColumn('mini_logo', 'string', ['limit' => 200, 'default' => '', 'comment' => '微信小程序logo'])

            // 卡号生成相关配置
            ->addColumn('tenant_pre', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户前缀'])
            ->addColumn('admin_pre', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户管理员前缀'])
            ->addColumn('number_pre', 'integer', ['limit' => 10, 'default' => 88, 'comment' => '卡号生成前缀数字'])

            //消息提醒管理员IDS
            ->addColumn('remind_admin_ids', 'string', ['limit' => 255, 'default' => '', 'comment' => '提醒管理员IDS'])

            // 时间相关
            ->addColumn('create_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->addIndex('tenant_id')
            ->create();
    }
}
