<?php 

declare(strict_types=1);

namespace app;

class Redis
{
    private $redis;
    public function __construct($redis){
        $this->redis = $redis;
    }
    public function get($name='')
    {
        $redis = $this->redis->get();
        $result = $redis->get($name);
        $this->redis->put($redis);
        return $result;
    }
    public function set($name="test",$value="123")
    {
        $redis = $this->redis->get();
        $result = $redis->set($name, $value);
        $this->redis->put($redis);
        return $result;
    }
}