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
use Jamespi\Consul\Controllers\ServiceController;
use Jamespi\Consul\Core\Consul;

class Http extends Service{

    /**
     * 服务初始化
     * @param array $config
     * @return mixed|void
     */
    public function init(array $config)
    {
        if (isset($config['HttpConfig']) && $config['HttpConfig']){
            if ( (!isset($config['HttpConfig']['host']) || !isset($config['HttpConfig']['port'])) || (isset($config['HttpConfig']['host']) && empty($config['HttpConfig']['host']) )|| (isset($config['HttpConfig']['port']) && empty($config['HttpConfig']['port'])) )
                echo "请求地址与端口不能为空！";
        }else{
            echo "缺乏请求参数！";
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
                    'daemonize' => $this->HttpConfig['daemonize'],
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
        echo "http服务启动啦";
        $this->_registerService($serv);
    }

    /**
     * 接收请求回调方法
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico')       {
            $response->end("<h1>404.</h1>");
            return;
        }else{
            $msg = 'world！';
            if (isset($request->server['request_uri']) && !empty($request->server['request_uri']) && $request->server['request_uri']!= '/'){

                $serviceList = $this->_getFileContent($request->server['request_uri']);
                if (!empty($serviceList)){
                    foreach ($serviceList as $value){
                        //根据 $controller, $action 映射到不同的控制器类和方法
                        $msg = call_user_func_array([$value['class'], $value['method']], ['james']);
                    }
                }

            }
            $response->header("Content-Type", "text/html; charset=utf-8");
            $response->end("<h1>Hello ".$msg.". #".rand(1000, 9999)."</h1>");
        }
    }

    /**
     * 查询匹配的注册服务
     * @param string $requestUri
     * @return array
     */
    private function _getFileContent(string $requestUri):array
    {
        $services = file_get_contents(dirname(__FILE__).'/registerService.txt');
        $serviceArr = explode("\n", trim($services, "\n"));
        $service = [];
        foreach($serviceArr as $key=>$value){
            if(strstr($value, rtrim($requestUri, "/"))){
                $option = explode("*", trim($value, "*"));
                switch(count($option)){
                    case 2:
                        $class = new ReflectionClass($option[0]);
                        $methods = $class->getMethods();
                        foreach ($methods as $v){
                            $service[] = [
                                'class' => $option[0],
                                'method' => $v->name
                            ];
                        }
                        break;
                    case 3:
                        $service[] = [
                            'class' => $option[0],
                            'method' => $option[2]
                        ];
                        break;
                }
            }
        }

        return $service;
    }

    /**
     * 注册服务
     * @param $arguments
     */
    private function _registerService($arguments=null)
    {
        $config = $this->ConsulConfig;
        $service = [
            [
                'id' => 'pp-rpc',
                'name' => 'pp-rpc',
                'address' => $config['host'],
                'port' => (int)$config['port'],
                'tags' => ['test'],
//                'checks'=> json_encode([
//                    'http'=> "http://".$arguments->host.":".$arguments->port,
//                    'interval'=> '5s'
//                ])
            ]
        ];

        $serviceModel = new ServiceController(new Consul(), $config['host'], $config['port']);
        return call_user_func_array([$serviceModel, 'registrationService'], $service);
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
        $config = $this->ConsulConfig;
        $serviceModel = new ServiceController(new Consul(), $config['host'], $config['port']);
        $serviceInfo = call_user_func_array([$serviceModel, 'checkHealthService'], ['pp-rpc']);
        $serviceInfo = json_decode($serviceInfo);

        echo PHP_EOL;
        //打印服务器字幕
//        swoole_set_process_name("PP Master Thread");
        cli_set_process_title("PP Master Thread");
        echo PHP_EOL.PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "|                  |----    |----    |----    |----     ----              |".PHP_EOL;
        echo "|                  |    |   |    |   |    |   |    |   |                  |".PHP_EOL;
        echo "|                  |---     |----    |----    |----    |                  |".PHP_EOL;
        echo "|                  |        |        | \      |        |                  |".PHP_EOL;
        echo "|                  |        |        |   \    |         ----              |".PHP_EOL;
        echo "--------------------------------------------------------------------------".PHP_EOL;
        echo "\033[1A\n\033[K-----------------------\033[47;30m PP Rpc Server \033[0m-----------------------------\n\033[0m";
        echo "    Version:0.1 Beta, PHP Version:".PHP_VERSION.PHP_EOL;
        if ($serviceInfo[0]->Status == 'passing') {
            echo "         The Server is \033[36m running \033[0m on HTTP" . PHP_EOL . PHP_EOL;
        }else{
            echo "         The Server is \033[31m ".$serviceInfo[0]->Status." \033[0m on HTTP,and the Server \e[31m Fails \e[0m to Start" . PHP_EOL . PHP_EOL;
        }
        echo "--------------------------\033[47;30m PORT \033[0m---------------------------\n";
        echo "                   HTTP:".$this->HttpConfig['port']."  TCP:".$this->HttpConfig['port']."\n\n";
        echo "------------------------\033[47;30m PROCESS \033[0m---------------------------\n";
        echo "      MasterPid---ManagerPid---WorkerId---WorkerPid".PHP_EOL.PHP_EOL;
    }

    /**
     * 使用帮助方法
     * @param $arguments
     */
    private function _helpUI($arguments=null)
    {
        echo PHP_EOL.PHP_EOL;
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
        echo PHP_EOL;
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
