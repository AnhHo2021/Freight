<?php

	$html["LOCATION"] = "<h1>ACCOUNTING : LIST VIEW</h1>";

	$vars["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_invoice_date"] = $_GET["asc_invoice_date"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_due_date"] = $_GET["asc_due_date"] == "DESC" ? "ASC" : "DESC";
	$vars["asc_days_old"] = $_GET["asc_days_old"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_shipped_pickup_date"] = $_GET["asc_shipped_pickup_date"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_shipped_tracknum"] = $_GET["asc_shipped_tracknum"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_company"] = $_GET["asc_company"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_cost"] = $_GET["asc_cost"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_total"] = $_GET["asc_total"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_profit"] = $_GET["asc_profit"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_margin"] = $_GET["asc_margin"] == "DESC" ? "ASC" : "DESC";
	switch($_GET["sort"]){
		case "id";
			$orderby = "id";
			if($vars["asc_id"] == "DESC") $orderby .=" DESC";
			break;
		case "invoice_date";
			$orderby = "invoice_date";
			if($vars["asc_invoice_date"] == "DESC") $orderby .=" DESC";
			break;
		case "due_date";
			$orderby = "due_date";
			if($vars["asc_due_date"] == "DESC") $orderby .=" DESC";
			break;
		case "days_old";
			$orderby = "days_old";
			if($vars["asc_days_old"] == "DESC") $orderby .=" DESC";
			break;
		case "shipped_pickup_date";
			$orderby = "shipped_pickup_date";
			if($vars["asc_shipped_pickup_date"] == "DESC") $orderby .=" DESC";
			break;
		case "shipped_tracknum";
			$orderby = "shipped_tracknum";
			if($vars["asc_shipped_tracknum"] == "DESC") $orderby .=" DESC";
			break;
		case "company";
			$orderby = "company";
			if($vars["asc_company"] == "DESC") $orderby .=" DESC";
			break;
		case "cost";
			$orderby = "cost";
			if($vars["asc_cost"] == "DESC") $orderby .=" DESC";
			break;
		case "total";
			$orderby = "total";
			if($vars["asc_total"] == "DESC") $orderby .=" DESC";
			break;
		case "profit";
			$orderby = "profit";
			if($vars["asc_profit"] == "DESC") $orderby .=" DESC";
			break;
		case "margin";
			$orderby = "margin";
			if($vars["asc_margin"] == "DESC") $orderby .=" DESC";
			break;
		default:
			$orderby = "company";
			if($vars["asc_company"] == "DESC") $orderby .=" DESC";
	}
	$vars["message"] = "";
	if(isset($_GET["noneselected"])) {
		$vars["message"] = "<span style='color:red'>Please select at least one invoice to print</span>";
	}
	$CUSTOMER_FILTER=($customer_id)?" AND s.customer_id ='$customer_id' ":"";
	$sql = "SELECT i.id,i.invoice_date,i.due_date,TO_DAYS(CURDATE())-TO_DAYS(i.due_date) AS days_old,s.shipped_pickup_date,s.shipped_tracknum,c.company,i.cost,i.total,i.profit,(i.profit / i.cost * 100) AS margin FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id WHERE s.status='invoiced' AND i.status='open' $CUSTOMER_FILTER ORDER BY $orderby";
	$list_array = $db->query($sql);
	if($list_array) {
		foreach($list_array AS $invoice_list) {
			foreach($invoice_list AS $k=>$x) {
				$v[$k] = $x;
			}
			$v["due_date"] = formatDateOnly($v["due_date"]);
			$v["invoice_date"] = formatDateOnly($v["invoice_date"]);
				if($v["shipped_pickup_date"] != "0000-00-00 00:00:00"){
					$v["shipped_pickup_date"] = formatDateOnly($v["shipped_pickup_date"]);
				}else{
					$v["shipped_pickup_date"] = "N/A";
				}
				$v["margin"] = ($v["cost"] != 0) ? number_format($v["margin"],2) : "0.00";

			$vars["total_cost"]+=$v["cost"];
			$vars["total_rate"]+=$v["total"];
			$vars["total_profit"]+=$v["profit"];

			$v["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
			$vars["accounting_listview_row"].=replace($v,rf($htmlpath."accounting_listview_row.html"));
		}
	} else {
		$vars["accounting_listview_row"] = "<tr><td colspan='9'>No shipments to be invoiced.</td></tr>";
	}

	if($vars["total_cost"]){
		$vars["total_margin"]=number_format(($vars["total_profit"]/$vars["total_cost"])*100,2);
	}else{
		$vars["total_margin"]="0.00";
	}
	$vars["total_cost"]=number_format($vars["total_cost"],2);
	$vars["total_rate"]=number_format($vars["total_rate"],2);
	$vars["total_profit"]=number_format($vars["total_profit"],2);

	$html["BODY"]=replace($vars,rf($htmlpath."accounting_listview.html"));

?>