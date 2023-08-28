<?php


namespace app\common\services\api;

use app\common\exceptions\UserException;
use app\common\model\user\User;
use app\common\services\third\WechatAuthService;
use think\facade\Log;
use function env;
use function request;

/**
 * 用户授权业务层
 *
 * Class UserOauthService
 * @package app\common\services\user
 */
class UserOauthService
{
    protected $tenantId = null;

    public function __construct($tenantId = '')
    {
        $this->tenant_id = $tenantId;
    }

    /**
     * 小程序登录
     *
     * @param $code
     * @param $tenantConfigModel
     * @param $platformType
     * @param $extendData
     * @return mixed
     */
    public function miniProgramLogin($code, $tenantConfigModel, $platformType, $extendData = [])
    {
        try {
            $provider = '';
            switch ($platformType) {
                case User::PLATFORM_WX_MINI_PROGRAM:
                    $miniAppId        = $tenantConfigModel->mini_app_id;
                    $minSecretId      = $tenantConfigModel->mini_secret_id;
                    $code2SessionInfo = (new WechatAuthService($miniAppId, $minSecretId))->getCode2Session($code);
                    $provider         = User::PROVIDER_WECHAT;
                    break;
            }

            // Log::info('授权登录返回的code&session数据-' . json_encode($code2SessionInfo));
            $userInfo = $this->handleUserOauth($code2SessionInfo, $tenantConfigModel, $platformType, $provider, $extendData);

            // 创建token
            $jwtService = new JwtService();
            $token      = $jwtService->createToken(['user_id' => $userInfo->id, 'tenant_id' => $userInfo->tenant_id, 'provider' => $provider, 'platform' => $platformType]);

            $newInfo = [
                'token'       => $token,
                'nickname'    => $userInfo->nickname,
                'mobile'      => $userInfo->mobile,
                'card_number' => $userInfo->card_number,
                'avatar'      => $userInfo->avatar,
            ];

            return $newInfo;
        } catch (\Exception $e) {
            Log::critical('失败平台：' . $platformType);
            Log::critical('小程序自动登录失败，失败原因：' . $e->getMessage() . '-' . $e->getLine() . '-' . $e->getTraceAsString());
            throw new UserException('授权登录失败');
        }
    }

    /**
     * 操作用户第三方授权
     *
     * @param $code2SessionInfo
     * @param $tenantConfigModel
     * @param $platform
     * @param $provider
     * @param $extendData
     * @return mixed
     */
    public function handleUserOauth($code2SessionInfo, $tenantConfigModel, $platform, $provider, $extendData = [])
    {
        // $temp = [
        //     'data'     => $code2SessionInfo,
        //     'platform' => $platform,
        //     'provider' => $provider,
        // ];
        // Log::info('第三方授权信息：' . json_encode($temp, JSON_UNESCAPED_UNICODE));

        $oauthData = array_merge($code2SessionInfo, [
            'provider' => $provider,
            'platform' => $platform,
        ]);

        $openid = $oauthData['openid'];

        $user = User::where('openid', $openid)
            ->find();

        $model = $extendData['model'] ?? ''; // 手机型号
        // 是否有手机号加密数据
        $sessionKey    = $oauthData['session_key'] ?? '';
        $encryptedData = $extendData['encryptedData'];
        $iv            = $extendData['iv'];

        $mobile = $this->decryptMobileData($encryptedData, $iv, $sessionKey, $platform, $tenantConfigModel->mini_app_id);


        // 优化数据
        if (!$user) {
            $userData = [
                'tenant_id'        => $tenantConfigModel->tenant_id,
                'nickname'         => '',
                'model'            => $model,
                'platform'         => $platform,
                'register_origin'  => 0,
                'register_ip'      => request()->ip(),
                'register_ip_addr' => '',
                'session_key'      => $sessionKey,
                'mobile'           => $mobile,
                'openid'           => $openid,
            ];
            // Log::info('写入注册数据', $userData);
            $user = User::create($userData);
        } else {
            $user->last_login_ip_addr = '';
            $user->last_login_time    = time();
            $user->last_login_ip      = request()->ip();
            $mobile && $user->mobile = $mobile;
            $sessionKey && $user->session_key = $sessionKey;
            $user->save();
        }

        return $user;
    }


