<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;
use app\common\facade\Token;
use app\tenant\library\TenantAuth;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;
use ba\Random;
use Throwable;

/**
 * 租户 - 管理员管理
 *
 */
class Admin extends Backend
{
    /**
     * TenantAdmin模型对象
     * @var \app\admin\model\TenantAdmin
     */
    protected object $model;

    protected array|string $preExcludeFields = ['create_time', 'update_time', 'password', 'salt', 'login_failure', 'last_login_time', 'last_login_ip'];

    protected array|string $quickSearchField = ['mobile', 'username', 'nickname'];

    protected array $withJoinTable = ['tenant'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\TenantAdmin;
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

        $whereNew = [];
        $tenantId = $this->request->param('tenant_id');
        if ($tenantId) {
            $whereNew['tenant_id'] = $tenantId;
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->where($whereNew)
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

            /**
             * 由于有密码字段-对方法进行重写
             * 数据验证
             */
            if ($this->modelValidate) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate;
                    $validate->scene('add')->check($data);
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            $salt   = Random::build('alnum', 16);
            $passwd = encrypt_password($data['password'] ?? '123456', $salt);

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                $data['salt']     = $salt;
                $data['password'] = $passwd;
                $result           = $this->model->save($data);
                if ($data['group_arr']) {
                    $groupAccess = [];
                    foreach ($data['group_arr'] as $datum) {
                        $groupAccess[] = [
                            'uid'       => $this->model->id,
                            'group_id'  => $datum,
                            'tenant_id' => $data['tenant_id'],
                        ];
                    }
                    Db::name('tenant_admin_group_access')->insertAll($groupAccess);
                }

                Db::commit();
            } catch (Throwable $e) {
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
            if ($this->modelValidate && (isset($data['nickname']) && isset($data['username']))) {
                try {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = new $validate;
                    $validate->scene('edit')->check($data);
                } catch (ValidateException $e) {
                    $this->error($e->getMessage());
                }
            }

            if (isset($data['password']) && $data['password']) {
                $this->model->resetPassword($data['id'], $data['password']);
            }

            if (isset($data['group_arr']) && implode(',', $row->group_arr) !== implode(',', $data['group_arr'])) {
                $groupAccess = [];
                foreach ($data['group_arr'] as $datum) {
                    $groupAccess[] = [
                        'uid'       => $row->id,
                        'group_id'  => $datum,
                        'tenant_id' => $data['tenant_id'],
                    ];
                }
                Db::name('tenant_admin_group_access')->where(['uid' => $row->id])->delete();
                Db::name('tenant_admin_group_access')->insertAll($groupAccess);
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
                $result = $row->save($data);
                Db::commit();
            } catch (PDOException|\Exception $e) {
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
     * @param array $ids
     *
     */
    public function del(array $ids = []): void
    {
        $this->error('禁止删除');
    }

    /**
     * 自动登录租户账号
     */
    public function autoLogin($id = null): void
    {
        $tenantAdmin = $this->model->find($id);
        if (!$tenantAdmin) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($tenantAdmin[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        $auth = TenantAuth::instance();

        // 随机生成token
        $token = Random::uuid();
        Token::set($token, 'tenant', $tenantAdmin->id, $auth->keeptime);

        // 登录初始化
        if(!$auth->init($token)){
            $this->error($auth->getError());
        }

        $this->success('登录成功', [
            'userInfo'  => $auth->getInfo(),
            'routePath' => '/tenant'
        ]);
    }
}
