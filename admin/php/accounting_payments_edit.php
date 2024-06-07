<?php
$html["LOCATION"] = "<h1>ACCOUNTING : RECEIVE PAYMENTS</h1>";

if($HTTP_POST_VARS){
	if($items){
		$db->query("INSERT INTO payment SET date_payment=NOW(), payment_admin_login_id='".$_LOGIN["id"]."', type='$type', amount='$amount', ref='$ref'");
		
		$payment_id = $db->lastid();
		foreach($items as $v){
			$pay_item = ${"applied_".$v};

			list($payment,$id)=explode("_",$pay_item);
			if($payment > 0){
				$db->query("INSERT INTO invoice_payment SET date_created=NOW(), payment_id='$payment_id', invoice_id='".$v."', amount='".$payment."'");
				
				setInvoiceTotals($v);
			}
		}
	}
	$msg .= "Payment Successfully Saved";
	header("Location: ?action=accounting_payments&msg=$msg");
	die();	
}

$vars["date_payment"] = date("Y-m-d");
$temp_temp=getEnum("payment","type");
if($temp_temp){
	foreach($temp_temp as $v){
		$vars["types"] .= '<option value="'.$v.'">'.$v.'</option>';
	}
}

//invoices
$sql = "SELECT customer.company, invoice.id as invoice_id, invoice.due_date, invoice.total_due, shipment.shipped_tracknum, shipment.o_city, shipment.o_state, shipment.o_zip, shipment.d_city, shipment.d_state, shipment.d_zip FROM invoice INNER JOIN shipment ON shipment.id=invoice.shipment_id INNER JOIN customer ON customer.id=shipment.customer_id WHERE shipment.invoice_final='1' AND invoice.status='open' AND shipment.customer_id='$id' ORDER BY invoice.due_date";
$sel_invoices = $db->query($sql);
if($sel_invoices) {
	$vars["RECEIVE_CUSTOMER"] = $sel_invoices[0]["company"];
	
	$row = rf($htmlpath."accounting_payments_edit_row.html");
	foreach($sel_invoices AS $v){
		$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
		$v["due_date"] = date("m/d/Y",strtotime($v["due_date"]));
		$v["origin"] = $v["o_city"]." ".$v["o_state"].", ".$v["o_zip"];
		$v["destination"] = $v["d_city"]." ".$v["d_state"].", ".$v["d_zip"];
		$total_due += $v["total_due"];

		$vars["jscript_invoices"] .= 'invoices['.$v["invoice_id"].'] = [];';
		$vars["jscript_invoices"] .= 'invoices['.$v["invoice_id"].'][0] = "'.$v["total_due"].'";'; // amt due
		$vars["jscript_invoices"] .= 'invoices['.$v["invoice_id"].'][1] = "0.00";'; //payment applied
		
		$vars["invoices"] .= replace($v, $row);
	}
	$vars["total_due"] = number_format($total_due,2);
}else{
	$sql = "SELECT company FROM customer WHERE id='$id'";
	$sel_cust = $db->query($sql);
	if($sel_cust){
		$vars["RECEIVE_CUSTOMER"] = $sel_cust[0]["company"];
	}else{
		$vars["RECEIVE_CUSTOMER"] = "";
	}
	$vars["total_due"] = 0.00;
	$vars["invoices"] = "<tr><td colspan='7' align='center'><b>No Invoice Exist</b></td></tr>";
}

$html["BODY"]=replace($vars,rf($htmlpath."accounting_payments_edit.html"));
?>