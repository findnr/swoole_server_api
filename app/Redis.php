<?php 
/*
 * @Author: findnr
 * @Date: 2024-03-27 13:38:38
 * @LastEditors: findnr
 * @LastEditTime: 2024-05-10 09:26:52
 * @Description: 
 */

declare(strict_types=1);

namespace app;

class Redis
{
    private $redisPools;
    public function __construct($redis){
        $this->redisPools = $redis;
    }
    public function get(string $name='')
    {
        $redis = $this->redisPools->get();
        $result = $redis->get($name);
        $this->redisPools->put($redis);
        return $result;
    }
    public function set(string $name='',string $value='')
    {
        $redis = $this->redisPools->get();
        $result = $redis->set($name, $value);
        $this->redisPools->put($redis);
        return $result;
    }
    public function getArr(string $name='')
    {
        $redis = $this->redisPools->get();
        $result = $redis->get($name);
        $this->redisPools->put($redis);
        if($result) return json_decode($result,true);
        return $result;
    }
    public function setArr(string $name='',array $value=[])
    {
        $redis = $this->redisPools->get();
        $result = $redis->set($name, json_encode($value,JSON_UNESCAPED_UNICODE));
        $this->redisPools->put($redis);
        return $result;

    }
    public function flush()
    {
        $redis = $this->redisPools->get();
        $redis->flushAll();
        $this->redisPools->put($redis);
    }
}