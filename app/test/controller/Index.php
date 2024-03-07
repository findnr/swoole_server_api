<?php 
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-07 21:41:39
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-03-07 21:45:23
 * @FilePath: \swoole_http_api\app\test\controller\Index.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app\test\controller;

use app\test\common\Test;
use app\Base;

class Index extends Base
{
    public function index()
    {
        $test=new Test();
        $data['code']=200;
        $data['msg']="Hello";
        $data['data']=['abc'=>'xyz','test'=>$test->test()];
        return $data;
    }
}   