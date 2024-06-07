<?php


// posts & retrieves data from watkins server for quoting

$service_id=7;
if($classes && $weights){
	$FAK_cost_discount=0;
	$carrier_fak_address_id=0;
	$customer_gets_fak=false;
	if($fak_class){unset($fak_class);}
	if($new_classes){unset($new_classes);}

	// FAKS ADDRESSES
	$origin_key = str_replace("'","\'",strtolower($shipment["o_to"]."|".$shipment["o_city"]."|".$shipment["o_state"]."|".$shipment["o_zip"]));
	$carrier_fak = $db->query("SELECT * FROM carrier_fak_address WHERE LOWER(CONCAT(company,'|',city,'|',state,'|',zip))='$origin_key' AND carrier_id='$carrier_id'");
	if($carrier_fak){
		$FAK_cost_discount=$carrier_fak[0]["discount"];
		$carrier_fak_address_id=$carrier_fak[0]["id"];

		// check customer for it
		$check = $db->query("SELECT id FROM customer_fak_address WHERE customer_id='".$shipment["customer_id"]."' AND carrier_fak_address_id='$carrier_fak_address_id'");
		if($check){
			$customer_gets_fak=true;
		}
	}

	$destination_key = str_replace("'","\'",strtolower($shipment["d_to"]."|".$shipment["d_city"]."|".$shipment["d_state"]."|".$shipment["d_zip"]));
	$carrier_fak = $db->query("SELECT * FROM carrier_fak_address WHERE LOWER(CONCAT(company,'|',city,'|',state,'|',zip))='$destination_key' AND carrier_id='$carrier_id'");
	if($carrier_fak && !$FAK_cost_discount){
		$FAK_cost_discount=$carrier_fak[0]["discount"];
		$carrier_fak_address_id=$carrier_fak[0]["id"];

		// check customer for it
		$check = $db->query("SELECT id FROM customer_fak_address WHERE customer_id='".$shipment["customer_id"]."' AND carrier_fak_address_id='$carrier_fak_address_id'");
		if($check){
			$customer_gets_fak=true;
		}
	}

	// MAIN CLASS FAK
	$fak_class_temp=$db->query("SELECT cl1.class AS class1,cl2.class AS class2 FROM carrier_fak_class INNER JOIN class_list cl1 ON carrier_fak_class.class_list_id1=cl1.id INNER JOIN class_list cl2 ON carrier_fak_class.class_list_id2=cl2.id WHERE carrier_id='$carrier_id' HAVING class1 <> class2");
	if($fak_class_temp){
		if($fak_class){unset($fak_class);}
		foreach($fak_class_temp as $v){
			$fak_class[$v["class1"]]=$v["class2"];
		}

		// do the cost calc from here
		foreach($classes as $temp_class){
			$new_classes[]=($fak_class[$temp_class])?$fak_class[$temp_class]:$temp_class;
		}
		$wat_costs_temp = autoRateWatkins($shipment["o_zip"],$shipment["d_zip"],$new_classes,$weights);
		if(is_array($wat_costs_temp)){
			$wat_cost=$wat_costs_temp["net_charge"];
		}
	}

	// FAK ADDRESSES CLASSES
	if($carrier_fak_address_id && $customer_gets_fak){
		$fak_class_temp=$db->query("SELECT cl1.class AS class1,cl2.class AS class2 FROM carrier_fak_address_class INNER JOIN class_list cl1 ON carrier_fak_address_class.class_list_id1=cl1.id INNER JOIN class_list cl2 ON carrier_fak_address_class.class_list_id2=cl2.id WHERE carrier_id='$carrier_id' AND carrier_fak_address_id='$carrier_fak_address_id' HAVING class1 <> class2");
		if($fak_class_temp){
			if($fak_class){unset($fak_class);}
			foreach($fak_class_temp as $v){
				$fak_class[$v["class1"]]=$v["class2"];
			}

			// we have a rate class for this fak address
			// loop through classes and give update it
			if($new_classes){unset($new_classes);}
			foreach($classes as $temp_class){
				$new_classes[]=($fak_class[$temp_class])?$fak_class[$temp_class]:$temp_class;
			}
			$classes=$new_classes;
		}
	}

	if(count($classes) > 4 || count($weights) > 4){
		$rate_errors[$carrier_id][]='Watkins will only quote on up to 4 commodities.';
	}else{
		$wat_rates = autoRateWatkins($shipment["o_zip"],$shipment["d_zip"],$classes,$weights);
		if(is_array($wat_rates) && $wat_rates["orig_direct"]=="true" && $wat_rates["dest_direct"]=="true"){
			// grab the costs
			$carrier_costs_temp = $db->query("SELECT * FROM carrier_cost WHERE carrier_id='$carrier_id' AND service_id='$service_id'");
			if($carrier_costs_temp){
				foreach($carrier_costs_temp as $v){
					if($FAK_cost_discount){
						$v["cost"]=$FAK_cost_discount;
					}
					$carrier_costs[$v["direction"]]=$v;
				}
			}

			if($carrier_costs){
				if($shipment["o_state"]==$shipment["d_state"] && $carrier_costs["intrastate"]){
					$direction = "intrastate";
				}
				if($shipment["o_state"]!=$shipment["d_state"] && $carrier_costs["interstate"]){
					$direction = "interstate";
				}
				$discount_cost = $carrier_costs[$direction]["cost"]/100;
				$discount_rate = $carrier_costs[$direction]["rate"]/100;
				$min_cost = $carrier_costs[$direction]["min_cost"];
				$min_rate = $carrier_costs[$direction]["min_rate"];

				// customer specific rates
				$customer_rates_temp = $db->query("SELECT * FROM customer_rate WHERE customer_id='".$shipment["customer_id"]."' AND carrier_id='$carrier_id' AND service_id='$service_id'");
				if($customer_rates_temp){
					foreach($customer_rates_temp as $v){
						$customer_rates[$v["direction"]]=$v;
					}
					$direction = "all";
					if($shipment["o_state"]==$shipment["d_state"] && $customer_rates["intrastate"]){
						$direction = "intrastate";
					}
					if($shipment["o_state"]!=$shipment["d_state"] && $customer_rates["interstate"]){
						$direction = "interstate";
					}
					if($customer_rates[$direction]["type"]=="discount"){
						$discount_rate = $customer_rates[$direction]["rate"]/100;
					}else{
						$markup_rate = $customer_rates[$direction]["rate"]/100;
					}
					$min_rate = $customer_rates[$direction]["min_rate"];
				}

				$net_charge = $wat_rates["net_charge"];
				if($wat_cost){
					// we have an fak for the class
					$cost = $wat_cost - ($wat_cost * $discount_cost);
				}else{
					$cost = $net_charge - ($net_charge * $discount_cost);
				}
				if($cost < $min_cost){$cost=$min_cost;}

				if(!$markup_rate){
					$rate = $net_charge - ($net_charge * $discount_rate);
				}else{
					$rate = $cost + ($cost * $markup_rate);
				}
				if($rate < $min_rate){$rate=$min_rate;}

				$db->query("INSERT INTO shipment_rate SET shipment_id='".$shipment["id"]."',carrier_id='$carrier_id',service_id='$service_id',transit_days='".$wat_rates["transit"]."',calc_cost='".$cost."',cost='".$cost."',calc_rate='".$rate."',rate='".$rate."'");
				$calc_rate_ids[]=$db->lastid();
			}else{
				$rate_errors[$carrier_id][]='No Carrier Costs Defined.';
			}
		}else{
			$rate_errors[$carrier_id][]=$wat_rates;
		}
	}
}


