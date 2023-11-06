<?php

namespace app\admin\model;

use think\Model;

/**
 * SensitiveData 模型
 */
class SensitiveData extends Model
{
    protected $name = 'security_sensitive_data';

    // 应用类型
    const APP_TYPE_PLATFORM = 'admin'; // 平台端
    const APP_TYPE_TENANT   = 'tenant'; // 租户端

    protected $autoWriteTimestamp = true;

    protected $type = [
        'data_fields' => 'array',
    ];
}
