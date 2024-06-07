<?php

switch($action){
  case "defaultsprocess":
    $db->query("DELETE FROM customer_default WHERE customer_id = '" . $customer_id ."'");
    $db->query("INSERT INTO customer_default SET customer_id = '" . $customer_id . "', o_addressbook_id = '$o_addressbook_id', d_addressbook_id = '$d_addressbook_id', commodity_id = '$commodity_id', service_type = '$service_type', req_pickup_time_start = '$req_pickup_time_start', req_pickup_time_end = '$req_pickup_time_end', req_delivery_time_start = '$req_delivery_time_start', req_delivery_time_end = '$req_delivery_time_end', insurance = '$insurance', liftgate = '$liftgate', residential = '$residential', inside = '$inside', tradeshow = '$tradeshow'");
    
	$vars["errors"]="Quote Defaults Saved";

	default:
		$vars["POPUP_DATA"]="";
		$vars["id"]=($_POST[id])?$_POST[id]:"0";
		$vars["do_ship"]=($SHIPIT)?"1":"0";
		$vars["quote_info_display"]="none";
		$vars["quote_noitems_display"]="none";
		$vars["product_book"]="";
    $vars["show_table"] = "";
    
		// post for setting vars
		if($_POST){
			foreach($_POST as $n=>$v){
				${$n}=stripslashes(str_replace('"','&quot;',$v));
			}
		}
		else
		{
		  // pull shit from db
		  $defaults = $db->query("SELECT * FROM customer_default WHERE customer_id = '" . $customer_id . "'");
		  $defaults = $defaults[0];
		  
		  if ($defaults)
		  {
			foreach ($defaults as $k => $v)
			  ${$k} = stripslashes($v);
		  }
		}
    
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
		
		// errors show
		$vars["error_show"]=($vars["errors"])?"block":"none";
    
    if ($customer_id)
    {
      // customer show
      $vars["origin_addressbook"]="";
      $vars["destination_addressbook"]="";
      
      $customer_addressbook = $db->query("SELECT * FROM customer_addressbook WHERE customer_id='".$customer_id."' ORDER BY `to`");
      if($customer_addressbook){
          foreach($customer_addressbook as $v){
            $key = str_replace(array('"'),array('&quot;'),$v["to"]."~|~".$v["address1"]."~|~".$v["address2"]."~|~".$v["city"]."~|~".$v["state"]."~|~".$v["zip"]."~|~".$v["contact_name"]."~|~".$v["contact_phone"]."~|~".$v["contact_fax"]."~|~".$v["contact_email"]);
  
            if($v["type"]=="origin" || $v["type"]=="both"){
              $vars["origin_addressbook"] .= '<option value="'.$v["id"].'"' . ($o_addressbook_id == $v["id"] || $o_addressbook == $v["id"] ? " selected" : "") . '>'.$v["to"].' - '.$v["address1"]." ".$v["address2"].' - '.$v["city"] .', '.$v["state"].' '.$v["zip"].'</option>';
            }
            if($v["type"]=="destination" || $v["type"]=="both"){
              $vars["destination_addressbook"] .= '<option value="'.$v["id"].'"' . ($d_addressbook_id == $v["id"] || $d_addressbook == $v["id"] ? " selected" : "") . '>'.$v["to"].' - '.$v["address1"]." ".$v["address2"].' - '.$v["city"] .', '.$v["state"].' '.$v["zip"].'</option>';
            }
          }
      }
  
  
      // product_book
      $product_books = $db->query("SELECT * FROM customer_commodity WHERE customer_id='".$customer_id."' ORDER BY name");
      if($product_books){
        foreach($product_books as $v){
          $product_book.='<option value="'.$v["id"].'"' . ($commodity_id == $v["id"] ? " selected" : "") . '>'.$v["name"].'</option>';
        }
        $vars["product_book"]='<center><b>Use Item From Product Book</b> <select name="commodity_id"><option value="0"></option>'.$product_book.'</select></center><br>';
      }
  
      // times
      if(!$req_pickup_time_start){$req_pickup_time_start="13:00:00";}
      if(!$req_pickup_time_end){$req_pickup_time_end="16:30:00";}
      if(!$req_delivery_time_start){$req_delivery_time_start="08:00:00";}
      if(!$req_delivery_time_end){$req_delivery_time_end="16:00:00";}
      for($x=6;$x<=19;$x++){
        // pickups
        $time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":00"));
        $time=sysTime($x. ":00");
        $vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_pickup_time_start){
          $vars["req_pickup_time_start_field"]=$time_raw;
          $vars["req_pickup_time_start_show"]=$time;
        }
        $vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_pickup_time_end){
          $vars["req_pickup_time_end_field"]=$time_raw;
          $vars["req_pickup_time_end_show"]=$time;
        }
        
        $time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":30"));
        $time=sysTime($x.":30");
        $vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_pickup_time_start){
          $vars["req_pickup_time_start_field"]=$time_raw;
          $vars["req_pickup_time_start_show"]=$time;
        }
        $vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_pickup_time_end){
          $vars["req_pickup_time_end_field"]=$time_raw;
          $vars["req_pickup_time_end_show"]=$time;
        }
  
        // deliveries
        $time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":00"));
        $time=sysTime($x. ":00");
        $vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_delivery_time_start){
          $vars["req_delivery_time_start_field"]=$time_raw;
          $vars["req_delivery_time_start_show"]=$time;
        }
        $vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';			
        if($time_raw==$req_delivery_time_end){
          $vars["req_delivery_time_end_field"]=$time_raw;
          $vars["req_delivery_time_end_show"]=$time;
        }
        
        $time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":30"));
        $time=sysTime($x.":30");
        $vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
        
        if($time_raw==$req_delivery_time_start){
          $vars["req_delivery_time_start_field"]=$time_raw;
          $vars["req_delivery_time_start_show"]=$time;
        }
        $vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';
        if($time_raw==$req_delivery_time_end){
          $vars["req_delivery_time_end_field"]=$time_raw;
          $vars["req_delivery_time_end_show"]=$time;
        }
      }
  
      // service options
      if(!$service){$service="ground";}
      if($service_type){$service=$service_type;}
      $vars["service_air"]=($service=="air")?" checked":"";
      $vars["service_ground"]=($service=="ground")?" checked":"";
      $vars["service"]=$service;
      $vars["service_show"]=($service=="air")?"Air":"Ground";
  
      // special mssg		
      $vars["special_pickup_mssg"]="none";
      $vars["special_delivery_mssg"]="none";
      $diff = (strtotime(date("Y-m-d")." ".$req_pickup_time_end)-strtotime(date("Y-m-d")." ".$req_pickup_time_start))/3600;
      if($diff <= 2){
        $vars["special_pickup_mssg"]="block";
      }
      list($check_h,$check_m,$check_s)=explode(":",$req_delivery_time_end);
      if($check_h < 13){
        $vars["special_delivery_mssg"]="block";
      }
        
      // additional services
      $vars["insurance"]=$insurance;
      $vars["liftgate_pickup"]=($liftgate=="pickup")?" selected":"";
      $vars["liftgate_delivery"]=($liftgate=="delivery")?" selected":"";
      $vars["liftgate_pickup_delivery"]=($liftgate=="pickup_delivery")?" selected":"";
      $vars["liftgate"]=$liftgate;
      switch($liftgate){
        case "pickup":$vars["liftgate_show"]="Pickup";break;
        case "delivery":$vars["liftgate_show"]="Delivery";break;
        case "pickup_delivery":$vars["liftgate_show"]="Pickup & Delivery";break;
        default:$vars["liftgate_show"]="N/A";break;
      }
      $vars["residential_pickup"]=($residential=="pickup")?" selected":"";
      $vars["residential_delivery"]=($residential=="delivery")?" selected":"";
      $vars["residential_pickup_delivery"]=($residential=="pickup_delivery")?" selected":"";
      $vars["residential"]=$residential;
      switch($residential){
        case "pickup":$vars["residential_show"]="Pickup";break;
        case "delivery":$vars["residential_show"]="Delivery";break;
        case "pickup_delivery":$vars["residential_show"]="Pickup & Delivery";break;
        default:$vars["residential_show"]="N/A";break;
      }
      $vars["inside_pickup"]=($inside=="pickup")?" selected":"";
      $vars["inside_delivery"]=($inside=="delivery")?" selected":"";
      $vars["inside_pickup_delivery"]=($inside=="pickup_delivery")?" selected":"";
      $vars["inside"]=$inside;
      switch($inside){
        case "pickup":$vars["inside_show"]="Pickup";break;
        case "delivery":$vars["inside_show"]="Delivery";break;
        case "pickup_delivery":$vars["inside_show"]="Pickup & Delivery";break;
        default:$vars["inside_show"]="N/A";break;
      }
      $vars["tradeshow_pickup"]=($tradeshow=="pickup")?" selected":"";
      $vars["tradeshow_delivery"]=($tradeshow=="delivery")?" selected":"";
      $vars["tradeshow_pickup_delivery"]=($tradeshow=="pickup_delivery")?" selected":"";
      $vars["tradeshow"]=$tradeshow;
      switch($tradeshow){
        case "pickup":$vars["tradeshow_show"]="Pickup";break;
        case "delivery":$vars["tradeshow_show"]="Delivery";break;
        case "pickup_delivery":$vars["tradeshow_show"]="Pickup & Delivery";break;
        default:$vars["tradeshow_show"]="N/A";break;
      }
    }
    else
      $vars["show_table"] = "none";

		$vars["default_table"]=replace($vars,rf($htmlpath."customers_defaults_table.html"));
		$html["BODY"]=replace($vars,rf($htmlpath."customers_defaults.html"));
}


?>