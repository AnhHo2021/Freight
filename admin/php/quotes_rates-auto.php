<?php

$debug=true;


// shipment data
$shipment = $db->query("SELECT * FROM shipment WHERE id='$id'");
if($shipment){
	$shipment=$shipment[0];

	$commodities = $db->query("SELECT shipment_commodity.*,class_list.class FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id WHERE shipment_id='$id'");	
	if($commodities){
		foreach($commodities as $v){
			if($v["class"]){
				if($v["class"]=="92.5"){$v["class"]="92";}
				if($v["class"]=="77.5"){$v["class"]="77";}
				$classes[]=$v["class"];	
			}
			if($v["weight"]){
				$weights[]=$v["weight"];	
			}
			if($v["dim_d"] && $v["dim_w"] && $v["dim_h"]){
				$dims[]=$v["dim_d"].":".$v["dim_w"].":".$v["dim_h"];	
			}
			if($v["pieces"]){
				$pieces[]=$v["pieces"];
			}
		}
	}
	
	//include("/opt/www/htdocs/admin.norwayfreight.com/php/rateQuote.php");
	include("/mnt/web/transport/transport/freight/admin/php/rateQuote.php");
		
	echo '<script>parent.location.href=\'index.php?action=quotes_edit&id='.$id.'\';</script>';
	die();
}


?>