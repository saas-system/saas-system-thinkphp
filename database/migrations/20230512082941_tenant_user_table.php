<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantUserTable extends Migrator
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
        // 用户表
        $table = $this->table('tenant_user', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 用户表']);
        $table
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('nickname', 'string', ['limit' => 50, 'default' => '', 'comment' => '昵称'])
            ->addColumn('real_name', 'string', ['limit' => 20, 'default' => '', 'comment' => '真实姓名'])
            ->addColumn('gender', 'boolean', ['default' => 0, 'comment' => '性别:0=未知,1=男,2=女'])
            ->addColumn('mobile', 'string', ['limit' => 10, 'default' => '', 'comment' => '手机号'])
            ->addColumn('id_card', 'string', ['limit' => 50, 'default' => '', 'comment' => '身份证'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像'])

            // 扩展信息
            ->addColumn('country', 'string', ['limit' => 50, 'default' => '', 'comment' => '国家'])
            ->addColumn('address', 'string', ['limit' => 100, 'default' => '', 'comment' => '地址'])
            ->addColumn('is_virtual', 'boolean', ['default' => 0, 'comment' => '是否虚拟用户:0=否,1=是'])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态标志:0=禁用,1=正常'])

            // 统计相关
            ->addColumn('last_login_ip', 'string', ['default' => '', 'comment' => '最后登录IP'])
            ->addColumn('last_login_time', 'biginteger', ['comment' => '最后登录时间', 'null' => true])
            ->addColumn('last_login_ip_addr', 'string', ['limit' => 50, 'default' => '', 'comment' => '最后登录地址'])
            ->addColumn('register_ip', 'string', ['default' => '', 'comment' => '注册时IP'])
            ->addColumn('register_origin', 'boolean', ['default' => 0, 'comment' => '注册来源:1=Android,2=iPhone,3=模拟器'])
            ->addColumn('register_ip_addr', 'string', ['limit' => 50, 'default' => '', 'comment' => '注册地址'])
            ->addColumn('model', 'string', ['limit' => 50, 'default' => '', 'comment' => '手机型号'])

            // 第三方相关
            ->addColumn('platform', 'string', ['limit' => 50, 'default' => '', 'comment' => '平台类型：'])
            ->addColumn('openid', 'string', ['limit' => 50, 'default' => '', 'comment' => '平台ID'])
            ->addColumn('extra', 'text', ['comment' => '第三方扩展字段', 'null' => true])
            ->addColumn('session_key', 'string', ['default' => '', 'comment' => '授权用:session_key'])

            // 时间相关
            ->addColumn('create_time', 'biginteger', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'biginteger', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '修改时间'])
            ->addColumn('delete_time', 'biginteger', ['comment' => '删除时间', 'null' => true])
            // 索引
            ->addIndex('mobile')
            ->addIndex('tenant_id')
            ->create();

    }
}
