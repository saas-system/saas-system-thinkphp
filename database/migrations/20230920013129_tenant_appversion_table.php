<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TenantAppversionTable extends Migrator
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
        $table = $this->table('tenant_app_version', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '租户 - APP版本表']);
        $table->addColumn('version', 'string', ['limit' => 30, 'null' => false, 'default' => '', 'comment' => '版本名称'])
            ->addColumn('version_code', 'integer', ['limit' => 11, 'null' => false, 'default' => 0, 'comment' => '版本号'])
            ->addColumn('size', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'comment' => '包大小'])
            ->addColumn('content', 'string', ['limit' => 300, 'null' => false, 'default' => '', 'comment' => '更新内容'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '下载地址'])
            ->addColumn('enforce', 'integer', ['limit' => 1, 'null' => false, 'default' => 0, 'comment' => '强制更新:1=是,0=否'])
            ->addColumn('status', 'integer', ['limit' => 1, 'null' => false, 'default' => 1, 'comment' => '状态:1=显示,0=隐藏'])
            ->addColumn('create_time', 'integer', ['limit' => 11, 'null' => false, 'default' => 0, 'comment' => '添加时间'])
            ->addColumn('update_time', 'integer', ['limit' => 11, 'null' => false, 'default' => 0, 'comment' => '修改时间'])
            ->addColumn('delete_time', 'integer', ['limit' => 11, 'null' => true, 'default' => null, 'comment' => '删除时间'])

            ->addIndex(['version_code'], ['unique' => true])
            ->create();
    }
}
