<?php

/**
 * 说明：http服务
 */
use Swoole\Process;

spl_autoload_register(function ($class) use ($path_root) {
    $tmp = explode('\\', $class);
    $len = count($tmp) - 1;
    $tmp[$len] = $tmp[$len] . '.php';
    $path_include = $path_root . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmp);
    if (is_file($path_include)) {
        include_once $path_include;
    }
});

include_once $path_root . DIRECTORY_SEPARATOR . 'app'. DIRECTORY_SEPARATOR . $script_name. DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR.'common.php';

ini_set('date.timezone', $server_config['timezone']);

$http = new Swoole\Http\Server($server_config['host'], $server_config['port']);
$defaule_config=[
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
    // 'log_file' => $dir . DIRECTORY_SEPARATOR . 'safetyexam_log',
];
$http->set(array_merge($defaule_config,$server_config['set']));

$http->on('start', function () use ($server_config)  {
    echo "Swoole http server is started at http://".$server_config['host'].":".$server_config['port']."\n";
});

$table_array = [];
foreach ($table_box as $k => $v) {
    ${$v['name']} = new Swoole\Table($v['num']);
    foreach ($v['content'] as $ks => $vs) {
        if(empty($vs['len'])){
            ${$v['name']}->column($vs['name'], $vs['type']);
        }else{
            ${$v['name']}->column($vs['name'], $vs['type'], $vs['len']);
        }
    }
    ${$v['name']}->create();
    array_push($table_array,[$v['name']=>${$v['name']}]);
}

$http->tables = $table_array;

$http->script_name = $script_name;
$http->path_root = $path_root;

$http->on('WorkerStart', function ($server, $worker_id) use ($http,$include_file,$include_dir,$header_info,$task_worker_list) {
    $http->header_info=$header_info;
    spl_autoload_register(function ($class) use ($http) {
        $tmp = explode('\\', $class);
        $len = count($tmp) - 1;
        $tmp[$len] = $tmp[$len] . '.php';
        $path_include = $http->path_root . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmp);
        if (is_file($path_include)) {
            include_once $path_include;
        }
    });
    //加载公共函数
    include_once $http->path_root.DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'function.php';
    //加载批定的类，在配制文件中设置
    foreach ($include_file as $k => $v) {
        include_once $v;
    }
    //配制文件的路由信息
    $http->router=$router;
    //加载指定目录下的所用PHP文件
    array_walk($include_dir,function($v){
        $file = glob($v);
        array_walk($file, function ($v) {
            include_once $v;
        });
    });
    //taskworker进程与work进程分别加载信息
    if ($server->taskworker) {
        //加载数据库，redis相关的配制信息，可在配制文件中设置
        foreach ($db_arr as $k=>$v) {
            if($v['is_active']['task_worker']){
                $server->db[$k]=$v['obj'];
                if($v['fun'] != ''){
                    $v['fun']($v['obj'],$v['num']);
                }
            }
        }
        //在task进程中可以运行的程序，类中必须要有run这个方法才能运行。
        foreach($task_worker_list as $v){
            (new $v($http))->run();
        }
    } else {
        //加载数据库，redis相关的配制信息，可在配制文件中设置
        foreach ($db_arr as $k=>$v) {
            if($v['is_active']['worker']){
                $server->db[$k]=$v['obj'];
                if($v['fun'] != ''){
                    $v['fun']($v['obj'],$v['num']);
                }
            }
        }
    }
});

$http->on('Request', function ($request, $response) use ($http) {
    foreach ($http->header_info as $k => $v) {
        $response->header($k,$v);
    }
    if ($request->server['request_method'] == 'OPTIONS') {
        $response->status(200);
        $response->end();
        return;
    };
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }
    foreach($http->router as $k => $v){
        $response->header('Content-Type',$v['Content-Type']);
        if ($request->server['path_info'] == $k || $request->server['request_uri'] == $k) {
            $response->end(json_encode($v['data'], JSON_UNESCAPED_UNICODE));
            return;
        }
    }
    $response->header('Content-Type','application/json; charset=utf-8');
    $path = explode('/', trim($request->server['request_uri'], '/'));

    $obj = 'app' . '\\' . $path[0] . '\\controller\\' . ucfirst($path[1]);


    try {
        $class = new $obj($http, $request, $response);
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
function hot_start($http,$hot_file){
    $process = new Process(function () use ($http,$hot_file) {
        $fd = inotify_init();
        foreach ($hot_file as $k => $v) {
            inotify_add_watch($fd, $v, IN_MODIFY | IN_CREATE | IN_DELETE | IN_ISDIR);
        }
        Co\run(function () use ($fd, $http) {
            // var_dump($http);
            Swoole\Event::add($fd, function () use ($fd, $http) {
                $info = inotify_read($fd);
                $http->reload();
            });
        });
    });
    $process->start();
}
switch (PHP_OS) {
    default:
    case 'Linux':
        function_exists('inotify_init') ? hot_start($http,$hot_file):'';
        break;
    case 'Darwin':
        break;
    case 'WINNT':
        break;
}

$http->start();
