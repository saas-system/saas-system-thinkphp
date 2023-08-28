<?php


namespace app\common\exceptions;

use Exception;
use think\exception\Handle;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;
use function json;

class ApiExceptionHandle extends Handle
{
    // http 状态码
    private $code;

    //错误消息
    private $message;

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        $logs    = '';
        $request = \request();
        if ($request) {
            $logs   = $request->ip() . ' ' . $request->method() . ' ' . trim($request->url(), '/');
            $userId = $request->user ? $request->user->id : '空';
            $logs   .= ' 用户ID:' . $userId;
            $params = $request->post();
            $params && $logs .= ' 提交参数：' . json_encode($params, JSON_UNESCAPED_UNICODE);
        }

        Log::critical($logs . "\n" . '报错信息：' . $exception->getMessage());

    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if ($e instanceof AuthException // 授权验证异常捕获
            || $e instanceof ValidateException // 验证异常捕获
            || $e instanceof UserException) { // 用户提示信息异常捕获

            $code          = $e->getCode();
            $this->code    = $code ?: 0;
            $this->message = $e->getMessage();

            $result = [
                'msg'  => $this->message,
                'code' => $this->code,
                'data' => '',
                'time' => $request->server('REQUEST_TIME'),
            ];
            return json($result);

        } else {
            return parent::render($request, $e);
        }
    }


    /**
     * 记录错误日志
     * @param Exception $e
     */
    public function recordLog(Exception $e, $request)
    {
        $message = $e->getMessage();
        $url     = $request->url() ?? '';

        Log::record('接口地址为：' . $url . ' 访问异常记录：' . $message, 'error');
    }
}
