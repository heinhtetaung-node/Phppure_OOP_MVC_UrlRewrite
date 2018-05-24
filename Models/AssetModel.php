<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Models;

use Base\Model\ApiModel;
class AssetModel extends ApiModel{
    //put your code here
    public function __construct($db=null, $username=null)
    {
        parent::__construct($db, $username);
    }     
    public function saveAsset($data){
        $now = date("Y-m-d H:i:s");
        $asset_item=array(
            's_item_key' => $data['s_item_key'],
            'asset_number' => $this->getAssetsNo($data['c_dep_m_key'], 1),
            'c_dep_m_key'=> $data['c_dep_m_key'],
            'created_at'=> $now,
            'updated_at' => $now,
            'created_by' =>  $data['created_by'],
            'updated_by'=> $data['updated_by'],
            'dlv_date' => ($data['dlv_date']=="")? null : $data['dlv_date'],
            'status' => 1 // always 1 because 1 is asset
        );
        $res = $this->insert('asset_items', $asset_item);
        if($res == 1)
        {
            $row = $this->getLastId("asset_items", "id");
            $id = $row["id"];  
            $arr=array(
                'asset_id'=> $id,
                'result'=> $res
            ); 
        }
        return $arr;
    }

    //資産番号採番
    public function getAssetsNo($dep_key,$j) {
        $init_assets_no = 0;
        $query = "select asset_number from asset_items 
                  where c_dep_m_key in (select dep_key from c_dep_v
                                        where area_key = (select area_key from c_dep_v where dep_key=$1))
          order by id desc limit 1";
        $results = $this->getOne($query, array($dep_key));
        if($results){
            $assets_no = (int)$results['asset_number']+$j;
        }else{
            $assets_no = $init_assets_no + $j;
        }
        return $assets_no;
    }
}