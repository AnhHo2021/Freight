<?php

$invoice_totals = $db->query("SELECT shipment_id,cost,total FROM invoice");
$shipment_totals = $db->query("SELECT shipment.id AS shipment_id,shipment_rate.id AS shipment_rate_id, shipment_rate.cost,shipment_rate.rate FROM shipment INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id");

foreach($invoice_totals as $v){
	$shipments[$v["shipment_id"]]["invoice_cost"]=$v["cost"];
	$shipments[$v["shipment_id"]]["invoice_rate"]=$v["total"];
}

foreach($shipment_totals as $v){
	$shipments[$v["shipment_id"]]["shipment_cost"]=$v["cost"];
	$shipments[$v["shipment_id"]]["shipment_rate"]=$v["rate"];
	$shipments[$v["shipment_id"]]["shipment_rate_id"]=$v["shipment_rate_id"];
}

foreach($shipments as $shipment_id=>$arr){
	$shipment_accessorial_totals = $db->query("SELECT cost, rate FROM shipment_rate_accessorial WHERE shipment_rate_id='$arr[shipment_rate_id]'");
	if($shipment_accessorial_totals){
		foreach($shipment_accessorial_totals as $vv){
			$shipments[$shipment_id]["shipment_cost"]+=$vv["cost"];
			$shipments[$shipment_id]["shipment_rate"]+=$vv["rate"];		
		}
	}
}

foreach($shipments as $shipment_id=>$arr){
	$arr["invoice_cost"]=number_format($arr["invoice_cost"],2,".","");
	$arr["shipment_cost"]=number_format($arr["shipment_cost"],2,".","");
	$arr["invoice_rate"]=number_format($arr["invoice_rate"],2,".","");
	$arr["shipment_rate"]=number_format($arr["shipment_rate"],2,".","");
	if($arr["invoice_cost"] != $arr["shipment_cost"] || $arr["invoice_rate"] != $arr["shipment_rate"]){
		echo "$shipment_id UPDATE $arr[invoice_cost] != $arr[shipment_cost] || $arr[invoice_rate] != $arr[shipment_rate]<br>";
	}
}
die();

?>