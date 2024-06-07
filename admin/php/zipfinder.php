<?php
if(isset($_GET["home"])){
	$vars["close_button"] = "";
} else {
	$vars["close_button"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='close' onClick=\"window.opener.location.reload(true);window.close();\">";
}
if(!empty($_POST["city"])) {
	
	$sql = "SELECT city,state,county,zip FROM zip WHERE city LIKE '%$_POST[city]%' ORDER BY zip";
	$zip_list = $db->query($sql);
	if($zip_list) {
		foreach($zip_list AS $v) {
			$v["o_or_d"] = (isset($_GET["Destination"])) ? "Destination" : "Origin";
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["zipfinder_zip_list_row"] .= replace($v,rf($htmlpath."zipfinder_zip_list_row.html"));
		}
	} else {
		$a["zipfinder_zip_list_row"] = "<tr><td>No cities were found by that name.</td></tr>";
	}
	$vars["zip_list"] = replace($a,rf($htmlpath."zipfinder_zip_list.html"));
	$vars["city_list"] = "";
} elseif (!empty($_POST["zip"]) && $_POST["radius"] == "0") {
	$sql = "SELECT city,state,county,zip FROM zip WHERE zip = '$_POST[zip]' ORDER BY zip";
	$zip_list = $db->query($sql);
	if($zip_list) {
		foreach($zip_list AS $v) {
			$v["o_or_d"] = (isset($_GET["Destination"])) ? "Destination" : "Origin";
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["zipfinder_zip_list_row"] .= replace($v,rf($htmlpath."zipfinder_zip_list_row.html"));
		}
	} else {
		$a["zipfinder_zip_list_row"] = "<tr><td>No cities were found by that name.</td></tr>";
	}
	$vars["zip_list"] = replace($a,rf($htmlpath."zipfinder_zip_list.html"));
	$vars["city_list"] = "";
} elseif (!empty($_POST["zip"])) {
	$sql = "SELECT latitude, longitude FROM zip WHERE zip = '$_POST[zip]'";
	$lat_long=$db->query($sql);
	$lat_long = $lat_long[0];
	$sql = "SELECT *, LPAD(CONCAT(ROUND(sqrt(power(69.1*(latitude - '$lat_long[latitude]'),2)+ power(69.1*(longitude-'$lat_long[longitude]')*cos(latitude/57.3),2)),2),' miles'),12,' ') AS themiles FROM zip WHERE  sqrt(power(69.1*(latitude - $lat_long[latitude]),2)+ power(69.1*(longitude-$lat_long[longitude])*cos(latitude/57.3),2)) <= '$_POST[radius]' ORDER BY themiles ASC";
	$city_list = $db->query($sql);
	if($city_list) {
		foreach($city_list AS $v) {
			$v["o_or_d"] = (isset($_GET["Destination"])) ? "Destination" : "Origin";
			$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
			$a["zipfinder_city_list_row"] .= replace($v,rf($htmlpath."zipfinder_city_list_row.html"));
		}
	} else {
		$a["zipfinder_city_list_row"] = "<tr><td>No cities were found with that zip code.</td></tr>";
	}
	$vars["city_list"] = replace($a,rf($htmlpath."zipfinder_city_list.html"));
	$vars["zip_list"] = "";
} else {
	$vars["city_list"] = "";
	$vars["zip_list"] = "";
}
$vars["o_or_d"] = (isset($_GET["Destination"])) ? "Destination" : "Origin";
$html["BODY"]=replace($vars,rf($htmlpath."zipfinder.html"));
?>
