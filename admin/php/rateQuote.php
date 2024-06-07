<?php

// remove the others
$rates = $db->query("SELECT * FROM shipment_rate WHERE shipment_id='$id'");
if($rates){
	foreach($rates as $v){
		$rate_ids[]=$v["id"];
	}
	$db->query("DELETE FROM shipment_rate WHERE shipment_id = '$id'");
	$db->query("DELETE FROM shipment_rate_accessorial WHERE shipment_rate_id IN (".implode(",",$rate_ids).")");
}

// excluded carriers for this customer
$exclude_carrier_ids[]=0;
$excludes = $db->query("SELECT * FROM customer_carrier_exclude WHERE customer_id='".$shipment["customer_id"]."'");
if($excludes){
	foreach($excludes as $v){
		$exclude_carrier_ids[]=$v["carrier_id"];
	}
}


// do the rates
$autorate = $db->query("SELECT * FROM autorate WHERE type='".$shipment["service_type"]."' AND carrier_id NOT IN (".implode(",",$exclude_carrier_ids).")");
if($autorate){
	$orig_classes=$classes;
	foreach($autorate as $v){
		if($service_only){$service_only;}
		if($v["service_only"]){
			$service_only = explode(",",$v["service_only"]);
		}
		$carrier_id=$v["carrier_id"];
		$classes=$orig_classes;
		include_once($v["file"]);
	}

	// add in the accessorials and calculate their costs
	if($calc_rate_ids){

		$sql = "SELECT accessorial_id FROM shipment_accessorial WHERE shipment_id = '$id'";
		$accessorial_list=$db->query($sql);
		if($accessorial_list) {
			foreach($accessorial_list AS $v) {
				$accessorial_ids[] = $v["accessorial_id"];
			}
		}	
		$accessorial_ids[] = 11;
		$accessorial_ids[] = 12;


		$total_ins_temp = $db->query("SELECT insurance FROM shipment WHERE id='$id'");
		$total_ins=($total_ins_temp)?$total_ins_temp[0]["insurance"]:0;

		$total_weight_temp = $db->query("SELECT SUM(weight) AS weight FROM shipment_commodity WHERE shipment_id='$id'");
		$total_weight=($total_weight_temp)?$total_weight_temp[0]["weight"]:0;


		// get the carriers
		$carrier_ids_temp = $db->query("SELECT id,carrier_id FROM shipment_rate WHERE shipment_id='$id'");
		if($carrier_ids_temp){
			foreach($carrier_ids_temp as $v){
				$carrier_ids[$v["id"]]=$v["carrier_id"];
			}

			// grab the carriers costs
			$carrier_accessorials_arr = $db->query("SELECT * FROM carrier_accessorial WHERE carrier_id IN (".implode(",",$carrier_ids).") AND accessorial_id IN (".implode(",",$accessorial_ids).")");
			if($carrier_accessorials_arr){
				foreach($carrier_accessorials_arr as $v){
					$carrier_accessorials[$v["carrier_id"]][$v["accessorial_id"]]=$v;
				}
			}


		


			// calculate it
			foreach($calc_rate_ids as $shipment_rate_id){
				$carrier_id=$carrier_ids[$shipment_rate_id];

				foreach($accessorial_ids as $accessorial_id){
					$save_accessorial=true;
					$cost=0;
					$rate=0;

					if($carrier_accessorials[$carrier_id][$accessorial_id]){
						// carrier costs
						$a_arr = $carrier_accessorials[$carrier_id][$accessorial_id];
						switch($a_arr["type"]){
							case "ins":
								if($total_ins > 0){
									$cost=ceil($total_ins / 100) * $a_arr["cost"];
									$rate=ceil($total_ins / 100) * $a_arr["rate"];
								}							
								break;
							case "per100":
								$cost=($total_weight / 100) * $a_arr["cost"];
								$rate=($total_weight / 100) * $a_arr["rate"];
								break;
							case "percent":
								$percent_accessorials_cost[$shipment_rate_id][$a_arr["accessorial_id"]]=$a_arr;
								$percent_accessorials_rate[$shipment_rate_id][$a_arr["accessorial_id"]]=$a_arr;
								$save_accessorial=false;
								break;
							case "dollar":
							default:
								$cost=$a_arr["cost"];
								$rate=$a_arr["rate"];
								break;
						}
						if($save_accessorial){
							if($cost && ($cost < $a_arr["cost_min"]) && $a_arr["cost_min"] > 0){
								$cost=$a_arr["cost_min"];
							}
							if($cost && ($cost > $a_arr["cost_max"]) && $a_arr["cost_max"] > 0){
								$cost=$a_arr["cost_max"];
							}
							if($rate && ($rate < $a_arr["rate_min"]) && $a_arr["rate_min"] > 0){
								$rate=$a_arr["rate_min"];
							}
							if($rate && ($rate > $a_arr["rate_max"]) && $a_arr["rate_max"] > 0){
								$rate=$a_arr["rate_max"];
							}
						}
					}
					
					if($save_accessorial){
						$db->query("INSERT INTO shipment_rate_accessorial SET shipment_rate_id='$shipment_rate_id',accessorial_id='$accessorial_id',calc_cost='$cost',calc_rate='$rate',cost='$cost',rate='$rate'");
					}
				}
			}

			// percent updates
			if($percent_accessorials_cost){
				// grab the totals
				$totals_arr = $db->query("SELECT id,carrier_id,cost,rate FROM shipment_rate WHERE shipment_id='$id'");
				if($totals_arr){
					foreach($totals_arr as $v){
						$totals[$v["id"]]["cost"]=$v["cost"];
						$totals[$v["id"]]["rate"]=$v["rate"];

						$acc = $db->query("SELECT SUM(cost) AS cost, SUM(rate) AS rate FROM shipment_rate_accessorial WHERE shipment_rate_id='".$v["id"]."'");
						if($acc){
							foreach($acc as $vv){
								$totals[$v["id"]]["cost"]+=$vv["cost"];	
								$totals[$v["id"]]["rate"]+=$vv["rate"];								
							}
						}
					}
				}

				foreach($percent_accessorials_cost as $shipment_rate_id=>$arr){
					foreach($arr as $accessorial_id=>$v){
						$cost = 0;
						$rate = 0;
						
						if($totals[$shipment_rate_id]){
							$cost = round(($totals[$shipment_rate_id]["cost"] * ($v["cost"]/100)),2);
							$rate = round(($totals[$shipment_rate_id]["rate"] * ($v["rate"]/100)),2);
						}
						if($rate && ($rate < $v["rate_min"]) && $v["rate_min"] > 0){
							$rate=$v["rate_min"];
						}
						if($rate && ($rate > $v["rate_max"]) && $v["rate_max"] > 0){
							$rate=$v["rate_max"];
						}

						$db->query("INSERT INTO shipment_rate_accessorial SET shipment_rate_id='$shipment_rate_id',accessorial_id='$accessorial_id',calc_cost='$cost',calc_rate='$rate',cost='$cost',rate='$rate'");
					}
				}
			}


			// customer markups
			$customer_accessorials_arr = $db->query("SELECT * FROM customer_accessorial WHERE customer_id='".$shipment["customer_id"]."' AND carrier_id IN (".implode(",",$carrier_ids).") AND accessorial_id IN (".implode(",",$accessorial_ids).")");
			if($customer_accessorials_arr){
				foreach($customer_accessorials_arr as $v){
					$customer_accessorials_all[$v["carrier_id"]][$v["accessorials_id"]]=$v;
				}

				foreach($calc_rate_ids as $shipment_rate_id){
					$shipment_rate_accessorial = $db->query("SELECT shipment_rate.carrier_id,shipment_rate_accessorial.* FROM shipment_rate_accessorial INNER JOIN shipment_rate ON shipment_rate_accessorial.shipment_rate_id=shipment_rate.id WHERE shipment_rate_accessorial.shipment_rate_id='$shipment_rate_id'");
					if($shipment_rate_accessorial){
						foreach($shipment_rate_accessorial as $v){
							if($customer_accessorials_all[$v["carrier_id"]]){
								$customer_accessorials = $customer_accessorials_all[$v["carrier_id"]];

								if($customer_accessorials[$v["accessorials_id"]] && $customer_accessorials[$v["accessorials_id"]]["rate"] > 0){
									$rate = round($v["cost"] + ($v["cost"] * ($customer_accessorials[$v["accessorials_id"]]["rate"]/100)),2);
																
									if($rate && ($rate < $customer_accessorials[$v["accessorials_id"]]["rate_min"]) && $customer_accessorials[$v["accessorials_id"]]["rate_min"] > 0){
										$rate=$customer_accessorials[$v["accessorials_id"]]["rate_min"];
									}
									if($rate && ($rate > $customer_accessorials[$v["accessorials_id"]]["rate_max"]) && $customer_accessorials[$v["accessorials_id"]]["rate_max"] > 0){
										$rate=$customer_accessorials[$v["accessorials_id"]]["rate_max"];
									}

									$db->query("UPDATE shipment_rate_accessorial SET calc_rate='$rate',rate='$rate' WHERE id='".$v["id"]."'");
								}
							}
						}
					}
				}
			}
		}				
	}

	// log any errors
	if($rate_errors){
		foreach($rate_errors as $carrier_id=>$arr){
			foreach($arr as $message){
				$db->query("INSERT INTO autorate_error SET date_error=NOW(),shipment_id='$id',carrier_id='$carrier_id',message='$message'");
			}
		}
	}
}

?>