<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Models;

use Base\Model\ApiModel;

class LoginModel extends ApiModel{
    //put your code here

    public function __construct()
    {
        parent::__construct();
    }

    public function checkLogin($CID,$UID,$PW){
    	$EID = $UID;
    	$conAmos = DbConnect(DB_DATABASE_NAME);

    	$user_info = "";
    	if(!$conAmos){
			$Error = "1";
			$Message = "会社ＩＤ認証のためのデータベース接続に失敗しました。";
			echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            exit;
		}else{
			
			$DirName = "";
			$strSQL_compid =  "SELECT db_name,coalesce(directory, '') as directory FROM c_compid_m ";

			$strSQL_compid .= "WHERE comp_id = \$1 and op_status = '1' and op_begin_date <= \$2 and op_end_date > \$2";
			$strPRM_compid = Array($CID,date("Ymd"));
			$rtn_compid = PgQuery(PROGRAM_NAME,$UID,$conAmos,$strSQL_compid,$strPRM_compid);
			if(!$rtn_compid){
				$Error = "CID";
				$Message = "会社ＩＤ認証に失敗しました。";
				echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            	exit;
			} else {
				$num_compid = @pg_num_rows($rtn_compid);
				
				if($num_compid > 0){
					$DbName 		= pg_fetch_result($rtn_compid,0,0);

					$DirName = pg_fetch_result($rtn_compid,0,1);

					$con = DbConnect($DbName );
					//接続に失敗したときエラー
					if(!$con){
						$Error = "ERR";
						$Message = "ユーザ認証のためのデータベース接続に失敗しました。";
						echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            			exit;
					}else{
						//会社情報マスタの存在チェック
						$comp_exists = 0;
						$strSQL_com = "select count(comp_key) from c_comp_m";
						$rtn_com = PgQuery(PROGRAM_NAME,$UID,$con,$strSQL_com,"");

						if($rtn_com){
							$comp_exists = pg_fetch_result($rtn_com,0,0);
						}
						if($UID != "admin" && $comp_exists == 0){
							$Error = "COM";
							$Message = "会社情報が設定されてい無い為ログインできません。";
							echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            				exit;
						} else {

							if($UID == "admin" || $DbName == DB_DATABASE_NAME){
								$strSQL_emp =  "SELECT a.dep_key,a.password FROM c_emp_m a ";
								$strSQL_emp .= "WHERE a.user_id = \$1 AND a.is_deleted != '1'" ;
							} else {
								$strSQL_emp =  "SELECT a.dep_key,a.password, a.* FROM c_emp_m a ";
								$strSQL_emp .= "LEFT JOIN c_dep_m b ON a.dep_key = b.dep_key ";
								$strSQL_emp .= "LEFT JOIN c_area_m c ON b.area_key = c.area_key ";
								$strSQL_emp .= "LEFT JOIN c_block_m d ON c.block_key = d.block_key ";
								$strSQL_emp .= "WHERE a.user_id = \$1 AND a.is_deleted != '1'" ;
								$strSQL_emp .= "and b.is_deleted = '0' and c.is_deleted = '0' and d.is_deleted = '0'" ;
							}
							
							$strPRM_emp = Array($EID);
							$rtn_emp = PgQuery(PROGRAM_NAME,$UID,$con,$strSQL_emp,$strPRM_emp);
							$num_emp = @pg_num_rows($rtn_emp);
							$user_info = @pg_fetch_assoc($rtn_emp);
							
							if(!$rtn_emp){
								$Error = "UID";
								$Message = "ユーザ認証に失敗しました。";
								echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
                				exit;
							} else {
								if($num_emp > 0){
									
									$DepKey 		= pg_fetch_result($rtn_emp,0,0);
									$PassWord		= pg_fetch_result($rtn_emp,0,1);

									if($PassWord == EnCrypt($PW)){
										$param = Array("0");
									}else{
										$Error = "PWD";
										$Message = "ユーザ認証に失敗しました。";
										echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            							exit;
									}
									
								} else {
									$Error = "UID";
									$Message = "ユーザ認証に失敗しました。";
									echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));;
            						exit;
								}
							}
						}
					}
				} else {
					$Error = "CID";
					$Message = "会社ＩＤ認証に失敗しました。";
					echo json_encode(array('result'=>FALSE, 'message'=>$Error ." : ".$Message));
            		exit;
				}
			}
		}
		// if ( $Error == "" ) {
  //           if ( ! chkAccessSrcAddr::isOkSrcAddr() ) {
  //           	$Error = 'BAD_SRC_ADDR';
  //               $Message = sprintf( "許可されていない発信アドレス(%s)です。", $_SERVER['REMOTE_ADDR'] ); 
  //           }
  //       }
        
        $insPW = $PW;
		if($Error == ""){
			$insPW = Pass2Hid($PW);
		}
		
		array_push($param,$CID);
		array_push($param,$UID);
		array_push($param,$insPW);
		array_push($param,$_SERVER[REMOTE_ADDR]);
		
		$strSQL  = "insert into c_login_d (Login_timestamp,error_reason,comp_id,user_id,password,ip_address) ";
		$strSQL .= "values (CURRENT_TIMESTAMP,\$1,\$2,\$3,\$4,\$5)";
		$rtn = PgQuery(PROGRAM_NAME,$UID,$conAmos,$strSQL,$param);
		
		echo json_encode(array('result'=>TRUE, 'message'=>"Successfully Login!", 'user_info' => $user_info));
        exit;
    }
}