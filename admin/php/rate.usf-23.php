<?php

$ACCESS_KEY = "B9jH8Yezh71EmHnIxsTh4iabp584WPtZ";
$ACCOUNT_CODE = "299776";
$SERVICE_COMPANY = "bestway";


$fuel_carrier_info = $db->query("SELECT carrier_accessorial.cost FROM carrier_accessorial INNER JOIN accessorial ON carrier_accessorial.accessorial_id=accessorial.id WHERE carrier_accessorial.carrier_id='$carrier_id' AND accessorial.type='fuel'");
if($fuel_carrier_info){
	$FUEL_BACKOUT = $fuel_carrier_info[0]["cost"]/100;
}else{
	$FUEL_BACKOUT = 0;
}

if(checkStates_23($shipment["o_state"],$shipment["d_state"]) && $FUEL_BACKOUT && $HTTP_HOST=="admin.trollcompany.com"){

	// posts & retrieves data from usf server for quoting, pickups, and tracking
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
			$usf_costs_temp = autoRateUSF_23($shipment["o_zip"],$shipment["d_zip"],$new_classes,$weights);
			if(is_array($usf_costs_temp)){
				$usf_cost=$usf_costs_temp["net_charge"];
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

		if(count($classes) > 5 || count($weights) > 5){
			$rate_errors[$carrier_id][]='USF will only quote on up to 5 commodities.';
		}

		$usf_rates = autoRateUSF_23($shipment["o_zip"],$shipment["d_zip"],$classes,$weights);
		if(is_array($usf_rates)){

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

				$net_charge = $usf_rates["net_charge"];
				if($usf_cost){
					// we have an fak for the class
					$cost = $usf_cost - ($usfcost * $discount_cost);
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

				$db->query("INSERT INTO shipment_rate SET shipment_id='".$shipment["id"]."',carrier_id='$carrier_id',service_id='$service_id',transit_days='".$usf_rates["transit"]."',calc_cost='".$cost."',cost='".$cost."',calc_rate='".$rate."',rate='".$rate."'");
				$calc_rate_ids[]=$db->lastid();
			}else{
				$rate_errors[$carrier_id][]='No Carrier Costs Defined';
			}
		}else{
			$rate_errors[$carrier_id][]=$usf_rates;
		}
	}
}


// does a quote
function autoRateUSF_23($origin_zip,$destination_zip,$classes,$weights){
	global $shipment,$debug,$carrier_id,$db,$ACCESS_KEY,$ACCOUNT_CODE,$SERVICE_COMPANY,$FUEL;

	// commodities
	if($classes){
		$i=1;
		$x=0;
		foreach($classes as $class){
			if($class=="77"){$class="77.5";}
			if($class=="92"){$class="92.5";}
			$URL_ADDON .= "shipmentClass".$i."=".$class."&shipmentWeight".$i."=".$weights[$x]."&";
			$x++;
			$i++;
		}
	}

	$request = "/RESTService/doRateQuote?accessKey=$ACCESS_KEY&destZipCode=".$shipment["d_zip"]."&originZipCode=".$shipment["o_zip"]."&chargeType=P&direction=3&accountCode=$ACCOUNT_CODE&serviceCompany=$SERVICE_COMPANY&$URL_ADDON&output=no_xsd";

//	$db->query("INSERT INTO autorate_log SET carrier_id='$carrier_id',date_request=NOW(),request='www.usfnet.com\n\n".mysql_escape_string($request)."'");
	$db->query("INSERT INTO autorate_log SET carrier_id='$carrier_id',date_request=NOW(),request='www.usfnet.com\n\n".mysqli_escape_string($db->conn, $request)."'");
	$log_id=$db->lastid();

	$fp = fsockopen("www.usfnet.com", 80, $errno, $errstr, 30);
	if($fp){
		$req =  "GET $request HTTP/1.1\r\n";
		$req .= "Host: www.usfnet.com\r\n";
		$req .= "User-Agent: Troll Company\r\n";
		$req .= "Content-Type: text/html\r\n";
		$req .= "Connection: close\r\n\r\n";

		fwrite($fp, $req);
		while(!feof($fp)) {
		   $rets[] = fgets($fp, 4096);
		}
		fclose($fp);
		if($rets){
			foreach($rets as $v){
				if($isRes || substr($v,0,5)=="<?xml"){
					$response.=trim($v);
					$isRes=true;
				}
			}

//			$db->query("UPDATE autorate_log SET date_response=NOW(),response='".mysql_escape_string($response)."' WHERE id='$log_id'");
			$db->query("UPDATE autorate_log SET date_response=NOW(),response='".mysqli_escape_string($db->conn, $response)."' WHERE id='$log_id'");

			require_once("xml2array.php");

			if($response){
				$xml = new xml2Array();
				$xml_array = $xml->parse($response);

				if($xml_array){
					if($xml_array["ERROR"]["MESSAGE"]["DATA"]){
						return "ERROR: ".$xml_array["ERROR"]["MESSAGE"]["DATA"];
					}else if($xml_array["RATEQUOTE"]){
						$originCompany = $xml_array["RATEQUOTE"]["ORIGINCOMPANY"]["DATA"];
						$destinationCompany = $xml_array["RATEQUOTE"]["DESTINATIONCOMPANY"]["DATA"];

						$return["net_charge"]=round($xml_array["RATEQUOTE"]["NETCHARGES"]["DATA"] / (1 + $FUEL_BACKOUT),2);
						$return["transit"]=$xml_array["RATEQUOTE"]["DAYS"]["DATA"];
						return $return;
					}
				}
			}
		}
	}
	return "ERROR: No Rate Returned";
}


function checkStates_23($o_state,$d_state){
	$states = array("CA","NV","AZ","NM","KS","OK","TX","AR","MS","LA");
	if(in_array($o_state,$states) && in_array($d_state,$states)){return true;}
	return false;
}

?>