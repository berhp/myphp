<?php
/**
 * Created by PhpStorm. 接口demo
 * User: huangping
 * Date: 2016/11/28
 * Time: 13:02
 */
namespace Home\Controller;
use Api\BaseApiController;
use Home\Model\UserModel;
class ApiController extends BaseApiController{
    protected $db;
    public function _init(){
        $this->db = new UserModel();
    }

    public function login(){
        $info = $this->db->login();
        return $this->jsonOutput($info);
    }


}