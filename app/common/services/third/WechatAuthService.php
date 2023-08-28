<?php

namespace app\common\services\third;

use ba\Http;
use Exception;
use think\facade\Log;

class WechatAuthService
{
    protected $appid;
    protected $secret;
    protected $loginUrl;
    private   $grant_type;
    /**
     * @var string
     */

    /**
     * @var string
     */

    public function __construct($appId, $secretId)
    {
        //小程序
        $this->appid  = $appId;
        $this->secret = $secretId;

        $this->grant_type = 'authorization_code';
        $this->loginUrl   = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=' . $this->grant_type;
    }

    /**
     * 获取code2Session
     *
     * @param $code
     * @return array
     */
    public function getCode2Session($code)
    {
        $url      = sprintf($this->loginUrl, $this->appid, $this->secret, $code);
        $result   = Http::get($url);
        $wxResult = json_decode($result, true);

        Log::info('微信授权openid等信息：' . json_encode($wxResult));

        if (empty($wxResult)) {
            throw new Exception('获取sessin_key及openID时异常');
        }
        if (isset($wxResult['errcode']) && $wxResult['errcode'] != 0) {
            throw new Exception($wxResult['errmsg']);
        }

        $item = [
            'openid'      => $wxResult['openid'],
            'session_key' => $wxResult['session_key'],
            'unionid'     => $wxResult['unionid'] ?? '',
        ];

        return $item;
    }
}
