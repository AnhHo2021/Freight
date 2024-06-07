<?php

if($id){
	$vars["msg"] = ($msg)?$msg:"";
	$vars["id"] = $id;    

	//$sql = "SELECT shipment.service_id,shipment_rate.transit_days,shipment.id as bol, shipment.o_po, shipment.d_po, shipment.shipped_tracknum, shipment.shipped_carrier_pronum, shipment.req_pickup_time_start, shipment.req_pickup_time_end, shipment.req_delivery_time_start, shipment.req_delivery_time_end, shipment.shipped_pickup_date AS date_shipment, service.name as service, shipment.o_to, shipment.o_attention, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.d_to, shipment.d_attention, shipment.d_address1, shipment.d_address2, shipment.o_zip, shipment.d_city, shipment.d_state, shipment.d_zip, SUM(shipment_rate.rate) as charges FROM shipment INNER JOIN service ON service.id=shipment.service_id INNER JOIN shipment_rate ON shipment_rate.shipment_id=shipment.id INNER JOIN shipment_commodity ON shipment_commodity.shipment_id=shipment.id WHERE shipment.id = '$id' GROUP BY shipment.id";
    $sql = "SELECT shipment.service_id,shipment_rate.transit_days,shipment.id as bol, shipment.o_po, shipment.d_po, shipment.shipped_tracknum, shipment.shipped_carrier_pronum, shipment.req_pickup_time_start, shipment.req_pickup_time_end, shipment.req_delivery_time_start, shipment.req_delivery_time_end, shipment.req_delivery_date, shipment.shipped_pickup_date AS date_shipment, service.name as service, shipment.o_to, shipment.o_attention, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.d_to, shipment.d_attention, shipment.d_address1, shipment.d_address2, shipment.o_zip, shipment.d_city, shipment.d_state, shipment.d_zip, SUM(shipment_rate.rate) as charges FROM shipment INNER JOIN service ON service.id=shipment.service_id INNER JOIN shipment_rate ON shipment_rate.shipment_id=shipment.id INNER JOIN shipment_commodity ON shipment_commodity.shipment_id=shipment.id WHERE shipment.id = '$id' GROUP BY shipment.id";
	$sel_shipment = $db->query($sql);
	if($sel_shipment){
		//$sql = "SELECT SUM(shipment_commodity.pieces) as tot_pieces, SUM(shipment_commodity.weight) as weight, SUM(shipment_commodity.dim_w) as dim_weight FROM shipment_commodity WHERE shipment_commodity.shipment_id='$id'";
        $sql = "SELECT SUM(shipment_commodity.pieces) as tot_pieces, SUM(shipment_commodity.weight) as weight, shipment_commodity.description as description FROM shipment_commodity WHERE shipment_commodity.shipment_id='$id'";
		$sel_commod = $db->query($sql);
		if($sel_commod){
			$sel_shipment[0]["tot_pieces"] = $sel_commod[0]["tot_pieces"];
			$sel_shipment[0]["weight"] = $sel_commod[0]["weight"];
			$sel_shipment[0]["description"] = $sel_commod[0]["description"];
		}else{
			$sel_shipment[0]["tot_pieces"] = "";
			$sel_shipment[0]["weight"] = "";
			$sel_shipment[0]["description"] = "";
		}
        /*
		$sql = "SELECT class_list.class as freight FROM class_list INNER JOIN shipment_commodity ON shipment_commodity.class_list_id=class_list.id WHERE shipment_commodity.shipment_id = '$id'";
		$sel_classes = $db->query($sql);
		if($sel_classes){
			foreach($sel_classes as $v){$classes[] = $v["freight"];}
			$sel_shipment[0]["freight"] = implode(", ",$classes);
		}else{
			$sel_shipment[0]["freight"] = "";
		}
        */
		$vars["est_delivery"]="";
		foreach($sel_shipment[0] as $n=>$v){
			if($n=="d_attention" || $n=="o_attention"){
				$vars[$n] = ($v != "")?$v."<br>":"";
            }elseif($n=="req_delivery_date"){
                $vars["est_delivery"] = date("m/d/Y",strtotime($v));
			}elseif($n=="date_shipment"){
				$vars[$n] = date("m/d/Y",strtotime($v));
                /*
				list($y,$m,$d)=explode("-",$v);

				$DAYS=$sel_shipment[0]["transit_days"];
				switch($sel_shipment[0]["service_id"]){
					case 1: 
					case 2:
						break;
					case 3:
					case 4:
					case 5:
					case 6:
						break;
					case 7:
					case 8:
						// pickup date not counted
						$DAYS++;
						break;
				}
				// saturday
				$check = date("w",mktime(0,0,0,$m,$d+$DAYS,$y));
				if($check == "6"){
					$DAYS++;
				}
				// sunday
				$check = date("w",mktime(0,0,0,$m,$d+$DAYS,$y));
				if($check == "0"){
					$DAYS++;
				}

				$vars["est_delivery"] = date("m/d/Y",mktime(0,0,0,$m,$d+$DAYS,$y));
                 * 
                 */
			}else{
				$vars[$n] = $v;
			}
		}
	
		$sql = "SELECT date_show, tracking FROM shipment_tracking WHERE shipment_id = '".$sel_shipment[0]["bol"]."' ORDER BY date_show DESC";
		$sel_status = $db->query($sql);
		if($sel_status){
			$vars["date_show"] = date("m/d/Y g:i a",strtotime($sel_status[0]["date_show"]));
			foreach($sel_status as $v){
				$vars["updates"] .= '<tr>';
					$date_show = ($v["date_show"]!="0000-00-00 00:00:00")?date("m/d/Y",strtotime($v["date_show"])):"N/A";
					$vars["updates"] .= '<td>'.$date_show.'</td>';
					$date_show_time = ($v["date_show"]!="0000-00-00 00:00:00")?date("g:i a",strtotime($v["date_show"])):"N/A";
					$vars["updates"] .= '<td>'.$date_show_time.'</td>';
					$vars["updates"] .= '<td>'.$v["tracking"].'</td>';
				$vars["updates"] .= '</tr>';
			}
		}else{
			$vars["date_show"] = "Not Available";
			$vars["updates"] = '<tr><td colspan="3" align="center"><b>Sorry there are no status updates at this time.</b></td></tr>';
		}
		/*
		$vars["email_1"] = '';
		$vars["email_2"] = '';
		$vars["fax_1"] = '';
		$vars["fax_2"] = '';
		$vars["html_1"] = '';
		$vars["html_2"] = '';
		
		//send out email
		$emailData=replace($vars,rf($htmlpath."trackingview.html"));
		$to_email = $email_to;
		$email_from = "support@trollcompany.com";
		$mail_subject="Troll: Shipment Tracking Details";

		if($HTTP_POST_VARS && $email_to != ""){
			$headers = "X-Sender:  $to_email <$to_email>\n"; // 
			$headers .="From: $email_from <$email_from>\n";
			$headers .= "Reply-To: $email_from <$email_from>\n";
			$headers .= "Date: ".date("r")."\n";
			$headers .= "Message-ID: <".date("YmdHis")."newell@".$_SERVER['SERVER_NAME'].">\n";
			$headers .= "Subject: $mail_subject\n";
			$headers .= "Return-Path: $email_from <$email_from>\n";
			$headers .= "Delivered-to: $email_from <$email_from>\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html;charset=ISO-8859-9\n";
			$headers .= "X-Priority: 1\n";
			$headers .= "Importance: High\n";
			$headers .= "X-MSMail-Priority: High\n";
			$headers .= "X-Mailer: ThirstyPixel Mailer With PHP!\n";
			
			$mail_body="Below is a summary of shipment #".$sel_shipment[0]["shipped_tracknum"].".\n\n\n";
			$mail_body.=$emailData."\n";
			$mail_body = stripslashes($mail_body);
								
			mail($to_email,$mail_subject,$mail_body,$headers);

			$msg="Email has been successfully sent.";
			header("Location: /?action=trackingview&id=$id&msg=$msg");
			die();
		}elseif($HTTP_POST_VARS && $fax_to != ""){
			$vars["html_1"] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><base href="http://www.trolltransport.com/"><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><title>Shipment Details</title></head><body>';
			$vars["html_2"] = '</body></html>';
		
			$fileatt      = "trackingview.html"; 
			$fileatt_type = "text/html"; 
			$fileatt_name = "ship_details.html";
			$message="Below is a summary of shipment #".$sel_shipment[0]["shipped_tracknum"].".\n\n\n";
			
			// Read the file to be attached ('rb' = read binary) 
			$file = fopen($htmlpath."trackingview.html",'rb');
			$data = replace($vars,fread($file,filesize($htmlpath.$fileatt))); 
			fclose($file);
			
			// Generate a boundary string 
			$semi_rand = md5(time()); 
			$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
			  
			// Add the headers for a file attachment 
			$headers = "From: $email_from\r\n" .
					"Return-Path: $email_from\r\n" .
					 "MIME-Version: 1.0\r\n" .
					 "Content-Type: multipart/mixed;\r\n" .
					 " boundary=\"{$mime_boundary}\"";
			// Add a multipart boundary above the plain message 
			$message = "This is a multi-part message in MIME format.\n\n" . 
						"--{$mime_boundary}\n" . 
						"Content-Type: text/plain; charset=\"iso-8859-1\"\n" . 
						"Content-Transfer-Encoding: 7bit\n\n" . 
						$message . "\n\n";
			// Base64 encode the file data 
			$data = chunk_split(base64_encode($data));
			// Add file attachment to the message 
			$message .= "--{$mime_boundary}\n" . 
						 "Content-Type: {$fileatt_type};\n" . 
						 " name=\"{$fileatt_name}\"\n" . 
						 "Content-Disposition: attachment;\n" . 
						 " filename=\"{$fileatt_name}\"\n" . 
						 "Content-Transfer-Encoding: base64\n\n" . 
						 $data . "\n\n" . 
						 "--{$mime_boundary}--\n"; 
						 
			mail($fax_to."@efaxsend.com",$mail_subject,$message,$headers);
			
			//$emailpdf = new emailPDF("","18017482567@efaxsend.com","Troll Transport",$email_from,$email_subject,$emailData,$file);
			
			$msg="Fax has been successfully sent.";
			header("Location: /?action=trackingview&id=$id&msg=$msg");
			die();
		}else{
			$vars["email_1"] = '<b>Email this info to:</b>';
			$vars["email_2"] = '<input type="text" name="email_to" size="20"><input type="submit" value="Go">';
			$vars["fax_1"] = '<b>Fax this info to:</b>';
			$vars["fax_2"] = '<input type="text" name="fax_to" size="20"><input type="submit" value="Go">';
		}
        */
		//$html["BODY"]=replace($vars,rf($htmlpath."trackingview.html"));
	}
    else
    {
        $msg = "No Information Available";
    }
}
?>