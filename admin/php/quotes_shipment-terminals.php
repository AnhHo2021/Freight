<?php

$terminals=$db->query("SELECT carrier_terminal.* FROM shipment_rate INNER JOIN carrier_terminal ON shipment_rate.carrier_id=carrier_terminal.carrier_id WHERE shipment_rate.id='$rate_id' ORDER BY carrier_terminal.name");
if($terminals){
	
	$shipment=$db->query("SELECT o_zip,d_zip FROM shipment WHERE id='$shipment_id'");
	if($shipment){
		$shipment=$shipment[0];
	}else{die();}

	// get the selected items
	$odata = $db->query("SELECT * FROM zip WHERE zip='".$shipment["o_zip"]."'");
	if($odata){
		$o_term_id=getNearestTerminal($odata[0]["latitude"],$odata[0]["longitude"],$terminals[0]["carrier_id"]);
	}

	$ddata = $db->query("SELECT * FROM zip WHERE zip='".$shipment["d_zip"]."'");
	if($ddata){
		$d_term_id=getNearestTerminal($ddata[0]["latitude"],$ddata[0]["longitude"],$terminals[0]["carrier_id"]);
	}

	echo "var elem = document.getElementById('shipped_o_carrier_terminal_id');";

	// terminals
	foreach($terminals as $v){
		foreach($v as $nn=>$vv){
			$v[$nn]=str_replace("'","\'",$vv);
		}

		echo "var elOptNew = document.createElement('option');";
		echo "elOptNew.text = '".$v["code"]." - ".$v["name"] ." - ". $v["city"] .", ". $v["state"] .", ".$v["zip"]."';";
		echo "elOptNew.value = '".$v["id"]."';";
		if($o_term_id == $v["id"]){
			echo "elOptNew.selected = true;";
		}
		echo "elem.add(elOptNew);";
	}



	echo "var elem = document.getElementById('shipped_d_carrier_terminal_id');";

	// terminals
	foreach($terminals as $v){
		foreach($v as $nn=>$vv){
			$v[$nn]=str_replace("'","\'",$vv);
		}
		echo "var elOptNew = document.createElement('option');";
		echo "elOptNew.text = '".$v["code"]." - ".$v["name"] ." - ". $v["city"] .", ". $v["state"] .", ".$v["zip"]."';";
		echo "elOptNew.value = '".$v["id"]."';";
		if($d_term_id == $v["id"]){
			echo "elOptNew.selected = true;";
		}
		echo "elem.add(elOptNew);";
	}
}



function getNearestTerminal($lat,$lon,$carrier_id){
	global $db;

	$sql = "SELECT carrier_terminal.id, ".
			" ROUND(sqrt(power(69.1*(zip.latitude - $lat),2)+ power(69.1*(zip.longitude - $lon)*cos(zip.latitude/57.3),2)),2) AS themiles ".
			" FROM ".
			"	carrier_terminal INNER JOIN zip ON ".
			"	carrier_terminal.zip=zip.zip ".
			" WHERE ".
			"	carrier_terminal.carrier_id='$carrier_id' ".
			" ORDER BY themiles ASC ".
			" LIMIT 1";
	$nearest = $db->query($sql);
	if($nearest){
		return $nearest[0]["id"];
	}
	return 0;
}

?>