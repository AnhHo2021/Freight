<?php
	if($view_type=="print"){
		$set_template  = "template.blank.html";
		$vars["view_type"] = "print";
	}else{
		$vars["view_type"] = "";
	}
	
	$html["LOCATION"] = "<h1>ACCOUNTING : BILLING REPORT</h1>";

	$vars["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_invoice_date"] = $_GET["asc_invoice_date"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_customer"] = $_GET["asc_customer"] == "DESC" ? "ASC" : "DESC";
	$vars["asc_rate"] = $_GET["asc_rate"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_carrier"] = $_GET["asc_carrier"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_pro"] = $_GET["asc_pro"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_cost"] = $_GET["asc_cost"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_address"] = $_GET["asc_address"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_city"] = $_GET["asc_city"] == "DESC" ? "ASC" : "DESC"; 
	$vars["asc_state"] = $_GET["asc_state"] == "DESC" ? "ASC" : "DESC";
	$vars["asc_zip"] = $_GET["asc_zip"] == "DESC" ? "ASC" : "DESC";
	switch($_GET["sort"]){
		case "id";
			$orderby = "id";
			if($vars["asc_id"] == "DESC") $orderby .=" DESC";
			break;
		case "invoice_date";
			$orderby = "i.invoice_date";
			if($vars["asc_invoice_date"] == "DESC") $orderby .=" DESC";
			break;
		case "customer";
			$orderby = "c.company";
			if($vars["asc_customer"] == "DESC") $orderby .=" DESC";
			break;
		case "rate";
			$orderby = "i.total";
			if($vars["asc_rate"] == "DESC") $orderby .=" DESC";
			break;
		case "carrier";
			$orderby = "carrier.company";
			if($vars["asc_carrier"] == "DESC") $orderby .=" DESC";
			break;
		case "pro";
			$orderby = "s.shipped_carrier_pronum";
			if($vars["asc_pro"] == "DESC") $orderby .=" DESC";
			break;
		case "cost";
			$orderby = "i.cost";
			if($vars["asc_cost"] == "DESC") $orderby .=" DESC";
			break;
		case "address";
			$orderby = "carrier.address1,carrier.address2";
			if($vars["asc_address"] == "DESC") $orderby .=" DESC";
			break;
		case "city";
			$orderby = "carrier.city";
			if($vars["asc_city"] == "DESC") $orderby .=" DESC";
			break;
		case "state";
			$orderby = "carrier.state";
			if($vars["asc_state"] == "DESC") $orderby .=" DESC";
			break;
		case "zip";
			$orderby = "carrier.zip";
			if($vars["asc_zip"] == "DESC") $orderby .=" DESC";
			break;
		default:
			$orderby = "i.id";
			if($vars["asc_company"] == "DESC") $orderby .=" DESC";
	}
	$vars["message"] = "";

	if($HTTP_POST_VARS){
		$filter .= ($start_date)?"AND TO_DAYS(s.invoice_final_date) >= TO_DAYS('".date("Y-m-d",strtotime($start_date))."') ":"";
		$filter .= ($end_date)?"AND TO_DAYS(s.invoice_final_date) <= TO_DAYS('".date("Y-m-d",strtotime($end_date))."') ":"";
	}else{
		$filter .= "AND TO_DAYS(s.invoice_final_date) >= TO_DAYS('".date("Y-m-d")."') ";
		$filter .= "AND TO_DAYS(s.invoice_final_date) <= TO_DAYS('".date("Y-m-d")."') ";
	}
	
	$sql = "SELECT i.id,i.invoice_date,s.shipped_carrier_pronum,s.shipped_pickup_date,c.company as customer,i.cost,i.total as rate,carrier.company as carrier, carrier.address1, carrier.address2, carrier.city, carrier.state, carrier.zip FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id INNER JOIN carrier ON carrier.id=s.shipped_carrier_id WHERE s.invoice_final='1' ".$filter." ORDER BY $orderby";
	$list_array = $db->query($sql);
	if($list_array) {
		foreach($list_array AS $invoice_list) {
			foreach($invoice_list AS $k=>$x) {
				$v[$k] = $x;
			}
			$v["invoice_date"] = formatDateOnly($v["invoice_date"]);

			$vars["total_cost"]+=$v["cost"];
			$vars["total_rate"]+=$v["rate"];
			$vars["total_profit"]+=$v["profit"];

			$v["bg"] = ($i++ % 2) ? $altbg1 : $altbg2;
			$vars["invoices"].=replace($v,rf($htmlpath."accounting_billing_row.html"));
		}
	} else {
		$vars["invoices"] = "<tr><td colspan='13'>No Results</td></tr>";
	}

	if($vars["total_cost"]){
		$vars["total_margin"]=number_format(($vars["total_profit"]/$vars["total_cost"])*100,2);
	}else{
		$vars["total_margin"]="0.00";
	}
	$vars["total_cost"]=number_format($vars["total_cost"],2);
	$vars["total_rate"]=number_format($vars["total_rate"],2);
	$vars["total_profit"]=number_format($vars["total_profit"],2);
		
	//set filter values
	$vars["start_date"] = ($start_date)?$start_date:date("Y-m-d");
	$vars["end_date"] = ($end_date)?$end_date:date("Y-m-d");

	$html["BODY"]=replace($vars,rf($htmlpath."accounting_billing.html"));

?>