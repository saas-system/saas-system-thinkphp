<?php
// +----------------------------------------------------------------------
// | NewThink [ Think More,Think Better! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2030 http://www.sxqibo.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：山西岐伯信息科技有限公司
// +----------------------------------------------------------------------
// | Author: yanghongwei  Date:2024/5/19 Time:08:49
// +----------------------------------------------------------------------

namespace app\common\library\token;

use think\Exception;
class TokenExpirationException extends Exception
{

    /**
     * Token过期异常
     */
    public function __construct(protected $message = '', protected $code = 409, protected $data = [])
    {
        parent::__construct($message, $code);
    }
}