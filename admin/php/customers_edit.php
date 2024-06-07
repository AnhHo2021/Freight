<?php

$DEFAULT_SALES=5;
$DEFAULT_FREIGHT=5;

function makeTypeOpts($selected)
{
  $ret = "";
  $types = getEnum("customer", "type");
  
  foreach ($types as $t)
    $ret .= "<option value=\"$t\"" . ($selected == $t ? " selected" : "") . ">$t</option>";
  
  return($ret);
}

switch($action){
	case "update";
        //die("sao lai vao day");
		if($_POST) {
			if(!$_POST["freight_admin_login_id"] || !$_POST["sales_admin_login_id"] || !$_POST["company"] || !$_POST["mail_address1"] || !$_POST["mail_city"] || !$_POST["mail_state"] || !$_POST["mail_zip"]
              //  || !$_POST["bill_address1"] || !$_POST["bill_city"] || !$_POST["bill_state"] || !$_POST["bill_zip"]  || !$_POST["main_contact_name"] || !$_POST["main_contact_phone"]
            ){
				$error_messages[] = "Please complete all required fields";
			}
			//MAKE SURE NOBODY ELSE HAS THE COMPANY NAME POSTED.
			$sql = "SELECT id FROM customer WHERE company = '$_POST[company]' AND id != '$_GET[id]'";
			$duplicate_check = $db->query($sql);
			if($duplicate_check) {
				$error_messages[] = "That company name is already in use (# {$duplicate_check[0]["id"]}).  Please enter another name.";
			}
			if(!$error_messages){
				if(!empty($_GET["id"])) {
					$sql_prefix = "UPDATE customer SET ";
					$sql_suffix = ", updated_admin_login_id = '$_LOGIN[id]', date_updated = NOW() WHERE id = '$_GET[id]'";
				} else {
					$sql_prefix = "INSERT INTO customer SET ";
					$sql_suffix = ", created_admin_login_id = '$_LOGIN[id]', date_created = NOW()";
				}
        
				$known_shipper=($_POST[known_shipper_ref]!="")?1:0;
				$sql = "$sql_prefix
				company = '$_POST[company]',
				sales_admin_login_id = '$_POST[sales_admin_login_id]',
				freight_admin_login_id = '$_POST[freight_admin_login_id]',
				mail_address1 = '$_POST[mail_address1]',
				mail_address2 = '$_POST[mail_address2]',
				mail_city = '$_POST[mail_city]',
				mail_state = '$_POST[mail_state]',
				mail_zip = '$_POST[mail_zip]',
				bill_address1 = '$_POST[bill_address1]',
				bill_address2 = '$_POST[bill_address2]',
				bill_city = '$_POST[bill_city]',
				bill_state = '$_POST[bill_state]',
				bill_zip = '$_POST[bill_zip]',
				main_contact_name = '$_POST[main_contact_name]',
				main_contact_phone = '$_POST[main_contact_phone]',
				main_contact_cell = '$_POST[main_contact_cell]',
				main_contact_fax = '$_POST[main_contact_fax]',
				main_contact_email = '$_POST[main_contact_email]',
				bill_contact_name = '$_POST[bill_contact_name]',
				bill_contact_phone = '$_POST[bill_contact_phone]',
				bill_contact_cell = '$_POST[bill_contact_cell]',
				bill_contact_fax = '$_POST[bill_contact_fax]',
				bill_contact_email = '$_POST[bill_contact_email]',
				alt1_contact_name = '$_POST[alt1_contact_name]',
				alt1_contact_phone = '$_POST[alt1_contact_phone]',
				alt1_contact_cell = '$_POST[alt1_contact_cell]',
				alt1_contact_fax = '$_POST[alt1_contact_fax]',
				alt1_contact_email = '$_POST[alt1_contact_email]',
				alt2_contact_name = '$_POST[alt2_contact_name]',
				alt2_contact_phone = '$_POST[alt2_contact_phone]',
				alt2_contact_cell = '$_POST[alt2_contact_cell]',
				alt2_contact_fax = '$_POST[alt2_contact_fax]',
				alt2_contact_email = '$_POST[alt2_contact_email]',
				known_shipper = '$known_shipper',
				known_shipper_ref = '$_POST[known_shipper_ref]',
				removed = '0',
        type = '$_POST[type]'
				$sql_suffix";
                //die($sql);
				$success = $db->query($sql);

				$customer_id=($_GET[id])?$_GET[id]:$db->lastid();
				header("Location: index.php?action=customers_edit&id=$customer_id&saved");
				exit();
			}
		}
	case "edit":
		// for access level
		if($SALES_REP_FILTER && $_GET["id"]){	
			$check = $db->query("SELECT id FROM customer WHERE id='$_GET[id]' $SALES_REP_FILTER");
			if(!$check){
				header("Location: index.php?action=customers_add");
				die();
			}
		}

		if($_GET["id"]) {
			$sql = "SELECT cus.*,c.fname AS created_fname,c.lname AS created_lname,u.fname AS updated_fname,u.lname AS updated_lname FROM customer cus LEFT JOIN admin_login c ON cus.created_admin_login_id=c.id LEFT JOIN admin_login u ON cus.updated_admin_login_id=u.id WHERE cus.id = '$_GET[id]'";
			$customer_data = $db->query($sql);
			
			// grab some numbers
			$quote_data = $db->query("SELECT SUBSTRING(date_created,1,7) AS month,SUBSTRING(date_created,1,4) AS year,COUNT(id) AS NUM FROM shipment WHERE shipment.customer_id='$_GET[id]' AND shipment.status != 'cancelled' AND (SUBSTRING(date_created,1,4)='".date("Y")."' OR SUBSTRING(date_created,1,4)='".date("Y",strtotime("last year"))."') GROUP BY SUBSTRING(date_created,1,7)");
			if($quote_data){
				foreach($quote_data as $v){
					if($v["year"]==date("Y")){
						$ytd_quotes+=$v["NUM"];
					}
					if($v["month"]==date("Y-m")){
						$mtd_quotes+=$v["NUM"];
					}
					if($v["year"]==date("Y",strtotime("last year"))){
						$lytd_quotes+=$v["NUM"];
					}
				}
			}
			$shipment_data = $db->query("SELECT SUBSTRING(shipped_pickup_date,1,7) AS month,SUBSTRING(shipped_pickup_date,1,4) AS year,COUNT(id) AS NUM FROM shipment WHERE shipment.customer_id='$_GET[id]' AND shipment.shipped='1' AND shipment.status != 'cancelled' AND (SUBSTRING(shipped_pickup_date,1,4)='".date("Y")."' OR SUBSTRING(shipped_pickup_date,1,4)='".date("Y",strtotime("last year"))."') GROUP BY SUBSTRING(shipped_pickup_date,1,7)");
			if($shipment_data){
				foreach($shipment_data as $v){
					if($v["year"]==date("Y")){
						$ytd_shipments+=$v["NUM"];
					}
					if($v["month"]==date("Y-m")){
						$mtd_shipments+=$v["NUM"];
					}
					if($v["year"]==date("Y",strtotime("last year"))){
						$lytd_shipments+=$v["NUM"];
					}
				}
			}

			$invoice_data = $db->query("SELECT SUBSTRING(invoice.invoice_date,1,7) AS month,SUBSTRING(invoice.invoice_date,1,4) AS year,SUM(invoice.cost) AS cost,SUM(invoice.profit) AS profit,SUM(invoice.total) AS total FROM invoice INNER JOIN shipment ON invoice.shipment_id=shipment.id WHERE shipment.customer_id='$_GET[id]' AND shipment.status != 'quote' AND (SUBSTRING(invoice.invoice_date,1,4)='".date("Y")."' OR SUBSTRING(invoice.invoice_date,1,4)='".date("Y",strtotime("last year"))."') GROUP BY SUBSTRING(invoice.invoice_date,1,7)");
			if($invoice_data){
				foreach($invoice_data as $v){
					if($v["year"]==date("Y")){
						$ytd_revenue+=$v["total"];
						$ytd_profit+=$v["profit"];						
					}
					if($v["month"]==date("Y-m")){
						$mtd_revenue+=$v["total"];
						$mtd_profit+=$v["profit"];
					}
					if($v["year"]==date("Y",strtotime("last year"))){
						$lytd_revenue+=$v["total"];
						$lytd_profit+=$v["profit"];						
					}
				}
			}
			$last_quote_temp = $db->query("SELECT MAX(shipment.date_created) AS last_quote FROM shipment WHERE shipment.customer_id='$_GET[id]' AND shipment.status != 'cancelled'");
			if($last_quote_temp && $last_quote_temp[0]["last_quote"] != "0000-00-00 00:00:00"){$last_quote=sysDate($last_quote_temp[0]["last_quote"]);}else{$last_quote="N/A";}
			$last_shipment_temp = $db->query("SELECT MAX(shipment.shipped_pickup_date) AS last_shipment FROM shipment WHERE shipment.customer_id='$_GET[id]' AND shipment.status != 'cancelled' AND shipment.shipped='1'");
			if($last_shipment_temp && $last_shipment_temp[0]["last_shipment"] != "0000-00-00 00:00:00"){$last_shipment=sysDate($last_shipment_temp[0]["last_shipment"]);}else{$last_shipment="N/A";}
			$customer_data[0]["mtd_quotes"]=number_format($mtd_quotes);
			$customer_data[0]["ytd_quotes"]=number_format($ytd_quotes);
			$customer_data[0]["lytd_quotes"]=number_format($lytd_quotes);
			$customer_data[0]["mtd_shipments"]=number_format($mtd_shipments);
			$customer_data[0]["ytd_shipments"]=number_format($ytd_shipments);
			$customer_data[0]["lytd_shipments"]=number_format($lytd_shipments);
			$customer_data[0]["mtd_revenue"]=number_format($mtd_revenue,2);
			$customer_data[0]["ytd_revenue"]=number_format($ytd_revenue,2);
			$customer_data[0]["lytd_revenue"]=number_format($lytd_revenue,2);
			$customer_data[0]["mtd_profit"]=number_format($mtd_profit,2);
			$customer_data[0]["ytd_profit"]=number_format($ytd_profit,2);
			$customer_data[0]["lytd_profit"]=number_format($lytd_profit,2);
			$customer_data[0]["last_quote"]=$last_quote;
			$customer_data[0]["last_shipment"]=$last_shipment;

			$vars["customers_more_info"] = replace($customer_data[0], rf($htmlpath."customers_more_info.html"));
			$vars["customers_more_info_2"] = replace($customer_data[0], rf($htmlpath."customers_more_info_2.html"));
			$vars["customers_more_info_3"] = replace($customer_data[0], rf($htmlpath."customers_more_info_3.html"));

			$vars["customers_fak_addresses"] = replace($u,rf($htmlpath."customers_fak_addresses.html"));
			$vars["customers_addrbook"] = replace($u,rf($htmlpath."customers_addrbook.html"));
		}
		if($customer_data) {
			foreach($customer_data[0] as $n=>$v){
				$vars[$n]=($_POST[$n])?$_POST[$n]:$v;
			}

      $vars["TYPE_OPTS"] = makeTypeOpts($customer_data[0]["type"]);
		} else {
			$vars = getFields("customer");
			$vars["customers_more_info"] = "";
			$vars["customers_more_info_2"] = "";
			$vars["customers_more_info_3"] = "";
			$vars["customers_fak_addresses"]="";
			$vars["customers_addrbook"]="";
			$vars["sales_admin_login_id"]=$DEFAULT_SALES;
			$vars["freight_admin_login_id"]=$DEFAULT_FREIGHT;
      //$vars["TYPE_OPTS"] = makeTypeOpts(false);
            $vars["TYPE_OPTS"] = makeTypeOpts("general");
		}
		$vars["error_message"]=($error_messages)?implode("<br>",$error_messages)."<br><br>":"";

		if(!$error_messages){
			$vars["error_message"] = "";
		}
		//GETTING SALES AND FREIGHT REPS
		$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
		$admins = $db->query($sql);
		$vars["sales_reps"] = "";
		foreach($admins AS $v) {
			$s=($vars["sales_admin_login_id"] == $v["id"])?" selected":"";
			$vars["sales_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}
		$vars["freight_reps"] = "";
		foreach($admins AS $v) {
			$s=($vars["freight_admin_login_id"] == $v["id"])?" selected":"";
			$vars["freight_reps"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}
		//GETTING STATES FOR SELECT STATEMENTS.
		$sql = "SELECT * FROM state ORDER BY state";
		$states = $db->query($sql);
		$vars["mailing_states"] = "";
		foreach($states AS $v) {
			$s=($vars["mail_state"] == $v["code"])?" selected":"";
			$vars["mailing_states"] .= "<option value='$v[code]'$s>$v[state]</option>\n";
		}
		foreach($states AS $v) {
			$s=($vars["bill_state"] == $v["code"])?" selected":"";
			$vars["billing_states"] .= "<option value='$v[code]'$s>$v[state]</option>\n";
			
		
		}
		if(!$vars["date_created"] || $vars["date_created"] == "0000-00-00 00:00:00"){
			$vars["show_date_created"] = "none";
		}else{
			$vars["show_date_created"] = "inline";
			$vars["date_created"] = formatDate($vars["date_created"]);
			$vars["creator"] = "$vars[created_fname] $vars[created_lname]";
		}
		if(!$vars["date_updated"] || $vars["date_updated"] == "0000-00-00 00:00:00"){
			$vars["show_date_updated"] = "none";
		}else{			
			$vars["show_date_updated"] = "inline";
			$vars["date_updated"] = formatDate($vars["date_updated"]);
			$vars["updater"] = "$vars[updated_fname] $vars[updated_lname]";
		}
		
		
		$u["id"] = $_GET["id"];
		$vars["error_message"]=(isset($saved) && !$error_messages)?"Customer Saved.<br><br>":"";
		
		$html["BODY"]=replace($vars,rf($htmlpath."customers_edit.html"));
		break;
}

		
?>
