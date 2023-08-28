<?php

namespace app\admin\controller\sms;

use app\common\controller\Backend;
use modules\sms\library\Helper;

/**
 * 模板变量管理
 *
 */
class Variable extends Backend
{
    /**
     * Variable模型对象
     * @var \app\admin\model\sms\Variable
     */
    protected object $model;

    protected array|string $quickSearchField = ['title', 'name'];

    protected array|string $defaultSortField = 'id,desc';

    protected array|string $preExcludeFields = ['create_time', 'update_time'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\sms\Variable;
    }

    public function getVar($id)
    {
        $val = Helper::calcVar($id);
        if ($val) {
            $this->success('', $val);
        }
        $this->error('计算失败，请检查变量设置');
    }

}
