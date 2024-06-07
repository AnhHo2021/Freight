<?php
switch($action) {
	case "updatelog":
		if($tracking){
			$sql = "INSERT INTO shipment_tracking SET ";
			foreach($_POST AS $k=>$b) {
				$sql .= "$k = '$b',";
			}
			$sql .= "created_admin_login_id = '$_LOGIN[id]',
			shipment_id = '$_GET[id]',
			date_created = NOW()";
			$success = $db->query($sql);
		}
		header("Location: ./?action=trackingfinder&id=$_GET[id]");
		exit;
	break;
	case "notes":
		$html["BODY"]=replace($vars,rf($htmlpath."trackingfinder_notes.html"));
	break;
	case "log":
		$sql="SELECT * FROM shipment_tracking WHERE shipment_id = '$_GET[id]' ORDER BY date_show DESC";
		$tracking_array=$db->query($sql);
		if($tracking_array) {
			foreach($tracking_array AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$v["date_show"] = formatDate($v["date_show"]);
				$vars["trackingfinder_log_row"] .= replace($v,rf($htmlpath."trackingfinder_log_row.html"));
			}
		} else {
			$vars["trackingfinder_log_row"] = "<tr><td colspan='2'>Currently no tracking updates available</td></tr>";
		}
		$html["BODY"]=replace($vars,rf($htmlpath."trackingfinder_log.html"));
	break;
	default:
		if($do=="requpdate"){
			//if(   eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$", $request_update_email)){
            if(filter_var($request_update_email, FILTER_VALIDATE_EMAIL)){
				$sql = "SELECT shipment.id as bol, shipment.o_po, shipment.d_po, shipment.shipped_tracknum, shipment.shipped_carrier_pronum, shipment.req_pickup_time_start, shipment.req_pickup_time_end, shipment.req_delivery_time_start, shipment.req_delivery_time_end, shipment.date_shipment, service.name as service, shipment.o_to, shipment.o_attention, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.d_to, shipment.d_attention, shipment.d_address1, shipment.d_address2, shipment.o_zip, shipment.d_city, shipment.d_state, shipment.d_zip, SUM(shipment_rate.rate) as charges FROM shipment INNER JOIN service ON service.id=shipment.service_id INNER JOIN shipment_rate ON shipment_rate.shipment_id=shipment.id INNER JOIN shipment_commodity ON shipment_commodity.shipment_id=shipment.id WHERE shipment.id = '$_GET[id]' GROUP BY shipment.id";
				$sel_shipment = $db->query($sql);
				if($sel_shipment){
					foreach($sel_shipment[0] as $n=>$v){$vv[$n] = $v;}
					$vv["carrier_contact_name"] = $request_update_contact;
					$vv["date_shipment"] = ($vv["date_shipment"] != "0000-00-00 00:00:00")?date("m/d/Y",strtotime($vv["date_shipment"])):"Not Available";
					$vv["origin"] = $vv["o_to"]." ".$vv["o_city"].", ".$vv["o_state"]." ".$vv["o_zip"];
					$vv["destination"] = $vv["d_to"]." ".$vv["d_city"].", ".$vv["d_state"]." ".$vv["d_zip"];

					$emailData=replace($vv,rf($htmlpath."tracking_carrier_status.html"));
					$to_email = $request_update_email;
//					$email_from = "support@norwayfreight.com";
					$email_from = "clint@myminicasa.com";
					$mail_subject="Norway Freight: Tracking Status Update Request";

					$headers = "X-Sender:  $to_email <$to_email>\n"; //
					$headers .="From: $email_from <$email_from>\n";
					$headers .= "Reply-To: $email_from <$email_from>\n";
					$headers .= "Date: ".date("r")."\n";
					$headers .= "Message-ID: <".date("YmdHis")."norway@".$_SERVER['SERVER_NAME'].">\n";
					$headers .= "Subject: $mail_subject\n";
					$headers .= "Return-Path: $email_from <$email_from>\n";
					$headers .= "Delivered-to: $email_from <$email_from>\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html;charset=ISO-8859-9\n";
					$headers .= "X-Priority: 1\n";
					$headers .= "Importance: High\n";
					$headers .= "X-MSMail-Priority: High\n";
					$headers .= "X-Mailer: PHP" . phpversion() . "!\n";

					$mail_body = $emailData."\n";
					$mail_body = stripslashes($mail_body);

                    $from_name ="Transport";
                    $receiver_name = $vv["d_to"];
                    mail_to($from_name,$receiver_name,$to_email,$mail_subject,$mail_body);
					//mail($to_email,$mail_subject,$mail_body,$headers);
				}
			}else{
				echo "<center><font color=red><b>Email Not Valid</b></font></center>";
			}
		}else if($do=="sendupdate"){
            //echo "<pre>";print_r("UPDATE shipment SET shipped_carrier_pronum='$shipped_carrier_pronum', status='$status' WHERE id = '$_GET[id]'");   echo "</pre>"; die();

			//update pro #
			$db->query("UPDATE shipment SET shipped_carrier_pronum='$shipped_carrier_pronum', status='$status' WHERE id = '$_GET[id]'");

			$sendto=array();
			foreach($_POST AS $n=>$v){
				if(substr($n,0,7)=="sendto_"){
					$id=substr($n,7);
					if(${"chk_sendto_".$id}){
						$sendto[]=$v;
					}
				}
			}
            $valid_sendtos =array();
			$has_sendto=false;
			if(count($sendto)){
				$has_sendto=true;
				foreach($sendto as $v){
					if(!strpos($v,"@")){
						$v = preg_replace('/[^0-9]+/','',$v);
						if(preg_match("/^[0-9]{10}$/",$v)){
							$v="1".$v."@efaxsend.com";
						}
					}
                    if(filter_var($v, FILTER_VALIDATE_EMAIL)) {
                        $valid_sendtos[]=$v;
                    }
					/*if(eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$", $v)){
						$valid_sendtos[]=$v;
					}*/
				}
			}
			if(count($valid_sendtos)>0){
				$sql = "SELECT shipment.id as bol, shipment.o_po, shipment.d_po, shipment.shipped_tracknum, shipment.shipped_carrier_pronum, shipment.req_pickup_time_start, shipment.req_pickup_time_end, shipment.req_delivery_time_start, shipment.req_delivery_time_end, shipment.date_shipment, service.name as service, shipment.o_to, shipment.o_attention, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.d_to, shipment.d_attention, shipment.d_address1, shipment.d_address2, shipment.o_zip, shipment.d_city, shipment.d_state, shipment.d_zip, SUM(shipment_rate.rate) as charges FROM shipment INNER JOIN service ON service.id=shipment.service_id INNER JOIN shipment_rate ON shipment_rate.shipment_id=shipment.id INNER JOIN shipment_commodity ON shipment_commodity.shipment_id=shipment.id WHERE shipment.id = '$_GET[id]' GROUP BY shipment.id";
				$sel_shipment = $db->query($sql);
				if($sel_shipment){
					$sql = "SELECT date_show, tracking FROM shipment_tracking WHERE shipment_id = '$_GET[id]' ORDER BY id DESC LIMIT 1";
					$sel_status = $db->query($sql);

					foreach($sel_shipment[0] as $n=>$v){$vv[$n] = $v;}
					$vv["latest_update"] = ($sel_status)?"<table cellpadding='5' cellspacing='5'><tr><td><b>".$sel_status[0]["tracking"]."</b></td><td>".date("m/d/Y g:i a",strtotime($sel_status[0]["date_show"]))." MST</td></tr></table>":"Not Available";
					$vv["date_shipment"] = ($vv["date_shipment"] != "0000-00-00 00:00:00")?date("m/d/Y",strtotime($vv["date_shipment"])):"Not Available";
					$vv["destination"] = $vv["d_to"]." ".$vv["d_city"].", ".$vv["d_state"]." ".$vv["d_zip"];

					$emailData=replace($vv,rf($htmlpath."tracking_customer_status.html"));
//					$email_from = "support@norwayfreight.com";
					$email_from = "clint@myminicasa.com";
					$mail_subject="Norway Freight: Tracking Status Update";

					$headers = "X-Sender:  $to_email <$to_email>\n"; //
					$headers .="From: $email_from <$email_from>\n";
					$headers .= "Reply-To: $email_from <$email_from>\n";
					$headers .= "Date: ".date("r")."\n";
					$headers .= "Message-ID: <".date("YmdHis")."norway@".$_SERVER['SERVER_NAME'].">\n";
					$headers .= "Subject: $mail_subject\n";
					$headers .= "Return-Path: $email_from <$email_from>\n";
					$headers .= "Delivered-to: $email_from <$email_from>\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Content-type: text/html;charset=ISO-8859-9\n";
					$headers .= "X-Priority: 1\n";
					$headers .= "Importance: High\n";
					$headers .= "X-MSMail-Priority: High\n";

					$mail_body = $emailData."\n";
					$mail_body = stripslashes($mail_body);

					foreach($valid_sendtos as $xx){
						mail($xx,$mail_subject,$mail_body,$headers);
					}
					//mail($to_email,$mail_subject,$mail_body,$headers);

					echo "<center><font color=red><b>Sent To: ".implode(", ",$valid_sendtos)."</b></font></center>";
				}
			}else{
				if($has_sendto){
					echo "<center><font color=red><b>Email(s) Not Valid</b></font></center>";
				}
			}

			// close window
			if($closewindow == "1"){
				echo "<script>window.opener.location.href='/index.php?action=shipments';window.opener.focus();window.close();</script>";
				die();
			}
		}

		$sql = "SELECT s.*,cus.company, cus.mail_address1, cus.mail_address2, cus.mail_city, cus.mail_state, cus.mail_zip, cus.main_contact_phone, cus.main_contact_fax, cus.main_contact_email, car.company AS carrier_company FROM shipment s, customer cus, carrier car WHERE s.shipped_carrier_id=car.id AND s.customer_id=cus.id AND s.id = '$_GET[id]'";
		$tracking_array = $db->query($sql);
		$vars = $tracking_array[0];

    $status_list = Array();

    if($vars["status"] != "invoiced"){
			$status_temp=getEnum("shipment","status");
			if($status_temp){
				foreach($status_temp as $svalue){
					if($svalue != "quote" && $svalue != "invoiced"){
						$status_list[]=$svalue;
					}
				}
			}
		}else{
			$status_list[]="invoiced";
		}

		$vars["status_options"] = "";
		foreach($status_list AS $status_value) {
			$selected = ($status_value == $vars["status"]) ? " SELECTED" : "";
			$vars["status_options"] .="<option value='$status_value'$selected>$status_value</option>\n";
		}

		$sql = "SELECT contact, email FROM carrier_contact WHERE carrier_id = '$vars[shipped_carrier_id]'";
		$email_array = $db->query($sql);
		if($email_array) {
			foreach($email_array AS $carrier_emails) {
				$vars["email_options"] .= "<option value='$carrier_emails[contact]_$carrier_emails[email]'>$carrier_emails[contact] ($carrier_emails[email])</option>\n";
			}
		}
		$vars["customer_address"] = "$vars[mail_address1] $vars[mail_address2]<br>$vars[mail_city] $vars[mail_state], $vars[mail_zip]";
		$vars["o_address"] = "$vars[o_address1] $vars[o_address2]<br>$vars[o_city] $vars[o_state], $vars[o_zip]";
		$vars["d_address"] = "$vars[d_address1] $vars[d_address2]<br>$vars[d_city] $vars[d_state], $vars[d_zip]";
		$date = getdate();
		$vars["today"] = date("Y-m-d H:i:s",$date[0]);
		$html["BODY"]=replace($vars,rf($htmlpath."trackingfinder.html"));
}
?>
