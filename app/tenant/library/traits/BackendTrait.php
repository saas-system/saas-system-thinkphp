<?php

namespace app\tenant\library\traits;

use think\facade\Config;
use think\facade\Db;
use Throwable;

/**
 * 后台控制器trait类
 * 已导入到 @var \app\common\controller\TenantBackend 中
 * 若需修改此类方法：请复制方法至对应控制器后进行重写
 */
trait BackendTrait
{
    /**
     * 排除入库字段
     * @param $params
     * @return mixed
     */
    protected function excludeFields($params): array
    {
        if (!is_array($this->preExcludeFields)) {
            $this->preExcludeFields = explode(',', (string)$this->preExcludeFields);
        }

        foreach ($this->preExcludeFields as $field) {
            if (array_key_exists($field, $params)) {
                unset($params[$field]);
            }
        }
        return $params;
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
        $fields = array_keys($this->model->getFields());
        $query  = $this->model;
        if (in_array('tenant_id', $fields)) {
            $query = $query->permission($this->auth->tenant_id, empty($this->withJoinTable) ? null : array_values($alias)[0]);
        }
        $res = $query->field($this->indexField)
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

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }

            $result = false;
            Db::startTrans();
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

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $id  = $this->request->param($this->model->getPk());
        $fields = array_keys($this->model->getFields());
        $query  = $this->model;
        if (in_array('tenant_id', $fields)) {
            $query = $query->permission($this->auth->tenant_id);
        }
        $row = $query->find($id);
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

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
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
                Db::commit();
            } catch (Throwable $e) {
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

    /**
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(): void
    {
        $ids = $this->request->param('ids/a', []);

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
                $count += $v->delete();
            }
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 排序
     * @param int $id 排序主键值
     * @param int $targetId 排序位置主键值
     */
    public function sortable(int $id, int $targetId): void
    {
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $row    = $this->model->find($id);
        $target = $this->model->find($targetId);

        if (!$row || !$target) {
            $this->error(__('Record not found'));
        }
        if ($row[$this->weighField] == $target[$this->weighField]) {
            $autoSortEqWeight = is_null($this->autoSortEqWeight) ? Config::get('buildadmin.auto_sort_eq_weight') : $this->autoSortEqWeight;
            if (!$autoSortEqWeight) {
                $this->error(__('Invalid collation because the weights of the two targets are equal'));
            }

            // 自动重新整理排序
            $all = $this->model->select();
            foreach ($all as $item) {
                $item[$this->weighField] = $item[$this->model->getPk()];
                $item->save();
            }
            unset($all);
            // 重新获取
            $row    = $this->model->find($id);
            $target = $this->model->find($targetId);
        }

        $ebak                      = $target[$this->weighField];
        $target[$this->weighField] = $row[$this->weighField];
        $row[$this->weighField]    = $ebak;
        $row->save();
        $target->save();

        $this->success();
    }

    /**
     * 加载为select(远程下拉选择框)数据，默认还是走$this->index()方法
     * 必要时请在对应控制器类中重写
     */
    public function select(): void
    {

    }
}
