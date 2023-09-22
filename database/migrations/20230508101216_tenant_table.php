<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantTable extends Migrator
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
        $table = $this->table('tenant', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - 租户信息表', 'id' => false]);
        $table->addColumn('id', 'string', ['limit' => 64, 'null' => false, 'comment' => '主键ID'])
            ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '租户名称'])
            ->addColumn('short_name', 'string', ['limit' => 50, 'default' => '', 'comment' => '简称'])
            ->addColumn('logo', 'string', ['limit' => 255, 'default' => '', 'comment' => '站点logo'])
            ->addColumn('contact_name', 'string', ['limit' => 50, 'default' => '', 'comment' => '联系人'])
            ->addColumn('mobile', 'string', ['limit' => 50, 'default' => '', 'comment' => '联系电话'])
            ->addColumn('province_id', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '省ID'])
            ->addColumn('city_id', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '城市ID'])
            ->addColumn('district_id', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '区ID'])
            ->addColumn('address', 'string', ['limit' => 50, 'default' => '', 'comment' => '详细地址'])
            ->addColumn('status', 'boolean', ['limit' => 1, 'default' => 1, 'comment' => '状态:0=禁用,1=正常'])
            ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])

            // 时间相关
            ->addColumn('expire_time', 'integer', ['comment' => '过期时间', 'null' => true])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'null' => true])
            ->setPrimaryKey('id')
            ->create();
    }
}
