<?php
$html["LOCATION"] = "<h1>ACCOUNTING : EDIT</h1>";

if(!empty($_POST)){
	foreach($_POST as $k=>$v) {
		if(strstr($k,"cost") || strstr($k,"rate")) {
			$data_array = explode("__",$k);
			if(!$data_array[2]) {
				$rate_array[$data_array[1]][$data_array[0]] = $v;
			} else {
				$accessorial_array[$data_array[1]][$data_array[2]][$data_array[0]] = $v;
			}
		}
	}
	//foreach($rate_array AS $real_rate_array) {}

	$sql = "DELETE FROM shipment_rate_accessorial WHERE shipment_rate_id = '$data_array[1]'";
	$success = $db->query($sql);

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

	$sql = "UPDATE shipment_rate SET ";
	$sql .= " cost = '".$rate_array[$rate_id]["cost"]."', rate = '".$rate_array[$rate_id]["rate"]."' WHERE id='$rate_id'";
	$success = $db->query($sql);

	$invoice_id=$_GET["id"];

	$sql = "SELECT shipment_rate.*,shipment.container_price,shipment.container_cost
	FROM shipment_rate
	LEFT JOIN shipment ON shipment.id = shipment_rate.shipment_id
	WHERE shipment_rate.id = '$rate_id'";
	$rate_info_array=$db->query($sql);
	$rate_info_array=$rate_info_array[0];
	$total_rate = $rate_info_array["rate"]; 
	$total_cost = $rate_info_array["cost"];
    //anh
    if(is_numeric($rate_info_array["container_price"])){
        $total_rate +=$rate_info_array["container_price"];
    }
    if(is_numeric($rate_info_array["container_cost"])){
        $total_cost +=$rate_info_array["container_cost"];
    }

    $sql = "SELECT * FROM shipment_rate_accessorial WHERE shipment_rate_id = '$rate_id'";
	$accessorial_info_array=$db->query($sql);
	
	if($accessorial_info_array){
		foreach($accessorial_info_array AS $accessorial_info) {
			$total_rate += $accessorial_info["rate"]; 
			$total_cost += $accessorial_info["cost"];
		}
	}
	$total_profit = $total_rate-$total_cost;
	
	$sql = "UPDATE invoice SET
	cost = '$total_cost',
	total = '$total_rate',
	profit = '$total_profit',
	total_due = '$total_rate',
	due_date = '$_POST[due_date]',
	invoice_date = '$_POST[invoice_date]'
	WHERE id = '$invoice_id'";
	$success=$db->query($sql);
	$vars["message"] = "Your invoice was successfully saved<br>";
}
if(!$vars["message"]) {
	$vars["message"] = "";
}
$sql = "SELECT i.id,i.invoice_date,i.due_date,i.shipment_id,i.status,s.shipped_tracknum,s.invoice_final,s.invoice_final_date FROM invoice i inner join shipment s on i.shipment_id=s.id WHERE i.id='$_GET[id]'";
$invoice_array = $db->query($sql);
if(!$invoice_array){die("No Invoice Found");}
$invoice_array[0]["invoice_final_date"] = ($invoice_array[0]["invoice_final_date"] != "0000-00-00 00:00:00")?date("m/d/Y g:i a",strtotime($invoice_array[0]["invoice_final_date"])):"Not Available";
$invoice_info = $invoice_array[0];

$sql = "SELECT container_price,container_cost, shipment_rate.*,carrier.company FROM shipment INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id LEFT JOIN carrier ON shipment_rate.carrier_id=carrier.id WHERE shipment_id = '$invoice_info[shipment_id]'";
$shipment_rates_array = $db->query($sql);
$rate_info = $shipment_rates_array[0];
$sql = "SELECT s.*,a.name,a.id AS real_accessorial_id FROM shipment_rate_accessorial s INNER JOIN accessorial a ON s.accessorial_id = a.id WHERE shipment_rate_id = '$rate_info[id]'";
$accessorial_array = $db->query($sql);
//GETTING SERVICE
$sql = "SELECT * FROM service";
$service = $db->query($sql);
if($shipment_rates_array) {
	foreach($shipment_rates_array AS $v) {
		$v["total_cost"]=$v["cost"] + $v["container_cost"];
		$v["total_rate"]=$v["rate"] + $v["container_price"];
		$v["profit"] = number_format($v["rate"] - $v["cost"],2); 
		$v["percent"] = ($v["cost"] != 0) ?	number_format($v["profit"] / $v["cost"] * 100,2) : number_format(0,2);
		if($accessorial_array) {
			foreach($accessorial_array AS $vv) {
				$vv["profit"] = number_format($vv["rate"] - $vv["cost"],2); 
				$vv["percent"] = ($vv["cost"] != 0) ?	number_format($vv["profit"] / $vv["cost"] * 100,2) : number_format(0,2);
				$v["total_cost"] += $vv["cost"];
				$v["total_rate"] += $vv["rate"];
				$vv["id"] = $v["id"];
				$vv["accessorial_id"] = $v["accessorial_id"];

				if($invoice_info["status"]=="open" && $invoice_info["invoice_final"]=="0"){
					$v["accounting_rates_list_table_row"] .= replace($vv, rf($htmlpath."accounting_rates_list_table_row.html"));
				}else{
					$v["accounting_rates_list_table_row"] .= replace($vv, rf($htmlpath."accounting_viewrates_list_table_row.html"));
				}
			}
		} else {$v["accounting_rates_list_table_row"] = "";};
		$v["total_profit"] = number_format($v["total_rate"] - $v["total_cost"],2);
		$v["total_percent"] = ($v["total_cost"] != 0) ?	number_format($v["total_profit"] / $v["total_cost"] * 100,2) : number_format(0,2);
		$v["total_cost"] = number_format($v["total_cost"],2);
		$v["total_rate"] = number_format($v["total_rate"],2);
		$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
		foreach($service AS $t) {
			$s = ($v["service_id"] == $t["id"]) ? " SELECTED" : "";
			$v["service_list"] .= "<option value=$t[id]$s>$t[name]</option>";
		}

		if($invoice_info["status"]=="open" && $invoice_info["invoice_final"]=="0"){
			$vars["accounting_rates"] = replace($v, rf($htmlpath."accounting_rates.html"));
		}else{
			$vars["accounting_rates"] = replace($v, rf($htmlpath."accounting_viewrates.html"));
		}
	}
}
foreach($invoice_info AS $gg=>$hh) {
	$vars[$gg] = $hh;
}


$vars["javascript"]="";
if($print=="true"){
	$vars["javascript"]='window.open("./?action=accounting_listprint&id='.$id.'","accounting_print","top=0,left=0,width=850,height=700,scrollbars=yes,menu=no,resizable=yes");';
}

if($invoice_info["status"]=="open" && $invoice_info["invoice_final"]=="0"){
	$html["BODY"]=replace($vars,rf($htmlpath."accounting_edit.html"));
}else{
	$vars["invoice_date"]=sysDate($vars["invoice_date"]);
	$vars["due_date"]=sysDate($vars["due_date"]);
	$html["BODY"]=replace($vars,rf($htmlpath."accounting_view.html"));
}

?>