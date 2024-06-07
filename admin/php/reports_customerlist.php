<?php

$html["LOCATION"] = "<h1>REPORTS : CUSTOMER LIST</h1>";
$REPORT="No Report Results";

if($do == "run"){
	//$filter .= ($last_quote)?"AND quote_date >= DATE_SUB(CURDATE(),INTERVAL ".$last_quote." DAY) ":"";
	//$filter .= ($last_shipment)?"AND shipment_date >= DATE_SUB(CURDATE(),INTERVAL ".$last_shipment." DAY) ":"";
	//$filter .= ($mtd)?"AND ytd >= '".$ytd."' ":"";
	//$filter .= ($mtd)?"AND mtd >= '".$mtd."' ":"";
	$filter .= ($reps)?"AND admin_login.id IN ('".implode("','",$reps)."') ":"";
}

if(!$orderby){$orderby="admin_login.fname, admin_login.lname";$orderdir="ASC";}
$html["orderby"]=$orderby;
$html["orderdir"]=$orderdir;
$vars["asc"]=($orderdir == "ASC")?"DESC":"ASC";

$sql = "SELECT CONCAT(admin_login.fname,' ',admin_login.lname) as rep,customer.id AS customer_id, customer.company AS customer FROM customer INNER JOIN admin_login ON admin_login.id=customer.sales_admin_login_id WHERE customer.removed='0' $SALES_REP_FILTER ".$filter." GROUP BY customer.id ORDER BY customer.company";
$sel_report = $db->query($sql);
if($sel_report){
	foreach($sel_report as $v){$customer_ids[]=$v["customer_id"];}


	$shipment_data = $db->query("SELECT shipment.customer_id,SUBSTRING(shipped_pickup_date,1,7) AS month,SUBSTRING(shipped_pickup_date,1,4) AS year,COUNT(id) AS NUM FROM shipment WHERE shipment.customer_id IN (".implode(",",$customer_ids).") AND shipment.shipped='1' AND shipment.status != 'cancelled' AND (SUBSTRING(shipped_pickup_date,1,4)='".date("Y")."' OR SUBSTRING(shipped_pickup_date,1,4)='".date("Y",strtotime("last year"))."') GROUP BY shipment.customer_id,SUBSTRING(shipped_pickup_date,1,7)");
	if($shipment_data){
		foreach($shipment_data as $v){
			if($v["year"]==date("Y")){
				$ytd_shipments[$v["customer_id"]]+=$v["NUM"];
			}
			if($v["month"]==date("Y-m")){
				$mtd_shipments[$v["customer_id"]]+=$v["NUM"];
			}
			if($v["year"]==date("Y",strtotime("last year"))){
				$lytd_shipments[$v["customer_id"]]+=$v["NUM"];
			}
		}
	}

	$invoice_data = $db->query("SELECT shipment.customer_id,SUBSTRING(invoice.invoice_date,1,7) AS month,SUBSTRING(invoice.invoice_date,1,4) AS year,SUM(invoice.cost) AS cost,SUM(invoice.profit) AS profit,SUM(invoice.total) AS total FROM invoice INNER JOIN shipment ON invoice.shipment_id=shipment.id WHERE shipment.customer_id IN (".implode(",",$customer_ids).") AND shipment.status != 'quote' AND (SUBSTRING(invoice.invoice_date,1,4)='".date("Y")."' OR SUBSTRING(invoice.invoice_date,1,4)='".date("Y",strtotime("last year"))."') GROUP BY shipment.customer_id,SUBSTRING(invoice.invoice_date,1,7)");
	if($invoice_data){
		foreach($invoice_data as $v){
			if($v["year"]==date("Y")){
				$ytd_revenue[$v["customer_id"]]+=$v["total"];
				$ytd_profit[$v["customer_id"]]+=$v["profit"];						
			}
			if($v["month"]==date("Y-m")){
				$mtd_revenue[$v["customer_id"]]+=$v["total"];
				$mtd_profit[$v["customer_id"]]+=$v["profit"];
			}
			if($v["year"]==date("Y",strtotime("last year"))){
				$lytd_revenue[$v["customer_id"]]+=$v["total"];
				$lytd_profit[$v["customer_id"]]+=$v["profit"];						
			}
		}
	}

	$last_quote_temp = $db->query("SELECT shipment.customer_id,MAX(shipment.date_created) AS last_quote FROM shipment WHERE shipment.customer_id IN (".implode(",",$customer_ids).") AND shipment.status != 'cancelled' GROUP BY shipment.customer_id");
	if($last_quote_temp){
		foreach($last_quote_temp as $v){
			if($v["last_quote"] != "0000-00-00 00:00:00"){
				$last_quotes[$v["customer_id"]]=sysDate($v["last_quote"]);
			}else{
				$last_quotes[$v["customer_id"]]="N/A";
			}
		}
	}

	$last_shipment_temp = $db->query("SELECT shipment.customer_id,MAX(shipment.shipped_pickup_date) AS last_shipment FROM shipment WHERE shipment.customer_id IN (".implode(",",$customer_ids).") AND shipment.status != 'cancelled' AND shipment.shipped='1' GROUP BY shipment.customer_id");
	if($last_shipment_temp){
		foreach($last_shipment_temp as $v){
			if($v["last_shipment"] != "0000-00-00 00:00:00"){
				$last_shipments[$v["customer_id"]]=sysDate($v["last_shipment"]);
			}else{
				$last_shipments[$v["customer_id"]]="N/A";
			}
		}
	}



	foreach($sel_report as $n=>$v){
		$show = true;
		$last_quote_days = ($last_quotes[$v["customer_id"]])?(time()-strtotime($last_quotes[$v["customer_id"]]))/86400:-1;
		if($last_quote && $last_quote_days <= $last_quote){$show=false;}
		
		$last_shipment_days = ($last_shipments[$v["customer_id"]])?(time()-strtotime($last_shipments[$v["customer_id"]]))/86400:-1;
		if($last_shipment && $last_shipment_days <= $last_shipment){$show=false;}

		$mtd_rev=($mtd_revenue[$v["customer_id"]])?$mtd_revenue[$v["customer_id"]]:0;
		if($mtd && $mtd_rev<=$mtd){$show=false;}

		$ytd_rev=($ytd_revenue[$v["customer_id"]])?$ytd_revenue[$v["customer_id"]]:0;
		if($ytd && $ytd_rev<=$ytd){$show=false;}

		if($show){
			$v["bg"] = ($r++ % 2) ? $altbg1 : $altbg2;

			$v["last_quote"]=($last_quotes[$v["customer_id"]])?$last_quotes[$v["customer_id"]]:"N/A";
			$v["last_shipment"]=($last_shipments[$v["customer_id"]])?$last_shipments[$v["customer_id"]]:"N/A";

			$v["mtd_revenue"]=number_format((($mtd_revenue[$v["customer_id"]])?$mtd_revenue[$v["customer_id"]]:0),2);
			$v["mtd_profit"]=number_format((($mtd_profit[$v["customer_id"]])?$mtd_profit[$v["customer_id"]]:0),2);
			$v["mtd_shipments"]=number_format((($mtd_shipments[$v["customer_id"]])?$mtd_shipments[$v["customer_id"]]:0),0);

			$v["ytd_revenue"]=number_format((($ytd_revenue[$v["customer_id"]])?$ytd_revenue[$v["customer_id"]]:0),2);
			$v["ytd_profit"]=number_format((($ytd_profit[$v["customer_id"]])?$ytd_profit[$v["customer_id"]]:0),2);
			$v["ytd_shipments"]=number_format((($ytd_shipments[$v["customer_id"]])?$ytd_shipments[$v["customer_id"]]:0),0);

			$tot["mtd_revenue_total"]+=$mtd_revenue[$v["customer_id"]];
			$tot["mtd_profit_total"]+=$mtd_profit[$v["customer_id"]];
			$tot["mtd_shipments_total"]+=$mtd_shipments[$v["customer_id"]];
			$tot["ytd_revenue_total"]+=$ytd_revenue[$v["customer_id"]];
			$tot["ytd_profit_total"]+=$ytd_profit[$v["customer_id"]];
			$tot["ytd_shipments_total"]+=$ytd_shipments[$v["customer_id"]];

			$vars["reports_row"] .= replace($v, rf($htmlpath."reports_customerlist_row.html"));
		}
	}
	$tot["mtd_revenue_total"]=number_format($tot["mtd_revenue_total"],2);
	$tot["mtd_profit_total"]=number_format($tot["mtd_profit_total"],2);
	$tot["mtd_shipments_total"]=number_format($tot["mtd_shipments_total"],2);
	$tot["ytd_revenue_total"]=number_format($tot["ytd_revenue_total"],2);
	$tot["ytd_profit_total"]=number_format($tot["ytd_profit_total"],2);
	$tot["ytd_shipments_total"]=number_format($tot["ytd_shipments_total"],0);
	$vars["reports_row"] .= replace($tot, rf($htmlpath."reports_customerlist_total.html"));
}else{
	$vars["reports_row"] = "<tr><td colspan='9' align='center'><b>No Report Results</b></td></tr>";
}

$v["asc"]=($orderdir == "ASC")?"DESC":"ASC";
$REPORT = replace($vars, rf($htmlpath."reports_customerlist_table.html"));




//set filter values
$vars["last_quote"] = ($last_quote)?$last_quote:"";
$vars["last_shipment"] = ($last_shipment)?$last_shipment:"";
$vars["ytd"] = ($ytd)?$ytd:"";
$vars["mtd"] = ($mtd)?$mtd:"";
$reps = ($reps)?$reps:"";

//reps
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
	$html["REPORT_NAME"]="CUSTOMER LIST";
	$html["REPORT_GENERATED"]=sysDateTime(date("Y-m-d G:i:s"));
}else{
	$vars["REPORT"]=$REPORT;
	$html["BODY"]=replace($vars,rf($htmlpath."reports_customerlist.html"));
}

?>