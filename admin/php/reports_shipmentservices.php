<?php

if($_LOGIN["access_level"] != "admin"){
	header("Location: /?action=reports");
	die();
}

$html["LOCATION"] = "<h1>REPORTS : SHIPMENT SERVICES</h1>";
$REPORT="No Report Results";

$services_list = $db->query("SELECT * FROM service");
foreach($services_list as $v){
	$s=is_array($services) && in_array($v["id"],$services) ? "selected" : "";
	$html["services"].='<option value="'.$v["id"].'"'.$s.'>'.$v["name"].'</option>';
}

if($do == "run"){
	$filter .= ($start_date)?"AND TO_DAYS(shipment.shipped_pickup_date) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
	$filter .= ($end_date)?"AND TO_DAYS(shipment.shipped_pickup_date) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
	//$filter .= ($start_date)?"AND TO_DAYS(invoice.invoice_date) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
	//$filter .= ($end_date)?"AND TO_DAYS(invoice.invoice_date) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
	$filter .= ($services)?"AND shipment.service_id IN ('".implode("','",$services)."') ":"";
}


$sql = "SELECT shipment.shipped_carrier_id AS carrier_id,carrier.company as carrier,service.id AS service_id,service.name as service,SUM(shipment_rate.rate) AS rate,SUM(shipment_rate.cost) AS cost, SUM(shipment_rate.rate)-SUM(shipment_rate.cost) AS profit FROM shipment INNER JOIN invoice ON shipment.invoice_id=invoice.id AND shipment.status!='quote' INNER JOIN carrier ON shipment.shipped_carrier_id=carrier.id INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id INNER JOIN service ON service.id=shipment.service_id WHERE 0=0 AND shipment.status != 'cancelled' ".$filter." GROUP BY shipment.shipped_carrier_id,shipment.service_id ORDER BY carrier.company";
$sel_report = $db->query($sql);

if($sel_report){
	$sql = "SELECT shipment_rate.carrier_id,shipment_rate.service_id,accessorial.id AS accessorial_id,accessorial.name AS accessorial,SUM(shipment_rate_accessorial.rate) AS rate,SUM(shipment_rate_accessorial.cost) AS cost, SUM(shipment_rate_accessorial.rate)-SUM(shipment_rate_accessorial.cost) AS profit FROM shipment INNER JOIN invoice ON shipment.invoice_id=invoice.id AND shipment.status!='quote' INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id INNER JOIN service ON service.id=shipment.service_id INNER JOIN shipment_rate_accessorial ON shipment_rate_accessorial.shipment_rate_id=shipment_rate.id INNER JOIN accessorial ON shipment_rate_accessorial.accessorial_id=accessorial.id WHERE 0=0 AND shipment.status !='cancelled'  ".$filter." GROUP BY shipment_rate.carrier_id,shipment_rate.service_id,shipment_rate_accessorial.accessorial_id";
	$accessorials = $db->query($sql);
	if($accessorials){
		foreach($accessorials as $v){
			if($v["cost"]>0 || $v["rate"]>0 || $v["profit"]>0){
				$sorted_accessorials[$v["carrier_id"]][$v["service_id"]][$v["accessorial_id"]]=$v;
			}
		}
	}

	//setup array
	foreach($sel_report as $v){
		$dataArray[$v["carrier_id"]][] = $v;
	}
	
	if($dataArray){
		foreach($dataArray as $n=>$v){
			$vv["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;
		
			$i=0;
			foreach($v AS $items) {
				//assign row template
				$row = ($i == 0) ? "reports_shipmentservices_row.html" : "reports_shipmentservices_row_2.html";				

				$vv["cost_total"] = 0;
				$vv["rate_total"] = 0;
				$vv["profit_total"] = 0;				

				//totals
				$vv["cost_total"] += $items["cost"];
				$vv["rate_total"] += $items["rate"];
				$vv["profit_total"] += $items["profit"];
				
				$tots["cost_total"] += $items["cost"];
				$tots["rate_total"] += $items["rate"];
				$tots["profit_total"] += $items["profit"];

				//row values
				$vv["carrier"] = $items["carrier"];
				$vv["service"] = $items["service"];
				$vv["accessorial"] = "Freight Charges";
				$vv["cost"] = $items["cost"];
				$vv["rate"] = $items["rate"];
				$vv["profit"] = $items["profit"];
				$vv["margin"] = ($items["cost"]>0)?($items["profit"]/$items["cost"])*100:0;

				$vv["cost"]=number_format($vv["cost"],2);
				$vv["rate"]=number_format($vv["rate"],2);
				$vv["profit"]=number_format($vv["profit"],2);
				$vv["margin"]=number_format($vv["margin"],2);
				
				//replace row vars
				$vars["reports_row"] .= replace($vv, rf($htmlpath.$row));
				$i++;

				// accessorials
				if($sorted_accessorials[$items["carrier_id"]][$items["service_id"]]){
					foreach($sorted_accessorials[$items["carrier_id"]][$items["service_id"]] as $xx){
						$xx["bg"]=$vv["bg"];

						$vv["cost_total"] += $xx["cost"];
						$vv["rate_total"] += $xx["rate"];
						$vv["profit_total"] += $xx["profit"];
						
						$tots["cost_total"] += $xx["cost"];
						$tots["rate_total"] += $xx["rate"];
						$tots["profit_total"] += $xx["profit"];
						
						$xx["margin"] = ($xx["cost"]>0)?($xx["profit"]/$xx["cost"])*100:0;
						$xx["margin"] = number_format($xx["margin"],2);

						$vars["reports_row"] .= replace($xx, rf($htmlpath."reports_shipmentservices_row_3.html"));
					}
				}

				// totals			
				$vv["margin_total"] = ($vv["cost_total"]>0)?($vv["profit_total"]/$vv["cost_total"])*100:0;

				$vv["cost_total"]=number_format($vv["cost_total"],2);
				$vv["rate_total"]=number_format($vv["rate_total"],2);
				$vv["profit_total"]=number_format($vv["profit_total"],2);
				$vv["margin_total"]=number_format($vv["margin_total"],2);

				$vars["reports_row"] .= replace($vv, rf($htmlpath."reports_shipmentservices_total.html"));
			}
		}				
		$tots["margin_total"] = ($tots["cost_total"]>0)?($tots["profit_total"]/$tots["cost_total"])*100:0;

		$tots["cost_total"]=number_format($tots["cost_total"],2);
		$tots["rate_total"]=number_format($tots["rate_total"],2);
		$tots["profit_total"]=number_format($tots["profit_total"],2);
		$tots["margin_total"]=number_format($tots["margin_total"],2);
		
		$vars["reports_row"] .= replace($tots, rf($htmlpath."reports_shipmentservices_grand_total.html"));
	}
}else{
	$vars["reports_row"] = "<tr><td colspan='7' align='center'><b>No Report Results</b></td></tr>";
}

$v["asc"]=($orderdir == "ASC")?"DESC":"ASC";
$REPORT = replace($vars, rf($htmlpath."reports_shipmentservices_table.html"));

//set filter values
$vars["start_date"] = ($start_date)?$start_date:"";
$vars["end_date"] = ($end_date)?$end_date:"";

if($print=="true"){
	$set_template="template.reportprint.html";
	$html["BODY"]=$REPORT;
	$html["REPORT_NAME"]="SHIPMENT SERVICES";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_shipmentservices.html"));
}

?>