<?php
$html["LOCATION"] = "<h1>ACCOUNTING : RECEIVE PAYMENTS</h1>";

if($HTTP_POST_VARS){
	$sql = "SELECT id, company, mail_address1, mail_address2, mail_city, mail_state, mail_zip, main_contact_name, main_contact_phone, main_contact_email FROM customer WHERE (id = '$search' OR company like '%$search%' OR main_contact_name like '%$search%')";
	$customer_list = $db->query($sql);
	if($customer_list) {
		$row = rf($htmlpath."accounting_payments_view_row.html");
		foreach($customer_list AS $v) {$num++;
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$v["address"] = "$v[mail_address1] $v[mail_address2]";
			$vars["customers_list"] .= replace($v, $row);
		}
	}else{
		$vars["customers_list"] = "<tr><th colspan='9'>No customers found.</th></tr>";
	}
}else{
	$vars["customers_list"] = "<tr><th colspan='9'>Search for a customer above to continue.</th></tr>";
}

$html["BODY"]=replace($vars,rf($htmlpath."accounting_payments_view.html"));
?>