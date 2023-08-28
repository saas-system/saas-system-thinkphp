<?php

namespace app\tenant\model;

use think\model\concern\SoftDelete;

/**
 * User
 */
class TenantUser extends BaseModel
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 字段类型转换
    protected $type = [];

    // 追加属性
    protected $append = [
        'create_time_text',
        'gender_text'
    ];

    protected $hidden = [
        'delete_time',
        'model',
        'register_ip',
        'register_origin',
        'register_ip_addr'
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?? ($data['create_time'] ?? '');
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getGenderTextAttr($value, $data)
    {
        $value  = $value ?? ($data['gender'] ?? '');
        $gender = [0 => '未知', 1 => '男', 2 => '女'];
        return $gender[$value] ?? '';
    }

}
