<?php

use think\facade\Db;
use think\migration\Migrator;

class Version206 extends Migrator
{
    /**
     * @throws Throwable
     */
    public function up(): void
    {
        $exist = Db::name('platform_config')->where('name', 'backend_entrance')->value('id');
        if (!$exist) {
            $rows  = [
                [
                    'name'  => 'backend_entrance',
                    'group' => 'basics',
                    'title' => 'Backend entrance',
                    'type'  => 'string',
                    'value' => '/platform',
                    'rule'  => 'required',
                    'weigh' => 1,
                ],
            ];
            $table = $this->table('platform_config');
            $table->insert($rows)->saveData();
        }
    }
}
