<?php

namespace app\api\controller;

use app\common\controller\Frontend;
use app\common\model\Attachment;

class Alioss extends Frontend
{
    /**
     * 细目
     * @var string
     */
    protected $topic = 'default';

    public function initialize(): void
    {
        parent::initialize();
    }

    public function callback()
    {
        $data       = $this->request->post();
        $params     = [
            'topic'    => $this->topic,
            'admin_id' => 0,
            'user_id'  => $this->auth->id,
            'url'      => $data['url'],
            'width'    => $data['width'] ?? 0,
            'height'   => $data['height'] ?? 0,
            'name'     => substr(htmlspecialchars(strip_tags($data['name'])), 0, 100),
            'size'     => $data['size'],
            'mimetype' => $data['type'],
            'storage'  => 'alioss',
            'sha1'     => $data['sha1']
        ];
        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
    }
}