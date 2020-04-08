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

namespace Jamespi\Rpc;

use ReflectionClass;
use Jamespi\Rpc\src\Server\Service;
use Jamespi\Rpc\src\Server\Http;
use Jamespi\Rpc\src\Server\Tcp;
use Jamespi\Rpc\src\Server\Websocket;

class Start
{
    protected $config;
    public function run($argv, array $config)
    {
        // 执行应用
        (new Service($config));
        switch ($argv[1]){
            case 'http:start':
                (new Http())->run();
                break;
            case 'tcp:start':
                (new Tcp())->run();
                break;
            case 'websocket:start':
                (new Websocket())->run();
                break;
            default:
                (new Http())->run();
                break;
        }
    }


}