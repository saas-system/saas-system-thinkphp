<?php

namespace app\admin\model\sms;

use think\Model;

/**
 * Log
 */
class Log extends Model
{
    // 表名
    protected $name = 'sms_log';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    /**
     * 写入发送数据
     *
     * @param $templateCode
     * @param $mobile
     * @param $sendResult
     * @return void
     */
    public static function addSendLog($mobile, $sendResult)
    {
        $status  = 1;
        $resCode = $sendResult['code'];
        $newData = $sendResult['data'];
        $msg     = $sendResult['msg'];

        if ($resCode != 1) {
            $status = 0;
        }

        $data = [
            'template_id' => $newData['template_id'],
            'code'        => $newData['code'],
            'mobile'      => $mobile,
            'title'       => $newData['title'],
            'template'    => $newData['template'],
            'content'     => $newData['content'],
            'data'        => json_encode($newData['data'], JSON_UNESCAPED_UNICODE),
            'status'      => $status,
            'memo'        => $msg,
        ];

        static::create($data);
    }
}
