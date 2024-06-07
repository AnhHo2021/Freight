<?php
require_once 'PHPMailer-5.2.27/PHPMailerAutoload.php';

function mail_to($from_name,$receiver_name,$to_email,$subject,$content,$path_file=null, $file_name=null){
    date_default_timezone_set('Etc/UTC');
    //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
    $body = '';
    $body.=$content;
    //print_r($body);
    //die();
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host =  "smtp.office365.com";
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPAuth = true;
    $mail->Username = "info@strongcontainers.com";
    $mail->Password = "Trouble54321!";
    $mail->setFrom("sales@strongcontainers.com", $from_name);
    $mail->addAddress($to_email, $receiver_name);

    $mail->Subject = $subject;
    if(!empty($path_file)){
        $mail->addAttachment($path_file, $file_name);
    }

    $tempDate = date("Y-m-d H:i:s");

    //$mail->Body    = $link;
    $mail->IsHTML(true); // send as HTML
    $mail->MsgHTML($body);

    if (!$mail->send()) {
        unset($Ob_manager);
        return $mail->ErrorInfo;
    } else {
        unset($Ob_manager);
        return 1;
    }

}
//----------------------------------------------------------
function fixDescArray($str){
	$str=str_replace("\r\n","",$str);
	$str=str_replace("\n","",$str);
	$str=str_replace('"','',$str);
	return $str;
}

function setInvoiceTotals($invoice_id){
	global $db;

	$sql = "SELECT (shipment_rate.rate + SUM(shipment_rate_accessorial.rate)) AS total_due FROM shipment INNER JOIN shipment_rate ON shipment_rate.shipment_id = shipment.id AND shipment_rate.id = shipment.shipped_shipment_rate_id INNER JOIN shipment_rate_accessorial ON shipment_rate_accessorial.shipment_rate_id = shipment_rate.id WHERE shipment.invoice_id = '$invoice_id' GROUP BY shipment.invoice_id";
	$sel_items = $db->query($sql);

	if($sel_items){
		$tot_invoice = $sel_items[0]["total_due"];

		//pull any payments applied
		$sel_payments = $db->query("SELECT SUM(amount) as tot_payments FROM invoice_payment WHERE invoice_id = '$invoice_id'");
		if($sel_payments){
			$real_invoice_total = $tot_invoice-$sel_payments[0]["tot_payments"];
		}else{
			$real_invoice_total = $tot_invoice;
		}
		$paid = ($real_invoice_total <= "0.00")?"closed":"open";
		$db->query("UPDATE invoice SET status='".$paid."', total_due='".$real_invoice_total."' WHERE id='$invoice_id'");
	}
}

function sendFax($marketing_type_id,$items){
	global $db,$pdfpath;

	foreach($items as $v){
		$sel_item = $db->query("SELECT * FROM marketing_letters WHERE id = '".$v."'");
		//$to      = "18017482567@efaxsend.com";
		//$to      = "18012074603@efaxsend.com";
		//$to        = "kbowen@thirstypixel.com";
		$fax = (substr($sel_item[0]["fax"],0,1) != "1")?"1".str_replace("-","",$sel_item[0]["fax"]):str_replace("-","",$sel_item[0]["fax"]);
		$to      = $fax."@efaxsend.com";
		$from = "letters@norwayfreight.com";
		$subject = "Norway Freight Letter";
		$message = "";

		// Generate a boundary string
		$semi_rand = md5(rand(100,9999).time());
		$mime_boundary = "----=_Multipart_Boundary_x{$semi_rand}x";

		$file_name    = $v.".pdf";
		$fileatt      = $pdfpath."generated/".$file_name;
		$fileatt_type = "application/pdf";
		$fileatt_name = "letter_".date("Y-m-d").".pdf";

		// Read the file to be attached ('rb' = read binary)
		$file = fopen($pdfpath."generated/".$file_name,"rb");
		$data = fread($file,filesize($pdfpath."generated/".$file_name));
		fclose($file);

		// Base64 encode the file data
		$data = chunk_split(base64_encode($data));

		// Add file attachment to the message
		$message_attachments .= "--{$mime_boundary}\n" .
					 "Content-Type: {$fileatt_type};\n" .
					 " name=\"{$fileatt_name}\"\n" .
					 "Content-Disposition: inline;\n" .
					 " filename=\"{$fileatt_name}\"\n" .
					 "Content-Transfer-Encoding: base64\n\n" .
					 $data . "\n\n";

		// Add the headers for a file attachment
		$headers =	"From: $from\n" .
					"Return-Path: $from\n" .
					"MIME-Version: 1.0\n" .
					"Content-Type: multipart/mixed;\n" .
					" boundary=\"{$mime_boundary}\"";

		// Add a multipart boundary above the plain message
		$message = "This is a multi-part message in MIME format.\n\n".
					$message_attachments ."--{$mime_boundary}--\n";

		mail($to, $subject, $message, $headers);
	}
}

