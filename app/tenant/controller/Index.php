<?php
declare (strict_types=1);

namespace app\tenant\controller;

use app\common\facade\Token;
use app\tenant\model\Tenant;
use ba\ClickCaptcha;
use think\facade\Config;
use think\facade\Validate;
use app\common\controller\TenantBackend as Backend;
use app\tenant\model\AdminLog;

class Index extends Backend
{
    protected array $noNeedLogin      = ['logout', 'login'];
    protected array $noNeedPermission = ['index'];

    public function index(): void
    {
        $adminInfo          = $this->auth->getInfo();
        $adminInfo['debug'] = (boolean)env('app_debug', false);
        $adminInfo['super'] = $this->auth->isSuperAdmin();
        unset($adminInfo['token'], $adminInfo['refresh_token']);

        $menus = $this->auth->getMenus();
        if (!$menus) {
            $this->error(__('No background menu, please contact super administrator!'));
        }
        $tenantName = Tenant::getTenantNameByTenantId($this->auth->tenant_id);
        unset($adminInfo['tenant_id']);

        $this->success('', [
            'adminInfo'  => $adminInfo,
            'menus'      => $menus,
            'siteConfig' => [
                'siteName' => $tenantName,
                'version'  => get_sys_config('version'),
                'cdnUrl'   => full_url(),
                'apiUrl'   => Config::get('buildadmin.api_url'),
                'upload'   => get_upload_config(),
            ]
        ]);
    }

    public function login(): void
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success(__('You have already logged in. There is no need to log in again~~'), [
                'routePath' => '/tenant'
            ], 302);
        }
        $captchaSwitch = Config::get('buildadmin.admin_login_captcha');

        // 检查提交
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keep     = $this->request->post('keep');

            $rule = [
                // 'mobile|' . __('Mobile')     => 'require|mobile',
                'username|' . __('Username') => 'require|length:3,30',
                'password|' . __('Password') => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            if ($captchaSwitch) {
                $rule['captchaId|' . __('CaptchaId')] = 'require';
                $rule['captchaInfo|' . __('Captcha')] = 'require';

                $data['captchaId']   = $this->request->post('captchaId');
                $data['captchaInfo'] = $this->request->post('captchaInfo');
            }
            $validate = Validate::rule($rule);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if ($captchaSwitch) {
                $captchaObj = new ClickCaptcha();
                if (!$captchaObj->check($data['captchaId'], $data['captchaInfo'])) {
                    $this->error(__('Captcha error'));
                }
            }

            AdminLog::instance()->setTitle(__('Login'));

            $res = $this->auth->login($username, $password, (bool)$keep);
            if ($res === true) {
                $this->success(__('Login succeeded!'), [
                    'userInfo'  => $this->auth->getInfo(),
                    'routePath' => '/tenant'
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ?: __('Incorrect user name or password!');
                $this->error($msg);
            }
        }

        $this->success('', [
            'captcha' => $captchaSwitch
        ]);
    }

    public function logout(): void
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refreshToken', '');
            if ($refreshToken)
                Token::delete((string)$refreshToken);
            $this->auth->logout();
            $this->success();
        }
    }
}
