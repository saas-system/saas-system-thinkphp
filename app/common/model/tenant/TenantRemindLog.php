<?php

namespace app\common\model\tenant;


use app\common\model\Base;

class  TenantRemindLog extends Base
{
    // 消息类型
    const REMIND_MESSAGE_TYPE_EXPIRE = 1; // 快过期提醒

    // 提醒方式
    const REMIND_METHOD_SMS   = 1; // 短信
    const REMIND_METHOD_EMAIL = 2; // 邮件

    /**
     * 与模型关联的表名
     * @var string
     */
    protected $name = 'tenant_remind_log';

    /**
     * 隐藏属性
     * @var array
     */
    protected $hidden = ['update_time'];

    /**
     * 追加字段
     * @var array
     */
    protected $append = [];

}
