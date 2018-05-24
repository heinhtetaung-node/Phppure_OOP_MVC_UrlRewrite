<?php
require "vendor/autoload.php";

use Controllers\LoginController;
use Controllers\CuponController;
use Controllers\EmployeeController;
use Controllers\InvoiceController;
use Controllers\AssetController;

$LoginController = new LoginController();
$CuponController = new CuponController();
$EmployeeController = new EmployeeController();
$InvoiceController = new InvoiceController();
$AssetController=new AssetController();


$header = $_GET['action'];
$PGMODE = $_GET['PGMODE'];
switch($header){

	case "login":
		$LoginController->checkLogin();
		break;

	case "bookletInputCheckFrm":
		$CuponController->Bookletinputcheckfrm();
		break;

	case "bookletInputCheckFrm2":
		$CuponController->Bookletinputcheckfrm();
		break;

	case "Couponbookdistr":
		$CuponController->Couponbookdistr();
		break;

	case "Couponbookrecovery":
		$CuponController->Couponbookrecovery();
		break;

	case "IdEntryfrm":
		$CuponController->IdEntryfrm();
		break;

	case "employee":
		if($PGMODE=="deleted"){
			$EmployeeController->getDeletedEmployee();
		}
		echo json_encode(array('result' => '404'));
		break;

	case "invoice":
		if($PGMODE=="saveinvoice"){
			$InvoiceController->saveinvoicedata();
		}
		echo json_encode(array('result' => '404'));
		break;

	case "asset":
		if($PGMODE=="saveasset"){
			$AssetController->saveasset();
		}
		echo json_encode(array('result' => '404'));
		break;

	default:
		echo json_encode(array('result' => '404'));
		break;
}
