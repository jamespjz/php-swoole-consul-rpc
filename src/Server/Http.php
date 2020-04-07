<?php
/**+----------------------------------------------------------------------
 * JamesPi RPC [php-swoole-consul-rpc]
 * +----------------------------------------------------------------------
 * HTTP protocol call service file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */
namespace Jamespi\Rpc\src\Server;

class Http extends Service{

    public function __construct()
    {
        parent::__construct();
    }

    public function run($config)
    {
        $this->http->on('request', function ($request, $response) {
            var_dump($request->get, $request->post);
            $response->header("Content-Type", "text/html; charset=utf-8");
            $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
        });

        $this->http->start();
    }
}
