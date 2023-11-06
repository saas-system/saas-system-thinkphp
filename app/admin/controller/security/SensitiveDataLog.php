<?php

namespace app\admin\controller\security;

use Throwable;
use think\facade\Db;
use app\common\controller\Backend;
use app\admin\model\SensitiveDataLog as SensitiveDataLogModel;

class SensitiveDataLog extends Backend
{
    /**
     * @var object
     * @phpstan-var SensitiveDataLogModel
     */
    protected object $model;

    // 排除字段
    protected string|array $preExcludeFields = [];

    protected string|array $quickSearchField = 'sensitive.name';

    protected array $withJoinTable = ['sensitive', 'tenant', 'pmadmin', 'tadmin'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new SensitiveDataLogModel();
    }

    /**
     * 查看
     * @throws Throwable
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

        list($where, $alias, $limit, $order) = $this->queryBuilder(['admin.nickname']);
        $query = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->with('admin')
            ->alias($alias);

        $searchs = $this->request->get("search/a", []);

        foreach ($searchs as $search) {
            $field = $search['field'];

            if ($field == 'admin.nickname') {
                // $query->hasWhere('admin', function ($q) use ($search) {
                //     $q->where('nickname', 'like', "%{$search['val']}%");
                // });

                $query->where(function ($q) use ($search) {
                    $q->where('pmadmin.nickname', 'like', "%{$search['val']}%")
                        ->whereOr('tadmin.nickname', 'like', "%{$search['val']}%");
                });
            }
        }

        $res = $query->where($where)
            ->order($order)
            ->paginate($limit);

        foreach ($res->items() as $item) {
            $item->id_value    = $item['primary_key'] . '=' . $item->id_value;
            $tenant            = $item->tenant;
            $item->tenant_name = $tenant ? $tenant->name : '平台';
        }
        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 详情
     * @param string|int|null $id
     * @throws Throwable
     */
    public function info(string|int $id = null): void
    {
        $row = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->where('sensitive_data_log.id', $id)
            ->find();
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 回滚
     * @param array|null $ids
     * @throws Throwable
     */
    public function rollback(array $ids = null): void
    {
        $data = $this->model->where('id', 'in', $ids)->select();
        if (!$data) {
            $this->error(__('Record not found'));
        }

        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $row) {
                if (Db::name($row->data_table)->where($row->primary_key, $row->id_value)->update([
                    $row->data_field => $row->before
                ])) {
                    $row->delete();
                    $count++;
                }
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were rollback'));
        }
    }
}
