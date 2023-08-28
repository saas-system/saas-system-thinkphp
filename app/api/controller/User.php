<?php

namespace app\api\controller;

use Throwable;
use app\common\controller\Frontend;

class User extends Frontend
{
    protected array $noNeedLogin = ['checkIn', 'logout'];

    protected array $noNeedPermission = ['index'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 会员首页初始化请求
     * @throws Throwable
     */
    public function index()
    {
    }

}
