<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Controllers;

use Base\Controller\ApiController;
use Models\LoginModel;

class LoginController extends ApiController{
    //put your code here
    private $LoginModel; 

    public function __construct()
    {
        parent::__construct();
        $this->LoginModel = new LoginModel();        
    }

    public function checkLogin(){
        if($this->method=="POST"){  // in APi controller request method
            $res = $this->LoginModel->checkLogin($_POST['db'], $_POST['username'], $_POST['password']);

            echo $res;exit;
        }
        echo json_encode(array("result"=>FALSE, "message"=>"method not allowed"));
    }
}