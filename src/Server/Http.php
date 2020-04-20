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

use ReflectionClass;
use Jamespi\Rpc\src\Api\HttpInterface;
use Swoole\Http\Server;
use Jamespi\Consul\Controllers\ServiceController;
use Jamespi\Consul\Core\Consul;

class Http extends Service implements HttpInterface {

    /**
     * HTTP服务实例
     * @var
     */
    protected $http;

    /**
     * buffer区
     * @var
     */
    protected $buffer = [];

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
        if ($this->HttpConfig['task_worker_num']){
            $setting = [
                'upload_tmp_dir' => $this->HttpConfig['upload_tmp_dir'],
                'daemonize' => $this->HttpConfig['daemonize'],
                'task_worker_num' => $this->HttpConfig['task_worker_num'],
                'worker_num' => $this->HttpConfig['worker_num']
            ];
        }else{
            $setting = [
                'upload_tmp_dir' => $this->HttpConfig['upload_tmp_dir'],
                'daemonize' => $this->HttpConfig['daemonize'],
                'worker_num' => $this->HttpConfig['worker_num']
            ];
        }

        try{
            $this->http = new Server($this->HttpConfig['host'], $this->HttpConfig['port']);
            $this->http->set($setting);

            $this->http->on('start', [$this, 'onStart']);
            $this->http->on('request', [$this, 'onRequest']);
            $this->http->on('WorkerStart', [$this, 'onWorkerStart']);
            $this->http->on('task', [$this, 'onTask']);
            $this->http->on('finish', [$this, 'onFinish']);
            $this->http->on('close', [$this, 'onClose']);
            $this->http->start(); //启动服务器
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * HTTP服务启动回调方法
     * @param \swoole_server $serv swoole_server对象
     */
    public function onStart(\swoole_server  $serv)
    {
        $this->buffer['masterPid'] = $serv->master_pid;
        $this->buffer['managerPid'] = $serv->manager_pid;
//        echo "http服务启动啦".swoole_cpu_num()."#".$serv->setting['worker_num']."#".$serv->setting['task_worker_num'].PHP_EOL;
    }

    /**
     * 接收请求回调方法
     * @param \swoole_http_request $request 请求对象
     * @param \swoole_http_response $response 响应对象
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $params = [];
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico')       {
            $response->end("<h1>404.</h1>");
            return;
        }else{
            $this->buffer['fd'] = $request->fd;
            $msg = 'world！';
            if (isset($request->server['request_uri']) && !empty($request->server['request_uri']) && $request->server['request_uri']!= '/'){

                $serviceList = $this->_getFileContent($request->server['request_uri']);
                if (!empty($serviceList)){
                    foreach ($serviceList as $value){
                        if (empty($value['method'])){
                            $msg = '请求方法不存在';
                        }else {
                            $this->buffer['class'] = $value['class'];
                            $data = (isset($request->server['query_string'])&&!empty($request->server['query_string']))? $request->server['query_string'] : '';

                            $datas = explode("&", $data);
                            foreach ($datas as $v){
                                $options = explode("=", $v);
                                if ($options[0] == 'callback'){
                                    $this->buffer['callback'] = $options[1];
                                }else{
                                    $params[$options[0]] = $options[1];
                                }
                            }
                            //根据 $controller, $action 映射到不同的控制器类和方法
                            if (isset($this->http->setting['task_worker_num']) && $this->http->setting['task_worker_num'] > 0){
                                $this->http->task(json_encode([
                                    'class' => $value['class'],
                                    'method' => $value['method'],
                                    'callback' => $value['callback'] ?? '',
                                    'data' => $params
                                ]));
                            }else{
                                $msg = call_user_func_array([$value['class'], $value['method']], [$params]);
                            }
                        }
                    }
                }

            }
            $response->header("Content-Type", "text/html; charset=utf-8");
            $response->end("<h1>Hello ".$msg.". #".rand(1000, 9999)."</h1>");
        }
    }

    /**
     * work/task进程启动时回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $worker_id 进程id
     */
    public function onWorkerStart(\swoole_server $serv, int $worker_id)
    {
        global $argv;
        echo "open worker ".$worker_id.PHP_EOL;
        $this->buffer['workerId'] = $serv->worker_id;
        $this->buffer['workerPid'] = $serv->worker_pid;
        if($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("{$argv[0]} PP task worker");
        } else {
            swoole_set_process_name("{$argv[0]} PP event worker");
            $result = json_decode($this->_registerService($serv), true);
            if ($result['status'] == 'success'){
                $this->_startUI($serv);
            }else{
                $serv->stop($worker_id, false);
            }
        }
    }

    /**
     * work进程终止时回调
     * @param \swoole_server $serv
     * @param int $worker_id
     */
    public function onWorkerStop(\swoole_server $serv, int $worker_id)
    {
        echo "close worker ".$worker_id.PHP_EOL;
    }

    /**
     * task进程调用回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $task_id task进程id
     * @param int $src_worker_id work进程id
     * @param string $data 任务内容
     * @return mixed
     */
    public function onTask(\swoole_server $serv, int $task_id, int $src_worker_id, string $data)
    {
        echo "task ".$task_id." work ".$src_worker_id. " data ".$data.PHP_EOL;
        sleep(1);
        $data = json_decode($data, true);
        $msg = call_user_func_array([$data['class'], $data['method']], [$data['data']]);
        return $msg;
    }

    /**
     * task执行完毕将结果发给work进程
     * @param \swoole_server $serv swoole_server对象
     * @param int $task_id 执行任务的task进程id
     * @param string $data 任务内容
     * @return mixed
     */
    public function onFinish(\swoole_server $serv, int $task_id, string $data)
    {
        echo "finish ".$task_id. " data ".$data.PHP_EOL;
        if ((isset($this->buffer['class'])&&!empty($this->buffer['class'])) && (isset($this->buffer['callback'])&&!empty($this->buffer['callback']))){
            try{
                $class = new ReflectionClass($this->buffer['class']);
                $class->getMethod($this->buffer['callback']);
                echo call_user_func_array([$this->buffer['class'], $this->buffer['callback']], ['success', $data]);
            }catch (\Exception $e){
                echo $e->getMessage();
            }
        }
    }

    /**
     * TCP客户端连接关闭，work进程中回调此函数
     * @param \swoole_server $serv swoole_server对象
     * @param int $fd 连接的文件描述符客户端id
     * @param int $reactorId 线程id
     * @return mixed
     */
    public function onClose(\swoole_server $serv, int $fd, int $reactorId)
    {
        echo "close ".$fd. " reactorId ".$reactorId.PHP_EOL;
        unset($this->buffer);
    }

    /**
     * 查询匹配的注册服务
     * @param string $requestUri
     * @return array
     */
    private function _getFileContent(string $requestUri):array
    {
        $services = file_get_contents(dirname(dirname(dirname(__FILE__))).'/registerService.txt');
        $serviceArr = explode("\n", trim($services, "\n"));
        $service = [];
        foreach($serviceArr as $key=>$value){
            if(strstr($value, rtrim($requestUri, "/"))){
                $option = explode("*", trim($value, "*"));
                switch(count($option)){
                    case 2:
                        $service[] = [
                            'class' => $option[0],
                            'method' => ''
                        ];
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
     * @param object $arguments
     */
    private function _registerService(object $arguments)
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

        try{
            $serviceModel = new ServiceController(new Consul(), $config['host'], $config['port']);
            $msg = call_user_func_array([$serviceModel, 'registrationService'], $service);
            return json_encode(['status'=>'success', 'msg'=>$msg]);
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>$e->getMessage()] );
        }
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
        echo "                   HTTP:".$this->HttpConfig['port']."  TCP:".$this->TcpConfig['port']."\n\n";
        echo "------------------------\033[47;30m PROCESS \033[0m---------------------------\n";
        if($serviceInfo[0]->Status == 'passing') {
            echo "      MasterPid：" . $this->buffer['masterPid'] . "---ManagerPid：" . $this->buffer['managerPid'] . "---WorkerId：" . $this->buffer['workerId'] . "---WorkerPid：" . $this->buffer['workerPid'] . PHP_EOL . PHP_EOL;
        }
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

    private function _startUI(object $arguments)
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
        echo "                   HTTP:".$this->HttpConfig['port']."  TCP:".$this->TcpConfig['port']."\n\n";
        echo "      MasterPid：".$arguments->master_pid."---ManagerPid：".$arguments->manager_pid."---WorkerId：".$arguments->worker_id."---WorkerPid：".$arguments->worker_pid.PHP_EOL;
    }

    /**
     * 回调魔术方法
     * @param string $name 回调方法名
     * @param string $arguments 回调参数
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }else{
            throw new \Exception('方法不存在:' . $name, 10);
        }
    }
}
