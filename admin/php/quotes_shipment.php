<?php
	$html["LOCATION"] = "<h1>QUOTES : CREATING SHIPMENT</h1>";
    //anh
    $sql = "SELECT container_cost,container_price FROM shipment WHERE id = '$_GET[id]'";
    $container_info=$db->query($sql);

	if(!empty($_POST)){
		// check tracking num
		$check_tracknum = $db->query("SELECT id FROM shipment WHERE id != '$_GET[id]' AND shipped_tracknum='$shipped_tracknum'");
		if($check_tracknum){die("Duplicate Tracking Number - Try Again.<br><a href=\"javascript:history.go(-1)\">< Back</a>");}

		$sql = "SELECT req_pickup_date FROM shipment WHERE id = '$_GET[id]'";
		$delivery_date=$db->query($sql);
		$req_pickup_date=$delivery_date[0]["req_pickup_date"];
		$sql = "SELECT carrier_id,service_id,transit_days FROM shipment_rate WHERE id = '$_POST[shipped_shipment_rate_id]'";
		$shipment_rate_array=$db->query($sql);
		$carrier_id=$shipment_rate_array[0]["carrier_id"];
		$service_id=$shipment_rate_array[0]["service_id"];
		$transit_time=$shipment_rate_array[0]["transit_days"];
		$sql = "SELECT * FROM shipment_rate WHERE id = '$_POST[shipped_shipment_rate_id]'";
		$rate_info_array=$db->query($sql);
		$rate_info_array=$rate_info_array[0];
		$total_rate = $rate_info_array["rate"];
		$total_cost = $rate_info_array["cost"];
		$sql = "SELECT * FROM shipment_rate_accessorial WHERE shipment_rate_id = '$_POST[shipped_shipment_rate_id]'";
		$accessorial_info_array=$db->query($sql);
		if($accessorial_info_array) {
			foreach($accessorial_info_array AS $accessorial_info) {
				$total_rate += $accessorial_info["rate"];
				$total_cost += $accessorial_info["cost"];
			}
		}
		$total_profit = $total_rate-$total_cost;
		$sql = "UPDATE shipment SET ";
		foreach($_POST AS $p1=>$p2) {
			if($p1 != "shipped_pickup_time" && $p1 != "shipped_delivery_time") {
				$sql .= "$p1 = '$p2',\n";
			}
		}
		$sql .= "
		date_updated = NOW(),
		updated_admin_login_id= '$_LOGIN[id]',
		date_shipment = NOW(),
		shipment_admin_login_id= '$_LOGIN[id]',
		shipped = '1',
		shipped_pickup_date = '$req_pickup_date',
		status = 'tobescheduled',
		service_id = '$service_id',
		shipped_carrier_id = '$carrier_id'
		WHERE id = '$_GET[id]'
		";

        //echo "<pre>";print_r($sql);echo "</pre>"; die();
		$success = $db->query($sql);
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
        // Make the invoice date 1 week after the requested pickup date.
        $real_pickup_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime(
                $real_pickup_date)) . " +1 week"));

        //total invoice anh
        $total_invoice =$total_rate;
        $total_cost =$total_cost;
        if($container_info) {
            foreach($container_info AS $item) {
                if(is_numeric($item["container_price"])){
                    $total_invoice += $item["container_price"];
                }

                if(is_numeric($item["container_cost"])){
                    $total_cost += $item["container_cost"];
                }
            }
        }
        $total_profit = $total_invoice - $total_cost;

        //echo "<pre>";print_r($container_cost);echo "</pre>";
       // echo "<pre>";print_r($container_price);echo "</pre>";
        //die();
       // $total_profit = $total_rate - $total_cost;

		$sql = "INSERT INTO invoice SET
		shipment_id = '$_GET[id]',
		status = 'open',
		cost = '$total_cost',
		total = '$total_invoice',
		profit = '$total_profit',
		total_due = '$total_invoice',
		invoice_date = '$real_pickup_date',
		due_date = DATE_ADD('$real_pickup_date', INTERVAL 20 DAY)";
		$success=$db->query($sql);

