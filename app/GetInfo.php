<?php 
/*
 * @Author: findnr
 * @Date: 2024-05-31 10:42:17
 * @LastEditors: findnr
 * @LastEditTime: 2024-05-31 10:51:02
 * @Description: 
 */

declare(strict_types=1);

namespace app;

class GetInfo
{
    private $root='';

    public function __construct()
    {
        
    }
    public static function init()
    {
        return new GetInfo();
    }
    public function getRoot(){
        $this->root == '' ?$this->root= dirname(__DIR__):'';
        return $this->root;
    }

    
}