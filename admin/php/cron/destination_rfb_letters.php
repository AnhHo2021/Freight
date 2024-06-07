#!/usr/bin/php -q
<?php

//include("/opt/www/htdocs/admin.norwayfreight.com/php/db.php");
include("/mnt/web/transport/transport/freight/admin/php/db.php");
$db = new DB("root","troll6");

$sel_letters = $db->query("SELECT d_to, customer_id, id, d_address1, d_address2, d_city, d_state, d_zip, d_contact_email, d_contact_fax FROM shipment WHERE d_letter = '0' GROUP BY d_to");

$past = array();
$sel_past = $db->query("SELECT d_to FROM shipment WHERE d_letter = '1' GROUP BY d_to");
if($sel_past){foreach($sel_past as $v){$past[] = $v["d_to"];}}

if($sel_letters){
	foreach($sel_letters as $v){
		if(!in_array($v["d_to"],$past)){
			//$db->query("INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='2', customer_id='".$v["customer_id"]."', shipment_id='".$v["id"]."', status='open', letter_type='email', company='".$v["d_to"]."', addr='".$v["d_address1"]."', addr2='".$v["d_address2"]."', city='".$v["d_city"]."', state='".$v["d_state"]."', zip='".$v["d_zip"]."', email='".$v["d_contact_email"]."', fax='".$v["d_contact_fax"]."'");
			echo "INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='2', customer_id='".$v["customer_id"]."', shipment_id='".$v["id"]."', status='open', letter_type='email', company='".$v["d_to"]."', addr='".$v["d_address1"]."', addr2='".$v["d_address2"]."', city='".$v["d_city"]."', state='".$v["d_state"]."', zip='".$v["d_zip"]."', email='".$v["d_contact_email"]."', fax='".$v["d_contact_fax"]."'<br>";
			$shipments[] = $v["id"];
		}
	}

	//update shipments so they don't get the letter again
	$db->query("UPDATE shipment SET d_letter='1' WHERE id IN ('".implode("','",$shipments)."')");
}

?>