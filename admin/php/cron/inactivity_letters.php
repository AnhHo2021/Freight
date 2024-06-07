#!/usr/bin/php -q
<?php

//include("/opt/www/htdocs/admin.norwayfreight.com/php/db.php");
include("/mnt/web/transport/transport/freight/admin/php/db.php");

$db = new DB("root","troll6");

$sel_shipment = $db->query("SELECT MAX(shipment.date_shipment) AS max_shipped, customer_id, customer.* FROM shipment INNER JOIN customer ON customer.id = shipment.customer_id WHERE shipment.shipped = '1' AND customer.inactivity_letter = '0' GROUP BY customer_id");
if($sel_shipment){
	$last_60 = mktime(0,0,0,date("m")-2,date("d"),date("Y"));
	foreach($sel_shipment as $v){
		if(strtotime($v["max_shipped"]) <= $last_60){
			//echo "INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='4', customer_id='".$v["customer_id"]."', shipment_id='0', status='open', letter_type='email', company='".$v["company"]."', addr='".$v["mail_address"]."', addr2='".$v["mail_address2"]."', city='".$v["mail_city"]."', state='".$v["mail_state"]."', zip='".$v["mail_zip"]."', email='".$v["main_contact_email"]."', fax='".$v["main_contact_fax"]."'<br>";
			$db->query("INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='4', customer_id='".$v["customer_id"]."', shipment_id='0', status='open', letter_type='email', company='".$v["company"]."', addr='".$v["mail_address"]."', addr2='".$v["mail_address2"]."', city='".$v["mail_city"]."', state='".$v["mail_state"]."', zip='".$v["mail_zip"]."', email='".$v["main_contact_email"]."', fax='".$v["main_contact_fax"]."'");
			$customers[] = $v["customer_id"];
		}
	}
	
	//update customers so they don't get the letter again
	$db->query("UPDATE customer SET inactivity_letter='1' WHERE id IN ('".implode("','",$customers)."')");
}

?>