<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateBusinessAdminTable extends Migrator
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
        // 业务员表
        $table = $this->table('business_admin', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '平台 - 业务员表']);
        $table->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '姓名'])
            ->addColumn('gender', 'boolean', ['default' => 0, 'comment' => '性别:0=未知,1=男,2=女'])
            ->addColumn('mobile', 'string', ['limit' => 11, 'default' => '', 'comment' => '手机号'])
            ->addColumn('memo', 'string', ['default' => '', 'comment' => '备注'])

            // 时间相关
            ->addColumn('create_time', 'integer', ['comment' => '添加时间'])
            ->addColumn('update_time', 'integer', ['comment' => '修改时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'null' => true])
            ->create();

        $this->table('tenant')
            ->addColumn('business_admin_ids', 'string', ['limit' => 200, 'null' => true, 'default' => null, 'comment' => '业务员ids', 'after' => 'mobile'])
            ->save();
    }
}
