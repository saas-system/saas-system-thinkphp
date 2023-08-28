<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;


/**
 * 单页管理
 *
 */
class Pages extends Backend
{
    /**
     * Pages模型对象
     * @var \app\common\model\common\Pages
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time', 'delete_time'];

    protected array|string $quickSearchField = ['id', 'title', 'alias'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\common\Pages;
        $this->request->filter('trim,htmlspecialchars');
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 查看
     */
    public function index(): void
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where(['pages.tenant_id' => ''])
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}