function createLetter($created_admin_login_id,$customer_id,$shipment_id,$marketing_type_id,$letter_type,$company,$addr,$addr2,$city,$state,$zip,$email,$fax){
	//create marketing letter
	global $db;

//	$result = mysql_query("INSERT INTO marketing_letters SET date_created=NOW(), created_admin_login_id='$created_admin_login_id', date_updated=NOW(), marketing_type_id='$marketing_type_id', updated_admin_login_id='$created_admin_login_id', customer_id='$customer_id', shipment_id='$shipment_id', status='open', letter_type='$letter_type', company='$company', addr='$addr', addr2='addr2', city='$city', state='$state', zip='$zip', email='$email', fax='$fax'");
	$result = mysqli_query($db->conn, "INSERT INTO marketing_letters SET date_created=NOW(), created_admin_login_id='$created_admin_login_id', date_updated=NOW(), marketing_type_id='$marketing_type_id', updated_admin_login_id='$created_admin_login_id', customer_id='$customer_id', shipment_id='$shipment_id', status='open', letter_type='$letter_type', company='$company', addr='$addr', addr2='addr2', city='$city', state='$state', zip='$zip', email='$email', fax='$fax'");
	return $result;
}


function setItem($name,$value,$align='C'){
	global $pdf,$debug;

	$itemPos = getItemPos($name);
	if($itemPos){
		$x=$itemPos["x"]/100;
		$y=$itemPos["y"]/100;
		$w=$itemPos["w"]/100;
		$h=.15;
		
		/*echo "<pre>";
		    print_r("x=".$x." y=".$y." w=".$w);
		echo "</pre>";*/

		//debug for positioning
		if($debug){
			$pdf->SetDrawColor(51,255,51);
			$pdf->SetXY($x,$y);
			$pdf->Cell($w,$itemPos["h"]/100,"",1,0,'C',0,'');
		}

		$pdf->ClippingRect($itemPos["x"]/100,$itemPos["y"]/100,$itemPos["w"]/100,$itemPos["h"]/100,$debug);

		$DATAs=explode("\n",$value);
		 
		if(count($DATAs)==1){
			$TEXT=$DATAs[0];
			
			$pdf->SetXY($x,$y);
			$pdf->Cell($itemPos["w"]/100,$itemPos["h"]/100,$TEXT,0,0,$align,0,'');
		}else{
			foreach($DATAs as $WRITE){
				$pdf->SetXY($x,$y);
				if($WRITE){
					$pdf->CellFitScale($w,$h,$WRITE,0);
				}
				$y+=$h;
			}
		}
		$pdf->UnsetClipping();
	}
}

function getItemPos($name){
	global $itemArr;

	foreach($itemArr as $myv){
		list($field_name,$myv)=explode("=",trim($myv));
		$vv=explode(",",$myv);
		$fields_out[$field_name]=$vv;
	}

	foreach($fields_out["f_name"] as $key=>$v){
		if($v==$name){
			$ret["x"]=$fields_out["f_x"][$key];
			$ret["y"]=$fields_out["f_y"][$key];
			$ret["w"]=$fields_out["f_w"][$key];
			$ret["h"]=$fields_out["f_h"][$key];
		}
	}
	return $ret;
}

function sysTime($time){
	return date("g:i a",strtotime(date("Y-m-d")." ".$time));
}


function sysDate($date){
	if($date){return date("m/d/Y",strtotime($date));}
}

function sysDateTime($date){
	if($date){return date("m/d/Y g:i a",strtotime($date));}
}

function rf($file){
	if(is_file($file)){
		return implode("",file($file));
	}else{
		fwrite($fp = fopen($file,"w"),basename($file));
		fclose($fp);
		chmod($file,0777);
	}
}

function rf_waiting_release($file){
    if(is_file($file)){
       // print_r(file($file));
       // echo "</br>";
       // print_r('------------------------------------------------------------------');
       // echo "</br>";
        return implode("",file($file));
    }else{
        fwrite($fp = fopen($file,"w"),basename($file));
        fclose($fp);
        chmod($file,0777);
    }
}


