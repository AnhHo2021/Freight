<?php
if(isset($_GET["home"])){
	$vars["close_button"] = "";
} else {
	$vars["close_button"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='close' onClick=\"window.opener.location.reload(true);window.close();\">";
}
if(!empty($_POST["quote"])) {
	$sql = "SELECT s.id, s.date_created, c.company, c.sales_admin_login_id FROM shipment s LEFT JOIN customer c ON s.customer_id=c.id WHERE (company LIKE '%$_POST[quote]%' OR s.id LIKE '%$_POST[quote]%' OR o_zip LIKE '%$_POST[quote]%') AND s.status='quote'";
	$quote_list = $db->query($sql);
	if($quote_list) {
		foreach($quote_list AS $v) {
			if($_LOGIN["access_level"] == "sales" && $v["sales_admin_login_id"] != $_LOGIN["id"]){

			}else{
				if(isset($_GET["home"])){
					$v["href"] = "window.parent.location.href='./?action=quotes_edit&id=$v[id]'";
				} else {
					$v["href"] = "window.opener.updateQuote('$v[id]','$v[company]');window.close();";
				}
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$v["date_created"]=sysDateTime($v["date_created"]);
				$a["quotefinder_list_row"] .= replace($v,rf($htmlpath."quotefinder_list_row.html"));
			}
		}
	} else {
		$a["quotefinder_list_row"] = "<tr><td>No quotes were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["quote_list"] = replace($a,rf($htmlpath."quotefinder_list.html"));
} else {
	$vars["quote_list"] = "";
}
$html["BODY"]=replace($vars,rf($htmlpath."quotefinder.html"));
?>
