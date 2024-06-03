<?php

namespace app\admin\controller\security;

use Throwable;
use app\common\controller\Backend;
use app\admin\model\DataRecycle as DataRecycleModel;

class DataRecycle extends Backend
{
    /**
     * @var object
     * @phpstan-var DataRecycleModel
     */
    protected object $model;

    // 排除字段
    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected string|array $quickSearchField = 'name';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new DataRecycleModel();
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

            $data                  = $this->excludeFields($data);
            $data['controller_as'] = str_ireplace('.php', '', $data['controller'] ?? '');
            $data['controller_as'] = strtolower(str_ireplace(['\\', '.'], '/', $data['controller_as']));

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate)
                            $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
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

        // 放在add方法内，就不需要额外添加权限节点了
        $app = $this->request->get('app', 'admin');
        $this->success('', [
            'controllers' => $this->getControllerList($app),
            'apps'        => $this->getAppList(),
        ]);
    }

    /**
     * 编辑
     * @param int|string|null $id
     * @throws Throwable
     */
    public function edit(int|string $id = null): void
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data                  = $this->excludeFields($data);
            $data['controller_as'] = str_ireplace('.php', '', $data['controller'] ?? '');
            $data['controller_as'] = strtolower(str_ireplace(['\\', '.'], '/', $data['controller_as']));

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate)
                            $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $row->save($data);
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
        $app = $this->request->get('app', $row->app);

        $this->success('', [
            'row'         => $row,
            'controllers' => $this->getControllerList($app),
            'apps'        => $this->getAppList(),
        ]);
    }

    protected function getControllerList($app = 'admin'): array
    {
        $outExcludeController = [
            'Addon.php',
            'Ajax.php',
            'Module.php',
            'Terminal.php',
            'Dashboard.php',
            'Index.php',
            'routine/AdminInfo.php',
            'user/MoneyLog.php',
            'user/ScoreLog.php',
        ];
        $outControllers       = [];
        $controllers          = get_controller_list($app);
        foreach ($controllers as $key => $controller) {
            if (!in_array($controller, $outExcludeController)) {
                $outControllers[$key] = $controller;
            }
        }
        return $outControllers;
    }

    protected function getAppList(): array
    {
        return [
            'admin'  => '平台端 - admin',
            'tenant' => '租户端 - tenant',
        ];
    }
}
