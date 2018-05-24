<?php

namespace Controllers;

use Base\Controller\ApiController;
use Models\EmployeeModel;

class EmployeeController extends ApiController
{
    private $EmployeeModel;

    public function __construct()
    {
        parent::__construct();
        $db = isset($_POST['db'])? $_POST['db'] : null;
        $username = isset($_POST['username'])? $_POST['username'] : null;
        $this->EmployeeModel = new EmployeeModel($db, $username);
    }

    public function getDeletedEmployee(){
        if(isset($_POST['employee_id'])){
            $res = $this->EmployeeModel->getDeletedEmployee($_POST['employee_id']);
            echo json_encode($res);
            exit;
        }
        echo json_encode(array('result'=>false, 'message'=>'method not allowed'));
        exit;
    }


}
