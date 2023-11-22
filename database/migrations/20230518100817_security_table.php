<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SecurityTable extends Migrator
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
        $table = $this->table('security_data_recycle', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '回收规则表']);
        $table->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '规则名称'])
            ->addColumn('controller', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '控制器'])
            ->addColumn('controller_as', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '控制器别名'])
            ->addColumn('data_table', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '对应数据表'])
            ->addColumn('primary_key', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '数据表主键'])
            ->addColumn('status', 'enum', ['values' => ['0', '1'], 'null' => false, 'default' => '0', 'comment' => '状态:0=禁用,1=启用'])
            ->addColumn('update_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '修改时间'])
            ->addColumn('create_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->create();

        $table = $this->table('security_data_recycle_log', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '数据回收记录表']);
        $table->addColumn('admin_id', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '操作管理员'])
            ->addColumn('recycle_id', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '回收规则ID'])
            ->addColumn('data', 'text', ['null' => true, 'comment' => '回收的数据'])
            ->addColumn('data_table', 'string', ['null' => false, 'default' => '', 'comment' => '数据表'])
            ->addColumn('primary_key', 'string', ['null' => false, 'default' => '', 'comment' => '数据表主键'])
            ->addColumn('is_restore', 'boolean', ['null' => false, 'default' => false, 'comment' => '是否已还原:0=否,1=是'])
            ->addColumn('ip', 'string', ['null' => false, 'default' => '', 'comment' => '操作者IP'])
            ->addColumn('useragent', 'string', ['null' => false, 'default' => '', 'comment' => 'User Agent'])
            ->addColumn('create_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->addIndex(['admin_id'])
            ->addIndex(['recycle_id'])
            ->create();

        $table = $this->table('security_sensitive_data', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '敏感数据表']);
        $table->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '规则名称'])
            ->addColumn('controller', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '控制器'])
            ->addColumn('controller_as', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '处理后的控制器名'])
            ->addColumn('data_table', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '对应数据表'])
            ->addColumn('primary_key', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '数据表主键字段'])
            ->addColumn('data_fields', 'text', ['null' => false, 'comment' => '敏感数据字段'])
            ->addColumn('status', 'boolean', ['null' => false, 'default' => 0, 'comment' => '状态:0=关闭,1=启用'])
            ->addColumn('update_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '修改时间'])
            ->addColumn('create_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->create();

        $table = $this->table('security_sensitive_data_log', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '敏感数据修改记录']);
        $table->addColumn('admin_id', 'integer', ['null' => false, 'default' => 0, 'comment' => '管理员'])
            ->addColumn('sensitive_id', 'integer', ['null' => false, 'default' => 0, 'comment' => '敏感数据规则ID'])
            ->addColumn('data_table', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '所在数据表'])
            ->addColumn('primary_key', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '数据表主键'])
            ->addColumn('data_field', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '被修改字段'])
            ->addColumn('data_comment', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '被修改项'])
            ->addColumn('id_value', 'string', ['limit' => 10, 'null' => false, 'default' => '', 'comment' => '被修改项主键值'])
            ->addColumn('before', 'text', ['null' => true, 'comment' => '修改前'])
            ->addColumn('after', 'text', ['null' => true, 'comment' => '修改后'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '操作者IP'])
            ->addColumn('useragent', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => 'User Agent'])
            ->addColumn('is_rollback', 'boolean', ['null' => false, 'default' => 0, 'comment' => '是否已回滚:0=否,1=是'])
            ->addColumn('create_time', 'integer', ['limit' => 10,'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->create();
    }
}
