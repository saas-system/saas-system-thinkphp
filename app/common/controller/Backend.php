<?php

namespace app\common\controller;

use think\db\exception\PDOException;
use think\exception\HttpResponseException;
use think\facade\Db;
use Throwable;
use think\Model;
use think\facade\Event;
use app\admin\library\Auth;

class Backend extends Api
{
    protected $adminGroupAccessTable = 'platform_admin_group_access';

    /**
     * 无需登录的方法，访问本控制器的此方法，无需管理员登录
     * @var array
     */
    protected array $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     * @var array
     */
    protected array $noNeedPermission = [];

    /**
     * 新增/编辑时，对前端发送的字段进行排除（忽略不入库）
     * @var array|string
     */
    protected array|string $preExcludeFields = [];

    /**
     * 权限类实例
     * @var Auth
     */
    protected Auth $auth;

    /**
     * 模型类实例
     * @var object
     * @phpstan-var Model
     */
    protected object $model;

    /**
     * 权重字段
     * @var string
     */
    protected string $weighField = 'weigh';

    /**
     * 默认排序
     * @var string|array id,desc 或 ['id' => 'desc']
     */
    protected string|array $defaultSortField = [];

    /**
     * 有序保证
     * 查询数据时总是需要指定 ORDER BY 子句，否则 MySQL 不保证排序，即先查到哪行就输出哪行且不保证多次查询中的输出顺序
     * 将以下配置作为数据有序保证（用于无排序字段时、默认排序字段相同时继续保持数据有序），不设置将自动使用 pk 字段
     * @var string|array id,desc 或 ['id' => 'desc']（有更方便的格式，此处为了保持和 $defaultSortField 属性的配置格式一致）
     */
    protected string|array $orderGuarantee = [];

    /**
     * 快速搜索字段
     * @var string|array
     */
    protected string|array $quickSearchField = 'id';

    /**
     * 是否开启模型验证
     * @var bool
     */
    protected bool $modelValidate = true;

    /**
     * 是否开启模型场景验证
     * @var bool
     */
    protected bool $modelSceneValidate = false;

    /**
     * 关联查询方法名，方法应定义在模型中
     * @var array
     */
    protected array $withJoinTable = [];

    /**
     * 关联查询JOIN方式
     * @var string
     */
    protected string $withJoinType = 'LEFT';

    /**
     * 开启数据限制
     * false=关闭
     * personal=仅限个人
     * allAuth=拥有某管理员所有的权限时
     * allAuthAndOthers=拥有某管理员所有的权限并且还有其他权限时
     * parent=上级分组中的管理员可查
     * 指定分组中的管理员可查，比如 $dataLimit = 2;
     * 启用请确保数据表内存在 admin_id 字段，可以查询/编辑数据的管理员为admin_id对应的管理员+数据限制所表示的管理员们
     * @var bool|string|int
     */
    protected bool|string|int $dataLimit = false;

    /**
     * 数据限制字段
     * @var string
     */
    protected string $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充字段值为当前管理员id
     * @var bool
     */
    protected bool $dataLimitFieldAutoFill = true;

    /**
     * 查看请求返回的主表字段控制
     * @var string|array
     */
    protected string|array $indexField = ['*'];

    /**
     * 引入traits
     * traits内实现了index、add、edit等方法
     */
    use \app\admin\library\traits\Backend;

    /**
     * 初始化
     * @throws Throwable
     */
    public function initialize(): void
    {
        parent::initialize();

        // 检测数据库连接
        try {
            Db::execute("SELECT 1");
        } catch (PDOException $e) {
            $this->error(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'));
        }
        $token      = get_auth_token();
        $this->auth = Auth::instance();
        if (!action_in_arr($this->noNeedLogin)) {
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'type' => $this->auth::NEED_LOGIN
                ], $this->auth::LOGIN_RESPONSE_CODE);
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

        // 管理员验权和登录标签位
        Event::trigger('backendInit', $this->auth);
    }

