<?php

use think\migration\Migrator;
use think\migration\db\Column;

class PlatformConfigTable extends Migrator
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
        $table = $this->table('platform_config', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '平台 - 系统配置']);
        $table->addColumn('name', 'string', ['limit' => 30, 'default' => '', 'comment' => '变量名'])
            ->addColumn('group', 'string', ['limit' => 30, 'default' => '', 'comment' => '分组'])
            ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '变量标题'])
            ->addColumn('tip', 'string', ['limit' => 100, 'default' => '', 'comment' => '变量描述'])
            ->addColumn('type', 'string', ['limit' => 30, 'default' => '', 'comment' => '类型:string,number,radio,checkbox,switch,textarea,array,datetime,date,select,selects'])
            ->addColumn('value', 'text', ['comment' => '变量值'])
            ->addColumn('content', 'text', ['comment' => '字典数据'])
            ->addColumn('rule', 'string', ['limit' => 100, 'default' => '', 'comment' => '验证规则'])
            ->addColumn('extend', 'string', ['limit' => 255, 'default' => '', 'comment' => '扩展属性'])
            ->addColumn('allow_del', 'boolean', ['default' => false, 'comment' => '允许删除:0=否,1=是'])
            ->addColumn('weigh', 'integer', ['default' => 0, 'comment' => '权重'])
            ->addIndex(['name'], ['unique' => true])
            ->create();
    }
}
