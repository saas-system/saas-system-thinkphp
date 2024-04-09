<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantAuthTable extends Migrator
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
        $table = $this->table('tenant_admin_group', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 管理分组表']);
        $table->addColumn('pid', 'integer', ['limit' => 10, 'null' => false, 'default' => 0, 'signed' => false, 'comment' => '上级分组'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '组名'])
            ->addColumn('rules', 'text', ['null' => false, 'comment' => '权限规则ID'])
            ->addColumn('create_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->addColumn('status', 'enum', ['values' => ['1', '0'], 'null' => false, 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
            ->create();

        $table = $this->table('tenant_admin_group_access', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 管理权限分组表']);
        $table->addColumn('uid', 'integer', ['limit' => 10, 'null' => false, 'comment' => '管理员ID'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('group_id', 'integer', ['limit' => 10, 'null' => false, 'comment' => '分组ID'])
            ->addIndex(['uid', 'group_id'], ['unique' => true, 'name' => 'uid_group_id'])
            ->addIndex(['uid'], ['name' => 'uid'])
            ->addIndex(['group_id'], ['name' => 'group_id'])
            ->create();

        // 菜单表
        $table = $this->table('tenant_menu_rule', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 菜单和权限规则表']);
        $table->addColumn('pid', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0, 'comment' => '上级菜单'])
            ->addColumn('type', 'enum', ['values' => ['menu_dir', 'menu', 'button'], 'default' => 'menu', 'comment' => '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮'])
            ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'comment' => '标题'])
            ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '规则名称'])
            ->addColumn('path', 'string', ['limit' => 100, 'default' => '', 'comment' => '路由路径'])
            ->addColumn('icon', 'string', ['limit' => 50, 'default' => '', 'comment' => '图标'])
            ->addColumn('menu_type', 'enum', ['values' => ['', 'tab', 'link', 'iframe'], 'null' => true, 'comment' => '菜单类型:tab=选项卡,link=链接,iframe=Iframe'])
            ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => 'Url'])
            ->addColumn('component', 'string', ['limit' => 100, 'default' => '', 'comment' => '组件路径'])
            ->addColumn('keepalive', 'boolean', ['default' => false, 'comment' => '缓存:0=关闭,1=开启'])
            ->addColumn('extend', 'enum', ['values' => ['none', 'add_rules_only', 'add_menu_only'], 'default' => 'none', 'comment' => '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
            ->addColumn('weigh', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '权重(排序)'])
            ->addColumn('status', 'enum', ['values' => ['1', '0'], 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
            ->addColumn('create_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'biginteger', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->addIndex('pid')
            ->addIndex('weigh')
            ->create();
    }
}
