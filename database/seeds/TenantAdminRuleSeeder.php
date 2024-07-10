<?php

use app\common\services\platform\MenuService;
use think\facade\Db;
use think\migration\Seeder;

/**
 * 租户菜单初始化seeder
 */
class TenantAdminRuleSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        // 清空数据
        $this->truncateData();

        // 初始化seeder
        try {
            $this->initMenuRule();

        } catch (\Exception $e) {
            \think\facade\Log::critical('初始化租户菜单失败，失败原因：' . $e->getMessage() . '-' . $e->getLine() . '-' . $e->getTraceAsString());
            dd('更新失败：' . $e->getMessage());
        }
    }

    protected function initMenuRule(): void
    {
        $menuList       = $this->getMenu();
        $extendMenuList = $this->getExtendMenuData();
        $newMenuList    = array_merge($menuList, $extendMenuList);

        Db::transaction(function () use ($newMenuList) {
            MenuService::menuCreateOrUpdate($newMenuList, 0, 'tenant');
        });
    }

    protected function getMenu(): array
    {
        return [
            [
                'type'      => 'menu',
                'title'     => '控制台',
                'name'      => 'dashboard',
                'path'      => 'dashboard',
                'icon'      => 'fa fa-dashboard',
                'menu_type' => 'tab',
                'url'       => '',
                'component' => '/src/views/tenant/dashboard.vue',
                'keepalive' => 1,
                'extend'    => 'none',
                'remark'    => 'remark_text',
                'weigh'     => 999,
                'status'    => 0,
                'sublist'   => [
                    [
                        'type'      => 'button',
                        'title'     => '查看',
                        'name'      => 'dashboard/index',
                    ],
                ]
            ],
            [
                'type'      => 'menu_dir',
                'title'     => '权限管理',
                'name'      => 'auth',
                'path'      => 'auth',
                'icon'      => 'fa fa-group',
                'menu_type' => '',
                'url'       => '',
                'component' => '',
                'keepalive' => 0,
                'extend'    => 'none',
                'remark'    => '',
                'weigh'     => 100,
                'status'    => 1,
                'sublist'   => [
                    [
                        'type'      => 'menu',
                        'title'     => '管理员管理',
                        'name'      => 'auth/admin',
                        'path'      => 'auth/admin',
                        'icon'      => 'el-icon-UserFilled',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/auth/admin/index.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 98,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('auth/admin'),
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '角色组管理',
                        'name'      => 'auth/group',
                        'path'      => 'auth/group',
                        'icon'      => 'fa fa-group',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/auth/group/index.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 99,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('auth/group'),
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '菜单规则管理',
                        'name'      => 'auth/rule',
                        'path'      => 'auth/rule',
                        'icon'      => 'el-icon-Grid',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/auth/rule/index.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 97,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('auth/rule'),
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '管理员日志管理',
                        'name'      => 'auth/adminLog',
                        'path'      => 'auth/adminLog',
                        'icon'      => 'el-icon-List',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/auth/adminLog/index.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 96,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('auth/adminLog', ['index']),
                    ],
                ],
            ],
            [
                'type'      => 'menu_dir',
                'title'     => '常规管理',
                'name'      => 'routine',
                'path'      => 'routine',
                'icon'      => 'fa fa-cogs',
                'menu_type' => null,
                'url'       => '',
                'component' => '',
                'keepalive' => 0,
                'extend'    => 'none',
                'remark'    => '',
                'weigh'     => 89,
                'status'    => 1,
                'sublist'   => [
                    [
                        'type'      => 'menu',
                        'title'     => '租户信息',
                        'name'      => 'routine/tenantInfo',
                        'path'      => 'routine/tenantInfo',
                        'icon'      => 'el-icon-Tools',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/routine/tenantInfo.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 88,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('routine/tenantInfo', ['index', 'edit']),
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '个人资料',
                        'name'      => 'routine/adminInfo',
                        'path'      => 'routine/adminInfo',
                        'icon'      => 'fa fa-user',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/routine/adminInfo.vue',
                        'keepalive' => 1,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 86,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('routine/adminInfo', ['index', 'edit', 'del']),
                    ],
                ],
            ],

        ];
    }

    /**
     * 获取扩展菜单数据
     *
     * @return array[]
     */
    protected function getExtendMenuData(): array
    {
        return [
            // 会员管理
            [
                'type'      => 'menu_dir',
                'title'     => '会员管理',
                'name'      => 'user',
                'path'      => 'user',
                'icon'      => 'fa fa-user',
                'menu_type' => '',
                'url'       => '',
                'component' => '',
                'keepalive' => 0,
                'extend'    => 'none',
                'remark'    => '',
                'weigh'     => 900,
                'status'    => 1,
                'sublist'   => [
                    [
                        'type'      => 'menu',
                        'title'     => '会员管理',
                        'name'      => 'user/user',
                        'path'      => 'user/user',
                        'icon'      => 'el-icon-UserFilled',
                        'menu_type' => 'tab',
                        'url'       => '',
                        'component' => '/src/views/tenant/user/user/index.vue',
                        'keepalive' => 0,
                        'extend'    => 'none',
                        'remark'    => '',
                        'weigh'     => 10,
                        'status'    => 1,
                        'sublist'   => MenuService::getCommonMenuData('user/user', ['index', 'edit', 'del', 'sortable', 'exportUser']),
                    ],
                ],
            ],
        ];
    }

    protected function truncateData(): void
    {

        $sql = 'truncate table tenant_menu_rule';
        \think\facade\Db::execute($sql);
    }
}
