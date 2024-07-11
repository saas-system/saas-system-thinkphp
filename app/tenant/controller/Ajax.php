<?php

namespace app\tenant\controller;

use think\Exception;
use think\facade\Cache;
use think\facade\Event;
use app\tenant\model\AdminLog;
use app\common\library\Upload;
use think\exception\FileException;
use app\common\controller\TenantBackend as Backend;

class Ajax extends Backend
{
    protected array $noNeedPermission = ['*'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload()
    {
        AdminLog::instance()->setTitle(__('upload'));
        $file = $this->request->file('file');
        try {
            $upload     = new Upload($file);
            $attachment = $upload->upload(null, $this->auth->id, $this->auth->tenant_id);
            unset($attachment['createtime'], $attachment['quote']);
        } catch (Exception|FileException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment ?? []
        ]);
    }

    public function clearCache()
    {
        $type = $this->request->post('type');
        if ($type == 'tp' || $type == 'all') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }
}
