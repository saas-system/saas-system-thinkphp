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

    protected string|int|bool $dataLimit = 'parent';

    protected array $withJoinTable = ['admin'];

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

        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->where('admin_log.tenant_id', $this->auth->tenant_id)
            ->where('admin.is_platform_admin', 0) // 只查出租户端管理员
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}
