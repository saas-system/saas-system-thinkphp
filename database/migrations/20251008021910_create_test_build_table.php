<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTestBuildTable extends Migrator
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
        $this->testBuild();
    }

    /**
     * 创建测试库表
     */
    public function testBuild(): void
    {
        if (!$this->hasTable('test_build')) {
            $table = $this->table('test_build', [
                'id'          => false,
                'comment'     => '知识库表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '标题', 'null' => false])
                ->addColumn('keyword_rows', 'string', ['limit' => 100, 'default' => '', 'comment' => '关键词', 'null' => false])
                ->addColumn('content', 'text', ['null' => true, 'default' => null, 'comment' => '内容'])
                ->addColumn('views', 'integer', ['comment' => '浏览量', 'default' => 0, 'signed' => false, 'null' => false])
                ->addColumn('likes', 'integer', ['comment' => '有帮助数', 'default' => 0, 'signed' => false, 'null' => false])
                ->addColumn('dislikes', 'integer', ['comment' => '无帮助数', 'default' => 0, 'signed' => false, 'null' => false])
                ->addColumn('note_textarea', 'string', ['limit' => 100, 'default' => '', 'comment' => '备注', 'null' => false])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=隐藏,1=正常', 'null' => false])
                ->addColumn('weigh', 'integer', ['comment' => '权重', 'default' => 0, 'null' => false])
                ->addColumn('update_time', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('create_time', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }
}
