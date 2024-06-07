<?php

$html["LOCATION"] = "<h1>QUOTES</h1>";
switch($action){
  case "prodbook":
		$commodities = $db->query("SELECT * FROM customer_commodity_items WHERE customer_commodity_id='$id'");
		if($commodities){
			foreach($commodities as $v){
				echo "addRow('".$v["pieces"]."','".$v["packaging_list_id"]."','".$v["dim_d"]."','".$v["dim_w"]."','".$v["dim_h"]."','".$v["class_list_id"]."','".$v["nmfc"]."','".$v["description"]."','".$v["weight"]."');";
			}
		}else{
			echo "addRow();";
		}
		die();
		break;
	case "ratesrow":
		if($accessorial_id > 0){
			$accessorial_ids[]=$accessorial_id;
		}
		if(!$accessorial_ids){
			//GET THE ACCESSORIALS SELECTED FOR THIS SHIPMENT.
			$sql = "SELECT accessorial_id FROM shipment_accessorial WHERE shipment_id = '$_GET[shipment_id]'";
			$accessorial_list=$db->query($sql);
			if($accessorial_list) {
				foreach($accessorial_list AS $v) {
					$accessorial_ids[] = $v["accessorial_id"];
				}
			}
			$accessorial_ids[]=11;
			$accessorial_ids[]=12;
		}
       // echo "id=";echo $_GET["shipment_id"]; die();
		if($accessorial_ids){	
			$total_ins_temp = $db->query("SELECT insurance FROM shipment WHERE id='$shipment_id'");
			$total_ins=($total_ins_temp)?$total_ins_temp[0]["insurance"]:0;

			$total_weight_temp = $db->query("SELECT SUM(weight) AS weight FROM shipment_commodity WHERE shipment_id='$shipment_id'");
			$total_weight=($total_weight_temp)?$total_weight_temp[0]["weight"]:0;

			// grab the carriers costs
			$carrier_accessorials = $db->query("SELECT * FROM carrier_accessorial WHERE carrier_id='$carrier_id' AND accessorial_id IN (".implode(",",$accessorial_ids).")");
			if($carrier_accessorials){
				foreach($carrier_accessorials as $v){
					switch($v["type"]){
						case "ins":
							if($total_ins > 0){
								$costs[$v["accessorial_id"]]=ceil($total_ins / 100) * $v["cost"];
								$rates[$v["accessorial_id"]]=ceil($total_ins / 100) * $v["rate"];

								
							}							
							break;
						case "per100":
							$costs[$v["accessorial_id"]]=($total_weight / 100) * $v["cost"];
							$rates[$v["accessorial_id"]]=($total_weight / 100) * $v["rate"];
							break;
						case "percent":
							$percent_accessorials_cost[$v["accessorial_id"]]=$v;
							$percent_accessorials_rate[$v["accessorial_id"]]=$v;
							break;
						case "dollar":
						default:
							$costs[$v["accessorial_id"]]=$v["cost"];
							$rates[$v["accessorial_id"]]=$v["rate"];
							break;
					}
					if($v["type"] != "percent"){
						if($costs[$v["accessorial_id"]] && ($costs[$v["accessorial_id"]] < $v["cost_min"]) && $v["cost_min"] > 0){
							$costs[$v["accessorial_id"]]=$v["cost_min"];
						}
						if($costs[$v["accessorial_id"]] && ($costs[$v["accessorial_id"]] > $v["cost_max"]) && $v["cost_max"] > 0){
							$costs[$v["accessorial_id"]]=$v["cost_max"];
						}
						if($rates[$v["accessorial_id"]] && ($rates[$v["accessorial_id"]] < $v["rate_min"]) && $v["rate_min"] > 0){
							$rates[$v["accessorial_id"]]=$v["rate_min"];
						}
						if($rates[$v["accessorial_id"]] && ($rates[$v["accessorial_id"]] > $v["rate_max"]) && $v["rate_max"] > 0){
							$rates[$v["accessorial_id"]]=$v["rate_max"];
						}
					}
					$accessorials_type[$v["accessorial_id"]]=$v["type"];
				}
			}	

			foreach($accessorial_ids as $accessorial_id){
				echo 'document.getElementById("cost__'.$id.'__'.$accessorial_id.'").value="'.number_format($costs[$accessorial_id],2,".","").'";';
				echo 'document.getElementById("rate__'.$id.'__'.$accessorial_id.'").value="'.number_format($rates[$accessorial_id],2,".","").'";';
			}

			
			if($percent_accessorials_cost){
				foreach($percent_accessorials_cost as $accessorial_id=>$v){
					echo 'calcPercentCharge(\''.$id.'\',\'cost__'.$id.'__'.$accessorial_id.'\',\'cost\',\''.$v["cost"].'\',\''.$v["cost_min"].'\',\''.$v["cost_max"].'\');';			
				}
			}

			if($percent_accessorials_rate){
				foreach($percent_accessorials_rate as $accessorial_id=>$v){
					echo 'calcPercentCharge(\''.$id.'\',\'rate__'.$id.'__'.$accessorial_id.'\',\'rate\',\''.$v["rate"].'\',\''.$v["rate_min"].'\',\''.$v["rate_max"].'\');';			
				}
			}

			// customer charges
			$customer_accessorial = $db->query("SELECT * FROM customer_accessorial WHERE customer_id='$customer_id' AND carrier_id='$carrier_id' AND accessorial_id IN (".implode(",",$accessorial_ids).") AND rate > 0");
			if($customer_accessorial){
				foreach($customer_accessorial as $v){
					echo 'calcCostPercentageCharge(\''.$id.'\',\'cost__'.$id.'__'.$v["accessorial_id"].'\',\'rate__'.$id.'__'.$v["accessorial_id"].'\',\''.$v["rate"].'\',\''.$v["rate_min"].'\',\''.$v["rate_max"].'\');';
				}
			}

			echo 'calcTotals(\''.$id.'\')';
		}
		die();
	case "rates-auto":
		include("quotes_rates-auto.php");
		break;
	case "rates":
		if(!empty($_POST)) {
			foreach($_POST as $k=>$v) {
				if(!in_array($k,array("goto"))){
					$data_array = explode("__",$k);
					if(!$data_array[2]) {
						$rate_array[$data_array[1]][$data_array[0]] = $v;
					} else {
						$accessorial_array[$data_array[1]][$data_array[2]][$data_array[0]] = $v;
					}
				}
			}
			if($rate_array) {
				$sql = "DELETE FROM shipment_rate WHERE shipment_id = '$_GET[id]'";
				$success = $db->query($sql);
				foreach($rate_array as $rate_id=>$v) {
                    if(empty($v["cost"]))  $v["cost"] ='0';
                    if(empty($v["rate"]))  $v["rate"] ='0';
                    if(empty($v["carrier_id"])) unset($v["carrier_id"]);
                    if(empty($v["transit_days"]))  unset($v["transit_days"]);
                    if(empty($v["service_id"]))  unset($v["service_id"]);

					if(empty($v["calc_cost"]) || $v["calc_cost"] == "0.00") $v["calc_cost"] = $v["cost"];
					if(empty($v["calc_rate"]) || $v["calc_rate"] == "0.00") $v["calc_rate"] = $v["rate"];
					$sql = "INSERT INTO shipment_rate SET ";
					foreach($v AS $name => $value) {
						$sql .= "$name = '$value',";
					}
					$sql .= " id = '$rate_id', shipment_id = '$_GET[id]'";
                    // echo "<pre>"; print_r($sql); echo "-----------------------</pre>";
					$success = $db->query($sql);
					$sql = "DELETE FROM shipment_rate_accessorial WHERE shipment_rate_id = '$rate_id'";
					$success = $db->query($sql);
				}
			}
           // echo "<pre>accessoria=";print_r($accessorial_array);echo "</pre>";
			if($accessorial_array) {
				$success = $db->query($sql);
				foreach($accessorial_array as $rate_id => $v) {
					foreach($v AS $accessorial_id => $accessorial_array) {
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
		if($goto){header("Location: $goto");die();}
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM shipment_rate WHERE id='$_GET[remove]' AND shipment_id = '$_GET[id]'";
			$success = $db->query($sql);
			$sql = "DELETE FROM shipment_rate_accessorial WHERE shipment_rate_id='$_GET[remove]'";
			$success = $db->query($sql);
		}
        $isadd ='';
        //,service_id ='1'
		if(isset($_GET["add"])) {
			$sql = "INSERT INTO shipment_rate SET
			shipment_id = '$_GET[id]',
			carrier_id = '1'";

			$success = $db->query($sql);
            $isadd =1;
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
        //echo "<pre>";print_r($shipment_rates_array);echo "</pre>";
		//GETTING SERVICE
		$sql = "SELECT * FROM service";
		$service = $db->query($sql);

        $sql = "SELECT container_cost,container_price FROM shipment WHERE id = '$_GET[id]'";
        $container_info=$db->query($sql);

        $v =array();
		if($shipment_rates_array) {

            $i = 0;
			foreach($shipment_rates_array AS $vv) {
                $i++;
                //echo "<pre>"; print_r($vv); echo "-----------------------</pre>";
				$sql = "SELECT accessorial.name,accessorial.id AS real_accessorial_id,shipment_rate_accessorial.* FROM accessorial LEFT JOIN shipment_rate_accessorial ON (shipment_rate_accessorial.accessorial_id=accessorial.id AND shipment_rate_accessorial.shipment_rate_id='$vv[id]') WHERE accessorial.id IN($accessorial_ids) ORDER BY accessorial.id";
				$accessorial_list=$db->query($sql);


                if($accessorial_list) {
					$vv["total_cost"]=$vv["cost"];
					$vv["total_rate"]=$vv["rate"];
					$vv["profit"] = number_format($vv["rate"] - $vv["cost"],2); 
					$vv["percent"] = ($vv["cost"] != 0) ?	number_format($vv["profit"] / $vv["cost"] * 100,2) : number_format(0,2);
                    foreach($accessorial_list AS $vvv) {
						$vv["total_cost"] += $vvv["cost"];
						$vv["total_rate"] += $vvv["rate"];
						$vvv["id"] = $vv["id"];
						$vvv["accessorial_id"] = $vv["accessorial_id"];

						$vvv["profit"] = number_format($vvv["rate"] - $vvv["cost"],2); 
						$vvv["percent"] = ($vvv["cost"] != 0) ?	number_format($vvv["profit"] / $vvv["cost"] * 100,2) : number_format(0,2);

						$vvv["cost"]=number_format($vvv["cost"],2,".","");
						$vvv["rate"]=number_format($vvv["rate"],2,".","");
						$vv["quotes_rates_list_table_row"] .= replace($vvv, rf($htmlpath."quotes_rates_list_table_row.html"));
					}
				} else {
                    $vv["quotes_rates_list_table_row"] = "";
                }

				$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				foreach($service AS $t) {
					$s = ($vv["service_id"] == $t["id"]) ? " SELECTED" : "";
					$vv["service_list"] .= "<option value=$t[id]$s>$t[name]</option>";
				}

                $totalrate = $vv["total_rate"];
                $totalcost = $vv["total_cost"];
				$vv["total_profit"] = number_format($vv["total_rate"] - $vv["total_cost"],2);
				$vv["total_percent"] = ($vv["total_cost"] != 0) ?	number_format($vv["total_profit"] / $vv["total_cost"] * 100,2) : number_format(0,2);
				$vv["total_cost"]=number_format($vv["total_cost"],2,".","");
				$vv["total_rate"]=number_format($vv["total_rate"],2,".","");

                $totalprofit = $vv["total_profit"];



               // echo "<pre>";print_r($container_info);echo "</pre>";
                $total_invoice =0;
                $total_cost =0;
                if($container_info) {
                    foreach($container_info AS $item) {
                     //   echo "<pre>";print_r($item);echo "</pre>";
                        if(is_numeric($item["container_price"])){
                            $total_invoice += $item["container_price"];
                        }

                        if(is_numeric($item["container_cost"])){
                            $total_cost += $item["container_cost"];
                        }
                    }
                }
                if(is_numeric($totalrate)){
                    $total_invoice +=  $totalrate;
                }
                if(is_numeric($totalcost)){
                    $total_cost +=  $totalcost;
                }

                $totalpercent =0;
                if(is_numeric($totalprofit)){
                    $totalprofit =  $total_invoice-$total_cost;
                    $totalpercent = ($total_cost != 0) ?	number_format($totalprofit / $total_cost * 100,2) : number_format(0,2);
                }
                $vv["total_rate"]=number_format($total_invoice,2,".","");
                $vv["total_cost"]=number_format($total_cost,2,".","");
                $vv["total_profit"]=number_format($totalprofit,2);
                $vv["total_percent"]=$totalpercent;
              //  echo "<pre>";  print_r($v); echo "----------------------------------------------</pre>";
                if($isadd ==1) {
                  //  echo "<pre>";  print_r($v); echo "</pre>";
                   // die();
                }
                $v["quotes_rates_list_table"] .= replace($vv, rf($htmlpath."quotes_rates_list_table.html"));
			}

		} else {
			$v["quotes_rates_list_table"] = "No carriers have been selected yet. <a href='javascript:addCarrier([id])'>Click here to add a carrier</a>";

        }
        $vars =array();
		$vars["id"] = $_GET["id"];
		$vars["quotes_rates_list_table"] = replace($v, rf($htmlpath."quotes_rates_list.html"));

		$vars["runScripts"]="";

		if($calc_rate_id){
			$calc_rate_ids=explode(",",$calc_rate_id);
			foreach($calc_rate_ids as $calc_rate_id){
				$vars["runScripts"].="calcRow($calc_rate_id,0);";
			}
			$vars["runScripts"].="setTimeout('document.carrierform.submit()',1000)";
		}

		$html["BODY"]=replace($vars, $vars["quotes_rates_list_table"]);

		break;
	case "notes":
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM shipment_note WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["note"])) {
			$sql = "INSERT INTO shipment_note SET
			shipment_id = '$_GET[id]',
			date_created = NOW(),
			created_admin_user_id = '$_LOGIN[id]',
			note = '$_POST[note]'";
			$success = $db->query($sql);
		}
		$sql = "SELECT * FROM shipment_note WHERE shipment_id = '$_GET[id]' ORDER BY date_created DESC";
		$notes = $db->query($sql);
		if($notes) {
			foreach($notes AS $v) {
				$v["date_created"] = formatDate($v["date_created"]);
				$v["note"] = nl2br($v["note"]);
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["quotes_notes_list_row"] .= replace($v, rf($htmlpath."quotes_notes_list_row.html"));
			}
		} else {
			$vars["quotes_notes_list_row"] = "<tr><td colspan='3'><b>Currently there are no notes</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$quotes_notes_list = replace($vars, rf($htmlpath."quotes_notes_list.html"));
		$html["BODY"]=replace($vars, $quotes_notes_list);
	break;
	case "logins":
	break;
	case "shipment-terminals":
		include("quotes_shipment-terminals.php");
		die();
		break;
	case "shipment":
		include("quotes_shipment.php");
		break;
	case "view-top":
	case "view-main":
		include("quotes_view.php");
		break;
	case "view":
	case "viewwindow":
	case "update":
	case "edit":
  case "getprodbook":
		include("quotes_edit.php");
		break;
	case "list":
	default:
		// page logic
		$vars["prev_page"]="";
		$vars["next_page"]="";
		$numpp = 25;
		if(!$page){$page=1;}
		$_sl = ($page - 1) * $numpp;
		$_el = $numpp + 6;
	
		//ORDERING LOGIC.
		$vars["asc_date_created"] = $_GET["asc_date_created"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_company"] = $_GET["asc_company"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_pieces"] = $_GET["asc_pieces"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_class"] = $_GET["asc_class"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_weight"] = $_GET["asc_weight"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_origin"] = $_GET["asc_origin"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_destination"] = $_GET["asc_destination"] == "DESC" ? "ASC" : "DESC";
		$vars["url"] = "";
		switch($_GET["sort"]){
			case "id";
				$orderby = "id";
				if($vars["asc_id"] == "DESC") $orderby .=" DESC";
				break;
			case "state";
				$orderby = "mail_state";
				if($vars["asc_state"] == "DESC") $orderby .=" DESC";
				break;
			case "address";
				$orderby = "mail_address1";
				if($vars["asc_address"] == "DESC") $orderby .=" DESC";
				break;
			case "city";
				$orderby = "mail_city";
				if($vars["asc_city"] == "DESC") $orderby .=" DESC";
				break;
			case "zip";
				$orderby = "mail_zip";
				if($vars["asc_zip"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_name";
				$orderby = "main_contact_name";
				if($vars["asc_contact_name"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_phone";
				$orderby = "main_contact_phone";
				if($vars["asc_contact_phone"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_email";
				$orderby = "main_contact_email";
				if($vars["asc_contact_email"] == "DESC") $orderby .=" DESC";
				break;
			default:
				$orderby = "id";
				if($vars["asc_company"] == "DESC") $orderby .=" DESC";
		}

		//MAINTAINING SEARCH INTEGRITY THROUGH THE ORDERING LOGIC.
		if(isset($_GET["search"])) {
			$vars["url"] .= "&search=$_GET[search]";
		}
		if(isset($_GET["search_alpha"])) {
			$vars["url"] .= "&search_alpha=$_GET[search_alpha]";
		}

		//SEARCH SWITCHES
		if(isset($_GET["search"])) {
			if(!empty($_GET["search"])) {
				$where = "( ";
				$field[] = "s.id";
				$field[] = "company";
				$field[] = "s.o_zip";
				$field[] = "s.o_state";
				$search_term = "$_GET[search]";
				foreach($field AS $fld) {
					$where .= " $fld LIKE '%$search_term%' OR";
				}
				$where = trim($where,"OR");
				if(empty($_GET["b_date"])){
					$where .= ")  AND";
				}
			}
			if(!empty($_GET["b_date"]) && !empty($_GET["e_date"])){
				if(!empty($_GET["search"])) {
					$where .= ") AND ";
				}
				$where .= "(TO_DAYS(s.date_created) BETWEEN TO_DAYS('$_GET[b_date]') AND TO_DAYS('$_GET[e_date]')) AND";
			}
			$where .= " s.status = 'quote' GROUP BY s.id ORDER BY $orderby";
		} elseif(isset($_GET["search_alpha"])) {
			$field = "company";
			switch($_GET["search_alpha"]) {
				case 0:
					$search_term =  "'0%' OR ".
											"company LIKE '1%' OR ".
											"company LIKE '2%' OR ".
											"company LIKE '3%' OR ".
											"company LIKE '4%' OR ".
											"company LIKE '5%' OR ".
											"company LIKE '6%' OR ".
											"company LIKE '7%' OR ".
											"company LIKE '8%' OR ".
											"company LIKE '9%'";
					break;
				case 1:
					$search_term = "'a%' OR company LIKE 'b%' OR company LIKE 'c%'";
					break;
				case 2:
					$search_term = "'d%' OR company LIKE 'e%' OR company LIKE 'f%'";
					break;
				case 3:
					$search_term = "'g%' OR company LIKE 'h%' OR company LIKE 'i%'";
					break;
				case 4:
					$search_term = "'j%' OR company LIKE 'k%' OR company LIKE 'l%'";
					break;
				case 5:
					$search_term = "'m%' OR company LIKE 'n%' OR company LIKE 'o%'";
					break;
				case 6:
					$search_term = "'p%' OR company LIKE 'q%' OR company LIKE 'r%'";
					break;
				case 7:
					$search_term = "'s%' OR company LIKE 't%' OR company LIKE 'u%'";
					break;
				case 8:
					$search_term = "'v%' OR company LIKE 'w%' OR company LIKE 'x%' OR company LIKE 'y%' OR company LIKE 'z%'";
					break;
			}
			$where = "$field LIKE $search_term AND s.status = 'quote' GROUP BY s.id ORDER BY $orderby";
		} else if($_GET[customer_id]){
			$where = "s.customer_id='$_GET[customer_id]' AND s.status = 'quote' GROUP BY s.id ORDER BY $orderby";	
		} else {
			$where = "company LIKE '%%' AND s.status = 'quote' GROUP BY s.id ORDER BY $orderby";
		}

		$where = "1=1 ".str_replace("customer","cus",$SALES_REP_FILTER)." AND ".$where;

//		$sql = "SELECT id, company, mail_address1, mail_address2, mail_city, mail_state, mail_zip, main_contact_name, main_contact_phone, main_contact_email FROM quote WHERE $where LIMIT $_sl, $_el";
		$sql = "SELECT s.req_pickup_date, s.req_pickup_time_start, s.req_pickup_time_end, s.date_created, s.id, cus.company, SUM(com.pieces) AS pieces, MAX(cla.class) AS class, SUM(com.weight) AS weight, s.o_state, s.o_zip, s.d_state, s.d_zip FROM shipment s LEFT JOIN shipment_commodity com ON com.shipment_id=s.id LEFT JOIN customer cus ON cus.id=s.customer_id LEFT JOIN class_list cla ON com.class_list_id=cla.id WHERE $where LIMIT $_sl, $_el";

		//print_r($sql);
		$quote_list = $db->query($sql);
		if($quote_list) {
			$num_res = count($quote_list);
			$next_page = ($num_res > $numpp && $num_res == $_el) ? true : false;

			$row = rf($htmlpath."quotes_list_row.html");
			foreach($quote_list AS $v) {$num++;
				if($num <= $numpp || !$next_page){
					$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
					$v["date_created"] = formatDate($v["date_created"]);
					$v["req_pickup_date"] = ($v["req_pickup_date"] == "0000-00-00") ? "not set" : date("m/d/Y",strtotime($v["req_pickup_date"])).' ('.date("g:i a",strtotime($v["req_pickup_time_start"])).'-'.date("g:i a",strtotime($v["req_pickup_time_end"])).')';
					$v["origin"] = "$v[o_state] $v[o_zip]";
					$v["destination"] = "$v[d_state] $v[d_zip]";
					$vars["quotes_list"] .= replace($v, $row);
				}
			}
			if($next_page){
				$npage = $page + 1;
				$vars["next_page"]='<a href="./?'.removeFromQS("page").'&page='.$npage.'">Next Page ></a>';			
			}
			if($page > 1){
				$ppage = $page - 1;
				$vars["prev_page"]='<a href="./?'.removeFromQS("page").'&page='.$ppage.'">< Previous Page</a>';
			}
		} else {
			$vars["quotes_list"] = "<tr><th colspan='9'>No quotes found.</th></tr>";
		}

		$html["LOCATION"] = "<h1>QUOTES : LIST</h1>";

		$html["BODY"]=replace($vars,rf($htmlpath."quotes_list.html"));	
}


?>