// does a quote
function autoRateWatkins($origin_zip,$destination_zip,$classes,$weights){
	global $carrier_id,$db;

	if($classes){
		$x=0;
		foreach($classes as $class){$x++;
			if($x<=4){
				$CLASSES_ARR[]="CLASS".$x."=".$class;
			}
		}
		$CLASSES="&".implode("&",$CLASSES_ARR);
	}
	if($weights){
		$x=0;
		foreach($weights as $weight){$x++;
			if($x<=4){
				$WEIGHTS_ARR[]="WEIGHT".$x."=".$weight;
			}
		}
		$WEIGHTS="&".implode("&",$WEIGHTS_ARR);
	}

	if($origin_zip && $destination_zip && $CLASSES && $WEIGHTS){
		$GET = "/autosub/autorate.asp?ORG=$origin_zip&DEST=$destination_zip".$CLASSES.$WEIGHTS;

//		$db->query("INSERT INTO autorate_log SET carrier_id='$carrier_id',date_request=NOW(),request='http://www.watkins.com".mysql_escape_string($GET)."'");
		$db->query("INSERT INTO autorate_log SET carrier_id='$carrier_id',date_request=NOW(),request='http://www.watkins.com".mysqli_escape_string($db->conn, $GET)."'");
		$log_id=$db->lastid();

		$fp = fsockopen("www.watkins.com", 80, $errno, $errstr, 30);
		if($fp){
			$req = "GET $GET HTTP/1.1\r\n";
			$req .= "Accept: */*\r\n";
			$req .= "User-Agent: TrollCompany 1.0\r\n";
			$req .= "Host: www.watkins.com\r\n";
			$req .= "Connection: Close\r\n\r\n";

			fwrite($fp, $req);
			while(!feof($fp)) {
			   $rets[] = fgets($fp, 4096);
			}
			fclose($fp);
			if($rets){
				$empty=false;
				foreach($rets as $v){
					if(trim($v) && $empty==true){
						$ret.=$v;
					}
					if(!trim($v)){
						$empty=true;
					}
				}

//				$db->query("UPDATE autorate_log SET date_response=NOW(),response='".mysql_escape_string($ret)."' WHERE id='$log_id'");
				$db->query("UPDATE autorate_log SET date_response=NOW(),response='".mysqli_escape_string($db->conn, $ret)."' WHERE id='$log_id'");

				$ret=preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $ret);
				preg_match("/<table>.+<\/table>/i",$ret,$match);
				if($match[0]){
					$table_data = $match[0];

					// fix the missing <tr>
					$table_data = str_replace("</td><tr>","</td></tr><tr>",$table_data);

					$regex1 =	"/<tr><td colspan=5>(.+)<\/td><\/tr>/Uis";
					preg_match_all($regex1,$table_data,$match1,PREG_SET_ORDER);
					if($match1){
						foreach($match1 as $arr){
							$table_data = str_replace($arr[0],"",$table_data);
						}
					}

					$regex2 =	"/<tr><td>CLASS\/WEIGHT\/RATE\/CHARGE [1-4]:<\/td><td>(.+)<\/td><td>(.+)<\/td><td>(.+)<\/td><td>(.+)<\/td><\/tr>/Uis";
					preg_match_all($regex2,$table_data,$match2,PREG_SET_ORDER);
					if($match2){
						foreach($match2 as $arr){
							$table_data = str_replace($arr[0],"",$table_data);
						}
					}

					$regex3 =	"/<tr><td>(.+):<\/td><td colspan=4>(.+)<\/td><\/tr>/Uis";
					preg_match_all($regex3,$table_data,$match3,PREG_SET_ORDER);
					if($match3){
						foreach($match3 as $arr){
							$table_data = str_replace($arr[0],"",$table_data);

							if(substr($arr[1],0,10)=="ORIGIN ZIP"){}
							if(substr($arr[1],0,15)=="ORIGIN TERMINAL"){
								$parts=explode(" ",$arr[2]);
								$return["orig_term"]=$parts[0];
								$return["orig_direct"]=(strpos($arr[2],"(Direct)"))?"true":"false";
							}
							if(substr($arr[1],0,8)=="DEST ZIP"){}
							if(substr($arr[1],0,13)=="DEST TERMINAL"){
								$parts=explode(" ",$arr[2]);
								$return["dest_term"]=$parts[0];
								$return["dest_direct"]=(strpos($arr[2],"(Direct)"))?"true":"false";
							}
							if(substr($arr[1],0,12)=="SERVICE DAYS"){
								$return["transit"] = $arr[2];
							}
							if(substr($arr[1],0,12)=="LTL DISCOUNT"){}
							if(substr($arr[1],0,11)=="MC DISCOUNT"){}
							if(substr($arr[1],0,8)=="MC FLOOR"){}
							if(substr($arr[1],0,12)=="GROSS CHARGE"){}
							if(substr($arr[1],0,10)=="NET CHARGE"){}
							if(substr($arr[1],0,19)=="ACCESSORIAL CHARGES"){
								$return["acc_charge"]=$arr[2]=str_replace(",","",$arr[2]);
							}
							if(substr($arr[1],0,14)=="FUEL SURCHARGE"){
								$return["fuel_charge"]=$arr[2]=str_replace(",","",$arr[2]);
							}

							//if(substr($arr[1],0,5)=="TOTAL"){
							if(strpos($arr[1],"TOTAL") || substr($arr[1],0,5)=="TOTAL"){
								$return["total_charge"]=$arr[2]=str_replace(",","",$arr[2]);
							}
						}
					}
					if($return && $return["total_charge"]){
						$return["net_charge"]=$return["total_charge"]-$return["fuel_charge"]-$return["acc_charge"];
						return $return;
					}
				}else{
					return "ERROR: ".$ret;
				}
			}
		}
	}

	return "ERROR: No Rate Returned";
}

?>