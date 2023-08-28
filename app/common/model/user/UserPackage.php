<?php

namespace app\common\model\user;

use app\common\model\Base;
use think\model\concern\SoftDelete;

class UserPackage extends Base
{
    use SoftDelete;

    /**
     * 与模型关联的表名
     * @var string
     */
    protected $name = 'tenant_user_package';

    /**
     * 隐藏属性
     * @var array
     */
    protected $hidden = ['delete_time'];

    /**
     * 追加字段
     * @var array
     */
    protected $append = [];

    protected $autoWriteTimestamp = 'int';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
