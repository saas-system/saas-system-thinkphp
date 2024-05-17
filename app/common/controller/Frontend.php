<?php

namespace app\common\controller;

use Throwable;
use think\facade\Event;
use app\common\library\Auth;
use think\exception\HttpResponseException;

class Frontend extends Api
{
    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需会员登录
     * @var array
     */
    protected array $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     * @var array
     */
    protected array $noNeedPermission = [];

    /**
     * 权限类实例
     * @var Auth
     */
    protected Auth $auth;

    /**
     * 初始化
     * @throws Throwable
     * @throws HttpResponseException
     */
    public function initialize(): void
    {
        parent::initialize();
        $token      = get_auth_token(['ba', 'user', 'token']);
        $this->auth = Auth::instance();
        if (!action_in_arr($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'routePath' => '/user/login'
                ], 302);
            }
            if (!action_in_arr($this->noNeedPermission)) {
                $routePath = ($this->app->request->controllerPath ?? '') . '/' . $this->request->action(true);
                if (!$this->auth->check($routePath)) {
                    $this->error(__('You have no permission'), [], 401);
                }
            }
        } elseif ($token) {
            try {
                $this->auth->init($token);
            } catch (HttpResponseException) {
            }
        }

        // 会员验权和登录标签位
        Event::trigger('frontendInit', $this->auth);
    }
}