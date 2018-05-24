<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
namespace Models;

use Base\Model\ApiModel;

class InvoiceModel extends ApiModel{
    //put your code here
    public function __construct($db=null, $username=null)
    {
        parent::__construct($db, $username);
    }
    public function getinvoicenumber(){
        $sql = "select invoice_number from inv_invoice order by invoice_number desc limit 1";
        $row=$this->getOne($sql, array());
        $invoice_number = $row["invoice_number"];
        $invoice_number = explode("-", $invoice_number);
        $invoice_number = $invoice_number[2]; 
        $invoice_number++;

        $len = strlen($invoice_number."");
        while($len!=4){
            $invoice_number = "0".$invoice_number;
            $len = strlen($invoice_number);
        }
        return $invoice_number;
    }

    public function saveDirectInvoice($data){
        $inv_id = array();
        foreach ($data as $d) {
            $d = (array)$d;
            $invoice = (array)$d["invoice"];
            $invoice_items = (array)$d["invoice_items"];
            $customer = (array)$d["customer"];
            $countinvoice = $this->getinvoicenumber();
           
            $checked_approved = $customer["checked_approved"]+0;

            if ($checked_approved=="2") {
               $invoice["approval_status"] = 2;
            }else{
                $invoice["approval_status"] = 0;
            }

            $invoice["invoice_number"] = date("Y-m")."-".$countinvoice;
            $invoice["billing_month"] = date("Ym"); // !important need to ask don't know how to set.
            $invoice["invoice_type"] = 0; 
            $invoice["sale_person"] = $invoice["sale_person"]; // !important test
            $invoice["created_date_timestamp"] = date("Y-m-d H:i:s");
            $invoice["edited_date_timestamp"] = date("Y-m-d H:i:s");
            //change to integer
            $invoice["grand_total"] = $invoice["grand_total"]+0;
            $invoice["total"] = $invoice["total"]+0;
            $invoice["tax"] = $invoice["tax"]+0;
            $invoice["discount"] = $invoice["discount"]+0;
            $invoice["department_id"] = $invoice["department_id"];

            $res = $this->insert('inv_invoice', $invoice);
        
            if($res == 1){
                $row = $this->getLastId("inv_invoice", "id"); 
                $invoice_id = $row["id"]; 
                array_push($inv_id, $invoice_id);
                $items = array();
                foreach($invoice_items as $item){
                    $item = (array)$item;
                    $item["invoice_id"] = $invoice_id+0;
                    $item["item_order"] = $item["item_order"]+0;
                    $item["quantity"] = $item["quantity"]+0;
                    $item["unit_price"] = $item["unit_price"]+0;
                    $item["created_date_timestamp"] = date("Y-m-d H:i:s");
                    $item["edited_date_timestamp"] = date("Y-m-d H:i:s");
                    array_push($items, $item);                
                }
                if(sizeof($items)>0){
                    $res = $this->db_multi_insert('inv_direct_invoice_items', $items);
                    if($res!=1){
                        $this->rollbackInvoice($inv_id);
                        return array('result'=>FALSE, 'response'=>$inv_id, 'errorInvoice'=>$d);
                    }
                }        
            }else{
                $this->rollbackInvoice($inv_id);                        
                return array('result'=>FALSE, 'response'=>$inv_id, 'errorInvoice'=>$d);
            }
        }
        return array('result'=>TRUE, 'response'=>$inv_id);
    }

    public function rollbackInvoice($inv_id){
        foreach ($inv_id as $invoice_id) {
            $this->delete("inv_invoice",'id',$invoice_id);
            $query = "DELETE FROM inv_direct_invoice_items WHERE invoice_id = \$1; ";
            $this->execute($query, array($invoice_id));
        }
    }

}