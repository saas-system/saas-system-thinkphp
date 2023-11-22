<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CommonSmsTable extends Migrator
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
        $table = $this->table('sms_template', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 -短信模版表']);
        $table->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '模版标题'])
            ->addColumn('code', 'string', ['limit' => 100, 'default' => '', 'comment' => '唯一标识'])
            ->addColumn('template', 'string', ['limit' => 100, 'default' => '', 'comment' => '服务商模板ID'])
            ->addColumn('content', 'text', ['comment' => '短信内容'])
            ->addColumn('variables', 'string', ['limit' => 200, 'default' => '', 'comment' => '模板变量'])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态:0=禁用,1=启用'])

            // 时间
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->create();

        $table = $this->table('sms_variable', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 -短信模版变量表']);
        $table->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '标题'])
            ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '变量名称'])
            ->addColumn('value_source', 'enum', ['values' => ['literal', 'func', 'sql'], 'null' => false, 'default' => 'literal', 'comment' => '变量值来源:literal=字面量,func=方法返回值,sql=sql查询结果'])
            ->addColumn('value', 'string', ['limit' => 200, 'default' => '', 'comment' => '变量值'])
            ->addColumn('sql', 'string', ['limit' => 500, 'default' => '', 'comment' => 'SQL语句'])
            ->addColumn('namespace', 'string', ['limit' => 200, 'default' => '', 'comment' => '命名空间'])
            ->addColumn('class', 'string', ['limit' => 100, 'default' => '', 'comment' => '类名'])
            ->addColumn('func', 'string', ['limit' => 100, 'default' => '', 'comment' => '方法名'])
            ->addColumn('param', 'string', ['limit' => 100, 'default' => '', 'comment' => '传递的参数'])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态:0=禁用,1=启用'])

            // 时间
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->create();

        $table = $this->table('sms_log', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 -短信发送记录表']);
        $table->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '标题'])
            ->addColumn('template_id', 'integer', ['limit' => 10, 'default' => 0, 'signed' => false, 'comment' => '模版ID'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '关联的租户ID'])
            ->addColumn('mobile', 'string', ['limit' => 100, 'default' => '', 'comment' => '发送手机号'])
            ->addColumn('code', 'string', ['limit' => 100, 'default' => '', 'comment' => '唯一标识'])
            ->addColumn('template', 'string', ['limit' => 100, 'default' => '', 'comment' => '服务商模板ID'])
            ->addColumn('content', 'text', ['comment' => '短信内容'])
            ->addColumn('data', 'text', ['comment' => '短信变量内容'])
            ->addColumn('status', 'boolean', ['default' => 0, 'comment' => '状态:0=未发送,1=发送成功 2=发送失败'])
            ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])

            // 时间

            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '更新时间'])
            ->addIndex('tenant_id')
            ->create();
    }
}
