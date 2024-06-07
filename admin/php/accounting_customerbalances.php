<?php

$html["LOCATION"] = "<h1>ACCOUNTING : CUSTOMER BALANCES</h1>";

$vars["asc_name"] = $_GET["asc_name"] == "ASC" ? "DESC" : "ASC";
switch($_GET["sort"]){
	case "name";
	default:
	$orderby = "c.company";
	if($vars["asc_name"] == "DESC") $orderby .=" DESC";
	break;
	//$orderby = "filter";
	//if($v["asc_company"] == "DESC") $orderby .=" DESC";
}

$sql = "SELECT c.id,c.company,CASE WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -90 THEN '90' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -60 THEN '60' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -30 THEN '30' ELSE 'current' END AS filter,SUM(i.total_due) AS total FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id WHERE s.status='invoiced' AND i.status='open' GROUP BY c.id,filter ORDER BY $orderby";
$balances_temp = $db->query($sql);
$vars["message"] = "";
if(isset($_GET["noneselected"])) {
	$vars["message"] = "<span style='color:red'>Please select at least one invoice to print</span>";
}
if($balances_temp){
	foreach($balances_temp as $vrr){				
		$balances["$vrr[id]__$vrr[company]"][$vrr["filter"]]=$vrr["total"];
	}
	foreach($balances AS $name => $list) {
		$name_id_array = explode("__",$name);
		$v["id"] = $name_id_array[0];
		$v["name"] = $name_id_array[1];
		$v["age_current"] = 0;
		$v["age_30"] = 0;
		$v["age_60"] = 0;
		$v["age_90"] = 0;
		foreach($list AS $key => $vv) {
			$v["age_$key"] = $vv;
			$totals["age_$key"] += $vv;
		}
		
		$v["age_total"] = number_format($v["age_current"] + $v["age_30"] + $v["age_60"] + $v["age_90"],2);

		$v["age_current"] = number_format($v["age_current"],2);
		$v["age_30"] = number_format($v["age_30"],2);
		$v["age_60"] = number_format($v["age_60"],2);
		$v["age_90"] = number_format($v["age_90"],2);

		$v["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
		$vars["accounting_customerbalances_row"] .= replace($v, rf($htmlpath."accounting_customerbalances_row.html"));
	}
}else{
	$vars["accounting_customerbalances_row"] = "<tr><td colspan=7><span style='color:red'>There are currently no customer balances found.</span></td></tr>";
}

$totals["age_total"] = number_format($totals["age_current"]+$totals["age_30"]+$totals["age_60"]+$totals["age_90"],2);
$totals["age_current"] = number_format($totals["age_current"],2);
$totals["age_30"] = number_format($totals["age_30"],2);
$totals["age_60"] = number_format($totals["age_60"],2);
$totals["age_90"] = number_format($totals["age_90"],2);


$html["BODY"]=replace($vars,rf($htmlpath."accounting_customerbalances.html"));
$html["BODY"]=replace($totals,$html["BODY"]);

?>