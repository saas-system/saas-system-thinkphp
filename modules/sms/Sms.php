<?php

namespace modules\sms;

use Throwable;
use think\Exception;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Event;
use think\facade\Log;
use think\facade\Validate;
use app\common\model\Config;
use app\common\library\Menu;
use Overtrue\EasySms\EasySms;
use modules\sms\library\Helper;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class Sms
{
    private string $uid = 'sms';

    public static function send($templateCode, $mobile, $tplVar = []): array
    {
        // 环境检查
        if (!extension_loaded('curl')) {
            throw new Exception('Please install curl extension');
        }
        // if (!ini_get('curl.cainfo')) {
        //     throw new Exception('Please configure curl.cainfo in the php.ini file');
        // }

        // 数据检查
        $validate = Validate::rule(['mobile' => 'require|mobile'])->message(['mobile' => 'Mobile format error']);
        if (!$validate->check(['mobile' => $mobile])) {
            throw new Exception($validate->getError());
        }
        $templateData = Db::name('sms_template')
            ->where('code', $templateCode)
            ->where('status', 1)
            ->find();
        if (!$templateData) {
            throw new Exception('SMS template does not exist');
        }
        if (!$templateData['template'] && !$templateData['content']) {
            throw new Exception('SMS template error');
        }

        // 配置检查
        $config = config('sms');
        if (!is_array($config['default']['gateways']) || count($config['default']['gateways']) <= 0) {
            throw new Exception('Please configure available service providers for SMS sending');
        }

        // 解析模板
        $template = Helper::analysisVariable($templateData['content'], $templateData['variables'], $tplVar);
        Event::trigger('TemplateAnalysisAfter', $template);

        try {
            $sendData = [
                'title'       => $templateData['title'],
                'template_id' => $templateData['id'],
                'template'    => $templateData['template'],
                'code'        => $templateData['code'],
                'content'     => $template['content'],
                'data'        => $template['variables'],
            ];

            $easySms = new EasySms($config);
            $res     = $easySms->send($mobile, [
                'template' => $templateData['template'],
                'content'  => $template['content'],
                'data'     => $template['variables'],
            ]);
            Log::info('发送短信返回数据：' . json_encode($res, JSON_UNESCAPED_UNICODE));

            return ['code' => 1, 'msg' => '成功', 'data' => $sendData];

        } catch (NoGatewayAvailableException $e) {
            // throw new Exception($e->getLastException()->getMessage());
            return ['code' => 0, 'msg' => $e->getLastException()->getMessage(), 'data' => $sendData];
        }
    }

    public function install(): void
    {
        $menu = [
            [
                'type'      => 'menu_dir',
                'title'     => '短信管理',
                'name'      => 'sms',
                'path'      => 'sms',
                'icon'      => 'el-icon-ChatLineRound',
                'menu_type' => 'tab',
                'children'  => [
                    [
                        'type'      => 'menu',
                        'title'     => '短信配置',
                        'name'      => 'sms/config',
                        'path'      => 'sms/config',
                        'icon'      => 'el-icon-Setting',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/sms/config.vue',
                        'keepalive' => '1',
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'sms/config/getConfigKey'],
                            ['type' => 'button', 'title' => '修改配置', 'name' => 'sms/config/saveConfig'],
                        ]
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '模板变量管理',
                        'name'      => 'sms/variable',
                        'path'      => 'sms/variable',
                        'icon'      => 'fa fa-asterisk',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/sms/variable/index.vue',
                        'keepalive' => '1',
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'sms/variable/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'sms/variable/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'sms/variable/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'sms/variable/del'],
                        ]
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '短信模板管理',
                        'name'      => 'sms/template',
                        'path'      => 'sms/template',
                        'icon'      => 'el-icon-Document',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/sms/template/index.vue',
                        'keepalive' => '1',
                        'remark'    => '不同服务商可能需要不同的模板ID，所以单个模板并不一定适用于所有服务商，若有轮询服务商发送的需求，多数情况需自行通过代码实现',
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'sms/template/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'sms/template/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'sms/template/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'sms/template/del'],
                        ]
                    ]
                ]
            ]
        ];
        Menu::create($menu);
    }

    /**
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('sms', true);
    }

    /**
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('sms');
        Config::addQuickEntrance('短信配置', '/admin/sms/config');

        // 恢复短信配置
        $config = Cache::pull('sms-module-config');
        if ($config) {
            @file_put_contents(config_path() . 'sms.php', $config);
        }
    }

    /**
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('sms');
        Config::removeQuickEntrance('短信配置');

        // 备份短信配置
        $config = @file_get_contents(config_path() . 'sms.php');
        if ($config) {
            Cache::set('sms-module-config', $config, 3600);
        }
    }

}
