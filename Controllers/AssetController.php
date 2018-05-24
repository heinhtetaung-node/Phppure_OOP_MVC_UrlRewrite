<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
 
namespace Controllers;
use Base\Controller\ApiController;
use Models\AssetModel;
use Validator;
class AssetController extends ApiController{
    

    private $AssetModel;
    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents("php://input")); 
        $db = isset($data->db)? $data->db: 'hkg';
        $username = isset($data->username)? $data->username: 'ophonbu';
        $this->AssetModel = new AssetModel($db, $username);
        header('Content-type: application/json');
    }

    public function saveasset()
    {   
        $data=array();
        if(!$this->method){
            echo json_encode(array("result"=>FALSE, "message"=>"method not allowed"));   
            exit; 
        }
        if($this->method!="POST"){   
            echo json_encode(array("result"=>FALSE, "message"=>"method not allowed"));   
            exit; 
        }
        $data = json_decode(file_get_contents("php://input"));        
        $data = (array)$data; 
        if(!$data){
            echo json_encode(array("result"=>FALSE, "message"=>"Something error"));    exit;
        }
        
        // validation
        if($data["s_item_key"]=="" || $data["c_dep_m_key"]=="" || $data["created_by"]=="" || !is_int($data["s_item_key"]) || !is_int($data["s_item_key"] )){
            echo json_encode(array("result"=>FALSE, "message"=>"Please insert required data."));    exit;
        }
        $res = $this->AssetModel->saveAsset($data);
        if($res['result']==1){
            echo json_encode(array('asset_id'=>$res['asset_id'],'result'=>TRUE, 'message'=>'Successfully Saved !!'));
            exit;
        }else{            
            echo json_encode(array('asset_id'=>NULL,'result'=>FALSE, 'message'=>'Error Occoured'));
            exit;
        }        
    }
}