    /**
     * 从第三方同步用户手机号
     *
     * @param $userId
     * @param $data
     * @param string $type
     * @return bool
     */
    public function syncUserMobile($userId, $data, $type = User::PLATFORM_WX_MINI_PROGRAM)
    {
        try {
            $encryptedData = $data['encryptedData'];
            $iv            = $data['iv'];
            $userInfo      = User::where('id', $userId)->find();
            if (!$userInfo) {
                throw new UserException('未找到用户授权信息', -1);
            }

            $mobile           = $this->decryptMobileData($encryptedData, $iv, $userInfo->session_key, $type, $userId);
            $userInfo         = User::where('id', $userId)->find();
            $userInfo->mobile = $mobile;
            $userInfo->save();

            return $mobile;
        } catch (\Exception $e) {
            $text = '';
            switch ($type) {
                case User::PLATFORM_WX_MINI_PROGRAM:
                    $text = '微信小程序';
                    break;
            }
            Log::critical("{$text}同步用户手机号失败，失败原因：" . $e->getMessage() . '-' . $e->getLine() . $e->getTraceAsString());

            throw new UserException('同步手机号失败', -1);
        }
    }


    /**
     * 解密手机号
     *
     * @param $encryptedData
     * @param $iv
     * @param $sessionKey
     * @param $type
     * @param $appId
     * @return mixed|string
     */
    public function decryptMobileData($encryptedData, $iv, $sessionKey, $type, $appId)
    {
        // Log::info('解密原始数据为：' . json_encode(['encrypt' => $encryptedData, 'iv' => $iv, 'session_key' => $sessionKey, 'appid' => $appId]));

        // $encryptedData = urldecode($encryptedData); // 备注：传回的数据进行了urlencode编码，所以需要解码urldecode进行解码

        $result = $this->decryptData($encryptedData, $iv, $sessionKey, $appId, $decryptData);

        Log::info('手机解密结果' . $result . json_encode(['en' => $encryptedData, 'iv' => $iv, 'session_key' => $sessionKey, 'type' => $type, 'appid' => $appId]));

        Log::info('手机解密信息' . json_encode($decryptData));

        if ($result !== 0) {
            throw new UserException('同步用户信息失败' . $result);
        }

        $decryptData = json_decode($decryptData, true);
        $mobile      = $decryptData['phoneNumber'] ?? '';
        return $mobile;
    }

    /**
     * 解密数据
     *
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey string  解密秘钥
     * @param $appId string app_id
     * @param $data string 解密后的原文
     * @return int 成功0，失败返回对应的错误码
     */
    protected function decryptData($encryptedData, $iv, $sessionKey, $appId, &$data)
    {
        /**
         * error code 说明.
         * <ul>
         *    <li>-41001: encodingAesKey 非法</li>
         *    <li>-41003: aes 解密失败</li>
         *    <li>-41004: 解密后得到的buffer非法</li>
         *    <li>-41005: base64加密失败</li>
         *    <li>-41016: base64解密失败</li>
         * </ul>
         */
        if (strlen($sessionKey) != 24) {
            return -41001;
        }

        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return -41002;
        }

        $aesIV     = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == null) {
            return -41003;
        }

        if ($dataObj->watermark->appid != $appId) {
            return -41003;
        }

        $data = $result;

        return 0;
    }

    /**
     * 根据平台类型获取app_id
     * @param $type
     * @return mixed|string
     */
    protected function getAppIdByPlatformType($type)
    {
        $appId = '';
        switch ($type) {
            case User::PLATFORM_WX_MINI_PROGRAM:
                $appId = env('APP_ID', '');
                break;
        }
        return $appId;
    }

    /**
     * 验证用户签名
     * @param $signature
     * @param $rawData
     * @param $sessionKey
     * @return bool
     */
    protected function checkUserInfoSign($signature, $rawData, $sessionKey)
    {
        $newSignature = sha1($rawData . $sessionKey);
        $temp         = [
            'sign'     => $signature,
            'new_sign' => $newSignature,
        ];
        // Log::info('签名对比：' . json_encode($temp));

        if ($signature != $newSignature) {
            throw new UserException('签名错误');
        }

        return true;
    }

}
