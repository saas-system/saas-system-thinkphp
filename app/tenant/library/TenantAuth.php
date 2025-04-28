<?php

namespace app\tenant\library;

use app\tenant\model\Tenant;
use ba\Random;
use think\Exception;
use think\facade\Db;
use think\facade\Config;
use app\tenant\model\Admin;
use app\tenant\model\AdminGroup;
use app\common\facade\Token;
use think\db\exception\DbException;
use think\db\exception\PDOException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

/**
 * 租户端管理员权限类
 *
 * @property int $id         管理员ID
 * @property string $username   管理员用户名
 * @property string $nickname   管理员昵称
 * @property string $email      管理员邮箱
 * @property string $mobile     管理员手机号
 */
class TenantAuth extends \ba\Auth
{
    /**
     * @var TenantAuth 对象实例
     */
    protected static ?TenantAuth $instance = null;

    /**
     * @var bool 是否登录
     */
    protected bool $loginEd = false;

    /**
     * @var string 错误消息
     */
    protected string $error = '';
    /**
     * @var Admin Model实例
     */
    protected ?Admin $model = null;

    /**
     * @var string 令牌
     */
    protected string $token = '';

    /**
     * @var string 刷新令牌
     */
    protected string $refreshToken = '';

    /**
     * @var int 令牌默认有效期
     */
    protected int $keepTime = 86400;

    /**
     * @var string[] 允许输出的字段
     */
    protected string $adminGroupAccessTable = 'tenant_admin_group_access';

    protected array $allowFields = ['id', 'username', 'mobile', 'nickname', 'avatar', 'last_login_time', 'tenant_id'];

    public function __construct(array $config = [])
    {
        $config = [
            'auth_group'        => 'tenant_admin_group', // 用户组数据表名
            'auth_group_access' => 'tenant_admin_group_access', // 用户-用户组关系表
            'auth_rule'         => 'tenant_menu_rule', // 权限规则表
        ];

        parent::__construct($config);
    }

