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
use Jamespi\Consul\Controllers\serviceController;
class Http extends Service{

    /**
     * 服务初始化
     * @param array $config
     * @return mixed|void
     */
    public function init(array $config)
    {
        if (array_key_exists('HttpConfig', $config) && $config['HttpConfig']){
            if ( (array_key_exists('host', $config['HttpConfig']) && empty($config['HttpConfig']['host']) )|| (array_key_exists('port', $config['HttpConfig']) && empty($config['HttpConfig']['port'])) )
                echo "请求地址与端口不能为空!";
        }
    }

    /**
     * 启动HTTP服务
     */
    public function run()
    {
        try{
            $http = new Server($this->HttpConfig['host'], $this->HttpConfig['port']);
            $http->set(
                [
                    'upload_tmp_dir' => $this->HttpConfig['upload_tmp_dir'],
//                    'task_worker_num' => $this->HttpConfig['task_worker_num'],
//                    'worker_num' => $this->HttpConfig['worker_num']
                ]
            );

            $http->on('start', [$this, 'onStart']);
            $http->on('request', [$this, 'onRequest']);
            $http->start(); //启动服务器
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * HTTP服务启动回调方法
     * @param $serv
     */
    public function onStart($serv)
    {
        var_dump($serv->ConsulConfig);
        echo "http服务启动啦";
        $this->_registerService();
    }

    /**
     * 接收请求回调方法
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {

        echo "http服务request参数".$request->header['host']."response参数";
        $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
    }

    /**
     * 注册服务
     * @param $arguments
     */
    private function _registerService($arguments=null)
    {
        $serviceModel = new serviceController();
        return call_user_func_array([$serviceModel, 'registrationService'], $arguments);
    }

    private function _deleteService($arguments=null)
    {

    }

    /**
     * 抓取获取运行状态命令方法
     * @param $arguments
     */
    private function _statusUI($arguments=null)
    {
        echo PHP_EOL;
        //打印服务器字幕
//        swoole_set_process_name("PP Master Thread");
        cli_set_process_title("PP Master Thread");
        echo PHP_EOL.PHP_EOL.PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "|                  |----    |----    |----    |----     ----              |".PHP_EOL;
        echo "|                  |    |   |    |   |    |   |    |   |                  |".PHP_EOL;
        echo "|                  |---     |----    |----    |----    |                  |".PHP_EOL;
        echo "|                  |        |        | \      |        |                  |".PHP_EOL;
        echo "|                  |        |        |   \    |         ----              |".PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "\033[1A\n\033[K-----------------------\033[47;30m PP Rpc Server \033[0m-----------------------------\n\033[0m";
        echo "    Version:0.1 Beta, PHP Version:".PHP_VERSION.PHP_EOL;
        echo "         The Server is \033[36m running \033[0m on HTTP".PHP_EOL.PHP_EOL;
        echo "--------------------------\033[47;30m PORT \033[0m---------------------------\n";
        echo "                   HTTP:".$this->HttpConfig['port']."  TCP:".$this->HttpConfig['port']."\n\n";
        echo "------------------------\033[47;30m PROCESS \033[0m---------------------------\n";
        echo "      MasterPid---ManagerPid---WorkerId---WorkerPid".PHP_EOL;
    }

    /**
     * 使用帮助方法
     * @param $arguments
     */
    private function _helpUI($arguments=null)
    {
        echo PHP_EOL.PHP_EOL.PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "|                  |----    |----    |----    |----     ----              |".PHP_EOL;
        echo "|                  |    |   |    |   |    |   |    |   |                  |".PHP_EOL;
        echo "|                  |---     |----    |----    |----    |                  |".PHP_EOL;
        echo "|                  |        |        | \      |        |                  |".PHP_EOL;
        echo "|                  |        |        |   \    |         ----              |".PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "USAGE: php index.php commond".PHP_EOL;
        echo "1. \033[36m start\033[0m,以debug模式开启服务，此时服务不会以daemon形式运行[当配置日志当中配置daemon=1时将以daemon模式开启服务]".PHP_EOL;
        echo "2. \e[36m start -d\e[0m,以daemon模式开启服务".PHP_EOL;
        echo "3. \e[36m status\e[0m,查看服务器的状态".PHP_EOL;
        echo "4. \e[36m stop\e[0m,停止服务器".PHP_EOL;
        echo "5. \e[36m help\e[0m,查看帮助文档，罗列所有操作命令".PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;
        exit;
    }

    private function _startUI($arguments=null)
    {
        echo PHP_EOL;
        //打印服务器字幕
//        swoole_set_process_name("PP Master Thread");
        cli_set_process_title("PP Master Thread");
        echo PHP_EOL.PHP_EOL.PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "|                  |----    |----    |----    |----     ----              |".PHP_EOL;
        echo "|                  |    |   |    |   |    |   |    |   |                  |".PHP_EOL;
        echo "|                  |---     |----    |----    |----    |                  |".PHP_EOL;
        echo "|                  |        |        | \      |        |                  |".PHP_EOL;
        echo "|                  |        |        |   \    |         ----              |".PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "\033[1A\n\033[K-----------------------\033[47;30m PP Rpc Server \033[0m-----------------------------\n\033[0m";
        echo "    Version:0.1 Beta, PHP Version:".PHP_VERSION.PHP_EOL;
        echo "--------------------------\033[47;30m PORT \033[0m---------------------------\n";
        echo "                   HTTP:".$this->HttpConfig['port']."  TCP:".$this->HttpConfig['port']."\n\n";
    }

    /**
     * 回调魔术方法
     * @param string $name 回调方法名
     * @param string $arguments 回调参数
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this, $name], $arguments);
    }
}
