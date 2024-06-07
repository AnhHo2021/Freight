<?php
$vars["place_holder"] = $_GET["place_holder"];
if(!empty($_POST["carrier"])) {
	$sql = "SELECT id, company FROM carrier WHERE company LIKE '%$_POST[carrier]%' OR id LIKE '%$_POST[carrier]%' OR zip LIKE '%$_POST[carrier]%'";
	$carrier_list = $db->query($sql);
	if($carrier_list) {
		foreach($carrier_list AS $v) {
			$v["place_holder"] = $_GET["place_holder"];
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["carrierfinder_list_row"] .= replace($v,rf($htmlpath."carrierfinder_list_row.html"));
		}
	} else {
		$a["carrierfinder_list_row"] = "<tr><td>No carriers were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["carrier_list"] = replace($a,rf($htmlpath."carrierfinder_list.html"));
} else {
	$vars["carrier_list"] = "";
}
$html["BODY"]=replace($vars,rf($htmlpath."carrierfinder.html"));
?>
