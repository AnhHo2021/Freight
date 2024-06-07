<?php

if(!empty($_POST["terminal"])) {
	$sql = "SELECT id, code, name FROM carrier_terminal WHERE carrier_id='$carrier_id' AND (name LIKE '%$_POST[terminal]%' OR id LIKE '%$_POST[terminal]%' OR zip LIKE '%$_POST[terminal]%' OR code LIKE '%$_POST[terminal]%')";
	$terminal_list = $db->query($sql);
	if($terminal_list) {
		foreach($terminal_list AS $v) {
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["terminalfinder_list_row"] .= replace($v,rf($htmlpath."terminalfinder_list_row.html"));
		}
	} else {
		$a["terminalfinder_list_row"] = "<tr><td>No terminals were found by that name, code, id, or zipcode.</td></tr>";
	}
	$vars["terminal_list"] = replace($a,rf($htmlpath."terminalfinder_list.html"));
} else {
	$vars["terminal_list"] = "";
}

$carrier_name_temp=$db->query("SELECT company FROM carrier WHERE id='$carrier_id'");
$vars["carrier"]=($carrier_name_temp)?$carrier_name_temp[0]["company"]:"";
$vars["carrier_id"]=$carrier_id;
$html["BODY"]=replace($vars,rf($htmlpath."terminalfinder.html"));

?>
