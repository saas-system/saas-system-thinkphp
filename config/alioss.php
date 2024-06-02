<?php
/**
 * @desc   阿里OSS配置文件
 */
return [
    'Bucket'          => env('alioss.Bucket', ''),
    'Endpoint'        => '',
    'AccessKeyId'     => env('alioss.ACCESS_KEY_ID', ''),
    'AccessKeySecret' => env('alioss.ACCESS_KEY_SECRET', ''),
    'Cdnurl'          => env('alioss.CDURL', ''),
];
