<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-02-21 11:23:57
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-03-27 13:31:27
 * @FilePath: \swooleapi\mysqlconfig.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE$
 */

declare(strict_types=1);

use \Swoole\Database\PDOConfig;
use \Swoole\Database\PDOPool;
use function \Swoole\Coroutine\go;
use function \Swoole\Coroutine\run;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

function get_mysql_obj($num)
{
    return new PDOPool((new PDOConfig)
                    ->withHost('127.0.0.1')
                    ->withPort(3306)
                    // ->withUnixSocket('/tmp/mysql.sock')
                    ->withDbName('')
                    ->withCharset('utf8')
                    ->withUsername('')
                    ->withPassword(''),
                $num
            );
}

function get_mysql_three($num)
{
    return new PDOPool((new PDOConfig)
                    ->withHost('127.0.0.1')
                    ->withPort(3306)
                    // ->withUnixSocket('/tmp/mysql.sock')
                    ->withDbName('')
                    ->withCharset('utf8')
                    ->withUsername('')
                    ->withPassword(''),
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
$db_arr=[
    'three'=>[
        'obj'=>get_mysql_three(2),
        'num'=>2,
        'fun'=>'connect_auto_time',
        'is_active'=>['task_worker'=>false,'worker'=>true]],
    'redis'=>[
        'obj'=>get_redis(5),
        'num'=>5,
        'fun'=>'',
        'is_active'=>['task_worker'=>false,'worker'=>true]]
];

function connect_auto_time($mysql,$num)
{
    \Swoole\Timer::tick(25200000, function () use ($mysql,$num) {
        for ($i = 0; $i < $num*10; $i++) {
            go(function()use($i,$mysql){
                $db=$mysql->get();
                $sql = 'select 1';
                $statement = $db->query($sql);
                $statement->fetchAll(\PDO::FETCH_ASSOC);
                $mysql->put($db);
            });
        }
    });

}
