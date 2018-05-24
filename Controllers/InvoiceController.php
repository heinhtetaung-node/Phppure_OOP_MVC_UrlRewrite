<?php

namespace Controllers;

use Base\Controller\ApiController;
use Models\InvoiceModel;

class InvoiceController extends ApiController
{
    private $InvoiceModel;
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents("php://input")); 
        $db = isset($data->db)? $data->db: null;
        $username = isset($data->username)? $data->username: null;
        $this->InvoiceModel = new InvoiceModel($db, $username);
        header('Content-type: application/json');
    }
    public function saveinvoicedata()
    {
        if(!$this->method){
            echo json_encode(array("result"=>FALSE, "message"=>"method not allowed"));   
            exit; 
        }
        if($this->method!="POST"){   
            echo json_encode(array("result"=>FALSE, "message"=>"method not allowed"));   
            exit; 
        }
        $data = json_decode(file_get_contents("php://input"));     

        $data = $data->postdata;
        $data = (array)$data;
        if(!$data){
            echo json_encode(array("result"=>FALSE, "message"=>"Something error"));    exit;
        }
        $res = $this->InvoiceModel->saveDirectInvoice($data);
        if($res['result']==1){
            echo json_encode(array('invoiceID'=>$res['response'],'result'=>TRUE, 'message'=>'Successfully Saved !!'));
            exit;
        }else{            
            echo json_encode(array('invoiceID'=>NULL,'result'=>FALSE, 'message'=>'Error Occoured'));
            exit;
        }
    }
}