//		$inserted_id = mysql_insert_id();
		$inserted_id = mysqli_insert_id($db->conn);

		$sql = "UPDATE shipment SET invoice_id = '$inserted_id', invoiced = '1' WHERE id = '$_GET[id]'";
		$success = $db->query($sql);
		header("Location: ./?action=shipments_edit&id=$_GET[id]");
	}

	$sql = "SELECT shipment.*,customer.company AS customer,al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname FROM shipment LEFT JOIN customer ON shipment.customer_id=customer.id LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id WHERE shipment.id = '$_GET[id]'";
	$shipment_data = $db->query($sql);
	if($shipment_data) {
		foreach($shipment_data[0] as $n=>$v){
			$vars[$n]=($_POST[$n])?$_POST[$n]:$v;
		}
        srand(((int)((double)microtime()*1000003)));

		getTrackNum();
		$vars["shipped_tracknum"] = $shipped_tracknum;

		$vars["shipped_carrier_pronum"] = "";
	} else {
		header("Location: ./?action=quotes");
	}

	// times
	$req_pickup_time_start=$vars["req_pickup_time_start"];
	$req_pickup_time_end=$vars["req_pickup_time_end"];
	$req_delivery_time_start=$vars["req_delivery_time_start"];
	$req_delivery_time_end=$vars["req_delivery_time_end"];

	$shipped_pickup_time="";
	$shipped_delivery_time="";

    $times = create_time_range("6:00", "19:30");
    foreach ($times as $key => $time) {
        $times[$key] = date('H:i:s', $time);
    }
    foreach($times as $time_raw)
    {
        $time = sysTime($time_raw);

        // pickups
        $vars["req_pickup_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_start)?" selected":"").'>'.$time.'</option>';
		$vars["req_pickup_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_pickup_time_end)?" selected":"").'>'.$time.'</option>';
		$vars["shipped_pickup_time"].='<option value="'.$time_raw.'"'.(($time_raw==$shipped_pickup_time)?" selected":"").'>'.$time.'</option>';

        // deliveries
        $vars["req_delivery_time_start"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_start)?" selected":"").'>'.$time.'</option>';
		$vars["req_delivery_time_end"].='<option value="'.$time_raw.'"'.(($time_raw==$req_delivery_time_end)?" selected":"").'>'.$time.'</option>';
		$vars["shipped_delivery_time"].='<option value="'.$time_raw.'"'.(($time_raw==$shipped_delivery_time)?" selected":"").'>'.$time.'</option>';
    }

	// get customer data
	$customer_temp=$db->query("SELECT * FROM customer WHERE id='".$vars["customer_id"]."'");
	if($customer_temp){
		$customer=$customer_temp[0];
	}

	//GETTING SALES AND FREIGHT REPS
	$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
	$admins = $db->query($sql);
	$vars["sales_reps"] = "";
	foreach($admins AS $v) {
		$s=($customer["sales_admin_login_id"] == $v["id"])?" selected":"";
		$vars["sales_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
	}
	$vars["freight_reps"] = "";
	foreach($admins AS $v) {
		$s=($customer["freight_admin_login_id"] == $v["id"])?" selected":"";
		$vars["freight_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
	}

	//GET THE ACCESSORIALS SELECTED FOR THIS SHIPMENT.
	$sql = "SELECT accessorial_id FROM shipment_accessorial WHERE shipment_id = '$_GET[id]'";
	$accessorial_list=$db->query($sql);
	if($accessorial_list) {
		foreach($accessorial_list AS $v) {
			$accessorial_ids .= "'$v[accessorial_id]',";
		}
		$accessorial_ids = "$accessorial_ids'11','12'";
	} else {$accessorial_ids = "'11','12'";}

	$sql = "SELECT shipment_rate.*,carrier.company FROM shipment_rate LEFT JOIN carrier ON shipment_rate.carrier_id=carrier.id WHERE shipment_id = '$_GET[id]'";
	$shipment_rates_array=$db->query($sql);
	if(!$shipment_rates_array) {
		header("Location: ./?action=quotes_edit&id=$_GET[id]&error=1");
		exit;
	}

	//FIGURE OUT WHICH RATE SHOULD BE CHECKED. (HIGHEST PROFIT)
	//BUILD THE ARRAY WITH THE PROFITS.
	$tester_array = $shipment_rates_array;
	foreach($tester_array AS $gg=>$hh) {
		$sql = "SELECT accessorial.name,accessorial.id AS real_accessorial_id,shipment_rate_accessorial.* FROM accessorial LEFT JOIN shipment_rate_accessorial ON (shipment_rate_accessorial.accessorial_id=accessorial.id AND shipment_rate_accessorial.shipment_rate_id='$hh[id]') WHERE accessorial.id IN($accessorial_ids) ORDER BY accessorial.id";
		$accessorial_list=$db->query($sql);
		$shipment_rates_array[$gg]["total_cost"]=$hh["cost"];
		$shipment_rates_array[$gg]["total_rate"]=$hh["rate"];
		foreach($hh AS $ggg=>$hhh) {
			$shipment_rates_array[$gg][$ggg] = $hhh;
		}
		if($accessorial_list) {
			foreach($accessorial_list AS $ppp) {
				$shipment_rates_array[$gg]["total_cost"] += $ppp["cost"];
				$shipment_rates_array[$gg]["total_rate"] += $ppp["rate"];
			}
			$shipment_rates_array[$gg]["total_profit"] = $shipment_rates_array[$gg]["total_rate"] - $shipment_rates_array[$gg]["total_cost"];
		}
	}

	//CYCLE THROUGH THE ARRAY TO FIND OUT WHICH ID HAS THE HIGHEST PROFIT.
	foreach($shipment_rates_array AS $q) {
		$profit_array[] = $q["total_profit"];
	}
	$max_profit = max($profit_array);
	foreach($shipment_rates_array AS $q) {
		if($q["total_profit"] == $max_profit) {
			$max_profit_id = $q["id"];
		}
	}

	$table = rf($htmlpath."shipments_rates_list_table.html");
	$row = rf($htmlpath."shipments_rates_list_table_row.html");
	foreach($shipment_rates_array AS $vv) {
		$vv["checked"] = ($vv["id"] == $max_profit_id) ? " CHECKED" : "";
		//GETTING SERVICE
		$sql = "SELECT name FROM service WHERE id = $vv[service_id]";
		$service = $db->query($sql);
		if(!$service){die("Select Carrier & Service");}

		$sql = "SELECT accessorial.name,accessorial.id AS real_accessorial_id,shipment_rate_accessorial.* FROM accessorial LEFT JOIN shipment_rate_accessorial ON (shipment_rate_accessorial.accessorial_id=accessorial.id AND shipment_rate_accessorial.shipment_rate_id='$vv[id]') WHERE accessorial.id IN($accessorial_ids) ORDER BY accessorial.id";
		$accessorial_list=$db->query($sql);
		if($accessorial_list) {
			$vv["total_cost"]=$vv["cost"];
			$vv["total_rate"]=$vv["rate"];
			foreach($accessorial_list AS $vvv) {
				$vvv["profit"] = number_format($vvv["rate"] - $vvv["cost"],2);
				$vvv["percent"] = ($vvv["cost"] != 0) ?	number_format($vvv["profit"] / $vvv["cost"] * 100,2) : number_format(0,2);
				$vv["total_cost"] += $vvv["cost"];
				$vv["total_rate"] += $vvv["rate"];
				$vvv["id"] = $vv["id"];
				$vvv["accessorial_id"] = $vv["accessorial_id"];
				$vv["shipments_rates_list_table_row"] .= replace($vvv, $row);
			}
		} else {$vv["shipments_rates_list_table_row"] = "";};

		$vv["total_profit"] = number_format($vv["total_rate"] - $vv["total_cost"],2);
		$vv["total_percent"] = ($vv["total_cost"] != 0) ?	number_format($vv["total_profit"] / $vv["total_cost"] * 100,2) : number_format(0,2);
		$vv["profit"] = number_format($vv["rate"] - $vv["cost"],2);
		$vv["percent"] = ($vv["cost"] != 0) ?	number_format($vv["profit"] / $vv["cost"] * 100,2) : number_format(0,2);
		$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
		$vv["service_name"] = $service[0]["name"];

		//$vv["total_cost"]=number_format($vv["total_cost"],2,".","");
		//$vv["total_rate"]=number_format($vv["total_rate"],2,".","");

        //anh
        $total_invoice =0;
        $total_cost =0;
        if($container_info) {
            foreach($container_info AS $item) {
                if(is_numeric($item["container_price"])){
                    $total_invoice += $item["container_price"];
                }

                if(is_numeric($item["container_cost"])){
                    $total_cost += $item["container_cost"];
                }
            }
        }

        $totalrate = $total_invoice + $vv["total_rate"];
        $totalcost = $total_cost + $vv["total_cost"];
        $totalprofit = $totalrate - $totalcost;
        //echo "<pre>totalrate =";echo $totalrate;echo "</pre>";

        $vv["total_cost"]=number_format($totalcost,2,".","");
        $vv["total_rate"]=number_format($totalrate,2,".","");
        $vv["total_profit"] = number_format($totalprofit,2);
        $vv["total_percent"] = ($totalcost != 0) ?	number_format($totalprofit / $totalcost * 100,2) : number_format(0,2);

		$ratestuff["shipments_rates_list_table"] .= replace($vv, $table);
	}
	//$vars["shipments_rates_list_table"] = replace($v, rf($htmlpath."shipments_rates_list.html"));

	$vars["shipped_pickup_newdate"] = "";
	$vars["shipped_delivery_newdate"] = "";

	$vars["shipments_rates"] = replace($ratestuff, rf($htmlpath."shipments_rates.html"));
	$vars["shipments_services"] = replace($vars, rf($htmlpath."quotes_shipments_services.html"));
	$html["BODY"]=replace($vars,rf($htmlpath."quotes_shipment.html"));



srand(((int)((double)microtime()*1000003)));
function getTrackNum(){
	global $db,$id,$shipped_tracknum;
	$shipped_tracknum = "1".date("md").rand(10,9999);

	$check = $db->query("SELECT id FROM shipment WHERE shipped_tracknum='$shipped_tracknum'");
	if($check){
		$shipped_tracknum="";
		getTrackNum();
	}
}
?>