    /**
     * 查询参数构建器
     * @throws Throwable
     */
    public function queryBuilder(): array
    {
        if (empty($this->model)) {
            return [];
        }
        $pk           = $this->model->getPk();
        $quickSearch  = $this->request->get("quickSearch/s", '');
        $limit        = $this->request->get("limit/d", 10);
        $order        = $this->request->get("order/s", '');
        $search       = $this->request->get("search/a", []);
        $initKey      = $this->request->get("initKey/s", $pk);
        $initValue    = $this->request->get("initValue", '');
        $initOperator = $this->request->get("initOperator/s", 'in');

        $search = $this->filterParams($search); // 过滤搜索参数

        $where              = [];
        $modelTable         = strtolower($this->model->getTable());
        $alias[$modelTable] = parse_name(basename(str_replace('\\', '/', get_class($this->model))));
        $mainTableAlias     = $alias[$modelTable] . '.';

        // 快速搜索
        if ($quickSearch) {
            $quickSearchArr = is_array($this->quickSearchField) ? $this->quickSearchField : explode(',', $this->quickSearchField);
            foreach ($quickSearchArr as $k => $v) {
                $quickSearchArr[$k] = str_contains($v, '.') ? $v : $mainTableAlias . $v;
            }
            $where[] = [implode("|", $quickSearchArr), "LIKE", '%' . str_replace('%', '\%', $quickSearch) . '%'];
        }
        if ($initValue) {
            $where[] = [$initKey, $initOperator, $initValue];
            $limit   = 999999;
        }

        // 通用搜索组装
        foreach ($search as $field) {
            if (!is_array($field) || !isset($field['operator']) || !isset($field['field']) || !isset($field['val'])) {
                continue;
            }

            $field['operator'] = $this->getOperatorByAlias($field['operator']);

            // 查询关联表字段，转换表别名（驼峰转小写下划线）
            if (str_contains($field['field'], '.')) {
                $fieldNameParts        = explode('.', $field['field']);
                $fieldNamePartsLastKey = array_key_last($fieldNameParts);

                // 忽略最后一个元素（字段名）
                foreach ($fieldNameParts as $fieldNamePartsKey => $fieldNamePart) {
                    if ($fieldNamePartsKey !== $fieldNamePartsLastKey) {
                        $fieldNameParts[$fieldNamePartsKey] = parse_name($fieldNamePart);
                    }
                }

                $fieldName = implode('.', $fieldNameParts);
            } else {
                $fieldName = $mainTableAlias . $field['field'];
            }

            // 日期时间
            if (isset($field['render']) && $field['render'] == 'datetime') {
                if ($field['operator'] == 'RANGE') {
                    $datetimeArr = explode(',', $field['val']);
                    if (!isset($datetimeArr[1])) {
                        continue;
                    }
                    $datetimeArr = array_filter(array_map("strtotime", $datetimeArr));
                    $where[]     = [$fieldName, str_replace('RANGE', 'BETWEEN', $field['operator']), $datetimeArr];
                    continue;
                }
                $where[] = [$fieldName, '=', strtotime($field['val'])];
                continue;
            }

            // 范围查询
            if ($field['operator'] == 'RANGE' || $field['operator'] == 'NOT RANGE') {
                $arr = explode(',', $field['val']);
                // 重新确定操作符
                if (!isset($arr[0]) || $arr[0] === '') {
                    $operator = $field['operator'] == 'RANGE' ? '<=' : '>';
                    $arr      = $arr[1];
                } elseif (!isset($arr[1]) || $arr[1] === '') {
                    $operator = $field['operator'] == 'RANGE' ? '>=' : '<';
                    $arr      = $arr[0];
                } else {
                    $operator = str_replace('RANGE', 'BETWEEN', $field['operator']);
                }
                $where[] = [$fieldName, $operator, $arr];
                continue;
            }

            switch ($field['operator']) {
                case '=':
                case '<>':
                    $where[] = [$fieldName, $field['operator'], (string)$field['val']];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                    $where[] = [$fieldName, $field['operator'], '%' . str_replace('%', '\%', $field['val']) . '%'];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$fieldName, $field['operator'], intval($field['val'])];
                    break;
                case 'FIND_IN_SET':
                    if (is_array($field['val'])) {
                        foreach ($field['val'] as $val) {
                            $where[] = [$fieldName, 'find in set', $val];
                        }
                    } else {
                        $where[] = [$fieldName, 'find in set', $field['val']];
                    }
                    break;
                case 'IN':
                case 'NOT IN':
                    $where[] = [$fieldName, $field['operator'], is_array($field['val']) ? $field['val'] : explode(',', $field['val'])];
                    break;
                case 'NULL':
                case 'NOT NULL':
                    $where[] = [$fieldName, strtolower($field['operator']), ''];
                    break;
            }
        }

