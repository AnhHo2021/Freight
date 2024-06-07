<?php
if(!empty($_POST["carrier"])) {
	$sql = "SELECT id, company FROM carrier WHERE company LIKE '%$_POST[carrier]%' OR id LIKE '%$_POST[carrier]%' OR zip LIKE '%$_POST[carrier]%'";
	$customer_list = $db->query($sql);
	if($customer_list) {
		foreach($customer_list AS $v) {
			$v["customer_id"] = $_GET["customer_id"];
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["addressfinder_list_row"] .= replace($v,rf($htmlpath."addressfinder_list_row.html"));
		}
	} else {
		$a["addressfinder_list_row"] = "<tr><td>No carriers were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["address_list"] = "";
	$vars["carrier_list"] = replace($a,rf($htmlpath."addressfinder_list.html"));
} elseif(isset($_GET["id"])) {
	$sql = "INSERT INTO customer_fak_address SET
	customer_id = '$_GET[customer_id]',
	carrier_fak_address_id = '$_GET[id]'";
	$success=$db->query($sql);
} elseif(isset($_GET["carrier_id"])) {
	$sql = "SELECT * FROM carrier_fak_address WHERE carrier_id = '$_GET[carrier_id]'";
	$address_array = $db->query($sql);
	if($address_array) {
		foreach($address_array AS $a) {
			$a["customer_id"] = $_GET["customer_id"];
			$a["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$z["addressfinder_list_addresses_row"] .= replace($a,rf($htmlpath."addressfinder_list_addresses_row.html"));
		}
	} else {
		$z["addressfinder_list_addresses_row"] = "<tr><td colspan='4'>No FAK addresses were found for that carrier.</td></tr>";
	}
	$vars["address_list"] = replace($z,rf($htmlpath."addressfinder_list_addresses.html"));
	$vars["carrier_list"] = "";
} else {
	$vars["carrier_list"] = "";
	$vars["address_list"] = "";
}
$vars["customer_id"] = $_GET["customer_id"];
$html["BODY"]=replace($vars,rf($htmlpath."addressfinder.html"));
?>
