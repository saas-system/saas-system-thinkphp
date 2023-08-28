<?php

namespace app\common\services\api;

use app\common\exceptions\AuthException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public $config = [];
    public $datTime = 86400; // 1天的时间戳
    public $expireDay = 30; // 失效期限 - 日
    public $updateDay = 5; // 失效前可更新期限 - 日， 必须小于失效期限

    public function __construct($key = null)
    {
        $this->config = [
            'key' => env('jwt.secret') ? env('jwt.secret') : $key,
            'iss' => env('jwt.iss'), //签发者 可选
            'aud' => env('jwt.aud'), //接收该JWT的一方，可选
            'exp' => bcmul($this->expireDay, $this->datTime), //过期时间
        ];
    }

    /**
     * @Notes:  创建token
     * @param string $data
     * @param string $exp_time
     * @param string $scopes
     * @return string
     */
    public function createToken($data = "")
    {
        //JWT标准规定的声明，但不是必须填写的；
        //iss: jwt签发者
        //sub: jwt所面向的用户
        //aud: 接收jwt的一方
        //exp: jwt的过期时间，过期时间必须要大于签发时间
        //nbf: 定义在什么时间之前，某个时间点后才能访问
        //iat: jwt的签发时间
        //jti: jwt的唯一身份标识，主要用来作为一次性token。
        //公用信息
        try {
            $token = $this->encode($data);
        } catch (\Firebase\JWT\ExpiredException $exception) {
            throw new AuthException('签名错误,错误码：' . $exception->getCode() . ',错误信息:' . $exception->getMessage());
        } catch (\Exception $exception) {
            throw new AuthException('签名错误,错误码：' . $exception->getCode() . ',错误信息:' . $exception->getMessage());
        }
        return $token; //返回信息
    }


    /**
     * @Notes:  验证token是否有效,默认验证exp,nbf,iat时间
     * @param $jwt
     * @return object
     */
    public function checkToken($token)
    {
        $key = $this->config['key'];
        try {
            \Firebase\JWT\JWT::$leeway = 60;//当前时间减去60，把时间留点余地

            $info = \Firebase\JWT\JWT::decode($token, new Key($key, 'HS256')); //HS256方式，这里要和签发的时候对应
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            //签名不正确
            throw new AuthException('签名错误', 401);
        } catch (\Firebase\JWT\BeforeValidException $e) {
            // 签名在某个时间点之后才能用
            throw new AuthException('token失效', 401);
        } catch (\Firebase\JWT\ExpiredException $e) {
            // token过期
            throw new AuthException('token过期', 401);
        } catch (\Exception $e) {
            //其他错误
            throw new AuthException('非法请求' . $e->getMessage());
        }
        //Firebase定义了多个 throw new，我们可以捕获多个catch来定义问题，catch加入自己的业务，比如token过期可以用当前Token刷新一个新Token

        return $info;
    }

    /**
     * 加密
     * @param $data
     * @return string
     */
    public function encode($data)
    {
        $config = $this->config;
        $key    = $config['key'];
        $time   = time();//当前时间
        $token  = [
            'iss'  => $config['iss'], //签发者 可选
            'aud'  => $config['aud'], //接收该JWT的一方，可选
            'iat'  => $time, //签发时间
            'nbf'  => $time - 1, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            'exp'  => $time + $config['exp'], //过期时间,这里设置30天
            'data' => $data
        ];
        try {
            $decoded = JWT::encode($token, $key, 'HS256');
            return $decoded;
        } catch (\Exception $e) {
            //其他错误
            throw new AuthException('加密失败,失败原因：' . $e->getMessage());
        }
    }

    /**
     * 解密
     * @param $data
     * @return \stdClass|string
     */
    public function decode($jwt)
    {
        $config = $this->config;
        $key    = $config['key'];
        try {
            //HS256方式，这里要和签发的时候对应
            return \Firebase\JWT\JWT::decode($jwt, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            //其他错误
            throw new AuthException('解密失败;失败原因：' . $e->getMessage());
        }
    }
}
