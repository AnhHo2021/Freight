<?php

if($_LOGIN["access_level"] != "admin"){
	header("Location: /?action=reports");
	die();
}

$html["LOCATION"] = "<h1>REPORTS : COMPANY SNAPSHOT</h1>";

// customers
$types = getEnum("customer", "type");
foreach($types as $v){
	$counts = $db->query("SELECT COUNT(*) FROM customer WHERE type = '$v' AND removed='0' GROUP BY type");
	if($counts){$count = $counts[0]["COUNT(*)"];}else{$count=0;}

	$vars["customer_types"].='<td class="tblHeader" align=center width=75>'.strtoupper($v).'</td>';
	$vars["customer_counts"].='<td align=center bgcolor="'.$altbg1.'">'.number_format($count,0).'</td>';
}

// ar
$sql = "SELECT CASE WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -90 THEN '90' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -60 THEN '60' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -30 THEN '30' ELSE 'current' END AS filter,SUM(i.total_due) AS revenue,SUM(i.profit) AS profit FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id WHERE s.status='invoiced' AND i.status='open' GROUP BY filter";
$ars = $db->query($sql);
if($ars){
	foreach($ars as $v){
		$ar[$v["filter"].'_revenue']=$v["revenue"];
		$ar[$v["filter"].'_profit']=$v["profit"];
	}
}
$vars["total_revenue"]=number_format($ar["current_revenue"]+$ar["30_revenue"]+$ar["60_revenue"]+$ar["90_revenue"],2);
$vars["current_revenue"]=number_format($ar["current_revenue"],2);
$vars["30_revenue"]=number_format($ar["30_revenue"],2);
$vars["60_revenue"]=number_format($ar["60_revenue"],2);
$vars["90_revenue"]=number_format($ar["90_revenue"],2);

$vars["total_profit"]=number_format($ar["current_profit"]+$ar["30_profit"]+$ar["60_profit"]+$ar["90_profit"],2);
$vars["current_profit"]=number_format($ar["current_profit"],2);
$vars["30_profit"]=number_format($ar["30_profit"],2);
$vars["60_profit"]=number_format($ar["60_profit"],2);
$vars["90_profit"]=number_format($ar["90_profit"],2);



// revenue
$vars["revenue_rows"]="";
$sql = "SELECT SUBSTRING(shipment.invoice_final_date,1,7) AS month, service.name as service, SUM(invoice.cost) AS cost, SUM(invoice.profit) AS profit, SUM(invoice.total) as rate FROM invoice INNER JOIN shipment ON shipment.invoice_id=invoice.id AND shipment.status='invoiced' INNER JOIN admin_login ON admin_login.id=shipment.sales_admin_login_id INNER JOIN service ON service.id=shipment.service_id WHERE TO_DAYS(shipment.invoice_final_date) >= TO_DAYS('".date("Y-m-01",strtotime("-2 months"))."') GROUP BY month,shipment.service_id ORDER BY month DESC,service.group,service.name";
$rev = $db->query($sql);
if($rev){
	foreach($rev as $v){
		$rev_sorted[$v["month"]][]=$v;
	}
	foreach($rev_sorted as $month=>$arr){
		$bg = ($r++ % 2) ? $altbg1 : $altbg2;
		$cost=$arr[0]["cost"];
		$rate=$arr[0]["rate"];
		$profit=$arr[0]["profit"];
		$vars["revenue_rows"].='<tr><td class="tblHeader">'.date("M Y",strtotime($month."-01")).'</td><td bgcolor="'.$bg.'">'.$arr[0]["service"].'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($arr[0]["cost"],2).'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($arr[0]["rate"],2).'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($arr[0]["profit"],2).'</td><td bgcolor="'.$bg.'" align=right>'.number_format(($arr[0]["profit"]/$arr[0]["cost"])*100,2).'%</td></tr>';
		if(count($arr)>1){
			$c=0;
			foreach($arr as $v){$c++;
				if($c>1){
					$cost+=$v["cost"];
					$rate+=$v["rate"];
					$profit+=$v["profit"];
					$vars["revenue_rows"].='<tr><td>&nbsp;</td><td bgcolor="'.$bg.'">'.$v["service"].'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($v["cost"],2).'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($v["rate"],2).'</td><td bgcolor="'.$bg.'" align=right>$'.number_format($v["profit"],2).'</td><td bgcolor="'.$bg.'" align=right>'.number_format(($v["profit"]/$v["cost"])*100,2).'%</td></tr>';
				}
			}
		}
		$vars["revenue_rows"].='<tr><td>&nbsp;</td><td bgcolor="'.$bg.'"><b>Totals</b></td><td bgcolor="'.$bg.'" align=right><b>$'.number_format($cost,2).'</b></td><td bgcolor="'.$bg.'" align=right><b>$'.number_format($rate,2).'</b></td><td bgcolor="'.$bg.'" align=right><b>$'.number_format($profit,2).'</b></td><td bgcolor="'.$bg.'" align=right><b>'.number_format(($profit/$cost)*100,2).'%</b></td></tr>';
	}
}


$REPORT = replace($vars, rf($htmlpath."reports_snapshot_table.html"));

if($print=="true"){
	$set_template="template.reportprint.html";
	$html["BODY"]=$REPORT;
	$html["REPORT_NAME"]="COMPANY SNAPSHOT";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_snapshot.html"));
}

?>