        // 数据权限
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$mainTableAlias . $this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        return [$where, $alias, $limit, $this->queryOrderBuilder()];
    }

    /**
     * 查询的排序参数构建器
     */
    public function queryOrderBuilder()
    {
        $pk    = $this->model->getPk();
        $order = $this->request->get("order/s") ?: $this->defaultSortField;

        if ($order && is_string($order)) {
            $order = explode(',', $order);
            $order = [$order[0] => $order[1] ?? 'asc'];
        }
        if (!$this->orderGuarantee) {
            $this->orderGuarantee = [$pk => 'desc'];
        } elseif (is_string($this->orderGuarantee)) {
            $this->orderGuarantee = explode(',', $this->orderGuarantee);
            $this->orderGuarantee = [$this->orderGuarantee[0] => $this->orderGuarantee[1] ?? 'asc'];
        }
        $orderGuaranteeKey = array_key_first($this->orderGuarantee);
        if (!array_key_exists($orderGuaranteeKey, $order)) {
            $order[$orderGuaranteeKey] = $this->orderGuarantee[$orderGuaranteeKey];
        }

        return $order;
    }

    /**
     * 数据权限控制-获取有权限访问的管理员Ids
     * @throws Throwable
     */
    protected function getDataLimitAdminIds(): array
    {
        if (!$this->dataLimit || $this->auth->isSuperAdmin()) {
            return [];
        }
        $adminIds = [];
        if ($this->dataLimit == 'parent') {
            // 取得当前管理员的下级分组们
            $parentGroups = $this->auth->getAdminChildGroups();
            if ($parentGroups) {
                // 取得分组内的所有管理员
                $adminIds = $this->auth->getGroupAdmins($parentGroups);
            }
        } elseif (is_numeric($this->dataLimit) && $this->dataLimit > 0) {
            // 在组内，可查看所有，不在组内，可查看自己的
            $adminIds = $this->auth->getGroupAdmins([$this->dataLimit]);
            return in_array($this->auth->id, $adminIds) ? [] : [$this->auth->id];
        } elseif ($this->dataLimit == 'allAuth' || $this->dataLimit == 'allAuthAndOthers') {
            // 取得拥有他所有权限的分组
            $allAuthGroups = $this->auth->getAllAuthGroups($this->dataLimit);
            // 取得分组内的所有管理员
            $adminIds = $this->auth->getGroupAdmins($allAuthGroups);
        }
        $adminIds[] = $this->auth->id;
        return array_unique($adminIds);
    }

    /**
     * 过滤原始的不能用buildParams 的条件
     */
    public function filterParams($filter, $nobuildfields = [])
    {
        if ($nobuildfields) {
            foreach ($filter as $k => $f) {
                if (in_array($f['field'], $nobuildfields)) {
                    unset($filter[$k]);
                }
            }
        }
        return $filter;
    }

    /**
     * 从别名获取原始的逻辑运算符
     * @param string $operator 逻辑运算符别名
     * @return string 原始的逻辑运算符，无别名则原样返回
     */
    protected function getOperatorByAlias(string $operator): string
    {
        $alias = [
            'ne'  => '<>',
            'eq'  => '=',
            'gt'  => '>',
            'egt' => '>=',
            'lt'  => '<',
            'elt' => '<=',
        ];

        return $alias[$operator] ?? $operator;
    }
}
