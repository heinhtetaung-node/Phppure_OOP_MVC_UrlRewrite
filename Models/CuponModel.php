<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Models;

use Base\Model\ApiModel;

class CuponModel extends ApiModel
{
	public function __construct($db=null, $username=null)
    {
        parent::__construct($db, $username);
    }

    /*
    * coupon distr save api
    * http://localhost/ap/purchase/api/Bookletinputcheckfrm
    * @param( db, coupon_key, emp_key, dist_date, created_at, updated_at )
    */
	public function Bookletinputcheckfrm($data)
	{
		$coupon = array(
			'coupon_key'	=> $data['coupon_key'],
			'emp_key'		=> $data['emp_key'],
			// 'dist_date' 	=> $data['dist_date'],
			'created_at' 	=> date("Y-m-d H:i:s"),
			// 'updated_at' 	=> date('Y-m-d'),
	        // 'is_recovered' 	=> 0,
		);
		//print_r($data);

  		$res = $this->insert('coup_distr', $coupon);
        if($res!=1){
            return "Failed to save coup_distr. May be emp_key not exist or coupon_key not exist.";
        }       

        return $res;
    }

    /*
    * copuon recovery save api
    * http://localhost/ap/purchase/api/Bookletinputcheckfrm2
    * @param( db, coupon_key, emp_key, dist_date, created_at, updated_at )
    */
    public function Bookletinputcheckfrm2($data)
    {
		$recoveryModel = array(
			'coupon_key'	    => $data['coupon_key'],
			'emp_key'			=> $data['emp_key'],			
			'created_at'		=> date("Y-m-d H:i:s"),			
		);


  		$res = $this->insert('coup_recovery', $recoveryModel);
        if($res!=1){
            return "Failed to save coup_recovery. May be emp_key not exist or coupon_key not exist.";
        }

        /*
        $this->execute(
            "
            UPDATE coup_distr
              SET is_recovered='1', updated_at=\$1
            WHERE coupon_key=\$2 AND emp_key=\$3 AND id=\$4
            "
        , array(date('Y-m-d'), $data['coupon_key'], $data['emp_key'], $data['distributed_id']));
        if($res!=1){
            return "Failed to update coupon distribution records.";
        }
        */
        return $res;
	}

    /*
    * Check for coupon distribution validity
    * Logic : coupon must not exist as "dist" status in history view
    * http://<server>/ap/purchase/api/Couponbookdistri
    * @param( db, book_code, is_recovered(optional) )
    */
    public function Couponbookdistr($data) //Couponbookdistr
    {
    	$param=array();
        // $is_recovered=isset($data['is_recovered'])?$data['is_recovered']:0;

    	if($data['book_code'] !='')
    	{
    		array_push($param, $data['book_code']);
        }

        /* step 1. check for coupon validity */
        $query = 
        "SELECT b.item_code, b.area_key, i.item_name, b.book_key, b.book_code, 
            c.coupon_key, c.coupon_code
        FROM coup_book AS b INNER JOIN 
            coup_detail AS c ON b.book_key = c.book_key INNER JOIN
            s_item_m AS i ON b.item_code = i.item_cd
        where book_code = $1;";

        $coupons = $this->getAll($query, $param);
        if (sizeof($coupons) == 0)
        {
            return array('result'=> FALSE, 'message'=>'invalid');
        }

        //step 2. check for distribution validity       
        $query = 
        "SELECT h.*, book_code 
        FROM coupon_histories AS h INNER JOIN 
            coup_book AS b ON h.book_key = b.book_key
        where book_code = $1 AND status = 'dist';";

        $result = $this->getAll($query,$param);
        if (sizeof($result) > 0)
        {
            //$count greater than 0 already exists
            return array('result'=> FALSE, 'message'=>'already');
        }

        return array('result' => TRUE, 'data' => $coupons);        
    }

    /*
    * Check for coupon recovery validity
    * Logic : coupon must exist as "dist" status in history view
    * http://<server>/ap/purchase/api/Couponbookrecovery
    * @param ( db, book_code, emp_key )
    */
 	public function Couponbookrecovery($data)
    {
    	$param=array();
    	if($data['book_code'] !='')
    	{
    		array_push($param, $data['book_code']);
        }

        /*  step 1. check for coupon validity */
        $query = 
        "SELECT b.item_code, b.area_key, i.item_name, b.book_key, b.book_code, 
            c.coupon_key, c.coupon_code, dis.id
        FROM coup_book AS b INNER JOIN 
            coup_detail AS c ON b.book_key = c.book_key INNER JOIN
            s_item_m AS i ON b.item_code = i.item_cd LEFT JOIN 
            coup_distr AS dis ON c.coupon_key = dis.coupon_key
        where book_code = $1;";

        $coupons = $this->getAll($query, $param);
        if (sizeof($coupons) == 0)
        {
            return array('result'=> FALSE, 'message'=>'invalid');
        }

        /*  step 2. check for distribution validity */
        $query = 
        "SELECT h.*, book_code 
        FROM coupon_histories AS h INNER JOIN 
            coup_book AS b ON h.book_key = b.book_key
        where book_code = \$1 AND status = 'dist';";

        $result = $this->getAll($query,$param);
        if(sizeof($result) <= 0){
            return array('result'=>false, 'message' => 'already');
        }

        return array('result' => TRUE, 'data' => $coupons);
    }

    /*
    * IdEntryfrm api
    * http://localhost/ap/purchase/api/IdEntryfrm
    * @param ( db, emp_cd, is_deleted )
    */
    public function IdEntryfrm($data)
    {
    	$param=array();
     	$emp_cd = $data['emp_cd'];
    	$is_deleted=isset($data['is_deleted'])?$data['is_deleted']:0;

        $tdate = date('Ymd');

    	if($data['emp_cd'] !='')
    	{
    		array_push($param, $emp_cd);
    		array_push($param, $is_deleted);
            array_push($param, $tdate);
    	}

    	$query = "
                  SELECT  *
    		        FROM c_emp_m
    		      WHERE emp_cd = $1
                    AND is_deleted =$2
                    AND taisyoku_date > $3 OR taisyoku_date=''
                ";
		$result = $this->getAll($query,$param);
		return $result;
    }

    private function deleteRecovery($coupon)
    {
        $query = "DELETE FROM coup_recovery WHERE coupon_key = \$1; ";
        $this->execute($query, array($coupon['coupon_key']));
    }
}
