<?php

namespace app\common\model;

use app\tenant\model\TenantUser;
use Throwable;
use think\Model;
use ba\Filesystem;
use think\facade\Event;
use app\admin\model\Admin;
use think\model\relation\BelongsTo;

/**
 * Attachment模型
 */
class Attachment extends Base
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    protected $append = [
        'suffix',
        'full_url'
    ];

    public function getSuffixAttr($value, $row): string
    {
        if ($row['name']) {
            $suffix = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
            return $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        }
        return 'file';
    }

    public function getFullUrlAttr($value, $row): string
    {
        return full_url($row['url']);
    }

    /**
     * 新增前
     * @throws Throwable
     */
    protected static function onBeforeInsert($model): bool
    {
        $repeat = $model->where([
            ['sha1', '=', $model->sha1],
            ['topic', '=', $model->topic],
            ['storage', '=', $model->storage],
        ])->find();
        if ($repeat) {
            $storageFile = Filesystem::fsFit(public_path() . ltrim($repeat['url'], '/'));
            if ($model->storage == 'local' && !file_exists($storageFile)) {
                $repeat->delete();
                return true;
            } else {
                $repeat->quote++;
                $repeat->last_upload_time = time();
                $repeat->save();
                return false;
            }
        }
        return true;
    }

    /**
     * 新增后
     */
    protected static function onAfterInsert($model): void
    {
        Event::trigger('AttachmentInsert', $model);

        if (!$model->last_upload_time) {
            $model->quote            = 1;
            $model->last_upload_time = time();
            $model->save();
        }
    }

    /**
     * 删除后
     */
    protected static function onAfterDelete($model): void
    {
        Event::trigger('AttachmentDel', $model);

        $filePath = Filesystem::fsFit(public_path() . ltrim($model->url, '/'));
        if (file_exists($filePath)) {
            unlink($filePath);
            Filesystem::delEmptyDir(dirname($filePath));
        }
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'user_id', 'id');
    }
}
