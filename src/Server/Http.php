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

use Swoole\Http\Server;
class Http extends Service{

    public function init(array $config)
    {
        if (array_key_exists('HttpConfig', $config) && $config['HttpConfig']){
            if ( (array_key_exists('host', $config['HttpConfig']) && empty($config['HttpConfig']['host']) )|| (array_key_exists('port', $config['HttpConfig']) && empty($config['HttpConfig']['port'])) )
                echo "请求地址与端口不能为空!";
        }
    }

    public function run()
    {
        try{
            $http = new Server($this->HttpConfig['host'], $this->HttpConfig['port']);
            $http->set(['upload_tmp_dir' => $this->HttpConfig['upload_tmp_dir']]);
            $http->set(['task_worker_num' => $this->HttpConfig['task_worker_num']]);
            $http->set(['worker_num' => $this->HttpConfig['worker_num']]);

            $http->on('request', [$this, 'onRequest']);
            $http->start(); //启动服务器
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    public function onStart($serv)
    {
        echo "http服务启动啦";
    }

    public function onRequest($request, $response)
    {
        echo "http服务request参数".$request->header['host']."response参数".$response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
    }
}
