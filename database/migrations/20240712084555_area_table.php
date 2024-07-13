<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class AreaTable extends Migrator
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
        // Create 'area' table
        $table = $this->table('area', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '地区表']);

        // Add columns
        $table->addColumn('name', 'string', ['limit' => 100, 'default' => null, 'comment' => '名称'])
            ->addColumn('pid', 'integer', ['default' => null, 'comment' => '父id'])
            ->addColumn('level', 'integer', ['default' => null, 'comment' => '层级:1=省,2=市,3=区/县'])
            ->addIndex(['pid'], ['name' => 'pid'])
            ->addIndex(['id'], ['unique' => true])
            ->create();
    }
}
