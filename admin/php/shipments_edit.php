<?php
switch($action){
	case "trackpop":
		if($action=="trackpop"){
			$vars["javascript"] = 'window.open("/?action=trackingfinder&id='.$_GET[id].'","tracking_finder","top=0,left=0,width=600,height=600,scrollbars=yes,menu=no,resizable=yes");';
		}
	case "bolpop":
		if($action=="bolpop"){
			$vars["javascript"] = 'window.open("/?action=shipments_bol&id='.$_GET[id].'","shipments_bol","top=0,left=0,width=850,height=700,scrollbars=yes,menu=no,resizable=yes");';
		}
	case "update":
       // echo $_POST["cost_verified"]; die();
        $commodity_rows = array();
		if($_POST) {
			if(!$_POST["cost_verified"]){
				$_POST["cost_verified"]="0";
				//$_POST["cost_verified_at"]="0000-00-00 00:00:00";
				$_POST["cost_verified_by"]="0";
			}else if($_POST["cost_verified"]=="1"){
				$sql = "SELECT shipment.*,DATE_FORMAT(shipment.shipped_pickup_date, '%H:%i:%s') AS shipped_pickup_time, DATE_FORMAT(shipment.shipped_delivery_date, '%H:%i:%s') AS shipped_delivery_time,DATE_FORMAT(shipment.shipped_pickup_date, '%Y-%m-%d') AS shipped_pickup_newdate,DATE_FORMAT(shipment.shipped_delivery_date, '%Y-%m-%d') AS shipped_delivery_newdate, customer.company AS customer,al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname FROM shipment LEFT JOIN customer ON shipment.customer_id=customer.id LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id WHERE shipment.id = '$_GET[id]'";
				$shipment_data = $db->query($sql);

                if(($shipment_data[0]["cost_verified_at"] == "0000-00-00 00:00:00") || ($shipment_data[0]["cost_verified_at"] == "1000-01-01 00:00:00")){
                    $_POST["cost_verified_at"]= date("Y-m-d H:i:s");
                    //echo "ngu";
                }else{
                    $_POST["cost_verified_at"]= $shipment_data[0]["cost_verified_at"];
                    //echo $shipment_data[0]["cost_verified_at"]; die("testst");
                }

				$_POST["cost_verified"]="1";
				//$_POST["cost_verified_at"]=($shipment_data[0]["cost_verified_at"] != "0000-00-00 00:00:00")? $shipment_data[0]["cost_verified_at"] : date("Y-m-d H:i:s");
				$_POST["cost_verified_by"]=$_LOGIN["id"];

			}

			if(!$_POST["expedited"]){
				$_POST["expedited"]="0";
			} else {
				$_POST["expedited"]="1";
			}

			if(!$_POST["roll_off"]){
				$_POST["roll_off"]="0";
			} else {
				$_POST["roll_off"]="1";
			}

			if(!$_POST["o_to"] || !$_POST["o_address1"] || !$_POST["o_city"] || !$_POST["o_state"] || !$_POST["o_zip"] || !$_POST["d_to"] || !$_POST["d_address1"] || !$_POST["d_city"] || !$_POST["d_state"] || !$_POST["d_zip"]){
				$error_messages[] = "<span style='color:red'>Please complete all required fields</span>";
			}
			$com_key=0;
            $product_id = 0;
            $container_cost =0;
            $container_price =0;

			foreach($_POST as $n=>$v){
                //echo "<pre>";echo $n; echo "</pre>";
               // echo "<pre>";echo "v =".$v; echo "</pre>";
				if($n=="product_id"){
					foreach($v as $nn=>$vv){
                       /* echo "<pre>";echo "nn="; print_r($nn); echo "</pre>";
                        echo "<pre>";echo "vv =";print_r($vv); echo "</pre>";
                        echo "<pre>";echo "com_key =";print_r($com_key); echo "</pre>";
                        echo "<pre>";echo "description =";print_r($_POST["description"][$nn]); echo "</pre>";
                        echo "<pre>";echo "weight =";print_r($_POST["weight"][$nn]); echo "</pre>";
                        echo "<pre>";echo "product_id =";print_r($_POST["product_id"][$nn]); echo "</pre>";
                        echo "<pre>";echo "container_cost =";print_r($_POST["container_cost"][$nn]); echo "</pre>";
                        echo "<pre>";echo "container_price =";print_r($_POST["container_price"][$nn]); echo "</pre>";
						*/
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
                        //echo "<pre>";echo "commodity_rows="; print_r($commodity_rows); echo "</pre>";
					}
				}


			}
			if(!$error_messages){
				$sql_prefix = "UPDATE shipment SET";
				$sql_suffix = ", updated_admin_login_id = '$_LOGIN[id]', date_updated = NOW() WHERE id = '$_GET[id]'";

                if($_POST["shipped_delivery_date"] =='') unset($_POST["shipped_delivery_date"]);
                if($container_cost ==0)$_POST["container_cost"]=0;
                if($container_price ==0)  $_POST["container_price"]=0;
                foreach($_POST AS $k=>$v) {
                    //echo "<pre>";echo $k; echo "</pre>";
					if($k == "shipped_pickup_date" && $_POST[shipped_pickup_date]) {
						$v = "$_POST[shipped_pickup_date] $_POST[shipped_pickup_time]";
						
					}

					if($k == "shipped_delivery_date" && ($_POST["shipped_delivery_date"] !='')) {
                         //echo "date = ";
                          //echo $_POST["shipped_delivery_date"]; die();
                        $v = "$_POST[shipped_delivery_date] $_POST[shipped_delivery_time]";
					}
					if($k != "transit_days" && $k != "carrier_id" && $k != "service_id" &&
                        $k != "formmode" && $k != "pieces" && $k != "packaging_list_id" &&
                        $k != "dim_d" && $k != "dim_w" && $k != "dim_h" && $k != "class_list_id" &&
                        $k != "nmfc" && $k != "description" && $k != "weight" &&
                        $k != "accessorial" && $k != "shipped_pickup_time" &&
                        $k != "shipped_delivery_time" &&
                        $k !="product_id" && $k !="container_cost" && $k !="container_price" &&
                        $k !="req_pickup_date" &&
                        $k !="req_delivery_date") {

                        if((!strstr($k,"cost") || strstr($k,"cost_verified")) && !strstr($k,"rate")) {
                            $inner_sql .= " $k = '$v',";
                        }
					}elseif($k =="product_id"){
                        if($product_id > 0){
                            $inner_sql .= " $k = '$product_id',";
                        }
                    }elseif($k =="container_cost"){
                        if($container_cost >=0){
                            $inner_sql .= " $k = '$container_cost',";
                        }
                    }elseif($k =="container_price"){
                        if($container_price >=0){
                            $inner_sql .= " $k = '$container_price',";
                        }
                    }elseif($k =="req_pickup_date" && $v !=""){
                        $inner_sql .= " $k = '$v',";
                    }
                    elseif($k =="req_delivery_date" && $v !=""){
                        $inner_sql .= " $k = '$v',";
                    }
				}

				$inner_sql = trim($inner_sql,",");

				$sql = "$sql_prefix $inner_sql $sql_suffix";
                //echo "<pre>";echo $sql;echo "</pre>"; die();
				$success = $db->query($sql);

				$new_id = $_GET["id"];

				$sql = "DELETE FROM shipment_commodity WHERE shipment_id = '$new_id'";
				$success = $db->query($sql);
				$sql = "DELETE FROM shipment_accessorial WHERE shipment_id = '$new_id'";
				$success = $db->query($sql);

				foreach($commodity_rows AS $value) {
					$sql_prefix = "INSERT INTO shipment_commodity SET shipment_id = '$new_id',";
					$inner_sql = "";
					foreach($value AS $k=>$v) {
						//$inner_sql .= " $k = '$v',";
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

				$allowed_accessorials=array(11,12);
				if(isset($_POST["accessorial"])) {
					foreach($_POST["accessorial"] AS $value) {
						$sql = "INSERT INTO shipment_accessorial SET
						shipment_id = '$new_id',
						accessorial_id = '$value'";
						$success = $db->query($sql);

						$allowed_accessorials[]=$value;
					}
				}

				foreach($_POST as $k=>$v) {
					if((strstr($k,"cost") || strstr($k,"rate"))  && !strstr($k,"cost_verified")) {
						$data_array = explode("__",$k);
						if(!$data_array[2]) {
							$rate_array[$data_array[1]][$data_array[0]] = $v;
						} else {
							$accessorial_array[$data_array[1]][$data_array[2]][$data_array[0]] = $v;
						}
					}
				}


				$shipped_data = $db->query("SELECT shipped_shipment_rate_id FROM shipment WHERE id='$_GET[id]'");
				if(!$shipped_data){die("Cannot Locate Shipment");}
				if($rate_array) {
					foreach($rate_array as $rv){
						$db->query("UPDATE shipment_rate SET carrier_id='".$_POST["carrier_id"]."',service_id='".$_POST["service_id"]."',transit_days='".$_POST["transit_days"]."',cost='".$rv["cost"]."',rate='".$rv["rate"]."' WHERE id='".$shipped_data[0]["shipped_shipment_rate_id"]."'");
					}
					$db->query("UPDATE shipment SET shipped_carrier_id='$_POST[carrier_id]',service_id='".$_POST["service_id"]."',shipped_shipment_rate_id='".$shipped_data[0]["shipped_shipment_rate_id"]."' WHERE id='$_GET[id]'");
				}

				$sql = "DELETE FROM shipment_rate_accessorial WHERE shipment_rate_id = '".$shipped_data[0]["shipped_shipment_rate_id"]."'";
				$success = $db->query($sql);
				if($accessorial_array) {
					$success = $db->query($sql);
					foreach($accessorial_array as $rate_id => $v) {
						foreach($v AS $accessorial_id => $accessorial_array) {
							if(in_array($accessorial_id,$allowed_accessorials)){
								if(empty($accessorial_array["calc_cost"]) || $accessorial_array["calc_cost"] == "0.00") $accessorial_array["calc_cost"] = $accessorial_array["cost"];
								if(empty($accessorial_array["calc_rate"]) || $accessorial_array["calc_rate"] == "0.00") $accessorial_array["calc_rate"] = $accessorial_array["rate"];
								$sql = "INSERT INTO shipment_rate_accessorial SET ";
								foreach($accessorial_array AS $name => $value) {
									$sql .= "$name = '$value',";
								}
								$sql .= " shipment_rate_id = '$rate_id', accessorial_id = '$accessorial_id'";
								$success = $db->query($sql);
							}
						}
					}
				}




				$sql = "SELECT invoice_id FROM shipment WHERE id = '$_GET[id]'";
				$invoice_id=$db->query($sql);
				$invoice_id=$invoice_id[0]["invoice_id"];

				$req_pickup_date=$_POST["req_pickup_date"];
				$req_delivery_date=$_POST["req_delivery_date"];
				$carrier_id=$_POST["carrier_id"];
				$transit_time=$_POST["transit_days"];
                    
				$sql = "SELECT * FROM shipment_rate WHERE id = '$rate_id'";
				$rate_info_array=$db->query($sql);
				$rate_info_array=$rate_info_array[0];
				$total_rate = $rate_info_array["rate"];
				$total_cost = $rate_info_array["cost"];

				$sql = "SELECT * FROM shipment_rate_accessorial WHERE shipment_rate_id = '$rate_id'";
				$accessorial_info_array=$db->query($sql);

				foreach($accessorial_info_array AS $accessorial_info) {
					$total_rate += $accessorial_info["rate"];
					$total_cost += $accessorial_info["cost"];
				}
				$total_profit = $total_rate-$total_cost;

				$real_pickup_date = $req_pickup_date;
				$real_transit_time = 0;
				for($i=0;$i<$transit_time;$i++) {
					$req_pickup_date_day = date("l",strtotime($req_pickup_date));
					$temp_date = explode("-",$req_pickup_date);
					$temp_date[2] ++;
					$req_pickup_date = implode("-",$temp_date);
					if($req_pickup_date_day != "Saturday" && $req_pickup_date_day != "Sunday") {
						$real_transit_time ++;
					}
				}
				/*
				$real_delivery_date = $req_delivery_date;
				$real_transit_time = 0;
				for($i=0;$i<$transit_time;$i++) {
					$req_delivery_date_day = date("l",strtotime($req_delivery_date));
					$temp_date = explode("-",$req_delivery_date);
					$temp_date[2] ++;
					$req_delivery_date = implode("-",$temp_date);
					if($req_delivery_date_day != "Saturday" && $req_delivery_date_day != "Sunday") {
						$real_transit_time ++;
					}
				}
				*/

                if(is_numeric($container_price)){
                    $total_rate += $container_price;
                }

                if(is_numeric($container_cost)){
                    $total_cost += $container_cost;
                }

                $total_profit = $total_rate - $total_cost;

				$sql = "UPDATE invoice SET
				cost = '$total_cost',
				total = '$total_rate',
				profit = '$total_profit',
				total_due = '$total_rate'
				WHERE id = '$invoice_id'";
				$success=$db->query($sql);
//print_r($sql);


				switch($_POST["formmode"]) {
					case "autoshipment":
					header("Location: index.php?action=shipments_edit&id=$new_id&saved&autoshipment");
					exit();
					break;
					case "save":
					header("Location: index.php?action=shipments_edit&id=$new_id&saved");
					exit();
					break;
					case "bol":
					header("Location: index.php?action=shipments_bolpop&id=$new_id&saved");
					break;
					case "tracking":
					header("Location: index.php?action=shipments_trackpop&id=$new_id&saved");
					break;
					case "copy":
					header("Location: index.php?action=quotes_edit&copy_id=$new_id");
					break;
					case "invoice":
					header("Location: index.php?action=shipments_invoice&id=$invoice_id&saved");
					break;
				}
			}
		}
	case "edit":
        $customerName ="";
        $customerContact ="";

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
				$error_messages[] = "Your shipment has been successfully saved";
			}
			if(isset($_GET["autoshipment"])) {
				$vars["javascript"] = "window.open('/?action=shipments_rates-auto&id=$_GET[id]','auto_shipment','top=0,left=0,width=200,height=200,scrollbars=yes,menu=no');";
			}
			$global_id = (isset($_GET["id"])) ? $_GET["id"] : $_GET["copy_id"];
			//MAKE SURE THE ID NUMBER IS A SHIPMENT, AND NOT A QUOTE.
			if($_GET["id"]) {
				$sql = "SELECT status FROM shipment WHERE id = '$_GET[id]'";
				$shipment_check=$db->query($sql);
				if($shipment_check && $shipment_check[0]["status"] == "quote") {
					header("Location: $_SERVER[PHP_SELF]?action=quotes_edit&id=$_GET[id]");
					exit;
				}
			}
			$sql = "SELECT shipment.*,
			shipment_rate.transit_days,
			DATE_FORMAT(shipment.shipped_pickup_date, '%H:%i:%s') AS shipped_pickup_time,
			DATE_FORMAT(shipment.shipped_delivery_date, '%H:%i:%s') AS shipped_delivery_time,
			DATE_FORMAT(shipment.shipped_pickup_date, '%Y-%m-%d') AS shipped_pickup_newdate,
			DATE_FORMAT(shipment.shipped_delivery_date, '%Y-%m-%d') AS shipped_delivery_newdate,
			customer.main_contact_name as custommerContact,
			customer.company AS customer,
			al.fname,al.lname, au.fname AS update_fname,
			au.lname AS update_lname
			FROM shipment
			LEFT JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id
			LEFT JOIN customer ON shipment.customer_id=customer.id
			LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id
			LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id
			WHERE shipment.id = '$global_id'";
			$shipment_data = $db->query($sql);

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
        $container_cost = 0;
        $container_price = 0;
		if($shipment_data) {
            //anh
            $container_cost = $shipment_data[0]["container_cost"];
            $container_price = $shipment_data[0]["container_price"];
            $customerName = $shipment_data[0]["customer"];
            $customerContact = $shipment_data[0]["custommerContact"];
			foreach($shipment_data[0] as $n=>$v){
				$vars[$n]=($_POST[$n])?$_POST[$n]:$v;
				$service_vars[$n]=($_POST[$n])?$_POST[$n]:$v;
			}

			// try to guess deliv date
			if($vars["req_delivery_date"]  == "0000-00-00"){
				$DAYS=$vars["transit_days"];
				if($DAYS){
					if(substr($vars["shipped_pickup_date"],0,4) != "0000"){
						list($y,$m,$d)=explode("-",$vars["shipped_pickup_date"]);
					}else{
						list($y,$m,$d)=explode("-",$vars["req_pickup_date"]);
					}

					switch($vars["service_id"]){
						case 1:
						case 2:
							break;
						case 3:
						case 4:
						case 5:
						case 6:
							break;
						case 7:
						case 8:
							// pickup date not counted
							$DAYS++;
							break;
					}
					// saturday
					$check = date("w",mktime(0,0,0,$m,$d+$DAYS,$y));
					if($check == "6"){
						$DAYS++;
					}
					// sunday
					$check = date("w",mktime(0,0,0,$m,$d+$DAYS,$y));
					if($check == "0"){
						$DAYS++;
					}
					$vars["req_delivery_date"] = date("Y-m-d",mktime(0,0,0,$m,$d+$DAYS,$y));
					$service_vars["req_delivery_date"]=$vars["req_delivery_date"];
				}
			}

			if(isset($_GET["copy_id"])) {
				$vars["id"] = "";
			}
			if(!isset($vars["javascript"])) $vars["javascript"] = "";
		} else {
			$vars = getFields("shipment");
			$service_vars["req_pickup_date"] = $vars["req_pickup_date"];
			$service_vars["req_delivery_date"] = $vars["req_delivery_date"];
			$service_vars["insurance"] = $vars["insurance"];
			$vars["javascript"] = "";
			$vars["shipments_more_info"] = "";
			$vars["shipments_more_info_2"] = "";
			$vars["shipments_more_info_3"] = "";
			if(!isset($vars["javascript"])) $vars["javascript"] = "";
			$accessorial_ids = $_POST["accessorial"];

			// default times
			$service_vars["req_pickup_time_start"]="12:00:00";
			$service_vars["req_pickup_time_end"]="17:00:00";
			$service_vars["req_delivery_time_start"]="12:00:00";
			$service_vars["req_delivery_time_end"]="17:00:00";
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
		if($service_vars["shipped_pickup_newdate"]=="0000-00-00"){$service_vars["shipped_pickup_newdate"]="";}
		if($service_vars["shipped_delivery_newdate"]=="0000-00-00"){$service_vars["shipped_delivery_newdate"]="";}
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

		//GETTING SALES AND FREIGHT REPS
		$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
		$admins = $db->query($sql);
		$service_vars["sales_reps"] = "";
		foreach($admins AS $v) {
			$s=($shipment_data[0]["sales_admin_login_id"] == $v["id"])?" selected":"";
			$service_vars["sales_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}
		$service_vars["freight_reps"] = "";
		foreach($admins AS $v) {
			$s=($shipment_data[0]["freight_admin_login_id"] == $v["id"])?" selected":"";
			$service_vars["freight_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}


		//GETTING ACCESSORIALS
		$sql = "SELECT * FROM accessorial WHERE type != 'insurance' && type != 'fuel'";
		$accessorial = $db->query($sql);
		$accessorial_vars["accessorial_list"] = "";
		foreach($accessorial AS $v) {
			if(isset($accessorial_ids)) {
				$s=(in_array($v["id"],$accessorial_ids))?" checked":"";
			} else {
				$s = "";
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

		// commodities
		$commodity_vars["commodity_rows"]="";
		$row = rf($htmlpath."shipments_edit_commodity_row.html");

		if($_POST) {
			$commodities = $commodity_rows;
		} else {
			$sql = "SELECT * FROM shipment_commodity WHERE shipment_id = '$global_id' ORDER BY id ASC";
			$commodities = $db->query($sql);
		}
        $containerCost =0;
		if(!$commodities){
			$commodities[0]["id"]="";
			$commodities[0]["shipment_id"]="";
			$commodities[0]["pieces"]="";
			$commodities[0]["weight"]="";
			$commodities[0]["packaging_list_id"]="";
			$commodities[0]["class_list_id"]="";
			$commodities[0]["dim_d"]="";
			$commodities[0]["dim_w"]="";
			$commodities[0]["dim_h"]="";
			$commodities[0]["nmfc"]="";
			$commodities[0]["description"]="";
            $commodities[0]["product_id"]="";
            $commodities[0]["container_cost"]="0";
            $commodities[0]["container_price"]="0";
		}
		if($commodities){
			foreach($commodities as $v){

                $containerCost +=  $v["container_cost"];
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
		}
        //print_r($containerCost); die();
		//GET THE ACCESSORIALS SELECTED FOR THIS SHIPMENT.
		$sql = "SELECT accessorial_id FROM shipment_accessorial WHERE shipment_id = '$_GET[id]'";
		$accessorial_list=$db->query($sql);
		if($accessorial_list) {
			$accessorial_ids = "";
			foreach($accessorial_list AS $v) {
				$accessorial_ids .= "'$v[accessorial_id]',";
			}
			$accessorial_ids = "$accessorial_ids'11','12'";
		} else {$accessorial_ids = "'11','12'";}


		$sql = "SELECT shipment_rate.*,carrier.company FROM shipment_rate LEFT JOIN carrier ON shipment_rate.carrier_id=carrier.id WHERE shipment_id = '$_GET[id]' AND shipment_rate.id='".$shipment_data[0]["shipped_shipment_rate_id"]."'";
		$shipment_rates_array=$db->query($sql);

		//GETTING SERVICE
		$sql = "SELECT * FROM service";
		$service = $db->query($sql);
       // $containerCost
        //$container_cost = $shipment_data[0]["container_cost"];
       // $container_price = $shipment_data[0]["container_price"];
		if($shipment_rates_array) {

			foreach($shipment_rates_array AS $v) {
				$sql = "SELECT accessorial.name,accessorial.id AS real_accessorial_id,shipment_rate_accessorial.* FROM accessorial LEFT JOIN shipment_rate_accessorial ON (shipment_rate_accessorial.accessorial_id=accessorial.id AND shipment_rate_accessorial.shipment_rate_id='$v[id]') WHERE accessorial.id IN($accessorial_ids) ORDER BY accessorial.id";
				$accessorial_list=$db->query($sql);
				if($accessorial_list) {
					$v["total_cost"]=$v["cost"];
					$v["total_rate"]=$v["rate"];
					$v["profit"] = number_format($v["rate"] - $v["cost"],2);
					$v["percent"] = ($v["cost"] != 0) ?	number_format($v["profit"] / $v["cost"] * 100,2) : number_format(0,2);
					foreach($accessorial_list AS $vv) {
						$vv["profit"] = number_format($vv["rate"] - $vv["cost"],2);
						$vv["percent"] = ($vv["cost"] != 0) ?	number_format($vv["profit"] / $vv["cost"] * 100,2) : number_format(0,2);
						$v["total_cost"] += $vv["cost"];
						$v["total_rate"] += $vv["rate"];
						$vv["id"] = $v["id"];
						$vv["accessorial_id"] = $v["accessorial_id"];
						$v["shipments_rates_list_table_row"] .= replace($vv, rf($htmlpath."shipments_rates_list_table_row_2.html"));
					}
				} else {$v["shipments_rates_list_table_row"] = "";};

                if(is_numeric($container_cost)){
                    $v["total_cost"] += $container_cost;
                }
                if(is_numeric($container_price)){
                    $v["total_rate"] +=$container_price;
                }
                $v["total_profit"] = number_format($v["total_rate"] - $v["total_cost"],2);
                $v["total_percent"] = ($v["total_cost"] != 0) ?	number_format($v["total_profit"] / $v["total_cost"] * 100,2) : number_format(0,2);

                $v["total_cost"]=number_format($v["total_cost"],2,".","");
                $v["total_rate"] = number_format($v["total_rate"],2,".","");
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				foreach($service AS $t) {
					$s = ($v["service_id"] == $t["id"]) ? " SELECTED" : "";
					$v["service_list"] .= "<option value=$t[id]$s>$t[name]</option>";
				}
			}
		}
		// times
		$req_pickup_time_start=$service_vars["req_pickup_time_start"];
		$req_pickup_time_end=$service_vars["req_pickup_time_end"];
		$req_delivery_time_start=$service_vars["req_delivery_time_start"];
		$req_delivery_time_end=$service_vars["req_delivery_time_end"];

        $times = create_time_range("6:00", "19:30");
        foreach ($times as $key => $time) {
            $times[$key] = date('H:i:s', $time);
        }

        foreach($times as $time_raw)
        {
            $time = sysTime($time_raw);

            // pickups
            $service_vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';

            // deliveries
            $service_vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
			$service_vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';
        }

		if(!empty($_GET["id"])) {
			$v["id"] = $_GET["id"];
			$vars["shipments_notes"] = replace($v, rf($htmlpath."shipments_notes.html"));
			$v["shipped_shipment_rate_id"]=$shipment_data[0]["shipped_shipment_rate_id"];
			$vars["shipments_rates"] = replace($v, rf($htmlpath."shipments_rates_2.html"));
		} else {
			$vars["shipments_notes"] = "";
			$vars["shipments_rates"] = "";
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
        $commodity_vars["total_container_cost"] = "$".$container_cost;
        $commodity_vars["total_container_price"] = "$".$container_price;
		$vars["shipments_services"] = replace($service_vars, rf($htmlpath."shipments_services.html"));
		$vars["shipments_commodities"] = replace($commodity_vars, rf($htmlpath."shipments_commodities.html"));
		//11/7/2023 anh
		//$vars["shipments_accessorials"] = replace($accessorial_vars, rf($htmlpath."shipments_accessorials.html"));

		//GET STATUS
		if($shipment_data[0]["status"] != "invoiced"){
			$status_temp=getEnum("shipment","status");
			if($status_temp){
				foreach($status_temp as $svalue){
					if($svalue != "quote" && $svalue != "invoiced"){
						$status_list[]=$svalue;
					}
				}
			}
		}else{
			$status_list[]="invoiced";
		}

		$vars["status_options"] = "";
		foreach($status_list AS $status_value) {
			$selected = ($status_value == $shipment_data[0]["status"]) ? " SELECTED" : "";
			
			$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
			
			
			$vars["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
		}

		if($_GET["error"] == 1) {
			$vars["error_message"] = "<span style='color:red'>Please add a carrier before creating a shipment.</span>";
		}

		// terminal stuff
		$shipped_o_carrier_terminal_id=0;
		if($shipment_data[0]["shipped_o_carrier_terminal_id"]){
			$carrier_terminal = $db->query("SELECT * FROM carrier_terminal WHERE carrier_id='".$shipment_data[0]["shipped_carrier_id"]."' AND id='".$shipment_data[0]["shipped_o_carrier_terminal_id"]."'");
			if($carrier_terminal){
				foreach($carrier_terminal[0] as $c_n=>$c_v){
					${"o_terminal_".$c_n}=$c_v;
				}
				$shipped_o_carrier_terminal_id=$carrier_terminal[0]["id"];
			}
		}
		$shipped_d_carrier_terminal_id=0;
		if($shipment_data[0]["shipped_d_carrier_terminal_id"]){
			$carrier_terminal = $db->query("SELECT * FROM carrier_terminal WHERE carrier_id='".$shipment_data[0]["shipped_carrier_id"]."' AND id='".$shipment_data[0]["shipped_d_carrier_terminal_id"]."'");
			if($carrier_terminal){
				foreach($carrier_terminal[0] as $c_n=>$c_v){
					${"d_terminal_".$c_n}=$c_v;
				}
				$shipped_d_carrier_terminal_id=$carrier_terminal[0]["id"];
			}
		}
		$vars["shipped_o_carrier_terminal_id"]=$shipped_o_carrier_terminal_id;
		$vars["shipped_d_carrier_terminal_id"]=$shipped_d_carrier_terminal_id;

		$vars["o_terminal_name"]=$o_terminal_name;
		$vars["o_terminal_code"]=($o_terminal_code)?"($o_terminal_code)":"";
		$vars["o_terminal_address1"]=$o_terminal_address1;
		$vars["o_terminal_address2"]=($o_terminal_address2)?"<br>".$o_terminal_address2:"";
		$vars["o_terminal_city"]=$o_terminal_city;
		$vars["o_terminal_state"]=$o_terminal_state;
		$vars["o_terminal_zip"]=$o_terminal_zip;
		$vars["o_terminal_contact"]=$o_terminal_contact;
		$vars["o_terminal_email"]=$o_terminal_email;
		$vars["o_terminal_phone"]=($o_terminal_phone)?"Ph: ".$o_terminal_phone:"";
		$vars["o_terminal_fax"]=($o_terminal_fax)?"Fx: ".$o_terminal_fax:"";

		$vars["d_terminal_name"]=$d_terminal_name;
		$vars["d_terminal_code"]=($d_terminal_code)?"($d_terminal_code)":"";
		$vars["d_terminal_address1"]=$d_terminal_address1;
		$vars["d_terminal_address2"]=($d_terminal_address2)?"<br>".$d_terminal_address2:"";
		$vars["d_terminal_city"]=$d_terminal_city;
		$vars["d_terminal_state"]=$d_terminal_state;
		$vars["d_terminal_zip"]=$d_terminal_zip;
		$vars["d_terminal_contact"]=$d_terminal_contact;
		$vars["d_terminal_email"]=$d_terminal_email;
		$vars["d_terminal_phone"]=($d_terminal_phone)?"Ph: ".$d_terminal_phone:"";
		$vars["d_terminal_fax"]=($d_terminal_fax)?"Fx: ".$d_terminal_fax:"";

		$vars["verified_by"]=$vars["cost_verified"] == "1" ? sysDateTime($vars["cost_verified_at"]) : "";
		$vars["cost_verified"]=$vars["cost_verified"] == "1" ? "checked" : "";
		$vars["roll_off"]=$vars["roll_off"] == "1" ? "checked" : "";
		$vars["expedited"]=$vars["expedited"] == "1" ? "checked" : "";
        //echo "<pre>";print_r($vars);echo "</pre>"; die();
        //$vars["company_name_default"] = $customerName;
        //$vars["contact_name_default"] = $customerContact;
        if($vars["d_to"]=="" && $vars["d_contact_name"] !=""){
            $vars["d_to"]=$vars["d_contact_name"];
        }
		$html["BODY"]=replace($vars,rf($htmlpath."shipments_edit.html"));
		break;
}


?>
