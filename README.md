# 皮皮RPC框架(PP RPC Server)
php整合swoole及consul进行封装成的RPC服务端框架，支持consul进行服务治理。

>使用版本说明：Consul v1.7.2 - PHP v7.4.1 - Swoole v4.4.12 - Composer v1.10

# 简要说明：
公司目前正在全面转微服务架构，这个RPC框架是为了让公司phper在分布式系统间能够简单方便调用各依赖服务的方法从而进行封装的框架，框架采用PHP+SWOOLE+CONSUL进行搭建，支持服务治理功能，支持HTTP/TCP/UDP/Websocket协议，目前为version 0.1-dev。

# 功能简介：
* 服务注册
* 服务发现
* 服务删除
* 服务健康检测
* 获取单节点服务列表
* HTTP调用
* TCP/UDP调用
* Websocket调用
* 其余特性参考 https://www.consul.io https://wiki.swoole.com

# 部署安装
* github下载
```
git clone https://github.com/jamespjz/php-swoole-consul-rpc.git
```
已经加入对composer支持，根目录下有个composer.json，请不要随意修改其中内容如果你明白你在做什么操作。
* composer下载
```
composer require jamespi/php-swoole-consul-rpc dev-master
```

# 使用方式
>调用PP RPC例子

```
//在您项目根目录的入口文件中加入如下代码：
require_once 'vendor/autoload.php';
use Jamespi\Rpc\Start;

$config = [
    'HttpConfig' => [
        'host' => '192.168.109.58',
        'port' => '9501'
    ],
    'TcpConfig' => [
        'host' => '192.168.109.58',
        'port' => '9501'
    ],
    'WebSocket' => [
        'host' => '192.168.109.58',
        'port' => '9501'
    ],
    'ConsulConfig' => [
        'host' => '192.168.109.58',
        'port' => '8500'
    ],
];

echo (new Start())->run($argv, $config);
```
> 使用命令
```
//(假如您的入口文件名为index.php)在命令行模式下输入如下命令：
php index.php [command] [option]
1.  start,以debug模式开启服务，此时服务不会以daemon形式运行[当配置日志当中配置daemon=1时将以daemon模式开启服务]
2.  start -d,以daemon模式开启服务
3.  status,查看服务器的状态
4.  stop,停止服务器
5.  help,查看帮助文档，罗列所有操作命令
```
> 参数说明：
* 客户端调用HTTP服务器方式：
```
1、直接访问：192.168.109.58:9501
2、带参数访问：192.168.109.58:9501?id=1
3、带请求路径及参数访问：192.168.109.58:9501/hello/index?id=1&uid=1
```
* 如下参数为框架内置参数，约定不能修改，否则可能会发生系统错误
---
1、HttpConfig：调用HTTP服务时对HTTP服务的配置参数
```
1、host：请求地址（必传）
2、port：请求端口（必传）
3、daemonize：守护进程化启用相关配置（默认为0 1：开启  0：不开启）
4、upload_tmp_dir：设置上传文件的临时目录。目录最大长度不得超过 220 字节
5、http_parse_post：针对 Request 对象的配置，设置 POST 消息解析开关，默认开启（true）
6、http_parse_cookie：针对 Request 对象的配置，关闭 Cookie 解析，将在 header 中保留未经处理的原始的 Cookies 信息。默认开启（true）
7、http_compression：针对 Response 对象的配置，启用压缩。默认为开启(v>4.1.0)
8、http_compression_level：压缩级别，针对 Response 对象的配置,等级范围是 1-9,等级越高压缩后的尺寸越小，但 CPU 消耗更多
```
---
2、TcpConfig：调用TCP服务时对TCP服务的配置参数
```
1、host：请求地址（必传）
2、port：请求端口（必传）
3、reactor_num：设置启动的线程数（小于等于worker_num）cpu核数的1-4倍
4、worker_num：设置启动的work进程数，cpu核数的1-4倍
5、max_request：设置work进程的最大任务数（达到max_request条件的不会立马关闭进程而是在max_wait_time后）
6、max_conn：服务器程序，最大允许的连接数
7、task_worker_num：配置task进程数量
8、task_max_request：设置 task 进程的最大任务数
9：daemonize：守护进程化【默认值：0】
10、log_file：指定 Swoole 错误日志文件【默认值：】(开启守护进程模式后)
11、log_level：设置 Server 错误日志打印的等级
12、heartbeat_check_interval：启用心跳检测【默认值：false】
13、heartbeat_idle_time：连接最大允许空闲的时间【默认值：】需要与 heartbeat_check_interval 配合使用
```
---
3、WebSocket：调用WebSocket服务时对WebSocket服务的配置参数
```

```
---
4、ConsulConfig：调用Consul进行服务治理时对Consul服务的配置参数
```
1、host：请求地址（必传）
2、port：请求端口（必传）
```

# 联系方式
* wechat：james-pi
* email：jianzhongpi@163.com
