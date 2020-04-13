<?php
/**+----------------------------------------------------------------------
 * JamesPi RPC [php-swoole-consul-rpc]
 * +----------------------------------------------------------------------
 * RPC protocol call service file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Rpc\src\Server;

abstract class Service
{
    /**
     * Http服务相关配置
     */
    protected $HttpConfig = [];

    /**
     * Tcp服务相关配置
     */
    protected $TcpConfig = [];

    /**
     * webSocket服务相关配置
     * @var array
     */
    protected $WebSocket = [];

    /**
     * consul配置
     * @var array
     */
    protected $ConsulConfig = [];

    /**
     * Service constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->init($config);
        /**
         * Tcp服务相关配置
         */
        if (isset($config['TcpConfig']) && $config['TcpConfig']){
            $this->TcpConfig = [
                //请求地址
                'host' => $config['TcpConfig']['host'],
                //请求端口
                'port' => $config['TcpConfig']['port'],
                //设置启动的线程数（小于等于worker_num）cpu核数的1-4倍
                'reactor_num' => ( isset($config['TcpConfig']['reactor_num'])&&$config['TcpConfig']['reactor_num'] )?$config['TcpConfig']['reactor_num']:2,
                //设置启动的work进程数，cpu核数的1-4倍
                'worker_num' => ( isset($config['TcpConfig']['worker_num'])&&$config['TcpConfig']['worker_num'] )?$config['TcpConfig']['worker_num']:0,
                //设置work进程的最大任务数（达到max_request条件的不会立马关闭进程而是在max_wait_time后）
                'max_request' => ( isset($config['TcpConfig']['max_request'])&&$config['TcpConfig']['max_request'] )?$config['TcpConfig']['max_request']:100,
                //服务器程序，最大允许的连接数
                'max_conn' => ( isset($config['TcpConfig']['max_conn'])&&$config['TcpConfig']['max_conn'] )?$config['TcpConfig']['max_conn']:500,
                //配置task进程数量
                'task' => [
                    //配置task进程数量
                    'task_worker_num' => ( isset($config['TcpConfig']['task_worker_num'])&&$config['TcpConfig']['task_worker_num'] )?$config['TcpConfig']['task_worker_num']:0,
                    //设置 task 进程的最大任务数
                    'task_max_request' => ( isset($config['TcpConfig']['task_max_request'])&&$config['TcpConfig']['task_max_request'] )?$config['TcpConfig']['task_max_request']:1,
                ],
                //守护进程化启用相关配置
                'daemonize' => [
                    //守护进程化【默认值：0】
                    'daemonize' => ( isset($config['TcpConfig']['daemonize'])&&$config['TcpConfig']['daemonize'] )?$config['TcpConfig']['daemonize']:0,
                    //指定 Swoole 错误日志文件【默认值：】(开启守护进程模式后)
                    'log_file' => ( isset($config['TcpConfig']['log_file'])&&$config['TcpConfig']['log_file'] )?$config['TcpConfig']['log_file']:'/var/log/swoole/error.log',
                    //设置 Server 错误日志打印的等级
                    'log_level' => ( isset($config['TcpConfig']['log_level'])&&$config['TcpConfig']['log_level'] )?$config['TcpConfig']['log_level']:2
                ],
                //心跳启动相关配置
                'keepalive' => [
                    //启用心跳检测【默认值：false】
                    'heartbeat_check_interval' => ( isset($config['TcpConfig']['heartbeat_check_interval'])&&$config['TcpConfig']['heartbeat_check_interval'] )?$config['TcpConfig']['heartbeat_check_interval']:false,
                    //连接最大允许空闲的时间【默认值：】需要与 heartbeat_check_interval 配合使用
                    'heartbeat_idle_time' => ( isset($config['TcpConfig']['heartbeat_idle_time'])&&$config['TcpConfig']['heartbeat_idle_time'] )?$config['TcpConfig']['heartbeat_idle_time']:60
                ],
            ];
        }


        /**
         * Http服务相关配置
         */
        if (isset($config['HttpConfig']) && $config['HttpConfig']){
            $this->HttpConfig = [
                //请求地址
                'host' => $config['HttpConfig']['host'],
                //请求端口
                'port' => $config['HttpConfig']['port'],
                //守护进程化启用相关配置
                'daemonize' => ( isset($config['HttpConfig']['daemonize'])&&$config['HttpConfig']['daemonize'] )?$config['HttpConfig']['daemonize']:0,
                //task进程数
                'task_worker_num' => ( array_key_exists('task_worker_num', $config['HttpConfig'])&&$config['HttpConfig']['task_worker_num'] )?$config['HttpConfig']['task_worker_num']:0,
                //work进程数
                'worker_num' => ( array_key_exists('worker_num', $config['HttpConfig'])&&$config['HttpConfig']['worker_num'] )?$config['HttpConfig']['worker_num']:0,
                //设置上传文件的临时目录。目录最大长度不得超过 220 字节
                'upload_tmp_dir' => ( isset($config['HttpConfig']['upload_tmp_dir'])&&$config['HttpConfig']['upload_tmp_dir'] )?$config['HttpConfig']['upload_tmp_dir']:'/data/uploadfiles/',
                //针对 Request 对象的配置，设置 POST 消息解析开关，默认开启
                'http_parse_post' => ( isset($config['HttpConfig']['http_parse_post'])&&$config['HttpConfig']['http_parse_post'] )?$config['HttpConfig']['http_parse_post']:true,
                //针对 Request 对象的配置，关闭 Cookie 解析，将在 header 中保留未经处理的原始的 Cookies 信息。默认开启
                'http_parse_cookie' => ( isset($config['HttpConfig']['http_parse_cookie'])&&$config['HttpConfig']['http_parse_cookie'] )?$config['HttpConfig']['http_parse_cookie']:true,
                //针对 Response 对象的配置，启用压缩。默认为开启(v>4.1.0)
                'http_compression' => ( isset($config['HttpConfig']['http_compression'])&&$config['HttpConfig']['http_compression'] )?$config['HttpConfig']['http_compression']:true,
                //压缩级别，针对 Response 对象的配置,等级范围是 1-9,等级越高压缩后的尺寸越小，但 CPU 消耗更多
                'http_compression_level' => ( isset($config['HttpConfig']['http_compression_level'])&&$config['HttpConfig']['http_compression_level'] )?$config['HttpConfig']['http_compression_level']:1,
            ];
        }

        /**
         * webSocket服务相关配置
         */
        $this->WebSocket = [

        ];

        /**
         * consul配置
         */
        $this->ConsulConfig = [
            'host' => (isset($config['ConsulConfig'])&&isset($config['ConsulConfig']['host'])&&!empty($config['ConsulConfig']['host']))?$config['ConsulConfig']['host']:'0.0.0.0',
            'port' => (isset($config['ConsulConfig'])&&isset($config['ConsulConfig']['port'])&&!empty($config['ConsulConfig']['port']))?$config['ConsulConfig']['port']:'80'
        ];
    }

    /**
     * 初始化方法
     * @param array $config 配置参数
     * @return mixed
     */
    abstract public function init(array $config);

    /**
     * 启动服务
     * @return mixed
     */
    abstract public function run();

}