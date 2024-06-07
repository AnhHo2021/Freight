<?php
if(isset($_GET["home"])){
	$vars["close_button"] = "";
} else {
	$vars["close_button"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='close' onClick=\"window.opener.location.reload(true);window.close();\">";
}
if(!empty($_POST["invoice"])) {
	$sql = "SELECT i.id, i.invoice_date, i.due_date,i.status, c.company, c.sales_admin_login_id FROM invoice i INNER JOIN shipment s ON i.id=s.invoice_id LEFT JOIN customer c ON s.customer_id=c.id WHERE (company LIKE '%$_POST[invoice]%' OR i.id LIKE '%$_POST[invoice]%' OR s.id LIKE '%$_POST[invoice]%' OR s.shipped_carrier_pronum LIKE '%$_POST[invoice]%' OR s.shipped_tracknum LIKE '%$_POST[invoice]%')";
	$invoice_list = $db->query($sql);
	if($invoice_list) {
		foreach($invoice_list AS $v) {
			if($_LOGIN["access_level"] == "sales" && $v["sales_admin_login_id"] != $_LOGIN["id"]){

			}else{
				if(isset($_GET["home"])){
					$v["href"] = "window.parent.location.href='./?action=accounting_edit&id=$v[id]'";
				} else {
					$v["href"] = "window.opener.updateInvoice('$v[id]','$v[company]');window.close();";
				}
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$v["invoice_date"]=sysDate($v["invoice_date"]);
				$v["due_date"]=sysDate($v["due_date"]);
				$a["invoicefinder_list_row"] .= replace($v,rf($htmlpath."invoicefinder_list_row.html"));
			}
		}
	} else {
		$a["invoicefinder_list_row"] = "<tr><td>No invoices were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["invoice_list"] = replace($a,rf($htmlpath."invoicefinder_list.html"));
} else {
	$vars["invoice_list"] = "";
}
$html["BODY"]=replace($vars,rf($htmlpath."invoicefinder.html"));
?>
