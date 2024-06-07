<?php


if($_LOGIN["access_level"] != "admin"){
	header("Location: /?action=reports");
	die();
}

$html["LOCATION"] = "<h1>REPORTS : CARRIER VOLUME</h1>";
$REPORT="No Report Results";

if($do == "run"){
	$filter .= ($start_date)?"AND TO_DAYS(invoice.invoice_date) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
	$filter .= ($end_date)?"AND TO_DAYS(invoice.invoice_date) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
}

if(!$orderby){$orderby="carrier.company";$orderdir="ASC";}
$html["orderby"]=$orderby;
$html["orderdir"]=$orderdir;
$vars["asc"]=($orderdir == "ASC")?"DESC":"ASC";

$sql = "SELECT shipment.shipped_carrier_id AS carrier_id,carrier.company as carrier, COUNT(shipment.id) AS num_shipments, SUM(shipment_rate.transit_days) AS transit_days,SUM(invoice.total) AS revenue,SUM(invoice.cost) AS cost, SUM(invoice.profit) AS profit FROM shipment INNER JOIN invoice ON shipment.invoice_id=invoice.id AND shipment.status='invoiced' INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id INNER JOIN carrier ON carrier.id=shipment.shipped_carrier_id WHERE 0=0 AND shipment.status !='cancelled' ".$filter." GROUP BY shipment.shipped_carrier_id ORDER BY ".$orderby." ".$orderdir;
$sel_report = $db->query($sql);

if($sel_report){
	//assign row template
	$row = "reports_carriervolume_row.html";

	foreach($sel_report AS $v){
		$totals["shipments"]+=$v["num_shipments"];
		$totals["revenue"]+=$v["revenue"];
		$totals["cost"]+=$v["cost"];
		$totals["profit"]+=$v["profit"];
	}

	foreach($sel_report AS $v){
		//row values
		$vv["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;
		$vv["carrier_id"] = $v["carrier_id"];
		$vv["carrier"] = $v["carrier"];
		
		$v["avg_transit"]=$v["transit_days"]/$v["num_shipments"];
		$v["avg_revenue"]=$v["revenue"]/$v["num_shipments"];
		$v["avg_cost"]=$v["cost"]/$v["num_shipments"];
		$v["avg_profit"]=$v["profit"]/$v["num_shipments"];

		$v["margin"]=($v["cost"]>0)?($v["profit"]/$v["cost"])*100:0;


		$v["perc_shipment"]=($totals["shipments"]>0)?($v["num_shipments"]/$totals["shipments"])*100:0;
		$v["perc_revenue"]=($totals["revenue"]>0)?($v["revenue"]/$totals["revenue"])*100:0;
		$v["perc_cost"]=($totals["cost"]>0)?($v["cost"]/$totals["cost"])*100:0;
		$v["perc_profit"]=($totals["profit"]>0)?($v["profit"]/$totals["profit"])*100:0;


		//totals
		$tot["transit_days"] += $v["transit_days"];
		$tot["num_shipments_total"] += $v["num_shipments"];
		$tot["revenue_total"] += $v["revenue"];
		$tot["cost_total"] += $v["cost"];
		$tot["profit_total"] += $v["profit"];

		$vv["num_shipments"] = number_format($v["num_shipments"]);
		$vv["avg_transit"] = number_format($v["avg_transit"],2);
		$vv["perc_shipment"] = number_format($v["perc_shipment"],2);
		$vv["revenue"] = number_format($v["revenue"],2);
		$vv["avg_revenue"] = number_format($v["avg_revenue"],2);
		$vv["perc_revenue"] = number_format($v["perc_revenue"],2);
		$vv["cost"] = number_format($v["cost"],2);
		$vv["avg_cost"] = number_format($v["avg_cost"],2);
		$vv["perc_cost"] = number_format($v["perc_cost"],2);
		$vv["profit"] = number_format($v["profit"],2);
		$vv["avg_profit"] = number_format($v["avg_profit"],2);
		$vv["perc_profit"] = number_format($v["perc_profit"],2);
		$vv["margin"] = number_format($v["margin"],2);
		
		//replace row vars
		$vars["reports_row"] .= replace($vv, rf($htmlpath.$row));
	}

	$tot["avg_transit_total"]=$tot["transit_days"]/$tot["num_shipments_total"];
	$tot["avg_revenue_total"]=$tot["revenue_total"]/$tot["num_shipments_total"];
	$tot["avg_cost_total"]=$tot["cost_total"]/$tot["num_shipments_total"];
	$tot["avg_profit_total"]=$tot["profit_total"]/$tot["num_shipments_total"];

	$tot["margin_total"]=($tot["cost_total"]>0)?($tot["profit_total"]/$tot["cost_total"])*100:0;

	$tot["num_shipments_total"] = number_format($tot["num_shipments_total"]);
	$tot["avg_transit_total"] = number_format($tot["avg_transit_total"],2);
	$tot["revenue_total"] = number_format($tot["revenue_total"],2);
	$tot["avg_revenue_total"] = number_format($tot["avg_revenue_total"],2);
	$tot["cost_total"] = number_format($tot["cost_total"],2);
	$tot["avg_cost_total"] = number_format($tot["avg_cost_total"],2);
	$tot["profit_total"] = number_format($tot["profit_total"],2);
	$tot["avg_profit_total"] = number_format($tot["avg_profit_total"],2);
	$tot["margin_total"] = number_format($tot["margin_total"],2);

	$vars["reports_row"] .= replace($tot, rf($htmlpath."reports_carriervolume_total.html"));
}else{
	$vars["reports_row"] = "<tr><td colspan='13' align='center'><b>No Report Results</b></td></tr>";
}

$v["asc"]=($orderdir == "ASC")?"DESC":"ASC";
$REPORT = replace($vars, rf($htmlpath."reports_carriervolume_table.html"));

//set filter values
$vars["start_date"] = ($start_date)?$start_date:"";
$vars["end_date"] = ($end_date)?$end_date:"";

if($print=="true"){
	$set_template="template.reportprint.html";
	$html["REPORT_NAME"]="CARRIER VOLUME";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
	$html["BODY"]=$REPORT;
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_carriervolume.html"));
}

?>