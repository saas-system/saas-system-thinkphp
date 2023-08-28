<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;

/**
 * 会员 - 基础管理
 *
 */
class User extends Backend
{
    /**
     * User模型对象
     * @var \app\common\model\user\User
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time', 'delete_time'];

    protected array|string $quickSearchField = ['id', 'card_number', 'nickname', 'real_name', 'mobile'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\user\User;
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

}
