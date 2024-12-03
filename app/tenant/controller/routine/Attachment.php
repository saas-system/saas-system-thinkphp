<?php

namespace app\tenant\controller\routine;

use ba\Filesystem;
use think\facade\Event;
use app\common\controller\TenantBackend as Backend;
use app\common\model\Attachment as AttachmentModel;
use Throwable;

class Attachment extends Backend
{
    protected object $model;

    protected string|array $quickSearchField = 'name';

    protected array $withJoinTable = ['admin', 'user'];

    protected string|array $defaultSortField = 'id,desc';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AttachmentModel();
    }

    /**
     * 删除
     * @param array $ids
     */
    public function del(): void
    {
        $ids = $this->request->param('ids/a', []);

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        try {
            foreach ($data as $v) {
                Event::trigger('AttachmentDel', $v);
                $filePath = Filesystem::fsFit(public_path() . ltrim($v->url, '/'));
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Filesystem::delEmptyDir(dirname($filePath));
                }
                $count += $v->delete();
            }
        } catch (Throwable $e) {
            $this->error(__('%d records and files have been deleted', [$count]) . $e->getMessage());
        }
        if ($count) {
            $this->success(__('%d records and files have been deleted', [$count]));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}
