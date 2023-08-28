<?php

use think\facade\Route;

Route::group('/common', function () {
    // 轮播图
    Route::get('/slideList', 'commonNew/getSlideList');

    // 页面信息
    Route::get('/getPagesInfo/<alias>', 'commonNew/getPagesInfo');

    // websocket地址
    Route::get('/getServerInfo', '/commonNew/getServerInfo');


    // 获取租户ID
    Route::get('/getTenantId', 'commonNew/getTenantId');

});

// 获取OSS签名信息
Route::get('/common/getOssConfig', 'commonNew/getOssConfig')->middleware(\app\common\middleware\CheckTokenMiddleware::class);





