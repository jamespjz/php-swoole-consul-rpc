<?php
/**+----------------------------------------------------------------------
 * JamesPi RPC [php-swoole-consul-rpc]
 * +----------------------------------------------------------------------
 * Entry startup file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Rpc\Start;

use Jamespi\Rpc\jrpc\App;
class Start
{
    public function run(){
        $this->init();
        // 执行应用
        App::run()->send();
    }

    public function init(){

    }
}