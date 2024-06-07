<?php
//$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, shipment.date_shipment, shipment.o_to,  FROM marketing_letters INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id INNER JOIN shipment ON shipment.id=marketing_letters.shipment_id WHERE marketing_letters.id IN ('".implode("','",$items)."')");
if($action=="dest_request"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password, shipment.d_to, shipment.d_address1, shipment.d_address2, shipment.d_city, shipment.d_state, shipment.d_zip FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id INNER JOIN shipment ON shipment.id=marketing_letters.shipment_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="org_request"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password, shipment.o_to, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.o_zip FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id INNER JOIN shipment ON shipment.id=marketing_letters.shipment_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="ns"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="inactivity"){
	$info=$db->query("SELECT customer.id, marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}

//print_r($info); die();
if($info){
	$tot_emails=0;
	foreach($info as $v){
		$w["date"] =  date("m/d/Y g:i a");
		$w["company"]=$v["company"]."<br>".$v["addr"]." ".$v["addr2"]."<br>".$v["city"].", ".$v["state"]." ".$v["zip"];
		$w["customer"]=$v["main_contact_name"];
		$w["username"]=$v["username"];
		$w["password"]=$v["password"];
		$w["company_name"]=$v["company"];
		
		if($action=="dest_request"){
			$w["lead_company"]=$v["d_to"];
			$w["lead_company_full"]=$v["d_to"]."<br>".$v["d_address1"]." ".$v["d_address2"]."<br>".$v["d_city"].", ".$v["d_state"]." ".$v["d_zip"];
		}elseif($action=="org_request"){
			$w["lead_company"]=$v["o_to"];
			$w["lead_company_full"]=$v["o_to"]."<br>".$v["o_address1"]." ".$v["o_address2"]."<br>".$v["o_city"].", ".$v["o_state"]." ".$v["o_zip"];
		}elseif($action=="inactivity"){
			$sel_last = $db->query("SELECT MAX(shipment.date_shipment) AS last_shipped FROM shipment INNER JOIN customer ON customer.id = shipment.customer_id WHERE shipment.shipped = '1' AND customer.id='".$v["customer_id"]."' GROUP BY customer_id LIMIT 1");
			$w["last_shipment"] = date("m/d/Y",strtotime($sel_last[0]["last_shipped"]));
		}

		$emailData=replace($w,rf($htpath."email/".$v["file"]));
		//print_r("123");
		$email_to =  $v["email"];
		$email_from = "sales@strongcontainers.com";
		$mail_subject=$v["subject"];
		
		$headers = "X-Sender:  $email_to <$email_to>\n"; // 
		$headers .="From: $email_from <$email_from>\n";
		$headers .= "Reply-To: $email_from <$email_from>\n";
		$headers .= "Date: ".date("r")."\n";
		$headers .= "Message-ID: <".date("YmdHis")."info@".$_SERVER['SERVER_NAME'].">\n";
		$headers .= "Subject: $mail_subject\n";
		$headers .= "Return-Path: $email_from <$email_from>\n";
		$headers .= "Delivered-to: $email_from <$email_from>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html;charset=ISO-8859-9\n";
		$headers .= "X-Priority: 1\n";
		$headers .= "Importance: High\n";
		$headers .= "X-MSMail-Priority: High\n";
		$headers .= "X-Mailer: PHP" . phpversion() ."\n";
	
		$mail_body = stripslashes($emailData);
        //$email_to ="anh@at1ts.com";
        $from_name ="Transport";
        $receiver_name = $v["d_to"];
       // mail($email_to,$mail_subject,$mail_body,$headers);
        mail_to($from_name,$receiver_name,$email_to,$mail_subject,$mail_body);
        $tot_emails++;
       // use wordwrap() if lines are longer than 70 characters
        /*$msg = "First line of text\nSecond line of text";
        $msg = wordwrap($msg,70);
        if(!mail("anh@at1ts.com","My subject",$msg)){
            var_dump(error_get_last()['message']);
            die("123456");
        }
        */
	}

	$msg = $tot_emails." email messages have been successfully sent.";
	header("Location: /?action=marketing&msg=$msg");
}
?>