function replace($array,$str){
	if(is_array($array)){
		foreach($array as $n=>$v){
			$search[]="[$n]";
			$replace[]="$v";
		}
		return str_replace($search,$replace,$str);
	}else{
		return $str;
	}
}

function replace_shipment_waiting_realse($array,$str){
    //print_r($str); die();
    if(is_array($array)){
        foreach($array as $n=>$v){
            $search[]="[$n]";
            $replace[]="$v";
        }
        return str_replace($search,$replace,$str);
    }else{
        return $str;
    }
}

function rf1($file){
    if(is_file($file)){
       // print_r(is_file($file));
      //  echo "</br>"; print_r("------------------------------"); echo "</br>";
        return implode("",file($file));
    }else{
        fwrite($fp = fopen($file,"w"),basename($file));
        fclose($fp);
        chmod($file,0777);
    }
}

function replace1($array,$str){
   // print_r($array); //die();
    if(is_array($array)){
        foreach($array as $n=>$v){
            $search[]="[$n]";
            $replace[]="$v";
        }
       echo "</br>";
        print_r("---------------////search//////---------------");
        print_r($search);
        echo "</br>";
        print_r("---------------///////replace///---------------");
        echo "</br>";
        print_r($replace);
        echo "</br>";
        print_r("---------------/////Str/////---------------");
        echo "</br>";
        print_r($str);
        return str_replace($search,$replace,$str);
    }else{
        return $str;
    }
}

function removeFromQS($var){
	global $QUERY_STRING,$_SERVER;

	$QUERY_STRING = $_SERVER["QUERY_STRING"];

	$vars=explode("&",$QUERY_STRING);
	if($vars){
		foreach($vars as $val){
			list($name,$value)=explode("=",$val);
			if(is_array($var)){
				if(!in_array($name,$var)){
					$newQS[]="$name=$value";
				}
			}else{
				if($name != $var){
					$newQS[]="$name=$value";
				}
			}
		}
	}
	if($newQS){
		$newQUERY_STRING=implode("&",$newQS);
	}else{
		$newQUERY_STRING=$QUERY_STRING;
	}

	return $newQUERY_STRING;
}


function getEnum($table,$col){
	global $db;
	$options=array();

//	$result=mysql_query("SHOW COLUMNS FROM $table LIKE '$col'");
	$result=mysqli_query($db->conn, "SHOW COLUMNS FROM $table LIKE '$col'");
//	if(mysql_num_rows($result)>0){
	if(mysqli_num_rows($result)>0){
//		$row=mysql_fetch_row($result);
		$row=mysqli_fetch_row($result);
		$options=explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
	}
	return $options;
}

function getFields($table){
	global $db;

	$ret = array();

//	$tbl = mysql_query("SHOW COLUMNS FROM $table");
	$tbl = mysqli_query($db->conn,"SHOW COLUMNS FROM $table");
	if($tbl){
//		while($row=mysql_fetch_array($tbl)) {
		while($row=mysqli_fetch_array($tbl)) {
			$ret[$row["Field"]]=$_POST[$row["Field"]];
		}
	}

	return $ret;
}

function formatDate($date_string) {
	$timestamp = strtotime($date_string);
	$date = date("F jS, Y - g:i a",$timestamp);
	return $date;
}

function formatDateOnly($date_string) {
	$timestamp = strtotime($date_string);
	$date = date("F jS, Y",$timestamp);
	return $date;
}

function formatTimeOnly($time_string) {
	$timestamp = strtotime($time_string);
	$time = date("g:i a",$timestamp);
	return $time;
}

/**
 * create_time_range
 *
 * @param mixed $start start time, e.g., 9:30am or 9:30
 * @param mixed $end   end time, e.g., 5:30pm or 17:30
 * @param string $by   1 hour, 1 mins, 1 secs, etc.
 * @access public
 * @return void
 */
function create_time_range($start, $end, $by='30 mins') {

    $start_time = strtotime($start);
    $end_time   = strtotime($end);

    $current    = time();
    $add_time   = strtotime('+'.$by, $current);
    $diff       = $add_time-$current;

    $times = array();
    while ($start_time < $end_time) {
        $times[] = $start_time;
        $start_time += $diff;
    }
    $times[] = $start_time;
    return $times;
}
?>