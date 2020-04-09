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
use Jamespi\Rpc\src\Server\Http;
use Jamespi\Rpc\src\Server\Tcp;
use Jamespi\Rpc\src\Server\Websocket;

class Start
{
    protected $config;
    public function run($argv, array $config)
    {
        $option = isset( $argv[2] ) ? $argv[2] : null ;
        // 执行应用
        switch ($argv[1]){
            case 'http:start':
                if($option == '-d'){
                    $config['HttpConfig']['daemonize'] = 1;
                }
                (new Http($config))->_startUI();
                (new Http($config))->run();
                break;
            case 'http:status':
                (new Http($config))->_statusUI();
                break;
            case 'http:help':
                (new Http($config))->_helpUI();
                break;
            case 'tcp:start':
                (new Tcp($config))->run();
                break;
            case 'websocket:start':
                (new Websocket($config))->run();
                break;
            default:
                (new Http($config))->run();
                break;
        }
    }


}