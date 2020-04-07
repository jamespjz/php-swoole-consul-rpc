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
        $this->init($config);
        // 执行应用
        (new Service($this->config));
        switch ($argv[1]){
            case 'http:start':
                (new Http())->run($this->config);
                break;
            case 'tcp:start':
                (new Tcp())->run($this->config);
                break;
            case 'websocket:start':
                (new Websocket())->run($this->config);
                break;
            default:
                (new Http())->run($this->config);
                break;
        }
    }

    public function init(array $data)
    {
        $config = require_once (__DIR__.'/src/config/config.php');
        $config['host'] = !empty($data['host']) ? $data['host'] : $config['host'];
        $config['port'] = !empty($data['port']) ? $data['port'] : $config['port'];
        $this->config;
    }
}