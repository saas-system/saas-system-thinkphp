<?php

namespace app\api\controller;

use app\common\controller\Api;

class Index extends Api
{
    protected $noNeedLogin = ['index'];

    public function initialize() : void
    {
        parent::initialize();
    }

    public function index()
    {
        $this->success('success');
    }
}
