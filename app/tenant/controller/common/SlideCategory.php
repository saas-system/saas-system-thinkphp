<?php

namespace app\tenant\controller\common;

use app\common\controller\TenantBackend as Backend;

/**
 * 轮播图分类
 *
 */
class SlideCategory extends Backend
{
    /**
     * SlideCategory模型对象
     * @var \app\common\model\common\SlideCategory
     */
    protected object $model;

    protected string|array $defaultSortField = 'id';

    protected string|array $preExcludeFields = ['id', 'create_time', 'update_time', 'delete_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\common\SlideCategory;
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}
