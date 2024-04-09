<?php

namespace app\common\services\platform;

use app\admin\model\MenuRule as PlatformMenuRule;
use app\tenant\model\MenuRule as TenantMenuRule;
use think\db\exception\PDOException;
use think\Exception;

class MenuService
{
    /**
     * 菜单创建或者更新
     *
     * @param $newMenu
     * @param $parent
     * @param $type
     * @return void
     */
    public static function menuCreateOrUpdate($newMenu, $parent = 0, $type = 'platform')
    {
        $pid   = $parent;
        $allow = array_flip(['type', 'title', 'name', 'path', 'icon', 'menu_type', 'url', 'component', 'keepalive', 'extend', 'remark', 'weigh', 'status']);
        foreach ($newMenu as $k => $v) {
            $hasChild     = isset($v['sublist']) && $v['sublist'] ? true : false;
            $data         = array_intersect_key($v, $allow);
            $data['icon'] = isset($data['icon']) ? $data['icon'] : ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            if (isset($v['pid'])) {
                $data['pid'] = $v['pid'];
            } else {
                $data['pid'] = $pid;
            }
            try {
                $name = $data['name'];
                if ($type == 'platform') {
                    $model = PlatformMenuRule::where('name', $name)->find();
                    if ($model) {
                        $model->save($data);
                        $menu = $model;
                    } else {
                        $menu = PlatformMenuRule::create($data);
                    }
                } else if ($type == 'tenant') {
                    $model = TenantMenuRule::where('name', $name)->find();
                    if ($model) {
                        $model->save($data);
                        $menu = $model;
                    } else {
                        $menu = TenantMenuRule::create($data);
                    }
                }

                if ($hasChild) {
                    self::menuCreateOrUpdate($v['sublist'], $menu['id'], $type);
                }
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    // 获取公共菜单数据
    public static function getCommonMenuData($prefix, $actionList = ['index', 'add', 'edit', 'del'])
    {
        $arrMap = [
            'index'              => '查看',
            'add'                => '添加',
            'edit'               => '编辑',
            'del'                => '删除',
            'sortable'           => '快速排序',
            'restore'            => '还原',
            'info'               => '查看详情',
            'rollback'           => '回滚',
            'install'            => '安装',
            'changeState'        => '调整状态',
            'uninstall'          => '卸载',
            'update'             => '更新',
            'generate'           => '生成',
            'delete'             => '删除',
            'print'              => '小票复打',

            // 租户相关
            'initData'           => '初始化数据',
            'clearData'          => '清除数据',
            'getTenantConfig'    => '获取配置',
            'config'             => '配置',
            'exportTenant'       => '导出租户',
            'autoLogin'          => '自动登录',

            // 会员相关
            'exportUser'         => '导出用户',

            // 短信管理
            'getConfigKey'       => '查看',
            'saveConfig'         => '修改配置',
        ];

        $list = [];
        foreach ($actionList as $action) {
            $list[] = [
                'type'      => 'button',
                'title'     => $arrMap[$action],
                'name'      => $prefix . '/' . $action,
                'path'      => '',
                'icon'      => '',
                'menu_type' => null,
                'url'       => '',
                'component' => '',
                'keepalive' => 0,
                'extend'    => 'none',
                'remark'    => '',
                'weigh'     => 1,
                'status'    => 1,
            ];
        }

        return $list;
    }
}
