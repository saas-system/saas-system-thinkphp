<?php

namespace app\common\model;

use think\model;

class Area extends model
{
    // 表名
    protected $name = 'area';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'false';
    protected $updateTime = false;

    // 省数据
    public static function getProvinceList()
    {
        return Area::where('pid', 0)->select();
    }

    // 城市数据
    public static function getCityList($pid)
    {
        return Area::where('pid', $pid)
            ->where('level', 2)
            ->select();
    }

    // 区数据
    public static function getDistrictList($pid)
    {
        return Area::where('pid', $pid)
            ->where('level', 3)
            ->select();
    }

    // 获取区域名称
    public static function getAreaName($id)
    {
        return Area::where('id', $id)->value('name');
    }

    public function children()
    {
        return $this->hasMany(Area::class, 'pid', 'id');
    }

}
