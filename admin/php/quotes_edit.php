<?php

function check_accessorial($name, &$vars)
{
  $arr = explode(" ", strtolower($name));
  $real_name = $arr[0];
  $type = $arr[1];
  
  if ($real_name == "trade")
    $real_name .= "show";
  
  if (!$vars[$real_name])
  {
    return(0);
  }
  
  if ($vars[$real_name] == "pickup_delivery")
  {
    return(1);
  }
  
  if ($vars[$real_name] == $type)
  {
    return(1);
  }
  
  return(0);
}

$vars["use_defaults"] = 0;

switch($action){
  case "getprodbook":
    echo "var _s = document.getElementById('prodbook_id');";
    echo "_s.options.length = 0;";
    echo "_s.options[0] = new Option('', '');";
    
    if ($customer_id)
    {
      $books = $db->query("SELECT * FROM customer_commodity WHERE customer_id = $customer_id");
      $default = $db->query("SELECT commodity_id FROM customer_default WHERE customer_id = $customer_id");
      
      if ($books)
      {
        $i = 1;
        $tmp = Array();
        
        foreach ($books as $b)
        {
          $tmp[$b["id"]] = $i;
          echo "_s.options[" . $i++ . "] = new Option('" . $b["name"] . "', '" . $b["id"] . "');";
        }
        
        if ($selectdefault && $default[0]["commodity_id"])
        {
          $default = $default[0];
          echo "_s.options[" . $tmp[$default["commodity_id"]] . "].selected = true;";
          echo "setCommodity(_s.value);";
        }
      }
    }
    
    exit;
    break;
    
	case "viewwindow":
		$vars["id"]=$id;
		echo replace($vars,rf($htmlpath."quotes_viewwindow.html"));	
		die();
	break;
	case "view":
		$vars["javascript"] = "window.open('./?action=quotes_viewwindow&id=$_GET[id]','quotes_viewwindow','top=0,left=0,width=850,height=700,scrollbars=no,menu=no,resizable=yes');";
	case "update";
		if($_POST) {
			if(!$_POST["o_city"] || !$_POST["o_state"] || !$_POST["o_zip"] || !$_POST["d_city"] || !$_POST["d_state"] || !$_POST["d_zip"]){
				$error_messages[] = "<span style='color:red'>Please complete all required fields</span>";
			}
			$com_key=0;
            $product_id = 0;
            $container_cost =0;
            $container_price =0;
			foreach($_POST as $n=>$v){
				if($n=="product_id"){
					foreach($v as $nn=>$vv){
						/*$commodity_rows[$com_key]["pieces"]=$vv;
						$commodity_rows[$com_key]["packaging_list_id"]=$_POST["packaging_list_id"][$nn];
						$commodity_rows[$com_key]["dim_d"]=$_POST["dim_d"][$nn];
						$commodity_rows[$com_key]["dim_w"]=$_POST["dim_w"][$nn];
						$commodity_rows[$com_key]["dim_h"]=$_POST["dim_h"][$nn]; 
						$commodity_rows[$com_key]["class_list_id"]=$_POST["class_list_id"][$nn]; 
						$commodity_rows[$com_key]["nmfc"]=$_POST["nmfc"][$nn];
                        */
						$commodity_rows[$com_key]["description"]=$_POST["description"][$nn]; 
						$commodity_rows[$com_key]["weight"]=$_POST["weight"][$nn];
                        $product_id = $commodity_rows[$com_key]["product_id"]=$_POST["product_id"][$nn];
                        $container_cost += $commodity_rows[$com_key]["container_cost"]=$_POST["container_cost"][$nn];
                        $container_price += $commodity_rows[$com_key]["container_price"]=$_POST["container_price"][$nn];
                        $com_key++;
					}
				}
			}
			if(!$error_messages){
				//save address in book - verify it doesn't already exist
				if($origin_save==1){
					$addr_orig_hash = md5($o_to.$o_address1.$o_address2.$o_city.$o_state.$o_zip);
					$sel_orig_match = $db->query("SELECT id FROM customer_addressbook WHERE MD5(CONCAT(`to`,address1,address2,city,state,zip))='".$addr_orig_hash."' AND customer_id='$customer_id'");
					$sel_orig_match_main = $db->query("SELECT id FROM customer WHERE MD5(CONCAT(company,mail_address1,mail_address2,mail_city,mail_state,mail_zip))='".$addr_orig_hash."' AND id='$customer_id'");
					if(!$sel_orig_match && !$sel_orig_match_main){
						$db->query("INSERT INTO customer_addressbook SET customer_id='$customer_id', type='origin', `to`='$o_to', address1='$o_address1', address2='$o_address2', city='$o_city', state='$o_state', zip='$o_zip', contact_name='$o_contact_name', contact_phone='$o_contact_phone', contact_fax='$o_contact_fax', contact_email='$o_contact_email'");
					}
				}
				if($dest_save==1){
					$addr_dest_hash = md5($d_to.$d_address1.$d_address2.$d_city.$d_state.$d_zip);
					$sel_dest_match = $db->query("SELECT id FROM customer_addressbook WHERE MD5(CONCAT(`to`,address1,address2,city,state,zip))='".$addr_dest_hash."' AND customer_id='$customer_id'");
					$sel_dest_match_main = $db->query("SELECT id FROM customer WHERE MD5(CONCAT(company,mail_address1,mail_address2,mail_city,mail_state,mail_zip))='".$addr_dest_hash."' AND id='$customer_id'");
					if(!$sel_dest_match && !$sel_dest_match_main){
						$db->query("INSERT INTO customer_addressbook SET customer_id='$customer_id', type='destination', `to`='$d_to', address1='$d_address1', address2='$d_address2', city='$d_city', state='$d_state', zip='$d_zip', contact_name='$d_contact_name', contact_phone='$d_contact_phone', contact_fax='$d_contact_fax', contact_email='$d_contact_email'");
					}
				}

				if(!empty($_GET["id"])) {
					$sql_prefix = "UPDATE shipment SET ";
					$sql_suffix = ", updated_admin_login_id = '$_LOGIN[id]', date_updated = NOW() WHERE id = '$_GET[id]'";
				} else {
					$sql_prefix = "INSERT INTO shipment SET ";
					$sql_suffix = ", created_admin_login_id = '$_LOGIN[id]', date_created = NOW()";
				}

                if($_POST["insurance"] == '') $_POST["insurance"] ='0.00';
                if($_POST["d_contact_name"] != '' && $_POST["d_to"] =='') $_POST["d_to"] = $_POST["d_contact_name"];
                if($container_cost==0) $_POST["container_cost"] =0;
                if($container_price==0) $_POST["container_price"] =0;
                foreach($_POST AS $k=>$v) {
					if($k != "formmode" && $k != "pieces" && $k != "packaging_list_id" && $k != "dim_d" &&
                        $k != "dim_w" && $k != "dim_h" && $k != "class_list_id" && $k != "nmfc" &&
                        $k != "description" && $k != "weight" && $k != "accessorial" && $k != "origin_save" &&
                        $k != "dest_save" && $k !="product_id" && $k !="container_cost" && $k !="container_price") {
						$inner_sql .= " $k = '$v',";
					}elseif($k =="product_id"){
                        if($product_id > 0){
                            $inner_sql .= " $k = '$product_id',";
                        }
                    }elseif($k =="container_cost"){
                        if($container_cost >= 0){
                            $inner_sql .= " $k = '$container_cost',";
                        }
                    }elseif($k =="container_price"){
                        if($container_price >= 0){
                            $inner_sql .= " $k = '$container_price',";
                        }
                    }
				}
				$inner_sql = trim($inner_sql,",");
				$sql = "$sql_prefix $inner_sql $sql_suffix";
               // echo $sql;
                //echo "</br>";
                //die($sql);
                ///test
				 $success = $db->query($sql);
				if(empty($_GET["id"])) {
//					$new_id = mysql_insert_id();
					$new_id = mysqli_insert_id($db->conn);
				} else {
					$new_id = $_GET["id"];
				}
//				print_r($sql);
//				exit;
				$sql = "DELETE FROM shipment_commodity WHERE shipment_id = '$new_id'";
				$success = $db->query($sql);
				$sql = "DELETE FROM shipment_accessorial WHERE shipment_id = '$new_id'";
				$success = $db->query($sql);
                foreach($commodity_rows AS $value) {
                    $sql_prefix = "INSERT INTO shipment_commodity SET shipment_id = '$new_id',";
                    $inner_sql = "";
                    foreach($value AS $k=>$v) {
                        if($k == "product_id"){
                            if($v !=0 || $v !="") {
                                $inner_sql .= " $k = '$v',";
                            }
                        }elseif($k == "container_cost"){
                            if($v !=""){
                                $inner_sql .= " $k = '$v',";
                            }
                        }elseif($k == "container_price"){
                            if($v !=""){
                                $inner_sql .= " $k = '$v',";
                            }
                        }elseif($k == "weight"){
                            if($v !="") {
                                $inner_sql .= " $k = '$v',";
                            }
                        }else{
                            $inner_sql .= " $k = '$v',";
                        }
                    }
                    $inner_sql = trim($inner_sql,",");
                    $sql = "$sql_prefix $inner_sql";
                    $success = $db->query($sql);
                }

               // die();
				if(isset($_POST["accessorial"])) {
					foreach($_POST["accessorial"] AS $value) {
						$sql = "INSERT INTO shipment_accessorial SET
						shipment_id = '$new_id',
						accessorial_id = '$value'";
						$success = $db->query($sql);
					}
				}
				switch($_POST["formmode"]) {
					case "autoquote":
					header("Location: index.php?action=quotes_edit&id=$new_id&saved&autoquote");
					exit();
					break;
					case "save":
					header("Location: index.php?action=quotes_edit&id=$new_id&saved");
					exit();
					break;
					case "view":
					header("Location: index.php?action=quotes_view&id=$new_id&saved");
					break;
					case "copy":
					header("Location: index.php?action=quotes_edit&copy_id=$new_id");
					break;
					case "shipment":
					header("Location: index.php?action=quotes_shipment&id=$new_id&saved");
					break;
				}
			}
		}
	case "edit":
        $default_data = Array();
        $ignore ="";
        $destinationaddress="";
        $customerName ="";
        $customerContact ="";
        if(!isset($_GET["id"])){
            $customer_id =1101;
            $_GET["customer_id"] = $customer_id;
            $ignore =1;

        }
        //echo "<pre>";echo"vvv=";echo $customer_id;echo "</pre>"; die();
		$AUTO_QUOTE="";
		if($SALES_REP_FILTER && $_GET["id"]){	
			$check = $db->query("SELECT shipment.id FROM shipment INNER JOIN customer ON shipment.customer_id=customer.id WHERE shipment.id='$_GET[id]' $SALES_REP_FILTER");
			if(!$check){
				header("Location: index.php?action=quotes_edit");
				die();
			}
		}
		if($SALES_REP_FILTER && $_GET["copy_id"]){	
			$check = $db->query("SELECT shipment.id FROM shipment INNER JOIN customer ON shipment.customer_id=customer.id WHERE shipment.id='$_GET[copy_id]' $SALES_REP_FILTER");
			if(!$check){
				header("Location: index.php?action=quotes_edit");
				die();
			}
		}


		if($_GET["id"] || $_GET["copy_id"]) {
			if(isset($_GET["saved"])) {
				$error_messages[] = "Your quote has been successfully saved";
			}
			if(isset($_GET["autoquote"])) {
				$AUTO_QUOTE = '<font color=red>Calculating Quote... please wait</font><iframe style="display:block;" id="autoquote_frame" name="autoquote_frame" src="./?action=quotes_rates-auto&id='.$_GET[id].'" frameBorder="0" scrolling="no" style="width:100px;height:1px;"></iframe>';				
			
			}
			$global_id = (isset($_GET["id"])) ? $_GET["id"] : $_GET["copy_id"];
			//MAKE SURE THE ID NUMBER IS A QUOTE, AND NOT A SHIPMENT.
			if($_GET["id"]) {
				$sql = "SELECT status FROM shipment WHERE id = '$_GET[id]'";
				$quote_check=$db->query($sql);
				if($quote_check && $quote_check[0]["status"] != "quote") {
					header("Location: $_SERVER[PHP_SELF]?action=shipments_edit&id=$_GET[id]");
					exit;
				}
			}
			$sql = "SELECT shipment.*,
			customer.main_contact_name as custommerContact,
			customer.company AS customer,
			al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname
			 FROM shipment
			 LEFT JOIN customer ON shipment.customer_id=customer.id
			 LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id
			 LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id
			 WHERE shipment.id = '$global_id'";
			$quote_data = $db->query($sql);

			if($global_id) {
				$sql = "SELECT accessorial_id FROM shipment_accessorial WHERE shipment_id = '$global_id'";
				$accessorial_data = $db->query($sql);
				if($_POST["accessorial"]) {
					$x=0;
					foreach($_POST["accessorial"] AS $v) {
						$accessorial_ids[$x] = $v;
						$x++;
					}
				} else {
					if($accessorial_data) {
						foreach($accessorial_data AS $v) {
							$accessorial_ids[] = $v["accessorial_id"];
						}
					}
				}
			}
		}
    else if ($customer_id)
    {
      //  echo "<pre>";echo $customer_id;echo "</pre>";
      $cdefaults = $db->query("SELECT * FROM customer_default WHERE customer_id = $customer_id LIMIT 1");
      $vars["use_defaults"] = 1;
      
      if ($cdefaults)
      {
        $cdefaults = $cdefaults[0];
        
        foreach ($cdefaults as $k => $v)
        {
          if ($k != "id")
            $default_data[$k] = stripslashes($v);
        }

          if($ignore !=1){
              $o_address = $db->query("SELECT * FROM customer_addressbook WHERE id = " . $cdefaults["o_addressbook_id"] . " LIMIT 1");

              if ($o_address)
              {
                  $o_address = $o_address[0];

                  foreach ($o_address as $k => $v)
                  {
                      if ($k != "id")
                          $default_data["o_" . $k] = stripslashes($v);
                  }
              }

              $d_address = $db->query("SELECT * FROM customer_addressbook WHERE id = " . $cdefaults["d_addressbook_id"] . " LIMIT 1");

              if ($d_address)
              {
                  $d_address = $d_address[0];

                  foreach ($d_address as $k => $v)
                  {
                      if ($k != "id")
                          $default_data["d_" . $k] = stripslashes($v);
                  }
              }
          }

      }

      //anh
        $customer_info = $db->query("SELECT company,main_contact_name FROM customer WHERE id = $customer_id LIMIT 1");
        if ($customer_info)
        {
            $customerName = $customer_info[0]["company"];
            $customerContact = $customer_info[0]["main_contact_name"];
        }
    }



        $container_cost = $quote_data[0]["container_cost"];
        $container_price = $quote_data[0]["container_price"];
       //echo "<pre>";print_r($quote_data);echo "</pre>"; die();
        /////////////
		if($quote_data) {
            //anh
            $customerName = $quote_data[0]["customer"];
            $customerContact = $quote_data[0]["custommerContact"];

			foreach($quote_data[0] as $n=>$v){
				$vars[$n]=($_POST[$n])?$_POST[$n]:$v;
				$service_vars[$n]=($_POST[$n])?$_POST[$n]:$v;
			}
			if(isset($_GET["copy_id"])) {
				$vars["id"] = "";
			}
			if(!isset($vars["javascript"])) $vars["javascript"] = "";
		} else if ($default_data) {
      foreach ($default_data as $k => $v)
      {
        $vars[$k] = $v;
        $service_vars[$k] = $v;
      }
      
      //$service_vars["service_type"] = $default_data["service_type"];
    } 
    
    if (!$quote_data) {
			$vars = array_merge(getFields("shipment"), $vars);
			$service_vars["req_pickup_date"] = $vars["req_pickup_date"];
			$service_vars["insurance"] = $vars["insurance"];
			$vars["javascript"] = "";
			$vars["quotes_more_info"] = "";
			$service_vars["special_instructions"] = "";
			$vars["quotes_more_info_2"] = "";
			$vars["quotes_more_info_3"] = "";
      $service_vars["req_pickup_date"] = date("Y-m-d");
			if(!isset($vars["javascript"])) $vars["javascript"] = "";
			$accessorial_ids = $_POST["accessorial"];

      if (!$default_data)
      {
        // default times
        $service_vars["req_pickup_time_start"]="08:00:00";
        $service_vars["req_pickup_time_end"]="16:30:00";
        $service_vars["req_delivery_time_start"]="09:00:00";
        $service_vars["req_delivery_time_end"]="18:00:00";
      }
			if($_GET["customer_id"]) {
				$sql = "SELECT company FROM customer WHERE id = '$_GET[customer_id]'";
				$customer_name = $db->query($sql);
				$vars["customer"] = $customer_name[0]["company"];
				$vars["customer_id"] = $_GET["customer_id"];
			} else {
				$vars["customer_id"] = "";
				$vars["customer"] = "";
			}
		}
		//if(!$service_vars["service_type"]){$service_vars["service_type"]="air";}
		$service_vars["service_type_air"]=($service_vars["service_type"]=="air")?" selected":"";
        if(!$service_vars["service_type"]){$service_vars["service_type"]="ground";}
		$service_vars["service_type_ground"]=($service_vars["service_type"]=="ground")?" selected":"";
		$vars["error_message"]=($error_messages)?implode("<br>",$error_messages)."<br><br>":"";

		if(!$error_messages){
			$vars["error_message"] = "";
		}
		//GETTING STATES FOR SELECT STATEMENTS.
		$sql = "SELECT * FROM state ORDER BY state";
		$states = $db->query($sql);
		$vars["origin_states"] = "";
		foreach($states AS $v) {
			$s=($vars["o_state"] == $v["code"])?" selected":"";
			$vars["o_states"] .= "<option value='$v[code]'$s>$v[code]</option>\n";
		}
		foreach($states AS $v) {
			$s=($vars["d_state"] == $v["code"])?" selected":"";
			$vars["d_states"] .= "<option value='$v[code]'$s>$v[code]</option>\n";
		}
    
		//GETTING ACCESSORIALS
		$sql = "SELECT * FROM accessorial WHERE type != 'insurance' && type != 'fuel'";
		$accessorial = $db->query($sql);
		$accessorial_vars["accessorial_list"] = "";
		foreach($accessorial AS $v) {
			if(isset($accessorial_ids)) {
				$s=(in_array($v["id"],$accessorial_ids))?" checked":"";
			} else {
				$s = check_accessorial($v["name"], $vars) ? "checked" : "";
			}
			$accessorial_vars["accessorial_list"] .= "<tr><td><label for='accessorial_$v[id]'>$v[name]</label></td><td><input type='checkbox' name='accessorial[]' id='accessorial_$v[id]' value='$v[id]'$s></td></tr>\n";
		}

		//GETTING PACKAGING
		$sql = "SELECT * FROM packaging_list WHERE removed != 0";
		$packaging = $db->query($sql);
		$commodity_vars["packaging_list"] = "";
		foreach($packaging AS $v) {
			$s = ($vars["service_id"] == $v["id"]) ? " SELECTED" : "";
			$commodity_vars["packaging_list"] .= "<option value=$v[id]>$v[packaging]</option>";
		}

		//GETTING CLASSES
		$sql = "SELECT * FROM class_list";
		$class = $db->query($sql);
		foreach($class AS $v) {
			$commodity_vars["class_list"] .= "<option value=$v[id]>$v[class]</option>";
		}

        //GETTING product
        $sql = "SELECT * FROM product";
        $product_list = $db->query($sql);
        foreach($product_list AS $v) {
            $commodity_vars["product_list"] .= "<option value=$v[id]>$v[product_name]</option>";
        }
        //echo "<pre>";print_r($commodity_vars["product_list"]);echo "</pre>------------";
		// commodities
		$commodity_vars["commodity_rows"]="";
		$row = rf($htmlpath."quotes_edit_commodity_row.html");
		if($_POST) {
			$commodities = $commodity_rows;
		} else {
            if($ignore !=1){
                $sql = "SELECT * FROM shipment_commodity WHERE shipment_id = '$global_id' ORDER BY id ASC";
                $commodities = $db->query($sql);
            }

		}
		if(!$commodities){
			$commodities[0]["id"]="";
			$commodities[0]["shipment_id"]="";
			$commodities[0]["pieces"]="";

			$commodities[0]["packaging_list_id"]="";
			$commodities[0]["class_list_id"]="";
			$commodities[0]["dim_d"]="";
			$commodities[0]["dim_w"]="";
			$commodities[0]["dim_h"]="";
			$commodities[0]["nmfc"]="";
            $commodities[0]["dim_weight"] = "0";
            $commodities[0]["description"]="";
            $commodities[0]["weight"]="";
            $commodities[0]["product_id"]="0";
            $commodities[0]["container_cost"]="0";
            $commodities[0]["container_price"]="0";
		}
		if($commodities){
			foreach($commodities as $v){
                $index++;
				$v["index"]=$index;
				
				$v["packaging_list"]="";
				if($packaging){
					foreach($packaging AS $vv) {
						$s=($v["packaging_list_id"]==$vv["id"])?" selected":"";
						$v["packaging_list"] .= "<option value=$vv[id]".$s.">$vv[packaging]</option>";
					}
				}

				$v["class_list"]="";
				if($class){
					foreach($class AS $vv) {
						$s=($v["class_list_id"]==$vv["id"])?" selected":"";
						$v["class_list"] .= "<option value=$vv[id]".$s.">$vv[class]</option>";
					}
				}

                $v["product_list"]="";
                if($product_list){
                    foreach($product_list AS $vv) {
                        $s=($v["product_id"]==$vv["id"])?" selected":"";
                        $v["product_list"] .= "<option value=$vv[id]".$s.">$vv[product_name]</option>";
                    }
                }


				$commodity_vars["commodity_rows"].=replace($v,$row);

			}

           // echo "<pre>";print_r($v["product_list"]);echo "</pre>";
           // die();
		}
    
    $vars["prod_book_opts"] = "<option></option>";
    
    // product book
    if ($customer_id)
    {
      $books = $db->query("SELECT * FROM customer_commodity WHERE customer_id = $customer_id");
      
      if ($books)
      {
        foreach ($books as $b)
          $vars["prod_book_opts"] .= "<option value=\"" . $b["id"] . "\">" . stripslashes($b["name"]) . "</option>";
      }
    }

        $commodity_vars["total_container_cost"] = "$".$container_cost;
        $commodity_vars["total_container_price"] = "$".$container_price;
		// times
		$req_pickup_time_start=$service_vars["req_pickup_time_start"];
		$req_pickup_time_end=$service_vars["req_pickup_time_end"];
		$req_delivery_time_start=$service_vars["req_delivery_time_start"];
		$req_delivery_time_end=$service_vars["req_delivery_time_end"];
		for($x=6;$x<=19;$x++){
			// pickups
			$time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x . ":00"));
			$time=sysTime($x . ":00");
			$service_vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';
			
			$time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":30"));
			$time=sysTime($x.":30");
			$service_vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';

			// deliveries
			$time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x . ":00"));
			$time=sysTime($x . ":00");
			$service_vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';
			
			$time_raw=date("H:i:00",strtotime(date("Y-m-d")." ".$x.":30"));
			$time=sysTime($x.":30");
			$service_vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';
		}

		if(!empty($_GET["id"])) {
			$v["id"] = $_GET["id"];
			$vars["quotes_notes"] = replace($v, rf($htmlpath."quotes_notes.html"));

			$v["calc_rate_id"]=$calc_rate_id;
			$v["AUTO_QUOTE"]=$AUTO_QUOTE;
			$vars["quotes_rates"] = replace($v, rf($htmlpath."quotes_rates.html"));
		} else {
			$vars["quotes_notes"] = "";
			$vars["quotes_rates"] = "";
		}
		if($vars["fname"]) {
			$vars["date_created"] = formatDate($vars["date_created"]);
			$vars["create_string"] = "Created on $vars[date_created] by $vars[fname] $vars[lname]";
			if($vars["update_fname"]) {
				$vars["date_updated"] = formatDate($vars["date_updated"]);
				$vars["update_string"] = "Updated on $vars[date_updated] by $vars[update_fname] $vars[update_lname]";
			} else {
				$vars["update_string"] = "";
			}
		} else {
			$vars["update_string"] = "";
			$vars["create_string"] = "";
		}

		$vars["quotes_services"] = replace($service_vars, rf($htmlpath."quotes_services.html"));
		$vars["quotes_commodities"] = replace($commodity_vars, rf($htmlpath."quotes_commodities.html"));
		$vars["quotes_accessorials"] = replace($accessorial_vars, rf($htmlpath."quotes_accessorials.html"));
       // $vars["company_name_default"] = $customerName;
        //$vars["contact_name_default"] = $customerContact;

		if($_GET["error"] == 1) {
			$vars["error_message"] = "<span style='color:red'>Please add a carrier before creating a shipment.</span>";
		}

        if($vars["d_to"]=="" && $vars["d_contact_name"] !=""){
            $vars["d_to"]=$vars["d_contact_name"];
        }
        //echo "<pre>";print_r($vars);echo "</pre>"; die();
		$html["BODY"]=replace($vars,rf($htmlpath."quotes_edit.html"));

		break;
}

		
?>
