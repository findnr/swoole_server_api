<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-02-21 11:23:57
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-03-07 21:33:06
 * @FilePath: \swooleapi\mysqlconfig.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE$
 */

declare(strict_types=1);

use \Swoole\Database\PDOConfig;
use \Swoole\Database\PDOPool;
use function \Swoole\Coroutine\go;
use function \Swoole\Coroutine\run;


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

function connect_auto_time($mysql,$num)
{
    \Swoole\Timer::tick(25200000, function () use ($mysql,$num) {
        for ($i = 0; $i < $num; $i++) {
            go(function()use($i,$mysql,$num){
                ${'mysql'.$i}=$mysql->get();
                $sql = 'select 1';
                $statement = ${'mysql'.$i}->query($sql);
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $mysql->put(${'mysql'.$i});
            });
        }
    });

}
