<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\common\Pages;
use app\common\model\common\Slide;
use app\common\model\tenant\TenantConfig;
use app\common\services\CommonService;
use app\Request;


class CommonNew extends Api
{

    /**
     * 获取页面信息
     * @param Request $request
     * @param string $alias
     */
    public function getPagesInfo(Request $request, string $alias = 'agreement')
    {
        if (!$alias) {
            $this->error('别名不能为空');
        }

        $info = Pages::where('alias', $alias)->find();

        if (!$info) {
            $this->error('信息不存在');
        }

        $this->success('成功', $info);
    }

    /**
     * 获取轮播图
     * @param Request $request
     */
    public function getSlideList(Request $request)
    {
        $position = $request->get('position');

        $tenantId = $request->get('tenant_id', '');
        if (!$tenantId) {
            $this->error('请传入租户ID');
        }

        if (empty($position)) {
            $this->error('请传入有效的位置参数');
        }

        $list = Slide::permission($tenantId)
            ->where('position', $position)
            ->where('status', 1)
            ->order('weigh', 'desc')
            ->limit(5)
            ->select();

        $this->success('成功', $list);
    }

    /**
     * 获取oss签名信息
     */
    public function getOssConfig(Request $request)
    {
        $oss  = get_sys_config('', 'upload');
        $id   = $oss['upload_access_id'] ?? '';
        $key  = $oss['upload_secret_key'] ?? '';
        $host = $oss['upload_cdn_url'] ?? '';

        if (empty($id) || empty($key) || empty($host)) {
            $this->error('oss配置不正确');
        }

        $name = $request->get('name');
        if (empty($name)) {
            $this->error('获取失败，请传入文件名');
        }
        $nameArr = explode('.', $name);


        $now        = time();
        $expire     = 10; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end        = $now + $expire;
        $expiration = $this->gmt_iso8601($end);

        $dir = 'uploads/' . date('Ymd', time()) . '/';//文件在oss中保存目录

        //最大文件大小.用户可以自己设置
        $condition    = ['content-length-range', 0, 209715200];
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        // $start        = ['starts-with', '$key', $dir];
        // $conditions[] = $start;

        $arr            = ['expiration' => $expiration, 'conditions' => $conditions];
        $policy         = json_encode($arr);
        $base64_policy  = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature      = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        // 防止重复
        $saveName = md5($name . $now . rand(1, 1000000));

        $response = [
            'id'                    => $id,
            'key'                   => $dir . $saveName . '.' . $nameArr[count($nameArr) - 1],
            'policy'                => $base64_policy,
            'signature'             => $signature,
            'expire'                => $end,
            'callback'              => '',
            'OSSAccessKeyId'        => $id,
            'success_action_status' => 200,
            'OSSAddress'            => $host
        ];

        $this->success('成功', $response);
    }



    private function gmt_iso8601($time)
    {
        try {
            $dtStr      = date("c", $time);
            $datetime   = new \DateTime($dtStr);
            $expiration = $datetime->format(\DateTimeInterface::ISO8601);
            $pos        = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration . "Z";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * 获取租户ID
     * @param Request $request
     * @return void
     */
    public function getTenantId(Request $request)
    {
        $miniAppId  = $request->get('mini_app_id', '');
        $unlockCode = $request->get('unlock_code', '');

        $tenantId = TenantConfig::getTenantIdByAppId($miniAppId, $unlockCode);
        if (!$tenantId) {
            $this->error("获取租户ID失败");
        }

        $this->success('获取成功', ['tenant_id' => $tenantId]);
    }
}
