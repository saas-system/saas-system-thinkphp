<?php

use think\facade\Route;

Route::group('/user', function () {
    // 退出登录
    Route::get('/logout', ['logout']);

    // 修改用户信息
    Route::post('/changeUserInfo', 'user/changeUserInfo');

    // 更新用户Token
    Route::get('/updateToken', 'user/updateToken');

    // 获取用户信息
    Route::get('/getUserInfo', 'user/getUserInfo');

    // 同步用户手机号
    Route::post('/syncUserMobile', 'user/syncUserMobile');


})->middleware(\app\common\middleware\CheckTokenMiddleware::class);


// 微信用户登录
Route::post('/user/wxMiniLogin', 'user/wxMiniLogin');
