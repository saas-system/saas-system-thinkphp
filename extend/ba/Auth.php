<?php

namespace ba;

use Throwable;
use think\facade\Db;

/**
 * 权限规则类
 */
class Auth
{
    /**
     * 用户有权限的规则节点
     */
    protected static array $rules = [];

    /**
     * 默认配置
     * @var array|string[]
     */
    protected array $config = [
        'auth_group'        => 'admin_group', // 用户组数据表名
        'auth_group_access' => 'admin_group_access', // 用户-用户组关系表
        'auth_rule'         => 'admin_rule', // 权限规则表
    ];

    /**
     * 子菜单规则数组
     * @var array
     */
    protected array $children = [];

    /**
     * 构造方法
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 魔术方法-获取当前配置
     * @param $name
     * @return mixed
     */
    public function __get($name): mixed
    {
        return $this->config[$name];
    }

    /**
     * 获取菜单规则列表
     * @access public
     * @param int $uid 用户ID
     * @return array
     * @throws Throwable
     */
    public function getMenus(int $uid): array
    {
        $this->children  = [];
        $originAuthRules = $this->getOriginAuthRules($uid);
        foreach ($originAuthRules as $rule) {
            $this->children[$rule['pid']][] = $rule;
        }

        // 没有根菜单规则
        if (!isset($this->children[0])) return [];

        return $this->getChildren($this->children[0]);
    }

    /**
     * 获取传递的菜单规则的子规则
     * @param array $rules 菜单规则
     * @return array
     */
    private function getChildren(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($rule['id'], $this->children)) {
                $rules[$key]['children'] = $this->getChildren($this->children[$rule['id']]);
            }
        }
        return $rules;
    }

    /**
     * 检查是否有某权限
     * @param string $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int    $uid      用户ID
     * @param string $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string $mode     如果不使用 url 则菜单规则name匹配到即通过
     * @return bool
     * @throws Throwable
     */
    public function check(string $name, int $uid, string $relation = 'or', string $mode = 'url'): bool
    {
        // 获取用户需要验证的所有有效规则列表
        $ruleList = $this->getRuleList($uid);
        if (in_array('*', $ruleList)) {
            return true;
        }

        if ($name) {
            $name = strtolower($name);
            if (str_contains($name, ',')) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = json_decode(strtolower(json_encode(request()->param(), JSON_UNESCAPED_UNICODE)), true);
        }
        foreach ($ruleList as $rule) {
            $query = preg_replace('/^.+\?/U', '', $rule);
            if ('url' == $mode && $query != $rule) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $rule      = preg_replace('/\?.*$/U', '', $rule);
                if (in_array($rule, $name) && $intersect == $param) {
                    // 如果节点相符且url参数满足
                    $list[] = $rule;
                }
            } elseif (in_array($rule, $name)) {
                $list[] = $rule;
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 获得权限规则列表
     * @param int $uid 用户id
     * @return array
     * @throws Throwable
     */
    public function getRuleList(int $uid): array
    {
        // 读取用户规则节点
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $originAuthRules = $this->getOriginAuthRules($uid);

        // 用户规则
        $rules = [];
        if (in_array('*', $ids)) {
            $rules[] = "*";
        }
        foreach ($originAuthRules as $rule) {
            $rules[$rule['id']] = strtolower($rule['name']);
        }
        return array_unique($rules);
    }

    /**
     * 获得权限规则原始数据
     * @param int $uid 用户id
     * @return array
     * @throws Throwable
     */
    public function getOriginAuthRules(int $uid): array
    {
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $idsCacheKey = md5(implode('', $ids) . $this->config['auth_rule']);
        if (empty(self::$rules[$idsCacheKey])) {
            $where   = [];
            $where[] = ['status', '=', '1'];
            // 如果没有 * 则只获取用户拥有的规则
            if (!in_array('*', $ids)) {
                $where[] = ['id', 'in', $ids];
            }
            self::$rules[$idsCacheKey] = Db::name($this->config['auth_rule'])
                ->withoutField(['remark', 'status', 'weigh', 'update_time', 'create_time'])
                ->where($where)
                ->order('weigh desc,id asc')
                ->select()
                ->toArray();

            foreach (self::$rules[$idsCacheKey] as $key => $rule) {
                if (!empty($rule['keepalive'])) self::$rules[$idsCacheKey][$key]['keepalive'] = $rule['name'];
            }
        }

        return self::$rules[$idsCacheKey];
    }

    /**
     * 获取权限规则ids
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getRuleIds(int $uid): array
    {
        // 用户的组别和规则ID
        $groups = $this->getGroups($uid);
        $ids    = [];
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        return array_unique($ids);
    }

    /**
     * 获取用户所有分组和对应权限规则
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getGroups(int $uid): array
    {
        $dbName = $this->config['auth_group_access'] ?: 'user';

        static $groups = [];
        if (isset($groups[$dbName][$uid])) {
            return $groups[$dbName][$uid];
        }

        if ($this->config['auth_group_access']) {
            $userGroups = Db::name($dbName)
                ->alias('aga')
                ->join($this->config['auth_group'] . ' ag', 'aga.group_id = ag.id', 'LEFT')
                ->field('aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules')
                ->where("aga.uid='$uid' and ag.status='1'")
                ->select()
                ->toArray();
        } else {
            $userGroups = Db::name($dbName)
                ->alias('u')
                ->join($this->config['auth_group'] . ' ag', 'u.group_id = ag.id', 'LEFT')
                ->field('u.id as uid,u.group_id,ag.id,ag.name,ag.rules')
                ->where("u.id='$uid' and ag.status='1'")
                ->select()
                ->toArray();
        }

        $groups[$dbName][$uid] = $userGroups ?: [];
        return $groups[$dbName][$uid];
    }
}