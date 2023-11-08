<?php

namespace app\tenant\controller\auth;

use ba\Random;
use Exception;
use app\common\controller\TenantBackend as Backend;
use app\tenant\model\Admin as AdminModel;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;

class Admin extends Backend
{
    /**
     * @var AdminModel
     */
    protected object $model;

    protected array|string $preExcludeFields = ['createtime', 'updatetime', 'password', 'salt', 'login_failure', 'last_login_time', 'last_login_ip'];

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
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->permission($this->auth->tenant_id)
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

    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            /**
             * 由于有密码字段-对方法进行重写
             * 数据验证
             */
            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate;
                    $validate->scene('add')->check($data);
                } catch (ValidateException $e) {
                    $this->error($e->getMessage());
                }
            }

            $salt   = Random::build('alnum', 16);
            $passwd = encrypt_password($data['password'], $salt);

            $data   = $this->excludeFields($data);
            $result = false;
            if ($data['group_arr'])
                $this->checkGroupAuth($data['group_arr']);
            Db::startTrans();
            try {
                $data['salt']      = $salt;
                $data['password']  = $passwd;
                $data['tenant_id'] = $this->auth->tenant_id;
                $result            = $this->model->save($data);
                if ($data['group_arr']) {
                    $groupAccess = [];
                    foreach ($data['group_arr'] as $datum) {
                        $groupAccess[] = [
                            'uid'       => $this->model->id,
                            'group_id'  => $datum,
                            'tenant_id' => $this->auth->tenant_id,
                        ];
                    }
                    Db::name($this->adminGroupAccessTable)->insertAll($groupAccess);
                }

                Db::commit();
            } catch (ValidateException|PDOException|Exception $e) {
                Db::rollback();
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

    public function edit($id = null): void
    {
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

            /**
             * 由于有密码字段-对方法进行重写
             * 数据验证
             */
            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate;
                    $validate->scene('edit')->check($data);
                } catch (ValidateException $e) {
                    $this->error($e->getMessage());
                }
            }

            if ($this->auth->id == $data['id'] && $data['status'] == '0') {
                $this->error(__('Please use another administrator account to disable the current account!'));
            }

            if (isset($data['password']) && $data['password']) {
                $this->model->resetPassword($data['id'], $data['password']);
            }

            $groupAccess = [];
            if ($data['group_arr']) {
                $checkGroups = [];
                foreach ($data['group_arr'] as $datum) {
                    if (!in_array($datum, $row->group_arr)) {
                        $checkGroups[] = $datum;
                    }
                    $groupAccess[] = [
                        'uid'       => $id,
                        'group_id'  => $datum,
                        'tenant_id' => $this->auth->tenant_id,
                    ];
                }
                $this->checkGroupAuth($checkGroups);
            }

            Db::name($this->adminGroupAccessTable)
                ->where('uid', $id)
                ->delete();

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {

                $result = $row->save($data);
                if ($groupAccess) {
                    Db::name($this->adminGroupAccessTable)->insertAll($groupAccess);
                }
                Db::commit();
            } catch (PDOException|Exception $e) {
                Db::rollback();
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
     * @param null $ids
     */
    public function del($ids = null): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $v) {
                if ($v->id != $this->auth->id) {
                    $count += $v->delete();
                    Db::name($this->adminGroupAccessTable)
                        ->where('uid', $v['id'])
                        ->delete();
                }
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    public function checkGroupAuth(array $groups)
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
