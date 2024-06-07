<?php

$html["LOCATION"] = "<h1>REPORTS : REVENUE REPORT</h1>";
$REPORT="No Report Results";

if($do == "run"){
	$filter .= ($start_date)?"AND TO_DAYS(shipment.invoice_final_date) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
	$filter .= ($end_date)?"AND TO_DAYS(shipment.invoice_final_date) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
	$filter .= ($reps)?"AND admin_login.id IN ('".implode("','",$reps)."') ":"";
}


$sql = "SELECT CONCAT(admin_login.fname,' ',admin_login.lname) as rep, service.name as service, SUM(invoice.cost) AS cost, SUM(invoice.profit) AS profit, SUM(invoice.total) as rate FROM invoice INNER JOIN shipment ON shipment.invoice_id=invoice.id AND shipment.status='invoiced' INNER JOIN admin_login ON admin_login.id=shipment.sales_admin_login_id INNER JOIN service ON service.id=shipment.service_id WHERE 0=0 ". str_replace("customer.","shipment.",$SALES_REP_FILTER) ." AND shipment.status ='invoiced' ".$filter." GROUP BY shipment.sales_admin_login_id,shipment.service_id ORDER BY rep,service.group,service.name";
$sel_report = $db->query($sql);

if($sel_report){

	//setup array
	foreach($sel_report as $v){
		$dataArray[$v["rep"]][] = $v;
	}
	
	if($dataArray){
		foreach($dataArray as $n=>$v){
			$vv["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;
			$vv["cost_total"] = 0;
			$vv["rate_total"] = 0;
			$vv["profit_total"] = 0;
			$vv["margin_total"] = 0;
			
			$i=0;
			$mytots["cost_total"] = 0;
			$mytots["rate_total"] = 0;
			$mytots["profit_total"] = 0;
			foreach($v AS $items) {
				//assign row template
				$row = ($i == 0) ? "reports_revenue_row.html" : "reports_revenue_row_2.html";
	
				//row values
				$vv["rep"] = $items["rep"];
				$vv["service"] = $items["service"];
				$vv["cost"] = $items["cost"];
				$vv["rate"] = $items["rate"];
				$vv["profit"] = $items["profit"];
				$vv["margin"] = $items["margin"];
				
				//totals
				$mytots["cost_total"] += $vv["cost"];
				$mytots["rate_total"] += $vv["rate"];
				$mytots["profit_total"] += $vv["profit"];

				$tots["cost_total"] += $vv["cost"];
				$tots["rate_total"] += $vv["rate"];
				$tots["profit_total"] += $vv["profit"];

				$vv["margin"] = ($items["cost"] > 0)?number_format(($items["profit"]/$items["cost"])*100,2):"0.00";	
				$vv["cost"] = number_format($vv["cost"],2);
				$vv["rate"] = number_format($vv["rate"],2);
				$vv["profit"] = number_format($vv["profit"],2);

				//replace row vars
				$vars["reports_row"] .= replace($vv, rf($htmlpath.$row));
				$i++;
			}	
			
			$mytots["bg"]=$vv["bg"];
			$mytots["cost_total"] = number_format($mytots["cost_total"],2);
			$mytots["rate_total"] = number_format($mytots["rate_total"],2);
			$mytots["profit_total"] = number_format($mytots["profit_total"],2);
			$mytots["margin_total"] = ($mytots["cost_total"] > 0)?number_format(($mytots["profit_total"]/$mytots["cost_total"])*100,2):"0.00";
			$mytots["margin_total"] = number_format($mytots["margin_total"],2);	
			
			$vars["reports_row"] .= replace($mytots, rf($htmlpath."reports_revenue_total.html"));
		}
		$tots["cost_total"] = number_format($tots["cost_total"],2);
		$tots["rate_total"] = number_format($tots["rate_total"],2);
		$tots["profit_total"] = number_format($tots["profit_total"],2);
		$tots["margin_total"] = ($tots["cost_total"] > 0)?number_format(($tots["profit_total"]/$tots["cost_total"])*100,2):"0.00";
		$tots["margin_total"] = number_format($tots["margin_total"],2);	

		$vars["reports_row"] .= replace($tots, rf($htmlpath."reports_revenue_gtotal.html"));
	}
}else{
	$vars["reports_row"] = "<tr><td colspan='6' align='center'><b>No Report Results</b></td></tr>";
}

$v["asc"]=($orderdir == "ASC")?"DESC":"ASC";
$REPORT = replace($vars, rf($htmlpath."reports_revenue_table.html"));

//set filter values
$vars["start_date"] = ($start_date)?$start_date:"";
$vars["end_date"] = ($end_date)?$end_date:"";
$reps = ($reps)?$reps:"";

//reps with invoiced shipments
$sql = "SELECT admin_login.id, CONCAT(admin_login.fname,' ',admin_login.lname) as rep FROM admin_login INNER JOIN shipment ON shipment.sales_admin_login_id=admin_login.id WHERE admin_login.removed='0' AND shipment.status='invoiced' GROUP BY admin_login.id ORDER BY admin_login.lname, admin_login.fname";
$sel_reps = $db->query($sql);
if($sel_reps){
	foreach($sel_reps as $v){
		if($_LOGIN["access_level"] == "sales" && $_LOGIN["id"] != $v["id"]){

		}else{
			if(is_array($reps)){
				$s = (in_array($v["id"],$reps))?" selected":"";
			}elseif($reps==""){
				$s = " selected";
			}else{
				$s = "";
			}
			$vars["reps"] .= '<option value="'.$v["id"].'"'.$s.'>'.$v["rep"].'</option>';
		}
	}
}

if($print=="true"){
	$set_template="template.reportprint.html";
	$html["BODY"]=$REPORT;
	$html["REPORT_NAME"]="REVENUE REPORT";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_revenue.html"));
}

?>