    /**
     * 魔术方法-管理员信息字段
     * @param $name
     * @return null|string 字段信息
     */
    public function __get($name): mixed
    {
        return $this->model?->$name;
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return TenantAuth
     */
    public static function instance(array $options = []): TenantAuth
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 根据Token初始化管理员登录态
     * @param $token
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function init($token): bool
    {
        if ($this->loginEd) {
            return true;
        }
        if ($this->error) {
            return false;
        }
        $tokenData = Token::get($token);
        if (!$tokenData) {
            return false;
        }
        $userId = intval($tokenData['user_id']);
        if ($userId > 0) {
            $this->model = Admin::where('id', $userId)->find();
            if (!$this->model) {
                $this->setError('Account not exist');
                return false;
            }
            if ($this->model['status'] != 'enable') {
                $this->setError('Account disabled');
                return false;
            }
            // 验证租户端状态
            $tenantResult = Tenant::checkTenantStatusInfo($this->model->tenant_id);
            if ($tenantResult !== true) {
                $this->setError($tenantResult);
                return false;
            }

            $this->token = $token;
            $this->loginSuccessful();
            return true;
        } else {
            $this->setError('Token login failed');
            return false;
        }
    }

    /**
     * 管理员登录
     * @param string $username
     * @param string $password
     * @param bool $keepTime
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login(string $username, string $password, bool $keepTime = false): bool
    {
        $this->model = Admin::where('username', $username)->find();
        if (!$this->model) {
            $this->setError('username is incorrect');
            return false;
        }
        if ($this->model->status == 'disable') {
            $this->setError('Account disabled');
            return false;
        }
        $adminLoginRetry = Config::get('buildadmin.admin_login_retry');
        if ($adminLoginRetry && $this->model->login_failure >= $adminLoginRetry && time() - $this->model->getData('last_login_time') < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }
        if ($this->model->password != encrypt_password($password, $this->model->salt)) {
            $this->loginFailed();
            $this->setError('Password is incorrect');
            return false;
        }
        if (Config::get('buildadmin.admin_sso')) {
            Token::clear('tenant', $this->model->id);
            Token::clear('tenant-refresh', $this->model->id);
        }

        // 验证租户端状态
        $tenantResult = Tenant::checkTenantStatusInfo($this->model->tenant_id);
        if ($tenantResult !== true) {
            $this->setError($tenantResult);
            return false;
        }

        if ($keepTime) {
            $this->setRefreshToken(2592000);
        }
        $this->loginSuccessful();
        return true;
    }

    /**
     * 设置刷新Token
     * @param int $keepTime
     */
    public function setRefreshToken(int $keepTime = 0)
    {
        $this->refreshToken = Random::uuid();
        Token::set($this->refreshToken, 'tenant-refresh', $this->model->id, $keepTime);
    }

    /**
     * 管理员登录成功
     * @return bool
     */
    public function loginSuccessful(): bool
    {
        if (!$this->model) {
            return false;
        }
        Db::startTrans();
        try {
            $this->model->login_failure   = 0;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();
            $this->loginEd = true;

            if (!$this->token) {
                $this->token = Random::uuid();
                Token::set($this->token, 'tenant', $this->model->id, $this->keepTime);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 管理员登录失败
     * @return bool
     */
    public function loginFailed(): bool
    {
        if (!$this->model) {
            return false;
        }
        Db::startTrans();
        try {
            $this->model->login_failure++;
            $this->model->last_login_time = time();
            $this->model->last_login_ip   = request()->ip();
            $this->model->save();

            $this->token   = '';
            $this->model   = null;
            $this->loginEd = false;
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 退出登录
     * @return bool
     */
    public function logout(): bool
    {
        if (!$this->loginEd) {
            $this->setError('You are not logged in');
            return false;
        }
        $this->loginEd = false;
        Token::delete($this->token);
        $this->token = '';
        return true;
    }

    /**
     * 是否登录
     * @return bool
     */
    public function isLogin(): bool
    {
        return $this->loginEd;
    }

    /**
     * 获取管理员模型
     * @return Admin
     */
    public function getAdmin(): Admin
    {
        return $this->model;
    }

    /**
     * 获取管理员Token
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 获取管理员刷新Token
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * 获取管理员信息 - 只输出允许输出的字段
     * @return array
     */
    public function getInfo(): array
    {
        if (!$this->model) {
            return [];
        }
        $info                 = $this->model->toArray();
        $info                 = array_intersect_key($info, array_flip($this->getAllowFields()));
        $info['token']        = $this->getToken();
        $info['refresh_token'] = $this->getRefreshToken();
        return $info;
    }

    /**
     * 获取允许输出字段
     * @return string[]
     */
    public function getAllowFields(): array
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出字段
     * @param $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 设置Token有效期
     * @param int $keepTime
     * @return void
     */
    public function setkeepTime(int $keepTime = 0): void
    {
        $this->keepTime = $keepTime;
    }

    public function check(string $name, int $uid = 0, string $relation = 'or', string $mode = 'url'): bool
    {
        return parent::check($name, $uid ?: $this->id, $relation, $mode);
    }

    public function getGroups(int $uid = 0): array
    {
        return parent::getGroups($uid ?: $this->id);
    }

    public function getRuleList(int $uid = 0): array
    {
        return parent::getRuleList($uid ?: $this->id);
    }

    public function getRuleIds(int $uid = 0): array
    {
        return parent::getRuleIds($uid ?: $this->id);
    }

    public function getMenus(int $uid = 0): array
    {
        return parent::getMenus($uid ?: $this->id);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('*', $this->getRuleIds());
    }

    /**
     * 获取管理员所在分组的所有子级分组
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAdminChildGroups(): array
    {
        $groupIds = Db::name($this->adminGroupAccessTable)
            ->where('uid', $this->id)
            ->select();
        $children = [];
        foreach ($groupIds as $group) {
            $this->getGroupChildGroups($group['group_id'], $children);
        }
        return array_unique($children);
    }

    public function getGroupChildGroups($groupId, &$children)
    {
        $childrenTemp = AdminGroup::where('pid', $groupId)->where('status', '1')->select();
        foreach ($childrenTemp as $item) {
            $children[] = $item['id'];
            $this->getGroupChildGroups($item['id'], $children);
        }
    }

    /**
     * 获取分组内的管理员
     * @param array $groups
     * @return array 管理员数组
     */
    public function getGroupAdmins(array $groups): array
    {
        return Db::name($this->adminGroupAccessTable)
            ->where('group_id', 'in', $groups)
            ->column('uid');
    }

    /**
     * 获取拥有"所有权限"的分组
     * @param string $dataLimit 数据权限
     * @return array 分组数组
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAllAuthGroups(string $dataLimit): array
    {
        $children = [];
        $groups = Db::name($this->adminGroupAccessTable)
            ->alias('aga')
            ->field('aga.id, aga.group_id, ag.name, p_ag.pid as parent_pid')
            ->where('aga.uid', $this->id)
            ->join($this->config['auth_group'] . ' ag', 'aga.group_id = ag.id', 'LEFT')
            ->join($this->config['auth_group'] . ' p_ag', 'ag.pid = p_ag.id', 'LEFT')
            ->select();
        foreach ($groups as $group) {
            // 系统管理组 可以显示同级别
            if($dataLimit == 'allAuth' || ($dataLimit == 'allAuthAndOthers' && $group['parent_pid'] == 0)){
                $children[] = $group['group_id'];
            }

            $this->getGroupChildGroups($group['group_id'], $children);
        }
        return array_unique($children);
    }

    /**
     * 设置错误消息
     * @param $error
     * @return $this
     */
    public function setError($error): TenantAuth
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 获取错误消息
     * @return float|int|string
     */
    public function getError(): string
    {
        return $this->error ? __($this->error) : '';
    }
}
