<?php
/*
 * @Author: findnr
 * @Date: 2024-05-30 14:41:02
 * @LastEditors: findnr
 * @LastEditTime: 2024-10-23 10:59:53
 * @Description: 
 */
declare(strict_types=1);
use app\GetInfo;
$root=GetInfo::init()->getRoot();
$dir = __DIR__;
//服务器配制信息
$server_config=[
    'timezone'=>'Asia/Shanghai',
    'port' => '9502',
    'host' => '0.0.0.0',
    'set'=>[
        'worker_num'    => 2,
        'daemonize' => false,
        'log_file' => $root . DIRECTORY_SEPARATOR . 'test_log', 
    ],
];
// $test_table->set('cost81f7b0a859e624580da7a', ['token' => '', 'time' => 0]);
// 高性能共享内存 Table
$table_box=[
    [
        'name' => 'table_test',
        'num' => 50,
        'content'=>[
            [
                'name'=>'token',
                'type'=>\Swoole\Table::TYPE_STRING,
                'len'=>32,
            ],
            [
                'name' => 'time',
                'type'=>\Swoole\Table::TYPE_INT,
            ]
        ],
    ],
];
//需要加载的文件，文件加载是在works进程中加载的
$include_file=[
    $dir. DIRECTORY_SEPARATOR. 'db.php',
    $dir. DIRECTORY_SEPARATOR. 'router.php',
];
$include_dir=[
    // __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . "*.php"
];
//返回的头信息（包括跨域的配制）
$header_info=[
    'Access-Control-Allow-Origin'=>'*',
    'Access-Control-Allow-Methods'=>'GET, POST, DELETE, PUT, PATCH, OPTIONS',
    'Access-Control-Allow-Headers'=>'Authorization, User-Agent, Keep-Alive, Content-Type, X-Requested-With',
];
// （动态检测文件变化，如有变量自动重新加载服务）只在linux中并且PHP安装inotify扩展才能使用
$hot_file=[
    $root.'/app',
    $root.'/app/xiehui/common',
    $root.'/app/xiehui/controller',
    $root.'/app/xiehui/config',
];
//task_worker进程要执行的任务
$task_worker_list=[];