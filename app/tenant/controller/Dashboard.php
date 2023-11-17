<?php

namespace app\tenant\controller;

use app\common\controller\TenantBackend as Backend;

class Dashboard extends Backend
{
    public function index(): void
    {
        $this->success('', [
            'remark' => get_tenant_route_remark()
        ]);
    }
}
