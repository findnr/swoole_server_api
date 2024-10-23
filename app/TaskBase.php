<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-07 21:32:31
 * @LastEditors: findnr
 * @LastEditTime: 2024-09-12 16:18:50
 * @FilePath: \swoole_http_api\app\Base.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app;

class TaskBase{

    public $common;

    public $dirs;

    public function __construct($http)
    {
        $this->common=$http;
        $this->dirs=$http->path_root;
        static::addOtherInfo();
    }
    protected function addOtherInfo()
    {
    }
}