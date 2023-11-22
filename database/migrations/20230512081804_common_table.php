<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CommonTable extends Migrator
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
        $table = $this->table('area', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 - 地区表']);
        $table->addColumn('name', 'string', ['limit' => 100, 'null' => true, 'comment' => '名称'])
            ->addColumn('pid', 'integer', ['null' => true, 'comment' => '父id'])
            ->addColumn('level', 'integer', ['null' => true, 'comment' => '层级:1=省,2=市,3=区/县'])
            ->addIndex('pid')
            ->create();

        $table = $this->table('attachment', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 - 附件表']);
        $table->addColumn('topic', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'comment' => '细目'])
            ->addColumn('tenant_id', 'string', ['limit' => 200, 'default' => '', 'comment' => '租户ID，空为平台'])
            ->addColumn('admin_id', 'integer', ['null' => false, 'default' => 0, 'comment' => '上传管理员ID'])
            ->addColumn('user_id', 'integer', ['null' => false, 'default' => 0, 'comment' => '上传用户ID'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '物理路径'])
            ->addColumn('width', 'integer', ['null' => false, 'default' => 0, 'comment' => '宽度'])
            ->addColumn('height', 'integer', ['null' => false, 'default' => 0, 'comment' => '高度'])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '原始名称'])
            ->addColumn('size', 'integer', ['null' => false, 'default' => 0, 'comment' => '大小'])
            ->addColumn('mimetype', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => 'mime类型'])
            ->addColumn('quote', 'integer', ['null' => false, 'default' => 0, 'comment' => '上传(引用)次数'])
            ->addColumn('storage', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '存储方式'])
            ->addColumn('sha1', 'string', ['limit' => 40, 'null' => false, 'default' => '', 'comment' => 'sha1编码'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('last_upload_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '最后上传时间'])
            ->addIndex(['admin_id'])
            ->addIndex(['user_id'])
            ->create();

        $table = $this->table('crud_log', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '公共 - crud记录表']);
        $table->addColumn('table_name', 'string', ['limit' => 200, 'default' => '', 'comment' => '数据表名'])
            ->addColumn('table', 'text', ['comment' => '数据表数据'])
            ->addColumn('fields', 'text', ['comment' => '字段数据'])
            ->addColumn('status', 'enum', ['values' => ['delete', 'success', 'error', 'start'], 'null' => false, 'default' => 'start', 'comment' => '状态:delete=已删除,success=成功,error=失败,start=生成中'])

            // 时间相关
            ->addColumn('create_time', 'integer', ['limit' => 10, 'null' => true, 'signed' => false, 'default' => null, 'comment' => '创建时间'])
            ->create();

        // token记录表
        $table = $this->table('token', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '用户Token表']);
        $table->addColumn('token', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Token'])
            ->addColumn('type', 'string', ['limit' => 15, 'null' => false, 'comment' => '类型'])
            ->addColumn('user_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => false, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('expire_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
            ->addIndex(['token'], ['unique' => true])
            ->create();

        // 验证码表
        $table = $this->table('captcha', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '验证码表']);
        $table->addColumn('key', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => '验证码Key'])
            ->addColumn('code', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => '验证码(加密后的,用于验证)'])
            ->addColumn('captcha', 'text', ['comment' => '验证码数据'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
            ->addColumn('expire_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
            ->addIndex('key', ['unique' => true])
            ->create();

        // 单页表
        $table = $this->table('tenant_pages', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '单页表']);
        $table->addColumn('title', 'string', ['limit' => 20, 'default' => '', 'comment' => '标题'])
            ->addColumn('tenant_id', 'string', ['limit' => 200, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('alias', 'string', ['limit' => 30, 'default' => '', 'comment' => '别名'])
            ->addColumn('content', 'text', ['comment' => '内容', 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])

            // 时间相关
            ->addColumn('create_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '添加时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '修改时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'null' => true])
            ->create();
    }
}
