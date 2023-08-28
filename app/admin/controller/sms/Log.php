<?php

namespace app\admin\controller\sms;

use app\common\controller\Backend;

/**
 * 公共 -短信发送记录管理
 *
 */
class Log extends Backend
{
    /**
     * Log模型对象
     * @var object
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'createtime', 'updatetime'];

    protected array|string $quickSearchField = ['id'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\sms\Log;
        $this->request->filter('trim,htmlspecialchars');
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}
