<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;
use app\common\model\tenant\TenantConfig;
use ba\Random;
use think\exception\ValidateException;
use think\facade\Db;

/**
 *
 *
 */
class Config extends Backend
{
    /**
     * Config模型对象
     * @var \app\common\model\tenant\TenantConfig
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array|string $quickSearchField = ['id'];

    protected array $withJoinTable = ['tenant'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\tenant\TenantConfig;
    }

    /**
     * 编辑
     */
    public function edit(): void
    {
        $id    = $this->request->param('id');
        $model = new \app\common\model\tenant\TenantConfig();
        $row   = $model->find($id);

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $tenantPre = $data['tenant_pre'] ?? '';

            if ($tenantPre) {
                if ($id) {
                    $result = TenantConfig::where('tenant_pre', $tenantPre)->where('id', '<>', $id)->find();
                } else {
                    $result = TenantConfig::where('tenant_pre', $tenantPre)->find();
                }

                if ($result) {
                    $this->error('租户前缀已存在，请重新输入');
                }
            }


            $result = false;
            Db::startTrans();
            try {
                if (!$row) {
                    unset($data['id']);
                    $result = $model->save($data);
                } else {
                    $result = $row->save($data);
                }

                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }
}
