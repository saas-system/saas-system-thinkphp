<?php

namespace app\admin\controller;

use app\common\model\Area;
use Throwable;
use ba\Terminal;
use think\Response;
use ba\TableManager;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Event;
use app\admin\model\AdminLog;
use app\common\library\Upload;
use app\common\controller\Backend;

class Ajax extends Backend
{
    protected array $noNeedPermission = ['*'];

    /**
     * 无需登录的方法
     * terminal 内部自带验权
     */
    protected array $noNeedLogin = ['terminal'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload()
    {
        AdminLog::setTitle(__('upload'));
        $file = $this->request->file('file');
        try {
            $upload     = new Upload($file);
            $attachment = $upload->upload(null, $this->auth->id);
            unset($attachment['create_time'], $attachment['quote']);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment ?? []
        ]);
    }

    /**
     * 获取省市区数据
     * @throws Throwable
     */
    public function area(): void
    {
        $this->success('', get_area());
    }

    public function getAreaList(): void
    {
        $province    = request()->get('province', '');
        $city        = request()->get('city', '');
        $quickSearch = request()->get('quickSearch', '');
        $where       = ['pid' => 0, 'level' => 1];

        if ($province !== '') {
            $where['pid']   = $province;
            $where['level'] = 2;
            if ($city !== '') {
                $where['pid']   = $city;
                $where['level'] = 3;
            }
        }

        if ($quickSearch) {
            $where[] = ['name', 'like', "%{$quickSearch}%"];
        }

        $limit = 30;
        $res   = Area::where($where)->field('id,name')->paginate($limit);
        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    public function buildSuffixSvg(): Response
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    /**
     * 获取已脱敏的数据库连接配置列表
     * @throws Throwable
     */
    public function getDatabaseConnections(): void
    {
        $connections     = config('database.connections');
        $desensitization = [];
        foreach ($connections as $key => $connection) {
            $connection        = TableManager::getConnectionConfig($key);
            $desensitization[] = [
                'type'     => $connection['type'],
                'database' => substr_replace($connection['database'], '****', 1, strlen($connection['database']) > 4 ? 2 : 1),
                'key'      => $key,
            ];
        }
        $this->success('', [
            'list' => $desensitization,
        ]);
    }

    /**
     * 获取表主键字段
     * @param ?string $table
     * @throws Throwable
     */
    public function getTablePk(?string $table = null): void
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }
        $table = TableManager::tableName($table);
        if (!TableManager::phinxAdapter(false)->hasTable($table)) {
            $this->error(__('Data table does not exist'));
        }
        $tablePk = Db::table($table)->getPk();
        $this->success('', ['pk' => $tablePk]);
    }

    public function getTableFieldList(): void
    {
        $table = $this->request->param('table');
        $clean = $this->request->param('clean', true);
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $tablePk = Db::name($table)->getPk();
        $this->success('', [
            'pk'        => $tablePk,
            'fieldList' => TableManager::getTableColumns($table, $clean),
        ]);
    }

    public function changeTerminalConfig(): void
    {
        AdminLog::setTitle(__('changeTerminalConfig'));
        if (Terminal::changeTerminalConfig()) {
            $this->success();
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearCache(): void
    {
        $type = $this->request->post('type');
        if ($type == 'tp' || $type == 'all') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }

    /**
     * 终端
     * @throws Throwable
     */
    public function terminal(): void
    {
        Terminal::instance()->exec();
    }
}
