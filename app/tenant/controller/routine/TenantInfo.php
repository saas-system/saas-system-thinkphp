<?php

namespace app\tenant\controller\routine;

use app\common\model\Area;
use app\common\model\tenant\AppVersion;
use app\common\model\tenant\TenantConfig;
use app\tenant\model\Tenant;
use app\common\controller\TenantBackend as Backend;
use think\facade\Db;
use Throwable;

class TenantInfo extends Backend
{
    protected object $model;

    // 排除字段
    protected array|string $preExcludeFields = ['status', 'delete_time', 'mobile', 'name', 'expire_time', 'expire_time_text', 'address', 'province_id', 'city_id', 'district_id'];

    // 输出字段
    protected array $authAllowFields = ['id', 'name', 'short_name', 'logo', 'contact_name', 'mobile', 'expire_time', 'address', 'province_id', 'city_id', 'district_id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new Tenant();
    }

    // 获取基础信息
    public function index(): void
    {
        $tenantId = $this->auth->tenant_id;
        $info     = Tenant::field($this->authAllowFields)
            ->with(['config'])
            ->where('id', $tenantId)
            ->find();

        if ($info) {
            $provinceName = Area::getAreaName($info->province_id);
            $cityName     = Area::getAreaName($info->city_id);
            $districtName = Area::getAreaName($info->district_id);

            $fullAddress        = $provinceName . $cityName . $districtName . $info->address;
            $info->full_address = $fullAddress;

            // app下载地址
            $appVersion = AppVersion::order('version_code', 'desc')->find();
            if ($appVersion) {
                $info->app_download_url = $appVersion->url;
            }
        }

        $this->success('', [
            'info' => $info
        ]);
    }

    public function edit($id = null): void
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

            if (isset($data['logo']) && $data['logo']) {
                $row->logo = $data['logo'];
                if ($row->save()) {
                    $this->success(__('Avatar modified successfully!'));
                }
            }

            $data   = $this->excludeFields($data);
            $result = false;
            Db::startTrans();
            try {
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
    }
}
