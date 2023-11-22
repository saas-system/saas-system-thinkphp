<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class TenantWalletTable extends Migrator
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
        // 编号记录生成表
        $table = $this->table('tenant_number_generate_record', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '编号记录生成表']);
        $table->addColumn('type', 'string', ['limit' => 30, 'default' => '', 'comment' => '单号类型'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('prefix', 'string', ['limit' => 100, 'default' => '', 'comment' => '前缀'])
            ->addColumn('year', 'string', ['limit' => 6, 'default' => '', 'comment' => '年'])
            ->addColumn('month', 'string', ['limit' => 2, 'default' => '', 'comment' => '月'])
            ->addColumn('number', 'integer', ['limit' => 11, 'default' => 1, 'comment' => '最后的数字'])
            ->addColumn('create_time', 'integer', ['comment' => '添加时间'])
            // 索引相关
            ->addIndex('type')
            ->addIndex('month')
            ->addIndex('number')
            ->addIndex(['tenant_id'])
            ->create();

        // 钱包表
        $table = $this->table('tenant_wallet', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '钱包 - 钱包表']);
        $table->addColumn('account_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '账户ID'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])
            ->addColumn('user_type', 'string', ['limit' => 10, 'default' => '', 'comment' => '用户类型：U=用户,P=平台,A=租户'])
            ->addColumn('user_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '用户ID'])
            ->addColumn('balance', 'decimal', ['scale' => 2, 'precision' => 10, 'default' => 0.00, 'comment' => '账户余额'])
            ->addColumn('income_amount', 'decimal', ['scale' => 2, 'precision' => 10, 'default' => 0.00, 'comment' => '总收入金额'])
            ->addColumn('outcome_amount', 'decimal', ['scale' => 2, 'precision' => 10, 'default' => 0.00, 'comment' => '总支出金额'])
            ->addColumn('freeze_amount', 'decimal', ['scale' => 2, 'precision' => 10, 'default' => 0.00, 'comment' => '冻结金额'])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '钱包状态标志:1=正常,2=冻结'])
            // 时间相关
            ->addColumn('create_time', 'integer', ['comment' => '添加时间'])
            ->addColumn('update_time', 'integer', ['comment' => '修改时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'null' => true])
            // 索引相关
            ->addIndex('account_id')
            ->addIndex('user_type')
            ->addIndex('user_id')
            ->create();

        //账户表
        $table = $this->table('tenant_wallet_account', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '钱包 - 账户表']);
        $table->addColumn('account_type_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '账户类型ID'])
            ->addColumn('account_type_name', 'string', ['limit' => 11, 'comment' => '账户类型名称'])
            ->addColumn('name', 'string', ['limit' => 100, 'comment' => '账户名称'])
            ->create();

        //账户类型表
        $table = $this->table('tenant_wallet_account_type', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '钱包 - 账户类型表']);
        $table->addColumn('name', 'string', ['limit' => 100, 'comment' => '账户类型'])
            ->addColumn('memo', 'string', ['limit' => 50, 'comment' => '简称'])
            ->create();

        //角色表
        $table = $this->table('tenant_wallet_role', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '钱包 - 角色表']);
        $table->addColumn('name', 'string', ['limit' => 100, 'comment' => '名称'])
            ->addColumn('short_name', 'string', ['limit' => 30, 'comment' => '简称'])
            ->create();

        //流水表
        $table = $this->table('tenant_wallet_flow', ['collation' => 'utf8mb4_unicode_ci', 'comment' => '钱包 - 流水表']);
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('tenant_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '租户ID'])

            //账户
            ->addColumn('from_account_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '来源账户ID'])
            ->addColumn('from_wallet_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '来源钱包ID'])
            ->addColumn('to_account_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '去向账户ID'])
            ->addColumn('to_wallet_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '去向钱包ID'])

            //订单
            ->addColumn('order_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '订单ID'])
            ->addColumn('order_number', 'string', ['limit' => 30, 'comment' => '订单号'])
            ->addColumn('order_type', 'string', ['comment' => '订单类型model'])

            //交易人员
            ->addColumn('trade_from_user_type', 'string', ['limit' => 10, 'comment' => '来源用户类型:U=用户,P=平台,A=代理商'])
            ->addColumn('trade_from_user_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '来源用户ID'])
            ->addColumn('trade_from_user_name', 'string', ['limit' => 10, 'comment' => '来源用户名称'])
            ->addColumn('trade_to_user_type', 'string', ['limit' => 10, 'comment' => '去向用户类型:U=用户,P=平台,A=代理商'])
            ->addColumn('trade_to_user_id', 'string', ['limit' => 64, 'default' => '', 'comment' => '去向用户ID'])
            ->addColumn('trade_to_user_name', 'string', ['limit' => 10, 'comment' => '去向用户名称'])

            //交易信息
            ->addColumn('trade_title', 'string', ['comment' => '交易标题'])
            ->addColumn('trade_number', 'string', ['limit' => 30, 'comment' => '交易号'])
            ->addColumn('trade_amount', 'decimal', ['scale' => 2, 'precision' => 10, 'default' => 0.00, 'comment' => '交易金额'])
            ->addColumn('user_balance', 'decimal', ['scale' => 2, 'precision' => 16, 'default' => 0.00, 'comment' => '用户余额'])
            ->addColumn('tenant_balance', 'decimal', ['scale' => 2, 'precision' => 16, 'default' => 0.00, 'comment' => '租户余额'])
            ->addColumn('trade_time', 'string', ['limit' => 10, 'default' => '', 'comment' => '交易时间'])
            ->addColumn('trade_content', 'text', ['comment' => '交易json报文明细'])

            //其他
            ->addColumn('business_type', 'integer', ['length' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '业务类型'])
            ->addColumn('pay_type', 'boolean', ['default' => 0, 'comment' => '支付类型'])
            ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
            ->addColumn('extra', 'text', ['comment' => '扩展字段'])
            ->addColumn('admin_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '操作管理员ID'])
            ->addColumn('is_user_visible', 'boolean', ['default' => 1, 'comment' => '是否用户可见：0=否 1=是'])

            // 时间相关
            ->addColumn('create_time', 'integer', ['comment' => '添加时间'])
            ->addColumn('update_time', 'integer', ['comment' => '修改时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'null' => true])

            // 索引相关
            ->addIndex('from_account_id')
            ->addIndex('from_wallet_id')
            ->addIndex('to_account_id')
            ->addIndex('to_wallet_id')
            ->addIndex('order_number')
            ->create();
    }
}
