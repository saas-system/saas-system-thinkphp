<?php

use think\facade\Db;
use app\admin\model\CrudLog;
use think\migration\Migrator;

class Version222 extends Migrator
{
    /**
     * @throws Throwable
     */
    public function up(): void
    {
        /**
         * 修复附件表 name 字段长度可能不够的问题
         */
        $attachment = $this->table('attachment');
        $attachment->changeColumn('name', 'string', ['limit' => 120, 'default' => '', 'comment' => '原始名称', 'null' => false])->save();

        /**
         * 用户表
         * 1. status 注释优化
         * 2. password 增加长度至 password_hash 建议值
         * 3. salt 注释中标记废弃待删除
         */
        $user = $this->table('user');
        $user->changeColumn('status', 'string', ['limit' => 30, 'default' => '', 'comment' => '状态:enable=启用,disable=禁用', 'null' => false])
            ->changeColumn('password', 'string', ['limit' => 255, 'default' => '', 'comment' => '密码', 'null' => false])
            ->changeColumn('salt', 'string', ['limit' => 30, 'default' => '', 'comment' => '密码盐（废弃待删）', 'null' => false])
            ->save();

        /**
         * 管理员表
         * 1. status 改为字符串存储
         * 2. 其他和以上用户表的改动相同
         */
        $admin = $this->table('admin');
        $admin->changeColumn('status', 'string', ['limit' => 30, 'default' => '', 'comment' => '状态:enable=启用,disable=禁用', 'null' => false])
            ->changeColumn('password', 'string', ['limit' => 255, 'default' => '', 'comment' => '密码', 'null' => false])
            ->changeColumn('salt', 'string', ['limit' => 30, 'default' => '', 'comment' => '密码盐（废弃待删）', 'null' => false])
            ->save();

        Db::name('admin')->where('status', '0')->update(['status' => 'disable']);
        Db::name('admin')->where('status', '1')->update(['status' => 'enable']);

        /**
         * CRUD 历史记录表
         */
        $crudLog = $this->table('crud_log');
        if (!$crudLog->hasColumn('comment')) {
            $crudLog
                ->addColumn('comment', 'string', ['limit' => 255, 'default' => '', 'comment' => '注释', 'null' => false, 'after' => 'table_name'])
                ->addColumn('sync', 'integer', ['default' => 0, 'signed' => false, 'comment' => '同步记录', 'null' => false, 'after' => 'fields'])
                ->save();

            $logs = CrudLog::select();
            foreach ($logs as $log) {
                if ($log->table['comment']) {
                    $log->comment = $log->table['comment'];
                    $log->save();
                }
            }
        }

        /**
         * 多个数据表的 status 字段类型修改为更合理的类型
         */
        $tables = ['admin_group', 'admin_rule', 'user_group', 'user_rule', 'security_data_recycle', 'security_sensitive_data', 'test_build'];
        foreach ($tables as $table) {
            if ($this->hasTable($table)) {
                $mTable = $this->table($table);
                $mTable->changeColumn('status', 'boolean', ['default' => 1, 'signed' => false, 'comment' => '状态:0=禁用,1=启用', 'null' => false])->save();

                // 原状态值兼容至新类型
                Db::name($table)->where('status', 1)->update(['status' => 0]);
                Db::name($table)->where('status', 2)->update(['status' => 1]);
            }
        }
    }
}
