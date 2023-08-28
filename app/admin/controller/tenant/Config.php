<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;

/**
 *
 *
 */
class Config extends Backend
{
    /**
     * Config模型对象
     * @var \app\common\model\tenant\TenantConfig
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array|string $quickSearchField = ['id'];

    protected array|string $withJoinTable = ['tenant'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\tenant\TenantConfig;
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}
