<?php
/*
 * @Author: findnr
 * @Date: 2024-05-31 08:41:50
 * @LastEditors: findnr
 * @LastEditTime: 2024-05-31 16:38:11
 * @Description: 
 */
declare(strict_types=1);

use \Swoole\Database\PDOConfig;
use \Swoole\Database\PDOPool;
use function \Swoole\Coroutine\go;
use \Swoole\Database\RedisConfig;
use \Swoole\Database\RedisPool;

function get_mysql_exam($num)
{
    return new PDOPool((new PDOConfig)
                    ->withHost('127.0.0.1')
                    ->withPort(3306)
                    // ->withUnixSocket('/tmp/mysql.sock')
                    ->withDbName('test')
                    ->withCharset('utf8')
                    ->withUsername('test')
                    ->withPassword('test'),
                $num
            );
}
function get_redis($num){
    return new RedisPool((new RedisConfig)
        ->withHost('127.0.0.1')
        ->withPort(6379)
        ->withAuth('')
        ->withDbIndex(0)
        ->withTimeout(1),$num
    );
}
$mysql_num=2;
// $redis_num=5;
$db_arr=[
    'exam'=>[
        'type'=>'mysql',
        'obj'=>get_mysql_exam($mysql_num),
        'num'=>$mysql_num,
        'fun'=>'mysql_keep_alive',
        'is_active'=>['task_worker'=>false,'worker'=>true]],
    // 'redis'=>[
    //     'type'=>'redis',
    //     'obj'=>get_redis($redis_num),
    //     'num'=>$redis_num,
    //     'fun'=>'redis_keep_alive',
    //     'is_active'=>['task_worker'=>false,'worker'=>true]]
];
function redis_keep_alive($obj,$num)
{
    \Swoole\Timer::tick(29000, function () use ($obj,$num) {
        for ($i = 0; $i < $num; $i++) {
            go(function()use($i,$obj){
                $redis = $obj->get();
                $redis->ping();
                $obj->put($redis);
            });
        }
    });
}
function mysql_keep_alive($obj,$num)
{
    \Swoole\Timer::tick(25200000, function () use ($obj,$num) {
        for ($i = 0; $i < $num*10; $i++) {
            go(function()use($i,$obj){
                $db=$obj->get();
                $sql = 'select 1';
                $statement = $db->query($sql);
                $statement->fetchAll(\PDO::FETCH_ASSOC);
                $obj->put($db);
            });
        }
    });
}
