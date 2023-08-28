<?php

namespace app\tenant\controller;

use app\common\model\Attachment;
use app\common\controller\TenantBackend as Backend;

class Alioss extends Backend
{
    /**
     * 细目
     * @var string
     */
    protected string $topic = 'default';

    public function initialize(): void
    {
        parent::initialize();
    }

    public function callback()
    {
        $data       = $this->request->post();
        $params     = [
            'topic'     => $this->topic,
            'admin_id'  => $this->auth->id,
            'user_id'   => 0,
            'tenant_id' => $this->auth->tenant_id,
            'url'       => $data['url'],
            'width'     => $data['width'] ?? 0,
            'height'    => $data['height'] ?? 0,
            'name'      => substr(htmlspecialchars(strip_tags($data['name'])), 0, 100),
            'size'      => $data['size'],
            'mimetype'  => $data['type'],
            'storage'   => 'alioss',
            'sha1'      => $data['sha1']
        ];
        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
    }
}
