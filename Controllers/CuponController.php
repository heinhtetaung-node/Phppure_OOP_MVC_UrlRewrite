<?php

namespace Controllers;

use Base\Controller\ApiController;
use Models\CuponModel;

class CuponController extends ApiController
{
     private $CuponModel;

    public function __construct()
    {
        parent::__construct();
        $db = isset($_POST['db'])? $_POST['db'] : null;
        $username = isset($_POST['username'])? $_POST['username'] : null;
        $this->CuponModel = new CuponModel($db, $username);
    }

    public function Bookletinputcheckfrm()
    {
        $this->checkOnlyPost();

        //$_POST['coupons'] contains array of coupons to process

        if ($_POST['coupons'] != null)
        {
            if($_GET['action']=='bookletInputCheckFrm')
            {
                //coupon distribution
                foreach ($_POST['coupons'] as $coupon)
                {
                    $res = $this->CuponModel->Bookletinputcheckfrm($coupon);
                    if ($res != 1)
                    {
                        //error occurred
                        echo json_encode(array("result"=>FALSE, "message"=>$res));
                        return;
                    }
                }
                echo json_encode(array("result"=>TRUE, "message"=>"Insert Data Success!!"));
            }

            if($_GET['action']=='bookletInputCheckFrm2')
            {
                //coupon recovery
                foreach ($_POST['coupons'] as $coupon)
                {
                    $res = $this->CuponModel->Bookletinputcheckfrm2($coupon);
                    if ($res != 1)
                    {
                        //error occurred
                        echo json_encode(array("result"=>FALSE, "message"=>$res));
                        return;
                    }
                }
                echo json_encode(array("result"=>TRUE, "message"=>"Insert Data Success!!"));
            }
        }
        else
        {
            echo json_encode(array("result"=>FALSE, "message"=> "No data to process."));
        }

        /*
        if ($_POST['coupon_key'] !='' && $_POST['emp_key']!="" && $_POST['dist_date']!="" && $_POST['created_at']!="" && $_POST['updated_at']!='')
        {
           $length =strlen($_POST['coupon_key']);
            if ($length>6) {
                echo json_encode(array("result"=>FALSE, "message"=>"coupon_key length is greater than 6!")); exit;
            }
            if($_GET['action']=='bookletInputCheckFrm'){
                $res = $this->CuponModel->Bookletinputcheckfrm($_POST);
            }
            if($_GET['action']=='bookletInputCheckFrm2'){
                $res = $this->CuponModel->Bookletinputcheckfrm2($_POST);
            }
            if($res==1){
                echo json_encode(array("result"=>TRUE, "message"=>"Insert Data Success!!"));
            }else{
                echo json_encode(array("result"=>FALSE, "message"=>$res));
            }
            exit;
        }

        echo json_encode(array("result"=>FALSE,"message"=>"Something Wrong. May be data is required or duplicate")); exit;
        */
    }

    public function Couponbookdistr()
    {
        $this->checkOnlyPost();
        if ($_POST['book_code']!='')
        {
            $res =  $this->CuponModel->Couponbookdistr($_POST);
            if($res['result']!=1){
                echo json_encode($res); exit;
            }
            echo json_encode($res); exit;
        }
        echo json_encode(array("result"=>FALSE,"message"=>"Something Wrong. May be data is required!")); exit;
    }

    public function Couponbookrecovery()
    {
        $this->checkOnlyPost();
        if ($_POST['book_code']!='' && $_POST['emp_key'])
        {
            $res =  $this->CuponModel->Couponbookrecovery($_POST);
            if($res['result']!=1){
                echo json_encode($res); exit;
            }
            echo json_encode($res);  exit;
        }
        echo json_encode(array("result"=>FALSE,"message"=>"Something Wrong. May be data is required!")); exit;
    }

    public function IdEntryfrm()
    {
        $this->checkOnlyPost();
        if ($_POST['emp_cd']!='')
        {
            $res = $this->CuponModel->IdEntryfrm($_POST);
            if($res==1){
                echo json_encode(array("result"=>TRUE, "message"=>"Insert Data Success!!"));
            }else{
                echo json_encode(array("result"=>FALSE, "message"=>$res));
            }
        }
        echo json_encode(array("result"=>FALSE,"message"=>"Something Wrong. May be data is required!")); exit;
    }


}
