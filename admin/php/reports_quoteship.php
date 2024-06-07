<?php

$html["LOCATION"] = "<h1>REPORTS : QUOTES VS SHIPMENTS</h1>";
$REPORT="No Report Results";

if(!$start_date){$start_date=date("Y-m-d",strtotime("-15 days"));}
if(!$end_date){$end_date=date("Y-m-d",strtotime("now"));}
if(!$req_start_date){$req_start_date=date("Y-m-d",strtotime("-5 days"));}
if(!$req_end_date){$req_end_date=date("Y-m-d",strtotime("now"));}

	$filter .= ($start_date)?"AND TO_DAYS(shipment.date_created) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
	$filter .= ($end_date)?"AND TO_DAYS(shipment.date_created) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
	$filter .= ($start_date)?"AND TO_DAYS(shipment.req_pickup_date) >= TO_DAYS('".date("Y-m-d",strtotime($req_start_date))."') ":"";
	$filter .= ($end_date)?"AND TO_DAYS(shipment.req_pickup_date) <= TO_DAYS('".date("Y-m-d",strtotime($req_end_date))."') ":"";
	$filter .= ($reps)?"AND admin_login.id IN ('".implode("','",$reps)."') ":"";



$sql = "SELECT CONCAT(admin_login.fname,' ',admin_login.lname) as rep,customer.company,shipment.id AS quote_id,shipment.*,SUM(com.pieces) AS pieces, MAX(cla.class) AS class, SUM(com.weight) AS weight FROM shipment INNER JOIN customer ON shipment.customer_id=customer.id LEFT JOIN shipment_commodity com LEFT JOIN class_list cla ON com.class_list_id=cla.id ON com.shipment_id=shipment.id INNER JOIN admin_login ON admin_login.id=shipment.created_admin_login_id WHERE shipment.status='quote' ".$SALES_REP_FILTER.$filter." GROUP BY shipment.id ORDER BY rep ";
$sel_report = $db->query($sql);
if($sel_report){
	foreach($sel_report AS$items) {						
		//replace row vars
		$items["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;
		$items["date_created"] = formatDate($items["date_created"]);
		$items["req_pickup_date"] = ($items["req_pickup_date"] == "0000-00-00") ? "not set" : date("m/d/Y",strtotime($items["req_pickup_date"])).'<br>('.date("g:i a",strtotime($items["req_pickup_time_start"])).'-'.date("g:i a",strtotime($items["req_pickup_time_end"])).')';
		$items["origin"] = "$items[o_city]<br>$items[o_state] $items[o_zip]";
		$items["destination"] = "$items[d_city]<br>$items[d_state] $items[d_zip]";
		$v["reports_row"] .= replace($items, rf($htmlpath."reports_quoteship_row.html"));
		$i++;
	}
}else{
	$v["reports_row"] = "<tr><td colspan='10' align='center'><b>No Report Results</b></td></tr>";
}

$v["asc"]=($orderdir == "ASC")?"DESC":"ASC";
$REPORT = replace($v, rf($htmlpath."reports_quoteship_table.html"));

//set filter values
$vars["start_date"] = ($start_date)?$start_date:"";
$vars["end_date"] = ($end_date)?$end_date:"";
$vars["req_start_date"] = ($req_start_date)?$req_start_date:"";
$vars["req_end_date"] = ($req_end_date)?$req_end_date:"";
$reps = ($reps)?$reps:"";

//all reps
$sql = "SELECT admin_login.id, CONCAT(admin_login.fname,' ',admin_login.lname) as rep FROM admin_login WHERE admin_login.removed='0' GROUP BY admin_login.id ORDER BY admin_login.lname, admin_login.fname";
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
	$html["REPORT_NAME"]="QUOTES VS SHIPMENTS";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_quoteship.html"));
}

?>