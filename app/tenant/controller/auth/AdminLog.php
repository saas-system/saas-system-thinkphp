<?php

namespace app\tenant\controller\auth;

use app\common\controller\TenantBackend as Backend;
use app\tenant\model\AdminLog as AdminLogModel;

class AdminLog extends Backend
{
    /**
     * @var AdminLogModel
     */
    protected object $model;

    protected array|string $preExcludeFields = ['createtime', 'admin_id', 'username'];

    protected array|string $quickSearchField = ['title'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AdminLogModel();
    }

    /**
     * 查看
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        if (!$this->auth->isSuperAdmin()) {
            $where[] = ['admin_id', '=', $this->auth->id];
        }
        $res = $this->model
            ->permission($this->auth->tenant_id)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
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
