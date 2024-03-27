<?php

namespace app\admin\controller\tenant;

use app\common\controller\Backend;
use app\common\model\tenant\TenantConfig;
use app\common\services\tenant\TenantService;
use ba\Random;
use think\db\exception\PDOException;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

/**
 * 租户 - 租户信息管理
 *
 */
class Tenant extends Backend
{
    /**
     * Tenant模型对象
     * @var \app\admin\model\Tenant
     */
    protected object $model;

    protected string|array $defaultSortField = 'create_time,desc';

    protected string|array $preExcludeFields = ['create_time', 'update_time'];

    protected string|array $quickSearchField = ['name', 'mobile'];

    protected array $withJoinTable = ['province', 'city', 'district', 'config'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Tenant;
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
            ->field($this->indexField)
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
                        $validate = new $validate;
                        if ($this->modelSceneValidate)
                            $validate->scene('add');
                        $validate->check($data);
                    }
                }

                $mobile = $data['mobile'];

                // $isRepeat = TenantAdmin::where('mobile', $mobile)->find();
                // if ($isRepeat) {
                //     throw new Exception('创建失败，手机号已存在');
                // }

                $data['id'] = Random::uuid();
                // $areaIds             = $data['area_ids'];
                // $data['province_id'] = $areaIds[0];
                // $data['city_id']     = $areaIds[1];
                // $data['district_id'] = $areaIds[2];
                $result = $this->model->save($data);

                // 初始化租户管理员信息
                $ip = $this->request->ip();
                TenantService::initTenantAdminGroup($this->model);

                Db::commit();
            } catch (ValidateException|PDOException|\Exception $e) {
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
     * 删除
     * @param array $ids
     */
    public function del(array $ids = []): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $this->error('禁止删除');

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
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

    /**
     * 获取配置
     */
    public function getTenantConfig(): void
    {
        $id  = $this->request->param('id');
        $row = (new \app\common\model\tenant\TenantConfig())->where(['tenant_id' => $id])->find();
        $this->success('成功', $row);
    }

    /**
     * 初始化租户数据
     */
    public function initData(): void
    {
        $tenantId = $this->request->post('id');
        if (!$tenantId) {
            $this->error('请传租户ID');
        }

        $row = $this->model->find($tenantId);
        if (!$row) {
            $this->error('租户不存在');
        }

        // 启动事务
        Db::startTrans();
        try {
            /* 1、初始化轮播图 */
            $slideCategoryModel = new \app\common\model\common\SlideCategory;
            $slideModel         = new \app\common\model\common\Slide;

            $slideCategoryModel->where('tenant_id', $tenantId)->delete();
            $slideModel->where('tenant_id', $tenantId)->delete();

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Log::critical("初始化租户数据发生错误：" . $e->__toString());
            // 回滚事务
            Db::rollback();

            $this->error('初始化数据失败');
        }

        $this->success('初始化数据成功');
    }

    /**
     * 清除租户数据
     * @return void
     */
    public function clearData(): void
    {
        $tenantId = $this->request->post('id');
        if (!$tenantId) {
            $this->error('请传租户ID');
        }

        $row = $this->model->find($tenantId);
        if (!$row) {
            $this->error('租户不存在');
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        // 启动事务
        Db::startTrans();
        try {
            // 清除租户相关表
            $deleteTables = [
                'tenant_admin_log' => 0,
                'tenant_pages'     => 1,
            ];

            foreach ($deleteTables as $tableName => $isSoft) {
                $db = Db::table($tableName)->where('tenant_id', $tenantId);
                if ($isSoft) {
                    $db = $db->whereNull('delete_time')->useSoftDelete('delete_time', time());
                }
                $db->delete();
            }

            // 提交事务
            Db::commit();

        } catch (\Exception $e) {
            Log::critical("清除租户数据发生错误：" . $e->__toString());
            // 回滚事务
            Db::rollback();

            $this->error('清除数据失败');
        }

        $this->success('清除数据成功');
    }

    /**
     * 导出租户
     */
    public function exportTenant()
    {
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($this->auth->id, $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        return (new TenantService())->exportTenant($this->queryBuilder());
    }
}
