<?php

/**
 * 说明：http服务
 */
ini_set('date.timezone', 'Asia/Shanghai');

use Swoole\Process;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

$http = new Swoole\Http\Server('0.0.0.0', 9502);

$dir = __DIR__;

$http->set(array(
    // 'reactor_num'   => 2,     // 线程数
    'worker_num'    => 2,     // 进程数
    // 'backlog'       => 128,   // 设置Listen队列长度
    // 'max_request'   => 50,    // 每个进程最大接受请求数
    // 'dispatch_mode' => 1,     // 数据包分发策略
    'daemonize' => false,
    // 'open_tcp_keepalive' => true,
    // 'tcp_keepidle' => 60, //4s没有数据传输就进行检测
    // 'tcp_keepinterval' => 1, //1s探测一次
    'heartbeat_idle_time'      => 60, // 表示一个连接如果60秒内未向服务器发
    // 'tcp_keepcount' => 5, //探测的次数，超过5次后还没回包close此连接送任何数据，此连接将被强制关闭
    'heartbeat_check_interval' => 60,  // 表示每60秒遍历一次
    'task_enable_coroutine' => true,
    'task_worker_num' => 1,
    // 'log_file' => $dir . DIRECTORY_SEPARATOR . 'shareapi_log',

));

$http->on('start', function ($server) use ($dir, $http) {
    echo "Swoole http server is started at http://0.0.0.0:9502\n";
});
$table_array=[];
$test_table = new Swoole\Table(50);
$test_table->column('token', Swoole\Table::TYPE_STRING, 32);
$test_table->column('time', Swoole\Table::TYPE_INT);
$test_table->create();

$test_table->set('cost81f7b0a859e624580da7a', ['token' => '', 'time' => 0]);
$table_array=['test_table'=>$test_table];

$http->tables=$table_array;

$http->on('WorkerStart', function ($server, $worker_id) use ($dir) {
    $mysql_array=[];
    spl_autoload_register(function ($class) use ($dir) {
        $tmp = explode('\\', $class);
        $len = count($tmp) - 1;
        $tmp[$len] = $tmp[$len] . '.php';
        $path_include = $dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmp);
        if (is_file($path_include)) {
            include_once $path_include;
        }
    });
    
    if ($server->taskworker) {
        echo "task workerId：{$worker_id}\n";
        echo "task worker_id：{$server->worker_id}\n";
        $num = 1;
        include_once $dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'function.php';
        include_once $dir . DIRECTORY_SEPARATOR . 'mysqlconfig.php';
        $mysql_array['mysql_test']=get_mysql_obj($num);
        $server->mysqls = $mysql_array;
        $file = glob($dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . "*.php");
        array_walk($file, function ($v) {
            include_once $v;
        });
    } else {
        $num = 2;
        include_once $dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'function.php';
        include_once($dir . DIRECTORY_SEPARATOR . 'mysqlconfig.php');
        $mysql_array['mysql_test']=get_mysql_obj($num);
        $server->mysqls = $mysql_array;
        connect_auto_time( $mysql_array['mysql_test'], $num);
        $file = glob($dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . "*.php");
        array_walk($file, function ($v) {
            include_once $v;
        });
    }
});

$http->on('Request', function ($request, $response) use ($dir, $http) {
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }
    $response->header('Content-Type', 'application/json; charset=utf-8');

    $path = explode('/', trim($request->server['request_uri'], '/'));

    $obj = 'app' . '\\' . $path[0] . '\\controller\\' . ucfirst($path[1]);


    try {
        $class = new $obj($http, $request, $dir, $response);
    } catch (\Throwable $th) {
        $response->end(json_encode(['code' => 400, 'path' => $obj], JSON_UNESCAPED_UNICODE));
        return;
    }
    try {
        $data = call_user_func_array([$class, $path[2]], []);
    } catch (\Throwable $th) {
        //throw $th;
        $response->end(json_encode(['code' => 400, 'path' => $obj, 'method' => $path[2], 'msg' => '没有找到这个方法'], JSON_UNESCAPED_UNICODE));
        return;
    }
    if (!empty($data['code']) &&  $data['code'] == 10000) {
        $response->end($data['data']);
    } else {
        $response->end(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
});
$http->on('task', function ($server, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]\n";
    $server->finish("{$data} -> OK");
});

$http->on('finish', function ($server, $task_id, $data) {
    echo "AsyncTask[{$task_id}] finished: {$data}\n";
});
$process = new Process(function () use ($dir, $http) {
    $fd = inotify_init();
    inotify_add_watch($fd, $dir . '/app', IN_MODIFY | IN_CREATE | IN_DELETE | IN_ISDIR);
    inotify_add_watch($fd, $dir . '/app/testcommon', IN_MODIFY | IN_CREATE | IN_DELETE | IN_ISDIR);
    inotify_add_watch($fd, $dir . '/app/test/controller', IN_MODIFY | IN_CREATE | IN_DELETE | IN_ISDIR);
    inotify_add_watch($fd, $dir . '/lib', IN_MODIFY | IN_CREATE | IN_DELETE | IN_ISDIR);
    Co\run(function () use ($fd, $http) {
        Swoole\Event::add($fd, function () use ($fd, $http) {
            $info = inotify_read($fd);
            $http->reload();
        });
    });
});

$process->start();

$http->start();
