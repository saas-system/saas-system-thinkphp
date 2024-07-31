<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;

/**
 * 平台 - 业务员管理
 *
 */
class BusinessAdmin extends Backend
{
    /**
     * BusinessAdmin模型对象
     * @var \app\common\model\tenant\BusinessAdmin
     */
    protected $model = null;
    
    protected $preExcludeFields = ['id', 'create_time', 'update_time', 'delete_time'];

    protected $quickSearchField = ['id'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\common\model\tenant\BusinessAdmin;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}