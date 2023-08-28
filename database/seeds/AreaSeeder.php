<?php

use think\migration\Seeder;
use think\facade\Db;

class AreaSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->truncateData();

        $this->initAreaData();
    }

    protected function truncateData()
    {
        $sqlList = [
            'truncate table area'
        ];

        foreach ($sqlList as $sql) {
            \think\facade\Db::execute($sql);
        }
    }

    protected function initAreaData()
    {
        $sqlPath = root_path() . 'database/area.sql';
        $this->handleSql($sqlPath);
    }

    /**
     * 执行sql
     */
    public function handleSql($sqlPath)
    {
        // 判断是否存在安装sql文件
        if (!is_file($sqlPath)) {
            return false;
        }

        $sqlData = file_get_contents($sqlPath);
        $sqlData = str_replace("{{prefix}}", config("database.connections.mysql.prefix"), $sqlData);

        if ($sqlData) {
            $sqlList = explode(";\n", str_replace("\r", "\n", $sqlData));
            foreach ($sqlList as $sql) {
                if (empty($sql)) {
                    continue;
                }
                // Log::info('---' . $sql);
                $sql = trim($sql);
                if (!empty($sql)) {
                    Db::execute($sql);
                }
            }
        }

        return true;
    }
}
