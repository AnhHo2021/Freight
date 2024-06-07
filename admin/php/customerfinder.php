<?php
if(isset($_GET["home"])){
	$vars["close_button"] = "";
} else {
	$vars["close_button"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='close' onClick=\"window.opener.location.reload(true);window.close();\">";
}
if(!empty($_POST["customer"])) {
	$sql = "SELECT id, company, main_contact_name FROM customer WHERE (company LIKE '%$_POST[customer]%' OR id LIKE '%$_POST[customer]%' OR mail_zip LIKE '%$_POST[customer]%') $SALES_REP_FILTER";
	$customer_list = $db->query($sql);
	if($customer_list) {
		foreach($customer_list AS $v) {
			if(isset($_GET["home"])){
				$v["href"] = "window.parent.location.href='./?action=customers_edit&id=$v[id]'";
			} else {
				$v["href"] = "window.opener.setTimeout('updateCustomer(\'" . $v["id"] . "\',\'" . $v["company"] . "\');', 2000); window.close();";
			}
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["customerfinder_list_row"] .= replace($v,rf($htmlpath."customerfinder_list_row.html"));
		}
	} else {
		$a["customerfinder_list_row"] = "<tr><td>No customers were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["customer_list"] = replace($a,rf($htmlpath."customerfinder_list.html"));
} else {
	$vars["customer_list"] = "";
}
$html["BODY"]=replace($vars,rf($htmlpath."customerfinder.html"));
?>
