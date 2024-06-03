<?php

namespace app\common\model\common;

use app\common\model\Base;
use think\model\concern\SoftDelete;

/**
 * Slide
 */
class Slide extends Base
{
    const SLIDE_POSITION_MINI_MALL        = 1; // 商城幻灯片
    const SLIDE_POSITION_MINI_MATCH       = 2; // 赛事列表
    const SLIDE_POSITION_MATCH_BACKGROUND = 3; // 比赛背景图

    use SoftDelete;

    // 表名
    protected $name = 'tenant_slide';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 隐藏字段
    protected $hidden = [
        'update_time',
        'delete_time'
    ];

    public function getImageAttr($value)
    {
        return $value ? full_url($value) : '';
    }

    protected static function onAfterInsert($model): void
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function category()
    {
        return $this->belongsTo(\app\common\model\common\SlideCategory::class, 'category_id', 'id');
    }

    /**
     * 获取比赛背景图
     * @return array
     */
    public static function getMatchBackgroundList($tenantId, $ignoreImageId = 0)
    {
        $position = static::SLIDE_POSITION_MATCH_BACKGROUND;
        if ($ignoreImageId) {
            $bgList = Slide::where('position', $position)
                ->where('tenant_id', $tenantId)
                ->where('id', '<>', $ignoreImageId)
                ->select();
        } else {
            $bgList = Slide::where('position', $position)
                ->where('tenant_id', $tenantId)
                ->select();
        }

        $uploadConfig = get_sys_config('upload_cdn_url');

        $newList = [];
        foreach ($bgList as $item) {
            $image     = $item->image;
            $imageUrl  = $uploadConfig . $image;
            $newList[] = [
                'image' => $imageUrl,
                'id'    => $item->id,
            ];
        }
        return $newList;
    }
}
