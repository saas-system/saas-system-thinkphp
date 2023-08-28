<?php

namespace app\common\exceptions;

use Throwable;

/**
 * 用户异常处理类
 * Class UserException
 * @package app\exceptions
 */
class UserException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $message = $errInfo[1] ?? '未知错误';
            $code    = $errInfo[0] ?? 400;
        }

        parent::__construct($message, $code, $previous);
    }
}
