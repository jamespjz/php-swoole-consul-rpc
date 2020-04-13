<?php
/**+----------------------------------------------------------------------
 * JamesPi RPC [php-swoole-consul-rpc]
 * +----------------------------------------------------------------------
 * HTTP protocol service interface file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Rpc\src\Api;

use Swoole\Http\Server;
interface HttpInterface
{
    /**
     * 新连接建立时在work进程中回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $fd 连接的文件描述符，连接的客户端id
     * @param int $reactorId 线程id
     */
    public function onConnect(\swoole_server  $serv, int $fd, int $reactorId);

    /**
     * HTTP服务启动回调方法
     * @param \swoole_server $serv swoole_server对象
     */
    public function onStart(\swoole_server $serv);

    /**
     * 接收请求回调方法
     * @param \swoole_http_request $request 请求对象
     * @param \swoole_http_response $response 响应对象
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response);

    /**
     * work/task进程启动时回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $worker_id work/task进程id
     */
    public function onWorkerStart(\swoole_server $serv, int $worker_id);

    /**
     * work进程终止时回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $worker_id work进程id
     */
    public function onWorkerStop(\swoole_server $serv, int $worker_id);

    /**
     * task进程调用回调
     * @param \swoole_server $serv swoole_server对象
     * @param int $task_id task进程id
     * @param int $src_worker_id work进程id
     * @param string $data 任务内容
     * @return mixed
     */
    public function onTask(\swoole_server $serv, int $task_id, int $src_worker_id, string $data);

    /**
     * task执行完毕将结果发给work进程
     * @param \swoole_server $serv swoole_server对象
     * @param int $task_id 执行任务的task进程id
     * @param string $data 任务内容
     * @return mixed
     */
    public function onFinish(\swoole_server $serv, int $task_id, string $data);

    /**
     * TCP客户端连接关闭，work进程中回调此函数
     * @param \swoole_server $serv swoole_server对象
     * @param int $fd 连接的文件描述符客户端id
     * @param int $reactorId 线程id
     * @return mixed
     */
    public function onClose(\swoole_server $serv, int $fd, int $reactorId);
}