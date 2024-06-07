<?php
if(isset($_GET["home"])){
	$vars["close_button"] = "";
} else {
	$vars["close_button"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='close' onClick=\"window.opener.location.reload(true);window.close();\">";
}
if(!empty($_POST["shipment"])) {
	$sql = "SELECT s.id,s.status, s.date_created, c.company, c.sales_admin_login_id FROM shipment s LEFT JOIN customer c ON s.customer_id=c.id WHERE (company LIKE '%$_POST[shipment]%' OR s.shipped_tracknum='$_POST[shipment]' OR s.id LIKE '%$_POST[shipment]%' OR o_zip LIKE '%$_POST[shipment]%' OR d_zip LIKE '%$_POST[shipment]%' OR shipped_carrier_pronum LIKE '%$_POST[shipment]%') AND s.status!='quote'";
	$shipment_list = $db->query($sql);
	if($shipment_list) {
		foreach($shipment_list AS $v) {
			if($_LOGIN["access_level"] == "sales" && $v["sales_admin_login_id"] != $_LOGIN["id"]){

			}else{
				if(isset($_GET["home"])){
					$v["href"] = "window.parent.location.href='./?action=shipments_edit&id=$v[id]'";
				} else {
					$v["href"] = "window.opener.updateShipment('$v[id]','$v[company]');window.close();";
				}
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$v["date_created"]=sysDateTime($v["date_created"]);
				$a["shipmentfinder_list_row"] .= replace($v,rf($htmlpath."shipmentfinder_list_row.html"));
			}
		}
	} else {
		$a["shipmentfinder_list_row"] = "<tr><td>No shipments were found by that name, id, or zipcode.</td></tr>";
	}
	$vars["shipment_list"] = replace($a,rf($htmlpath."shipmentfinder_list.html"));
} else {
	$vars["shipment_list"] = "";
}
$html["BODY"]=replace($vars,rf($htmlpath."shipmentfinder.html"));
?>
