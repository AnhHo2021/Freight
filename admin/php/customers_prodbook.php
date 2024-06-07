<?php

$customers = $db->query("SELECT * FROM customer WHERE 1=1 $SALES_REP_FILTER ORDER BY company, main_contact_name DESC");
if(!$customers){
	header("Location: ./?action=customers");
	die();
}
$vars["customer_list"] = "";


foreach ($customers as $c)
{
  $vars["customer_list"] .= "<option value=\"" . $c["id"] . "\"" . ($c["id"] == $customer_id ? " selected" : "") . ">" . stripslashes($c["company"]) . " - " . stripslashes($c["main_contact_name"]) . "</option>";
}

$_customer_id = $customer_id;

if ($customer_id)
{
   
  $vars["customer_id"] = $customer_id;
  
  if($mode=="remove"){
    $sel_permission = $db->query("SELECT id FROM customer_commodity WHERE id = '$id' AND customer_id='$_customer_id'");
    if($sel_permission){
      $db->query("DELETE FROM customer_commodity WHERE id = '$id' AND customer_id='$_customer_id'");
      $db->query("DELETE FROM customer_commodity_items WHERE customer_commodity_id = '$id'");
      $msg = "The record has been successfully deleted.";
      header("Location: ./?action=customers_prodbook&msg=$msg&customer_id=$customer_id");
      die();
    }else{
      die();
    }
  }
  if($_POST){
    if($id=="NEW"){
      $sql = "INSERT INTO customer_commodity SET customer_id='$_customer_id', name='$name'";
      $db->query($sql);
      $id = $db->lastid();
    }else{
      $sql = "UPDATE customer_commodity SET name='$name' WHERE id = '$id' AND customer_id='$_customer_id'";
      $db->query($sql);
    }
    
    $com_key=0;
    foreach($_POST as $n=>$v){
      if($n=="pieces"){
        foreach($v as $nn=>$vv){
          $commodity_rows[$com_key]["pieces"]=$vv;
          $commodity_rows[$com_key]["packaging_list_id"]=$_POST["packaging_list_id"][$nn];
          $commodity_rows[$com_key]["dim_d"]=$_POST["dim_d"][$nn];
          $commodity_rows[$com_key]["dim_w"]=$_POST["dim_w"][$nn];
          $commodity_rows[$com_key]["dim_h"]=$_POST["dim_h"][$nn]; 
          $commodity_rows[$com_key]["class_list_id"]=$_POST["class_list_id"][$nn]; 
          $commodity_rows[$com_key]["nmfc"]=$_POST["nmfc"][$nn]; 
          $commodity_rows[$com_key]["description"]=$_POST["description"][$nn]; 
          $commodity_rows[$com_key]["weight"]=$_POST["weight"][$nn];
          $com_key++;
        }
      }
    }
  
    $sql = "DELETE FROM customer_commodity_items WHERE customer_commodity_id = '$id'";
    $success = $db->query($sql);	
    if($commodity_rows){
      foreach($commodity_rows AS $value) {
        $sql_prefix = "INSERT INTO customer_commodity_items SET customer_commodity_id = '$id',";
        $inner_sql = "";
        foreach($value AS $k=>$v) {
          $inner_sql .= " $k = '$v',";
        }
        $inner_sql = trim($inner_sql,",");
        $sql = "$sql_prefix $inner_sql";
        $success = $db->query($sql);
      }
    }
    $msg="The product book has been successfully saved.";
    
    header("Location: ./?action=customers_prodbook&customer_id=$customer_id");
    die();
  }

  if($id){
    $vars["id"]=$id;
    $vars["commodity_rows"]="";
    $row = rf($htmlpath."customers_prodbook_editrow.html");
  
    $vars["name"] = "";
  
    if($id != "NEW"){
      $sql = "SELECT customer_commodity_items.*, customer_commodity.name FROM customer_commodity_items INNER JOIN customer_commodity ON customer_commodity.id=customer_commodity_items.customer_commodity_id WHERE customer_commodity.id = '$id' AND customer_commodity.customer_id = '$_customer_id'";
      $commodities = $db->query($sql);
      $vars["name"] = ($commodities)?$commodities[0]["name"]:"";
    }
    if(!$commodities){
      $commodities[0]["id"]="";
      $commodities[0]["shipment_id"]="";
      $commodities[0]["pieces"]="";
      $commodities[0]["weight"]="";
      $commodities[0]["dim_weight"]="";
      $commodities[0]["packaging_list_id"]="";
      $commodities[0]["class_list_id"]="";
      $commodities[0]["dim_d"]="";
      $commodities[0]["dim_w"]="";
      $commodities[0]["dim_h"]="";
      $commodities[0]["nmfc"]="";
      $commodities[0]["description"]="";
    }
    
    // packaging
    $sql = "SELECT * FROM packaging_list WHERE removed != 0";
    $packaging = $db->query($sql);
    foreach($packaging AS $v) {
      $s = ($vars["service_id"] == $v["id"]) ? " SELECTED" : "";
      $vars["packaging_list"] .= "<option value=$v[id]>$v[packaging]</option>";
    }
  
    // classes
    $sql = "SELECT * FROM class_list";
    $class = $db->query($sql);
    foreach($class AS $v) {
      $vars["class_list"] .= "<option value=$v[id]>$v[class]</option>";
    }
    
    if($commodities){
      foreach($commodities as $v){$index++;
        $v["index"]=$index;
        
        $v["packaging_list"]="";
        if($packaging){
          foreach($packaging AS $vv) {
            $s=($v["packaging_list_id"]==$vv["id"])?" selected":"";
            $v["packaging_list"] .= "<option value=$vv[id]".$s.">$vv[packaging]</option>";
            if($v["packaging_list_id"]==$vv["id"]){
              $v["packaging_list_show"]=$vv["packaging"];
            }
          }
        }
  
        $v["class_list"]="";
        if($class){
          foreach($class AS $vv) {
            $s=($v["class_list_id"]==$vv["id"])?" selected":"";
            $v["class_list"] .= "<option value=$vv[id]".$s.">$vv[class]</option>";
            if($v["class_list_id"]==$vv["id"]){
              $v["class_list_show"]=$vv["class"];
            }
          }
        }
        
        $v["dim_weight"]="0";
        if($v["dim_d"] && $v["dim_w"] && $v["dim_h"]){
          $volweight = $v["pieces"] * ($v["dim_d"] * $v["dim_w"] * $v["dim_h"]);
          $v["dim_weight"]=($volweight)?ceil($volweight/194):"0";
        }
  
        $vars["commodity_rows"].=replace($v,$row);
      }
    }
  
    $html["BODY"] = replace($vars, rf($htmlpath."customers_prodbook_edit.html"));
  }else{
    $sql = "SELECT customer_commodity.* FROM customer_commodity WHERE customer_commodity.customer_id = '$_customer_id' ORDER BY name";
    $prod_array = $db->query($sql);
    if($prod_array) {
      foreach($prod_array AS $v) {
        $v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
        $vars["customers_prodbook_list_row"] .= replace($v, rf($htmlpath."customers_prodbook_row.html"));
      }
    } else {
      $vars["customers_prodbook_list_row"] = "<tr><td colspan='2' nowrap><b>No Results</b></td></tr>";
    }
    $customers_prodbook_list = replace($vars, rf($htmlpath."customers_prodbook.html"));
    $html["BODY"]=replace($vars, $customers_prodbook_list);
  }
}
else
{
   
  $customers_prodbook_list = replace($vars, rf($htmlpath."customers_prodbook_nocust.html"));
  $html["BODY"]=replace($vars, $customers_prodbook_list);
}
?>