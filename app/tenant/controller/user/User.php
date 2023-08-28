<?php

namespace app\tenant\controller\user;

use app\common\controller\TenantBackend;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

/**
 * 会员 - 基础管理
 *
 */
class User extends TenantBackend
{
    /**
     * User模型对象
     * @var
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time', 'delete_time'];

    protected array|string $quickSearchField = ['id', 'card_number', 'nickname', 'real_name', 'mobile'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\user\User();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 获取用户的基本信息
     */
    public function findUserInfo()
    {
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            if (empty($id)) {
                $this->error("请选择用户！");
            }
            $data = $this->model
                ->permission($this->auth->tenant_id)
                ->find($id);
            $this->success('获取成功', $data);
        }
        $this->error('获取失败');
    }


    /**
     * 导出用户
     */
    public function exportUser()
    {
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($this->auth->id, $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $result = null;

            try {
                $result = '';

            } catch (Exception|\PhpOffice\PhpSpreadsheet\Exception $e) {
                $this->error('导出会员信息失败', $e);
            }

            if (!$result) {
                $this->error('导出会员信息失败');
            }

            $this->success('导出会员信息成功', '/storage/' . $result . '.xlsx');
        }

        $this->error('导出会员信息失败');
    }

}
