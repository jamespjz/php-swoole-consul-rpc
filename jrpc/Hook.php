<?php
/**+----------------------------------------------------------------------
 * JamesPi RPC [php-swoole-consul-rpc]
 * +----------------------------------------------------------------------
 * Hook function file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */
namespace Jamespi\Rpc\jrpc;

class Hook {

    public $hookPath = ''; //钩子路径
    public $object; //钩子实例化类
    public $path; //完整钩子文件目录

    /**
     * 构造函数
     * Hook constructor.
     */
    public function __construct()
    {
        $current_path  = str_replace("\\", '/', getcwd()); //获取当前文件目录
        $this->path = $current_path.$this->hookPath;
    }


    public function runHook($class, $function, $param = []){
        $result = new ReflectionClass('Jamespi\Rpc\jrpc\App');
        $className = strrchr($class, '\\');
        $urlName = substr($class, 0, (stripos($class,$className)+1));
    }

    public function getAllClass(){

    }


}