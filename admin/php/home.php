<?php

$vars["date"] = date("m/d/Y");

$sql = "SELECT created_admin_login_id, description FROM calendar WHERE admin_login_id = '$_LOGIN[id]' AND date = CURDATE()";
$calendar=$db->query($sql);
if($calendar) {
	foreach($calendar AS $calendar_row) {
		$bg = ($x++ % 2) ? $altbg1 : $altbg2;
		$vars["calendar_row"] .= "<tr bgcolor=\"$bg\" style=\"cursor:hand\" onmouseover=\"style.backgroundColor='white';\" onmouseout=\"style.backgroundColor='[bg]'\"><td>$calendar_row[created_admin_login_id]</td><td>$calendar_row[description]</td></tr>";
	}
} else {
	$vars["calendar_row"] = "<tr bgcolor=\"$altbg1\"><td colspan='2'><b>No Items</b></td></tr>";
}
$html["BODY"]=replace($vars,rf($htmlpath."home.html"));

?>