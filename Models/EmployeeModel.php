<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Models;

use Base\Model\ApiModel;

class EmployeeModel extends ApiModel
{
	public function __construct($db=null, $username=null)
    {
        parent::__construct($db, $username);
    }

    /*
    * employee deleted user api
    * http://localhost/ap/purchase/api/employee/deleted
    * @param( db, employee_id, is_deleted )
    */
	public function getDeletedEmployee($employee_id)
	{
		$query = " SELECT * FROM c_emp_m WHERE emp_cd = $1 AND is_deleted!='1' ";
        $result = $this->getOne($query,array($employee_id));
        if(sizeof($result)>0){
            return array('result'=>true, 'empinfo' => array('taisyouku_date' => $result['taisyouku_date'], 'emp_key' => $result['emp_key'], 'emp_name' => $result['emp_name']));
        }
        return array('result' => false, 'message' => 'Employee neither exist nor deleted');
    }

}
