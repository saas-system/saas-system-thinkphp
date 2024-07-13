<?php

use think\migration\Seeder;
use think\facade\Db;

class InitAreaSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        $this->truncateData();

        $this->initAreaData();
    }

    protected function truncateData(): void
    {
        $sqlList = [
            'truncate table area'
        ];

        foreach ($sqlList as $sql) {
            \think\facade\Db::execute($sql);
        }
    }

    protected function initAreaData(): bool
    {
        // 读取 city.json 文件内容
        $data = root_path() . 'database/seeds/areaData.json';

        $data = json_decode(file_get_contents($data), true);

        // 定义插入数据的数组
        $areas = [];

        // 遍历 province_list
        foreach ($data['province_list'] as $id => $name) {
            $areas[] = [
                'id' => $id,
                'name' => $name,
                'pid' => 0,
                'level' => 1
            ];
        }

        // 遍历 city_list
        foreach ($data['city_list'] as $id => $name) {
            // 获取该市所属的省份 ID
            $provinceId = substr($id, 0, 2) . '0000';
            $areas[] = [
                'id' => $id,
                'name' => $name,
                'pid' => $provinceId,
                'level' => 2
            ];
        }

        // 遍历 county_list
        foreach ($data['county_list'] as $id => $name) {
            // 获取该区县所属的城市 ID
            $cityId = substr($id, 0, 4) . '00';
            $areas[] = [
                'id' => $id,
                'name' => $name,
                'pid' => $cityId,
                'level' => 3
            ];
        }

        // 插入数据到数据库
        $this->table('area')->insert($areas)->save();

        return true;
    }
}
