<?php

namespace app\tenant\library\traits;


trait PermissionTrait
{
    /**
     * 过滤租户端权限
     *
     * @param $query
     * @param $tenantId
     * @param $modelType
     * @return mixed
     */
    public function scopePermission($query, $tenantId, $modelType = '')
    {
        if ($modelType) {
            $query->where("{$modelType}.tenant_id", $tenantId);
        } else {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

}
