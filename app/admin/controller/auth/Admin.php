<?php

namespace app\admin\controller\auth;

use Throwable;
use think\facade\Db;
use app\common\controller\Backend;
use app\admin\model\Admin as AdminModel;

class Admin extends Backend
{
    /**
     * 模型
     * @var object
     * @phpstan-var AdminModel
     */
    protected object $model;

    protected array|string $preExcludeFields = ['create_time', 'update_time', 'password', 'salt', 'login_failure', 'last_login_time', 'last_login_ip'];

    protected array|string $quickSearchField = ['username', 'nickname'];

    /**
     * 开启数据限制
     */
    protected string|int|bool $dataLimit = 'allAuthAndOthers';

    protected string $dataLimitField = 'id';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AdminModel();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withoutField('login_failure,password,salt')
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

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate();
                    $validate->scene('add')->check($data);
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            $passwd = $data['password']; // 密码将被排除不直接入库

            $data   = $this->excludeFields($data);
            $result = false;
            if ($data['group_arr'])
                $this->checkGroupAuth($data['group_arr']);
            $this->model->startTrans();
            try {
                $result = $this->model->save($data);
                if ($data['group_arr']) {
                    $groupAccess = [];
                    foreach ($data['group_arr'] as $datum) {
                        $groupAccess[] = [
                            'uid'      => $this->model->id,
                            'group_id' => $datum,
                        ];
                    }
                    Db::name($this->adminGroupAccessTable)->insertAll($groupAccess);
                }
                $this->model->commit();

                if (!empty($passwd)) {
                    $this->model->resetPassword($this->model->id, $passwd);
                }
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate();
                    $validate->scene('edit')->check($data);
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            if ($this->auth->id == $data['id'] && $data['status'] == 'disable') {
                $this->error(__('Please use another administrator account to disable the current account!'));
            }

            if (!empty($data['password'])) {
                $this->model->resetPassword($row->id, $data['password']);
            }

            $groupAccess = [];
            if ($data['group_arr']) {
                $checkGroups = [];
                foreach ($data['group_arr'] as $datum) {
                    if (!in_array($datum, $row->group_arr)) {
                        $checkGroups[] = $datum;
                    }
                    $groupAccess[] = [
                        'uid'      => $id,
                        'group_id' => $datum,
                    ];
                }
                $this->checkGroupAuth($checkGroups);
            }

            Db::name($this->adminGroupAccessTable)
                ->where('uid', $id)
                ->delete();

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                $result = $row->save($data);
                if ($groupAccess) {
                    Db::name($this->adminGroupAccessTable)->insertAll($groupAccess);
                }

                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        unset($row['salt'], $row['login_failure']);
        $row['password'] = '';
        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * @throws Throwable
     */
    public function del(): void
    {
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids     = $this->request->param('ids/a', []);
        $where[] = [$this->model->getPk(), 'in', $ids];
        $data    = $this->model->where($where)->select();

        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                if ($v->id != $this->auth->id) {
                    $count += $v->delete();
                    Db::name($this->adminGroupAccessTable)
                        ->where('uid', $v['id'])
                        ->delete();
                }
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 检查分组权限
     * @throws Throwable
     */
    private function checkGroupAuth(array $groups): void
    {
        if ($this->auth->isSuperAdmin()) {
            return;
        }
        $authGroups = $this->auth->getAllAuthGroups('allAuthAndOthers');
        foreach ($groups as $group) {
            if (!in_array($group, $authGroups)) {
                $this->error(__('You have no permission to add an administrator to this group!'));
            }
        }
    }
}
