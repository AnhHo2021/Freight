<?php

	$html["LOCATION"] = "<h1>ACCOUNTING : AGE VIEW</h1>";
	$a["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_invoice_date"] = $_GET["asc_invoice_date"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_due_date"] = $_GET["asc_due_date"] == "DESC" ? "ASC" : "DESC";
	$a["asc_days_old"] = $_GET["asc_days_old"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_shipped_pickup_date"] = $_GET["asc_shipped_pickup_date"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_shipped_tracknum"] = $_GET["asc_shipped_tracknum"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_company"] = $_GET["asc_company"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_cost"] = $_GET["asc_cost"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_total"] = $_GET["asc_total"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_profit"] = $_GET["asc_profit"] == "DESC" ? "ASC" : "DESC"; 
	$a["asc_margin"] = $_GET["asc_margin"] == "DESC" ? "ASC" : "DESC";
	switch($_GET["sort"]){
		case "id";
			$orderby = "id";
			if($a["asc_id"] == "DESC") $orderby .=" DESC";
			break;
		case "invoice_date";
			$orderby = "invoice_date";
			if($a["asc_invoice_date"] == "DESC") $orderby .=" DESC";
			break;
		case "due_date";
			$orderby = "due_date";
			if($a["asc_due_date"] == "DESC") $orderby .=" DESC";
			break;
		case "days_old";
			$orderby = "days_old";
			if($a["asc_days_old"] == "DESC") $orderby .=" DESC";
			break;
		case "shipped_pickup_date";
			$orderby = "shipped_pickup_date";
			if($a["asc_shipped_pickup_date"] == "DESC") $orderby .=" DESC";
			break;
		case "shipped_tracknum";
			$orderby = "shipped_tracknum";
			if($a["asc_shipped_tracknum"] == "DESC") $orderby .=" DESC";
			break;
		case "company";
			$orderby = "company";
			if($a["asc_company"] == "DESC") $orderby .=" DESC";
			break;
		case "cost";
			$orderby = "cost";
			if($a["asc_cost"] == "DESC") $orderby .=" DESC";
			break;
		case "total";
			$orderby = "total";
			if($a["asc_total"] == "DESC") $orderby .=" DESC";
			break;
		case "profit";
			$orderby = "profit";
			if($a["asc_profit"] == "DESC") $orderby .=" DESC";
			break;
		case "margin";
			$orderby = "margin";
			if($a["asc_margin"] == "DESC") $orderby .=" DESC";
			break;
		default:
			$orderby = "company";
			if($a["asc_company"] == "DESC") $orderby .=" DESC";
	}

	//$orderby = "i.id DESC";

	$sql = "SELECT i.id,i.invoice_date,i.due_date,TO_DAYS(CURDATE())-TO_DAYS(i.due_date) AS days_old,CASE WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -90 THEN '90' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -60 THEN '60' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -30 THEN '30' ELSE 'current' END AS filter,s.shipped_pickup_date,s.shipped_tracknum,c.company,i.cost,i.total,i.profit,(i.profit / i.cost * 100) AS margin FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id WHERE s.status='invoiced' AND i.status='open' ORDER BY $orderby";
	$ages_temp = $db->query($sql);
	$vars["message"] = "";
	if(isset($_GET["noneselected"])) {
		$vars["message"] = "<span style='color:red'>Please select at least one invoice to print</span>";
	}


	$vars["age_current"] = "Nothing current";
	$vars["age_30"] = "Nothing 30 days overdue";
	$vars["age_60"] = "Nothing 60 days overdue";
	$vars["age_90"] = "Nothing 90 days overdue";

	if($ages_temp){
		foreach($ages_temp as $v){
			$ages[$v["filter"]][]=$v;
		}

		foreach($ages AS $age => $list) {
			if($a){unset($a);}
			$a["age"] = "age_$age";
			switch($age) {
				case "current":					
					foreach($list AS $h) {
						foreach($h AS $vvv=>$uuu) {
							$vv[$vvv] = $uuu;
						}
						$vv["due_date"] = formatDateOnly($vv["due_date"]);
						$vv["invoice_date"] = formatDateOnly($vv["invoice_date"]);
						if($vv["shipped_pickup_date"] != "0000-00-00 00:00:00"){
							$vv["shipped_pickup_date"] = formatDateOnly($vv["shipped_pickup_date"]);
						}else{
							$vv["shipped_pickup_date"] = "N/A";
						}
						$vv["margin"] = ($vv["cost"] != 0) ? number_format($vv["margin"],2) : "0.00";

						$a["total_cost"]+=$vv["cost"];
						$a["total_rate"]+=$vv["total"];
						$a["total_profit"]+=$vv["profit"];

						$vv["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
						$a["accounting_listview_row"] .= replace($vv, rf($htmlpath."accounting_listview_row.html"));
					}
					break;
				case "30":
					foreach($list AS $g=>$h) {
						foreach($h AS $vvv=>$uuu) {
							$vv[$vvv] = $uuu;
						}
						$vv["due_date"] = formatDateOnly($vv["due_date"]);
						$vv["invoice_date"] = formatDateOnly($vv["invoice_date"]);
						if($vv["shipped_pickup_date"] != "0000-00-00 00:00:00"){
							$vv["shipped_pickup_date"] = formatDateOnly($vv["shipped_pickup_date"]);
						}else{
							$vv["shipped_pickup_date"] = "N/A";
						}
						$vv["margin"] = ($vv["cost"] != 0) ? number_format($vv["margin"],2) : "0.00";

						$a["total_cost"]+=$vv["cost"];
						$a["total_rate"]+=$vv["total"];
						$a["total_profit"]+=$vv["profit"];

						$vv["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
						$a["accounting_listview_row"] .= replace($vv, rf($htmlpath."accounting_listview_row.html"));
					}
					break;
				case "60":
					foreach($list AS $g=>$h) {
						foreach($h AS $vvv=>$uuu) {
							$vv[$vvv] = $uuu;
						}
						$vv["due_date"] = formatDateOnly($vv["due_date"]);
						$vv["invoice_date"] = formatDateOnly($vv["invoice_date"]);
						if($vv["shipped_pickup_date"] != "0000-00-00 00:00:00"){
							$vv["shipped_pickup_date"] = formatDateOnly($vv["shipped_pickup_date"]);
						}else{
							$vv["shipped_pickup_date"] = "N/A";
						}
						$vv["margin"] = ($vv["cost"] != 0) ? number_format($vv["margin"],2) : "0.00";

						$a["total_cost"]+=$vv["cost"];
						$a["total_rate"]+=$vv["total"];
						$a["total_profit"]+=$vv["profit"];

						$vv["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
						$a["accounting_listview_row"] .= replace($vv, rf($htmlpath."accounting_listview_row.html"));
					}
					break;
				case "90":
					foreach($list AS $g=>$h) {
						foreach($h AS $vvv=>$uuu) {
							$vv[$vvv] = $uuu;
						}
						$vv["due_date"] = formatDateOnly($vv["due_date"]);
						$vv["invoice_date"] = formatDateOnly($vv["invoice_date"]);
						if($vv["shipped_pickup_date"] != "0000-00-00 00:00:00"){
							$vv["shipped_pickup_date"] = formatDateOnly($vv["shipped_pickup_date"]);
						}else{
							$vv["shipped_pickup_date"] = "N/A";
						}
						$vv["margin"] = ($vv["cost"] != 0) ? number_format($vv["margin"],2) : "0.00";

						$a["total_cost"]+=$vv["cost"];
						$a["total_rate"]+=$vv["total"];
						$a["total_profit"]+=$vv["profit"];

						$vv["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
						$a["accounting_listview_row"] .= replace($vv, rf($htmlpath."accounting_listview_row.html"));
					}
					break;
			}

			if($a["total_cost"]){
				$a["total_margin"]=number_format(($a["total_profit"]/$a["total_cost"])*100,2);
			}else{
				$a["total_margin"]="0.00";
			}
			$a["total_cost"]=number_format($a["total_cost"],2);
			$a["total_rate"]=number_format($a["total_rate"],2);
			$a["total_profit"]=number_format($a["total_profit"],2);

			$vars["age_$age"] = replace($a, rf($htmlpath."accounting_ageview_table.html"));
		}
	}else{
		$vars["message"] = "<span style='color:red'>There are currently no open invoices found.</span>";
	}

	$html["BODY"]=replace($vars,rf($htmlpath."accounting_ageview.html"));

?>