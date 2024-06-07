#!/usr/bin/php -q
<?php

//include("/opt/www/htdocs/admin.norwayfreight.com/php/db.php");
include("/mnt/web/transport/transport/freight/admin/php/db.php");
$db = new DB("root","troll6");

$sel_letters = $db->query("SELECT o_to, customer_id, id, o_address1, o_address2, o_city, o_state, o_zip, o_contact_email, o_contact_fax FROM shipment WHERE o_letter = '0' GROUP BY o_to");

$past = array();
$sel_past = $db->query("SELECT o_to FROM shipment WHERE o_letter = '1' GROUP BY o_to");
if($sel_past){foreach($sel_past as $v){$past[] = $v["o_to"];}}

if($sel_letters){
	foreach($sel_letters as $v){
		if(!in_array($v["o_to"],$past)){
			//$db->query("INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='2', customer_id='".$v["customer_id"]."', shipment_id='".$v["id"]."', status='open', letter_type='email', company='".$v["o_to"]."', addr='".$v["o_address1"]."', addr2='".$v["o_address2"]."', city='".$v["o_city"]."', state='".$v["o_state"]."', zip='".$v["o_zip"]."', email='".$v["o_contact_email"]."', fax='".$v["o_contact_fax"]."'");
			echo "INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='2', customer_id='".$v["customer_id"]."', shipment_id='".$v["id"]."', status='open', letter_type='email', company='".$v["o_to"]."', addr='".$v["o_address1"]."', addr2='".$v["o_address2"]."', city='".$v["o_city"]."', state='".$v["o_state"]."', zip='".$v["o_zip"]."', email='".$v["o_contact_email"]."', fax='".$v["o_contact_fax"]."'<br>";
			$shipments[] = $v["id"];
		}
	}

	//update shipments so they don't get the letter again
//	$db->query("UPDATE shipment SET o_letter='1' WHERE id IN ('".implode("','",$shipments)."')");
}

?>