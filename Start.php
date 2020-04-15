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
use think\Exception;

class Start
{
    /**
     * 服务配置项
     * @var
     */
    protected $config;

    /**
     * 服务启动
     * @param $argv
     * @param array $config
     */
    public function run($argv, array $config)
    {
        $option = isset( $argv[2] ) ? $argv[2] : null ;
        // 执行应用
        switch ($argv[1]){
            case 'http:start':
                if($option == '-d'){
                    $config['HttpConfig']['daemonize'] = 1;
                }
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

    /**
     * 服务注册
     * @param string $namespace 命名空间
     * @param string $url 请求路径
     * @param string $method 请求方法
     * @return bool
     */
    public function registerService(string $namespace, string $url, string $method):string
    {
        try{
            $class = new ReflectionClass($namespace);
            $path = dirname(__FILE__).'/registerService.txt';
            $path = strval(str_replace("\0", "", $path));
            $class->getMethod($method);
            $txt = $namespace."*".$url."*".$method."\n";
            file_put_contents($path, $txt, FILE_APPEND);
        }catch (\Exception $e){
            return 'Error：'.$e->getMessage();
        }

        return 'success';
    }

}