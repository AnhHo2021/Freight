<?php


if($_LOGIN["access_level"] != "admin"){
	header("Location: /?action=reports");
	die();
}

$html["LOCATION"] = "<h1>REPORTS : A/R DETAIL</h1>";

$vars["total_min"] = $_POST["total_min"];

//if($HTTP_POST_VARS){
//	$filter = ($total_min)?" AND i.total_due >= '".$total_min."'":"";
//}


$sql = "SELECT c.id AS customer_id,c.company,i.id AS invoice_id,CASE WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -90 THEN '90' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -60 THEN '60' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -30 THEN '30' ELSE 'current' END AS filter,i.total_due AS total FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id WHERE s.status='invoiced' AND i.status='open' ORDER BY c.company";
$balances_temp = $db->query($sql);
if($balances_temp){
	//CREATE THE INITIAL BALANCES ARRAY
	foreach($balances_temp as $vrr){				
		$balances["$vrr[customer_id]__$vrr[company]"]["$vrr[invoice_id]"]["filter"] = $vrr["filter"];
		$balances["$vrr[customer_id]__$vrr[company]"]["$vrr[invoice_id]"]["total"] = $vrr["total"];
		$balances["$vrr[customer_id]__$vrr[company]"]["grand_total"] += $vrr["total"];
	}
	if(isset($_POST["total_min"]) && $_POST["total_min"] > 0) {
		//CHECK FOR THE FILTER...  IF IT'S GREATER THAN THE GRAND TOTAL OF THAT CUSTOMER, ERASE IT.
		foreach($balances AS $name => $invoice_array) {
			if($invoice_array["grand_total"] < $_POST["total_min"]) {
				unset($balances[$name]);
			}
		}
	}
	if($balances) {
		foreach($balances AS $name => $invoice_array) {
			$name_id_array = explode("__",$name);
			$vv["customer_id"] = $name_id_array[0];
			$vv["name"] = $name_id_array[1];
			$vv["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;
			$vv["current_total"] = 0;
			$vv["30_total"] = 0;
			$vv["60_total"] = 0;
			$vv["90_total"] = 0;
			$vv["grand_total"] = 0;
			$i=0;
			foreach($invoice_array AS $invoice_id => $items) {
				//MAKE SURE THE "grand_total" PORTION OF THE ARRAY IS SKIPPED.
				if($invoice_id != "grand_total") {
					$vv["age_current"] = 0;
					$vv["age_30"] = 0;
					$vv["age_60"] = 0;
					$vv["age_90"] = 0;
					$row = ($i == 0) ? "reports_ardetail_row.html" : "reports_ardetail_row_2.html";
					$vv["invoice_id"] = $invoice_id;
					$vv["age_$items[filter]"] = $items["total"];
					$vv["age_total"] = $vv["age_current"] + $vv["age_30"] + $vv["age_60"] + $vv["age_90"];
					$vv["current_total"] += $vv["age_current"];
					$vv["30_total"] += $vv["age_30"];
					$vv["60_total"] += $vv["age_60"];
					$vv["90_total"] += $vv["age_90"];
					$vv["grand_total"] += $vv["age_total"];

					$vv["age_current"] = number_format($vv["age_current"],2);
					$vv["age_30"] = number_format($vv["age_30"],2);
					$vv["age_60"] = number_format($vv["age_60"],2);
					$vv["age_90"] = number_format($vv["age_90"],2);
					$vv["age_total"] = number_format($vv["age_total"],2);

					$v["reports_ardetail_row"] .= replace($vv, rf($htmlpath.$row));
					$i++;
				}
			}

			$v["current_total"] += $vv["current_total"];
			$v["30_total"] += $vv["30_total"];
			$v["60_total"] += $vv["60_total"];
			$v["90_total"] += $vv["90_total"];
			$v["grand_total"] += $vv["grand_total"];
			
			$vv["current_total"] = number_format($vv["current_total"],2);
			$vv["30_total"] = number_format($vv["30_total"],2);
			$vv["60_total"] = number_format($vv["60_total"],2);
			$vv["90_total"] = number_format($vv["90_total"],2);
			$vv["grand_total"] = number_format($vv["grand_total"],2);
			$v["reports_ardetail_row"] .= replace($vv, rf($htmlpath."reports_ardetail_total.html"));
		}
	}else{
		$REPORT="No Report Results";
	}
}

$v["current_total"] = number_format($v["current_total"],2);
$v["30_total"] = number_format($v["30_total"],2);
$v["60_total"] = number_format($v["60_total"],2);
$v["90_total"] = number_format($v["90_total"],2);
$v["grand_total"] = number_format($v["grand_total"],2);

$REPORT = replace($v, rf($htmlpath."reports_ardetail_table.html"));


if($_GET["do"] == "run"){
	if($REPORT){
		
	}
}

if($print=="true"){
	$set_template="template.reportprint.html";
	$html["BODY"]=$REPORT;
	$html["REPORT_NAME"]="A/R DETAIL";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_ardetail.html"));
}

?>