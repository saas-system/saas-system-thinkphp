<?php
return [
    \app\common\middleware\AllowCrossDomain::class,
    \think\middleware\LoadLangPack::class,
    \app\common\middleware\AdminLog::class,
];
