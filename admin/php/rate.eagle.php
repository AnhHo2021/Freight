<?php

$addonDF=.15;
$minDF=25;
$addonGZ=.35;
$minGZ=35;

// quotes shipments using eagle data
if($pieces && $weights && $dims){

	// get the points
	$points = $db->query("SELECT points.*,states.zonenum FROM eagle.points INNER JOIN eagle.states ON points.state=states.state WHERE points.zipcode IN ('".$shipment["o_zip"]."','".$shipment["d_zip"]."')");
	if($points){
		foreach($points as $v){
			if($v["zipcode"]==$shipment["o_zip"]){
				$orig_points=$v;
			}
			if($v["zipcode"]==$shipment["d_zip"]){
				$dest_points=$v;
			}
		}
		if($orig_points && $dest_points){
			// get the billable region (zone)
			$region = $db->query("SELECT region FROM eagle.regions WHERE from_zonenum='".$orig_points["zonenum"]."' AND to_zonenum='".$dest_points["zonenum"]."'");
			if($region){
				// change region to "C" if beyond
				$rated_region = $region[0]["region"];
				$real_region = $rated_region;
				if(in_array($rated_region,array("D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"))){
					$rated_region="C";
				}

				// get the rates
				$rates_temp = $db->query("SELECT * FROM eagle.rates WHERE region='$rated_region'");
				if($rates_temp){
					foreach($rates_temp as $v){
						$rates[$v["service_id"]]=$v["rate"];
					}

					// grab the costs
					$carrier_costs_temp = $db->query("SELECT * FROM carrier_cost WHERE carrier_id='$carrier_id' AND type='markup'");
					if($carrier_costs_temp){
						foreach($carrier_costs_temp as $v){
							$carrier_costs_all[$v["service_id"]][$v["direction"]]=$v;
						}
					}

					// customer specific rates
					$customer_rates_temp = $db->query("SELECT * FROM customer_rate WHERE customer_id='".$shipment["customer_id"]."' AND carrier_id='$carrier_id' AND type='markup'");
					if($customer_rates_temp){
						foreach($customer_rates_temp as $v){
							$customer_rates_all[$v["service_id"]][$v["direction"]]=$v;
						}
					}

					if($carrier_costs_all){
						// loop through services offered
						$services = $db->query("SELECT * FROM carrier_services WHERE carrier_id='$carrier_id'");
						if($services){
							// if we are only doing certain services, remove from array
							if($service_only){
								foreach($services as $v){
									if(in_array($v["service_id"],$service_only)){
										$new_services=$v;
									}
								}
								$services=$new_services;
							}

							foreach($services as $v){
								if($rates[$v["service_id"]] && $carrier_costs_all[$v["service_id"]]){
									$carrier_costs = $carrier_costs_all[$v["service_id"]];
									
									$direction="";
									if($shipment["o_state"]==$shipment["d_state"] && $carrier_costs["intrastate"]["rate"]>0){
										$direction = "intrastate";
									}
									if($shipment["o_state"]!=$shipment["d_state"] && $carrier_costs["interstate"]["rate"]>0){
										$direction = "interstate";
									}

									if($direction){
										$main_cost = $carrier_costs[$direction]["cost"]/100;
										$main_rate = $carrier_costs[$direction]["rate"]/100;
										$min_cost = $carrier_costs[$direction]["min_cost"];
										$min_rate = $carrier_costs[$direction]["min_rate"];

										// customer specific rates
										if($customer_rates_all[$v["service_id"]]){
											$customer_rates=$customer_rates_all[$v["service_id"]];
											if($shipment["o_state"]==$shipment["d_state"] && $customer_rates["intrastate"]["rate"]>0){
												$direction = "intrastate";
											}
											if($shipment["o_state"]!=$shipment["d_state"] && $customer_rates["interstate"]["rate"]>0){
												$direction = "interstate";
											}
											if($customer_rates[$direction]["rate"] > 0){	
												$main_rate = $customer_rates[$direction]["rate"]/100;
											}
											if($customer_rates[$direction]["min_rate"] > 0){
												$min_rate = $customer_rates[$direction]["min_rate"];
											}
										}
										
										switch($v["service_id"]){
											case 2:
												$transit=1;break;
											case 4:
												$transit=2;break;
											case 6:
												$transit=5;break;
											default:
												$transit=0;
										}
										
										// get billable weight
										$chg_weight = getChgWeight($v["service_id"]);


										$cost=round($chg_weight * $rates[$v["service_id"]],2);

										// d-f addon cost
										if(in_array($real_region,array("D","E","F"))){
											$addon_cost=round($chg_weight * $addonDF,2); 
											if($addon_cost<$minDF){$addon_cost=$minDF;}
											$cost+=$addon_cost;
										}

										// g-z addon cost
										if(in_array($real_region,array("G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"))){
											$addon_cost=round($chg_weight * $addonGZ,2); 
											if($addon_cost<$minGZ){$addon_cost=$minGZ;}
											$cost+=$addon_cost;
											$transit++;
										}

										$rate=round(($cost * $main_rate)+$cost,2);
										if($cost < $min_cost){$cost=$min_cost;}
										if($rate < $min_rate){$rate=$min_rate;}

										$db->query("INSERT INTO shipment_rate SET shipment_id='$id',carrier_id='$carrier_id',service_id='".$v["service_id"]."',transit_days='".$transit."',calc_cost='".$cost."',cost='".$cost."',calc_rate='".$rate."',rate='".$rate."'");
										$calc_rate_ids[]=$db->lastid();
									}
								}
							}
						}
						
					}else{
						$rate_errors[$carrier_id][]='No Carrier Costs Found - must be "markup"';
					}
				}else{
					$rate_errors[$carrier_id][]='Rates not found.';
				}
			}else{
				$rate_errors[$carrier_id][]='Region not found.';
			}
		}else{
			$rate_errors[$carrier_id][]='Origin & Destination points not found.';
		}	
	}
}



function getChgWeight($service_id){
	global $dims,$pieces,$weights;

	$dim_factor = 195;

	// hub truck service
	if($service_id == 6){
		$dim_factor = 250;
	}

	$dim_weight = 0;
	foreach($dims as $n=>$v){
		$vs=explode(":",$v);
		$dim_weight += (($vs[0] * $vs[1] * $vs[2])/$dim_factor) * $pieces[$n];
		$act_weight += $weights[$n];
	}
	$dim_weight=round($dim_weight,2);

	$chg_weight = $act_weight;
	if($dim_weight > $act_weight){
		$chg_weight = $dim_weight;
	}	
	return $chg_weight;
}

?>