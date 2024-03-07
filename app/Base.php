<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-07 21:32:31
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-03-07 21:40:52
 * @FilePath: \swoole_http_api\app\Base.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app;

use lib\SqlCreate;

class Base{

    public $common;

    public $req;
    
    public $request;
    
    public $response;
    
    public $header;
    
    public $dirs;
    
    public $sql;

    public function __construct($http,$request,$dirs,$response)
    {
        $this->common=$http;
        $this->response=$response;
        $this->request=$request;
        $this->dirs=$dirs;
        $this->sql = new SqlCreate();
        $this->sql->setLogPath($this->dirs);
        include_once $this->dirs.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'function.php';
        $this->_get_merge();
        $this->header= $this->request->header;
    }
    public function _get_merge(){
        
        $post = $this->request->post == NULL ? [] :$this->request->post;
        $get = $this->request->get == NULL ? [] : $this->request->get;
        $this->req=array_merge($post,$get);
    }
    
    public function getBody(){
        return $this->request->getContent();
    }
}