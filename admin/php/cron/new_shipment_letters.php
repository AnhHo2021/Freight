#!/usr/bin/php -q
<?php

//include("/opt/www/htdocs/admin.norwayfreight.com/php/db.php");
include("/mnt/web/transport/transport/freight/admin/php/db.php");
$db = new DB("root","troll6");

$sel_letters = $db->query("SELECT customer.*, shipment.id as shipment_id FROM customer INNER JOIN shipment ON shipment.customer_id=customer.id WHERE customer.new_shipment_letter='0' AND shipment.shipped='1' GROUP BY customer.id");

if($sel_letters){
	foreach($sel_letters as $v){
		$db->query("INSERT INTO marketing_letters SET date_created=NOW(), marketing_type_id='1', customer_id='".$v["id"]."', shipment_id='".$v["shipment_id"]."', status='open', letter_type='email', company='".$v["company"]."', addr='".$v["mail_address"]."', addr2='".$v["mail_address2"]."', city='".$v["mail_city"]."', state='".$v["mail_state"]."', zip='".$v["mail_zip"]."', email='".$v["main_contact_email"]."', fax='".$v["main_contact_fax"]."'");
		$customers[] = $v["id"];
	}

	//update customers so they don't get the letter again
	$db->query("UPDATE customer SET new_shipment_letter='1' WHERE id IN ('".implode("','",$customers)."')");
}

?>