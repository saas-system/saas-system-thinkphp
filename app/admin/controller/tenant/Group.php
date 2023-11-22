<?php

namespace app\admin\controller\tenant;

use ba\Tree;
use app\tenant\model\AdminRule;
use app\admin\model\TenantAdminGroup;
use app\common\controller\Backend;

class Group extends Backend
{
    /**
     * 修改、删除分组时对操作管理员进行鉴权
     * 本管理功能部分场景对数据权限有要求，修改此值请额外确定以下的 absoluteAuth 实现的功能
     * allAuthAndOthers=管理员拥有该分组所有权限并拥有额外权限时允许
     */
    protected string $authMethod = 'allAuthAndOthers';

    /**
     * @var TenantAdminGroup
     */
    protected object $model;

    protected array|string $preExcludeFields = ['create_time', 'update_time'];

    protected array|string $quickSearchField = 'name';

    /**
     * @var Tree
     */
    protected Tree $tree;

    /**
     * 远程select初始化传值
     * @var array|string
     */
    protected array|string $initValue;

    /**
     * 搜索关键词
     * @var array
     */
    protected string $keyword;

    /**
     * 是否组装Tree
     * @var bool
     */
    protected bool $assembleTree;

    /**
     * 登录管理员的角色组
     */
    protected array $adminGroups = [];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new TenantAdminGroup();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get("initValue/a", '');
        $this->keyword   = $this->request->request("quickSearch");

        // 有初始化值时不组装树状（初始化出来的值更好看）
        $this->assembleTree = $isTree && !$this->initValue;
    }

    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getGroups(),
            'remark' => get_route_remark()
        ]);
    }

    public function select(): void
    {
        $data = $this->getGroups([['status', '=', 1]]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    public function getGroups($where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get("initKey/s", $pk);

        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        $tenantId = $this->request->get('tenant_id');
        if (!empty($tenantId)) {
            $where[] = ['tenant_id', '=', $tenantId];
        }

        $data = $this->model->where($where)->select()->toArray();

        // 获取第一个权限的名称供列表显示-s
        foreach ($data as &$datum) {
            if ($datum['rules']) {
                if ($datum['rules'] == '*') {
                    $datum['rules'] = __('Super administrator');
                } else {
                    $rules = explode(',', $datum['rules']);
                    if ($rules) {
                        $rulesFirstTitle = AdminRule::where('id', $rules[0])->value('title');
                        $datum['rules']  = count($rules) == 1 ? $rulesFirstTitle : $rulesFirstTitle . '等 ' . count($rules) . ' 项';
                    }
                }
            } else {
                $datum['rules'] = __('No permission');
            }
        }
        // 获取第一个权限的名称供列表显示-e

        // 如果要求树状，此处先组装好 children
        return $this->assembleTree ? $this->tree->assembleChild($data) : $data;
    }
}
