<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-07 21:32:31
 * @LastEditors: findnr
 * @LastEditTime: 2024-05-30 14:24:26
 * @FilePath: \swoole_http_api\app\Base.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app;

class Base{

    public $common;

    public $req;
    
    public $request;
    
    public $response;
    
    public $header;
    
    public $dirs;
    
    public $sql;

    public function __construct($http,$request,$response)
    {
        $this->common=$http;
        $this->response=$response;
        $this->request=$request;
        $this->dirs=$http->path_root;
        // include_once $this->dirs.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'function.php';
        $this->_get_merge();
        $this->header= $this->request->header;
        static::addOtherInfo();
    }
    protected function addOtherInfo()
    {
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