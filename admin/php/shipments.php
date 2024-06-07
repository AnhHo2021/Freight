<?php
switch($action){
	case "bol-top":
	case "bol-main":
	case "bol":
		include("shipments_bol.php");
		break;
	case "rates":
		break;
	case "notes":
		break;
	case "logins":
		break;
	case "invoice":
		header("Location: ./?action=accounting_edit&id=$_GET[id]");
		break;
	case "trackpop":
	case "bolpop":
	case "view":
	case "update";
	case "edit":
		include("shipments_edit.php");
		break;
	case "history":
		$v["customer_id"]=$customer_id;
		//ORDERING LOGIC.
		$v["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC";
		$v["asc_status"] = $_GET["asc_status"] == "DESC" ? "ASC" : "DESC";
		$v["asc_carrier"] = $_GET["asc_carrier"] == "DESC" ? "ASC" : "DESC";
		$v["asc_terminal"] = $_GET["asc_terminal"] == "DESC" ? "ASC" : "DESC";
		$v["asc_pickup_date"] = $_GET["asc_pickup_date"] == "DESC" ? "ASC" : "DESC";
		$v["asc_delivery_date"] = $_GET["asc_delivery_date"] == "DESC" ? "ASC" : "DESC";
		$v["asc_o_city"] = $_GET["asc_o_city"] == "DESC" ? "ASC" : "DESC";
		$v["asc_d_city"] = $_GET["asc_d_city"] == "DESC" ? "ASC" : "DESC";
		$v["asc_cost"] = $_GET["asc_cost"] == "DESC" ? "ASC" : "DESC";
		$v["asc_total"] = $_GET["asc_total"] == "DESC" ? "ASC" : "DESC";
		$v["asc_profit"] = $_GET["asc_profit"] == "DESC" ? "ASC" : "DESC";
		$v["asc_service"] = $_GET["asc_service"] == "DESC" ? "ASC" : "DESC";
		$v["asc_tracking"] = $_GET["asc_tracking"] == "DESC" ? "ASC" : "DESC";
		$v["asc_o_terminal"] = $_GET["asc_o_terminal"] == "DESC" ? "ASC" : "DESC";
		$v["url"] = "";
		switch($_GET["sort"]){
			case "id";
				$orderby = "s.id";
				if($v["asc_id"] == "DESC") $orderby .=" DESC";
				break;
			case "status";
				$orderby = "s.status";
				if($v["asc_status"] == "DESC") $orderby .=" DESC";
				break;
			case "delivery_date";
				$orderby = "shipped_delivery_date";
				if($v["asc_delivery_date"] == "DESC") $orderby .=" DESC";
				break;
			case "pickup_date";
				$orderby = "shipped_pickup_date";
				if($v["asc_pickup_date"] == "DESC") $orderby .=" DESC";
				break;
			case "o_city";
				$orderby = "o_city";
				if($v["asc_o_city"] == "DESC") $orderby .=" DESC";
				break;
			case "d_city";
				$orderby = "d_city";
				if($v["asc_d_city"] == "DESC") $orderby .=" DESC";
				break;
			case "cost";
				$orderby = "cost";
				if($v["asc_cost"] == "DESC") $orderby .=" DESC";
				break;
			case "total";
				$orderby = "total";
				if($v["asc_total"] == "DESC") $orderby .=" DESC";
				break;
			case "profit";
				$orderby = "profit";
				if($v["asc_profit"] == "DESC") $orderby .=" DESC";
				break;
			case "carrier";
				$orderby = "carrier";
				if($v["asc_carrier"] == "DESC") $orderby .=" DESC";
				break;
			case "tracking";
				$orderby = "shipped_tracknum";
				if($v["asc_tracking"] == "DESC") $orderby .=" DESC";
				break;
			case "o_terminal";
				$orderby = "o_terminal";
				if($v["asc_o_terminal"] == "DESC") $orderby .=" DESC";
				break;
			case "service";
				$orderby = "service";
				if($v["asc_service"] == "DESC") $orderby .=" DESC";
				break;
			default:
				$orderby = "shipped_pickup_date DESC";
		}

		//$sql = "SELECT s.*,cus.company,i.cost,i.total,i.profit,car.company AS carrier, cto.name AS o_terminal, ctd.name AS d_terminal, service.name AS service, s.shipped_tracknum AS tracking FROM shipment s INNER JOIN shipment_rate sr ON sr.id=s.shipped_shipment_rate_id INNER JOIN service ON sr.service_id=service.id LEFT JOIN carrier_terminal cto ON s.shipped_o_carrier_terminal_id=cto.id LEFT JOIN carrier_terminal ctd ON s.shipped_d_carrier_terminal_id=ctd.id INNER JOIN carrier car ON s.shipped_carrier_id=car.id LEFT JOIN customer cus ON s.customer_id=cus.id LEFT JOIN invoice i ON s.invoice_id=i.id WHERE s.status != 'quote' AND s.status != 'cancelled' AND s.customer_id='$customer_id' ORDER BY $orderby";
		$sql = "SELECT s.*,s.shipped_carrier_id AS carrier_id,
		CONCAT(s.o_to,'<br>',s.o_address1,' ',s.o_address2,'<br>',s.o_city,', ',s.o_state,' ',s.o_zip,'<br>',s.o_contact_name,'<br>ph: ',s.o_contact_phone,'<br>fx: ',s.o_contact_fax,'<br>','po: ',s.o_po) AS o_popup,
		CONCAT(s.d_to,'<br>',s.d_address1,' ',s.d_address2,'<br>',s.d_city,', ',s.d_state,' ',s.d_zip,'<br>',s.d_contact_name,'<br>ph: ',s.d_contact_phone,'<br>fx: ',s.d_contact_fax,'<br>','po: ',s.d_po) AS d_popup,
		cus.company,
		CONCAT(cus.mail_address1,' ',cus.mail_address2,'<br>',cus.mail_city,', ',cus.mail_state,' ',cus.mail_zip,'<br>',cus.main_contact_name,'<br>ph: ',cus.main_contact_phone,'<br>fx: ',cus.main_contact_fax) AS cus_popup,cus.known_shipper,i.cost,i.total,i.profit,car.company AS carrier,
		CONCAT('acct: ',car.account_num,'<br>',car.address1,' ',car.address2,'<br>',car.city,', ',car.state,' ',car.zip,'<br>ph: ',car.phone,'<br>fx: ',car.fax) AS car_popup, cto.code AS o_terminal,CONCAT(cto.name,'<br>',cto.address1,' ',cto.address2,'<br>',cto.city,', ',cto.state,' ',cto.zip,'<br>',cto.contact,'<br>ph: ',cto.phone,'<br>fx: ',cto.fax) AS oterm_popup,
		ctd.code AS d_terminal,
		CONCAT(ctd.name,'<br>',ctd.address1,' ',ctd.address2,'<br>',ctd.city,', ',ctd.state,' ',ctd.zip,'<br>',ctd.contact,'<br>ph: ',ctd.phone,'<br>fx: ',ctd.fax) AS dterm_popup, service.name AS service,
		CONCAT(sr.transit_days,' day transit') AS service_popup, s.shipped_tracknum AS tracking
		FROM shipment s INNER JOIN shipment_rate sr ON sr.id=s.shipped_shipment_rate_id INNER JOIN service ON sr.service_id=service.id LEFT JOIN carrier_terminal cto ON s.shipped_o_carrier_terminal_id=cto.id LEFT JOIN carrier_terminal ctd ON s.shipped_d_carrier_terminal_id=ctd.id INNER JOIN carrier car ON s.shipped_carrier_id=car.id LEFT JOIN customer cus ON s.customer_id=cus.id LEFT JOIN invoice i ON s.invoice_id=i.id WHERE s.status != 'quote' AND s.status != 'cancelled' AND s.customer_id='$customer_id' ".str_replace("customer","cus",$SALES_REP_FILTER)." ORDER BY $orderby";
		$list_array_raw = $db->query($sql);

		//CREATE AN ARRAY WITH ALL ATTRIBUTES, SO WE DON'T HAVE TO ITERATE FOR THE SAME THINGS BELOW.
		if($list_array_raw) {
			foreach($list_array_raw AS $k => $l) {
				foreach($l AS $o=>$p) {
					$list_array[$k][$o] = $p;
                   // print_r("k =".$k);
                    //print_r("o =".$o);
				}

				if(substr($l["shipped_pickup_date"],0,4) != "0000") {
					$list_array[$k]["pickup_date"] = formatDateOnly($l["shipped_pickup_date"]);
				} else {
					$list_array[$k]["pickup_date"] = "N/A";
				}
				if(substr($l["shipped_delivery_date"],0,4) != "0000") {
					$list_array[$k]["delivery_date"] = formatDateOnly($l["shipped_delivery_date"]);
				} else {
					$list_array[$k]["delivery_date"] = "N/A";
				}
				$list_array[$k]["cost"] = "\$$l[cost]";
				$list_array[$k]["profit"] = "\$$l[profit]";
				$list_array[$k]["total"] = "\$$l[total]";
				$list_array[$k]["origin"] = "$l[o_city] $l[o_state], $l[o_zip]";
				$list_array[$k]["destination"] = "$l[d_city] $l[d_state], $l[d_zip]";
			}
		}

		$v["pickup_date"] = ($_GET["sort"] == "pickup_date") ? "<u>Pickup Date</u>" : "Pickup Date";
		$v["delivery_date"] = ($_GET["sort"] == "delivery_date") ? "<u>Delivery Date</u>" : "Delivery Date";
		$v["customer"] = ($_GET["sort"] == "company") ? "<u>Customer</u>" : "Customer";
		$v["carrier"] = ($_GET["sort"] == "carrier") ? "<u>Carrier</u>" : "Carrier";
		$v["terminal"] = ($_GET["sort"] == "terminal") ? "<u>Terminal</u>" : "Terminal";
		$v["service"] = ($_GET["sort"] == "service") ? "<u>Service</u>" : "Service";
		$v["origin"] = ($_GET["sort"] == "o_city") ? "<u>Origin</u>" : "Origin";
		$v["destination"] = ($_GET["sort"] == "d_city") ? "<u>Destination</u>" : "Destination";
		$v["tracking"] = ($_GET["sort"] == "tracking") ? "<u>Tracking</u>" : "Tracking";
		$v["cost"] = ($_GET["sort"] == "cost") ? "<u>Cost</u>" : "Cost";
		$v["invoice"] = ($_GET["sort"] == "total") ? "<u>Invoice</u>" : "Invoice";
		$v["margin"] = ($_GET["sort"] == "profit") ? "<u>Margin</u>" : "Margin";

		if($list_array) {
			foreach($list_array AS $vv) {
				$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$v["rows"] .= replace($vv,rf($htmlpath."shipments_history_row.html"));
			}
		}
		if(!$v["rows"]) $v["rows"] = "<tr><td colspan='3'>No shipments found</td></tr>";
		$vars["shipments"]=replace($v,rf($htmlpath."shipments_history_table.html"));

		$html["LOCATION"] = "<h1>SHIPMENTS : HISTORY</h1>";

		$html["BODY"]=replace($vars,rf($htmlpath."shipments_history.html"));
		break;
	case "list":
	default:
		if(isset($_POST["status"])) {
			foreach($_POST AS $k=>$h) {
				if($k != "status" && substr($k,0,10) != "checkall__") {
					$in .= "'$k',";
				}
			}
			if($in){
				$in = trim($in,",");
                //echo "<pre>";print_r($in);echo "</pre>"; die();
				$sql = "UPDATE shipment SET status='$_POST[status]' WHERE id IN($in)";
				$success = $db->query($sql);

				// if being set to delivery get last tracking update
				if($_POST["status"] == "delivered"){
					$tracking_update = $db->query("SELECT shipment_id,MAX(date_show) AS max_track_date FROM shipment_tracking WHERE shipment_id IN ($in) GROUP BY shipment_id");
					if($tracking_update){
						foreach($tracking_update as $v){
							$db->query("UPDATE shipment SET shipped_delivery_date='$v[max_track_date]' WHERE id='$v[shipment_id]'");
						}
					}
				}
			}
		}

		if(isset($_POST["check"])) {
			foreach($_POST AS $k=>$g) {
				if($k != "check") $cookie_string .= "$k,";
			}
			$cookie_string = trim($cookie_string,",");
			setcookie("norway_freight_status",$cookie_string);
			header("Location: $_SERVER[REQUEST_URI]");
		}
		$html["LOCATION"] = "<h1>SHIPMENTS : LIST</h1>";

		//SETTING ADMIN FILTRATION
//		$admin_filter = $_LOGIN["id"];
//		if($_LOGIN["access_level"] == "admin") {
//			if(isset($_GET["filter"])) {
//				$admin_filter = $_GET["filter"];
//			}
//			//SETTING FILTER OPTIONS.
//			$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
//			$admins = $db->query($sql);
//			foreach($admins AS $admin_list) {
//				$s=($admin_filter == $admin_list["id"]) ? " selected":"";
//				$filter_options .= "<option value='$admin_list[id]'$s>$admin_list[lname], $admin_list[fname] ($admin_list[access_level])</option>\n";
//			}
//			$vars["admin_filter"] = "<td NOWRAP>View Filter :</td><form method='GET'><td NOWRAP><input type='hidden' name='action' value='$_GET[action]'><select name='filter'>$filter_options</select><input type='submit' value='go'></td></form>";
//		} else {
			$vars["admin_filter"] = "";
//		}



		//GETTING STATUSES
		if($status){unset($status);}
		$status_temp=getEnum("shipment","status");
		if($status_temp){
			foreach($status_temp as $svalue){
				if($svalue != "quote" && $svalue != "invoiced"){
					$status[]=$svalue;
				}
			}
		}

		//ORDERING LOGIC.
		$v["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC";
		$v["asc_req_pickup_date"] = $_GET["asc_req_pickup_date"] == "DESC" ? "ASC" : "DESC";
		$v["asc_req_pickup_time_start"] = $_GET["asc_req_pickup_time_start"] == "DESC" ? "ASC" : "DESC";
		$v["asc_req_delivery_date"] = $_GET["asc_req_delivery_date"] == "DESC" ? "ASC" : "DESC";
		$v["asc_req_delivery_time_start"] = $_GET["asc_req_delivery_time_start"] == "DESC" ? "ASC" : "DESC";
		$v["asc_company"] = $_GET["asc_company"] == "DESC" ? "ASC" : "DESC";
		$v["asc_o_city"] = $_GET["asc_o_city"] == "DESC" ? "ASC" : "DESC";
		$v["asc_d_city"] = $_GET["asc_d_city"] == "DESC" ? "ASC" : "DESC";
		$v["asc_cost"] = $_GET["asc_cost"] == "DESC" ? "ASC" : "DESC";
		$v["asc_total"] = $_GET["asc_total"] == "DESC" ? "ASC" : "DESC";
		$v["asc_profit"] = $_GET["asc_profit"] == "DESC" ? "ASC" : "DESC";
		$v["asc_carrier"] = $_GET["asc_carrier"] == "DESC" ? "ASC" : "DESC";
		$v["asc_o_terminal"] = $_GET["asc_o_terminal"] == "DESC" ? "ASC" : "DESC";
		$v["asc_service"] = $_GET["asc_terminal"] == "DESC" ? "ASC" : "DESC";
		$v["asc_tracking"] = $_GET["asc_tracking"] == "DESC" ? "ASC" : "DESC";
		$v["url"] = "";
		switch($_GET["sort"]){
			case "id";
				$orderby = "s.id";
				if($v["asc_id"] == "DESC") $orderby .=" DESC";
				break;
			case "req_delivery_date";
				$orderby = "req_delivery_date";
				if($v["asc_req_delivery_date"] == "DESC") $orderby .=" DESC";
				break;
			case "req_delivery_time_start";
				$orderby = "req_delivery_time_start";
				if($v["asc_req_delivery_time_start"] == "DESC") $orderby .=" DESC";
				break;
			case "req_pickup_date";
				$orderby = "req_pickup_date";
				if($v["asc_req_pickup_date"] == "DESC") $orderby .=" DESC";
				break;
			case "req_pickup_time_start";
				$orderby = "req_pickup_time_start";
				if($v["asc_req_pickup_time_start"] == "DESC") $orderby .=" DESC";
				break;
			case "o_city";
				$orderby = "o_city";
				if($v["asc_o_city"] == "DESC") $orderby .=" DESC";
				break;
			case "d_city";
				$orderby = "d_city";
				if($v["asc_d_city"] == "DESC") $orderby .=" DESC";
				break;
			case "cost";
				$orderby = "cost";
				if($v["asc_cost"] == "DESC") $orderby .=" DESC";
				break;
			case "o_terminal";
				$orderby = "o_terminal";
				if($v["asc_o_terminal"] == "DESC") $orderby .=" DESC";
				break;
			case "total";
				$orderby = "total";
				if($v["asc_total"] == "DESC") $orderby .=" DESC";
				break;
			case "profit";
				$orderby = "profit";
				if($v["asc_profit"] == "DESC") $orderby .=" DESC";
				break;
			case "req_delivery_date";
				$orderby = "req_delivery_date";
				if($v["asc_req_delivery_date"] == "DESC") $orderby .=" DESC";
				break;
			case "req_delivery_time_start";
				$orderby = "req_delivery_time_start";
				if($v["asc_req_delivery_time_start"] == "DESC") $orderby .=" DESC";
				break;
			case "company";
				$orderby = "cus.company";
				if($v["asc_company"] == "DESC") $orderby .=" DESC";
				break;
			case "carrier";
				$orderby = "carrier";
				if($v["asc_carrier"] == "DESC") $orderby .=" DESC";
				break;
			case "terminal";
				$orderby = "terminal";
				if($v["asc_terminal"] == "DESC") $orderby .=" DESC";
				break;
			case "service";
				$orderby = "service";
				if($v["asc_service"] == "DESC") $orderby .=" DESC";
				break;
			case "tracking";
				$orderby = "shipped_tracknum";
				if($v["asc_tracking"] == "DESC") $orderby .=" DESC";
				break;
			default:
				$orderby = "cus.company";
		}

		//CHECK COOKIE TO SEE WHAT FILTERS ARE ALL READY ON
		if(isset($_COOKIE["norway_freight_status"])) {
			$statuses = explode(",",$_COOKIE["norway_freight_status"]);
			$status_tobescheduled = (in_array("tobescheduled",$statuses)) ? " CHECKED" : "";
			$status_scheduled = (in_array("scheduled",$statuses)) ? " CHECKED" : "";
			$status_intransit = (in_array("intransit",$statuses)) ? " CHECKED" : "";
			$status_outfordelivery = (in_array("outfordelivery",$statuses)) ? " CHECKED" : "";
			$status_delivered = (in_array("delivered",$statuses)) ? " CHECKED" : "";
			$status_problem = (in_array("problem",$statuses)) ? " CHECKED" : "";

		} else {
			$status_tobescheduled = " CHECKED";
			$status_scheduled = " CHECKED";
			$status_intransit = " CHECKED";
			$status_outfordelivery = " CHECKED";
			$status_delivered = " CHECKED";
			$status_problem = " CHECKED";
		}
		$vars["filters"] = "<td NOWRAP><input type='hidden' name='check' value='1'><input type='checkbox' id='tobescheduled_id' name='tobescheduled'$status_tobescheduled><label for='tobescheduled_id'>Waiting for Release</label></td><td NOWRAP><input type='checkbox' id='scheduled_id' name='scheduled'$status_scheduled><label for='scheduled_id'>Release Ready</label></td><td NOWRAP><input type='checkbox' id='intransit_id' name='intransit'$status_intransit><label for='intransit_id'>Scheduled</label></td><td NOWRAP><input type='checkbox' id='outfordelivery_id' name='outfordelivery'$status_outfordelivery><label for='outfordelivery_id'>Picked up</label></td><td NOWRAP><input type='checkbox' id='delivered_id' name='delivered'$status_delivered><label for='delivered_id'>Delivered</label></td><td NOWRAP><input type='checkbox' id='problem_id' name='problem'$status_problem><label for='problem_id'>Has Problems</label></td><td NOWRAP><input type='submit' value='save'></td>";

//		$sql = "SELECT s.*,sr.transit_days,s.shipped_carrier_id AS carrier_id,CONCAT(s.o_to,'<br>',s.o_address1,' ',s.o_address2,'<br>',s.o_city,', ',s.o_state,' ',s.o_zip,'<br>',s.o_contact_name,'<br>ph: ',s.o_contact_phone,'<br>fx: ',s.o_contact_fax,'<br>','po: ',s.o_po) AS o_popup,CONCAT(s.d_to,'<br>',s.d_address1,' ',s.d_address2,'<br>',s.d_city,', ',s.d_state,' ',s.d_zip,'<br>',s.d_contact_name,'<br>ph: ',s.d_contact_phone,'<br>fx: ',s.d_contact_fax,'<br>','po: ',s.d_po) AS d_popup,cus.company,CONCAT(cus.mail_address1,' ',cus.mail_address2,'<br>',cus.mail_city,', ',cus.mail_state,' ',cus.mail_zip,'<br>',cus.main_contact_name,'<br>ph: ',cus.main_contact_phone,'<br>fx: ',cus.main_contact_fax) AS cus_popup,cus.known_shipper,i.cost,i.total,i.profit,car.company AS carrier,CONCAT('acct: ',car.account_num,'<br>',car.address1,' ',car.address2,'<br>',car.city,', ',car.state,' ',car.zip,'<br>ph: ',car.phone,'<br>fx: ',car.fax,'<br>main: [main_contact]') AS car_popup, cto.code AS o_terminal,CONCAT(cto.name,'<br>',cto.address1,' ',cto.address2,'<br>',cto.city,', ',cto.state,' ',cto.zip,'<br>',cto.contact,'<br>ph: ',cto.phone,'<br>fx: ',cto.fax) AS oterm_popup, ctd.code AS d_terminal,CONCAT(ctd.name,'<br>',ctd.address1,' ',ctd.address2,'<br>',ctd.city,', ',ctd.state,' ',ctd.zip,'<br>',ctd.contact,'<br>ph: ',ctd.phone,'<br>fx: ',ctd.fax) AS dterm_popup, service.name AS service, CONCAT(sr.transit_days,' day transit') AS service_popup, s.shipped_tracknum AS tracking FROM shipment s INNER JOIN shipment_rate sr ON sr.id=s.shipped_shipment_rate_id INNER JOIN service ON sr.service_id=service.id LEFT JOIN carrier_terminal cto ON s.shipped_o_carrier_terminal_id=cto.id LEFT JOIN carrier_terminal ctd ON s.shipped_d_carrier_terminal_id=ctd.id INNER JOIN carrier car ON s.shipped_carrier_id=car.id LEFT JOIN customer cus ON s.customer_id=cus.id LEFT JOIN invoice i ON s.invoice_id=i.id WHERE s.status != 'quote' AND s.status != 'cancelled' AND s.status != 'invoiced' AND s.status != 'tobeinvoiced' AND s.freight_admin_login_id = '$admin_filter' ".str_replace("customer","cus",$SALES_REP_FILTER)." ORDER BY $orderby";
		$sql = "SELECT s.*,sr.transit_days,s.shipped_carrier_id AS carrier_id,
		CONCAT(s.o_to,'<br>',s.o_address1,' ',s.o_address2,'<br>',s.o_city,', ',s.o_state,' ',s.o_zip,'<br>',s.o_contact_name,'<br>ph: ',s.o_contact_phone,'<br>fx: ',s.o_contact_fax,'<br>','po: ',s.o_po) AS o_popup,
		CONCAT(s.d_to,'<br>',s.d_address1,' ',s.d_address2,'<br>',s.d_city,', ',s.d_state,' ',s.d_zip,'<br>',s.d_contact_name,'<br>ph: ',s.d_contact_phone,'<br>fx: ',s.d_contact_fax,'<br>','po: ',s.d_po) AS d_popup,
		cus.company,
		CONCAT(cus.mail_address1,' ',cus.mail_address2,'<br>',cus.mail_city,', ',cus.mail_state,' ',cus.mail_zip,'<br>',cus.main_contact_name,'<br>ph: ',cus.main_contact_phone,'<br>fx: ',cus.main_contact_fax) AS cus_popup,
		cus.known_shipper,i.cost,i.total,i.profit,car.company AS carrier,
		CONCAT('acct: ',car.account_num,'<br>',car.address1,' ',car.address2,'<br>',car.city,', ',car.state,' ',car.zip,'<br>ph: ',car.phone,'<br>fx: ',car.fax,'<br>main: [main_contact]') AS car_popup,
		cto.code AS o_terminal,
		CONCAT(cto.name,'<br>',cto.address1,' ',cto.address2,'<br>',cto.city,', ',cto.state,' ',cto.zip,'<br>',cto.contact,'<br>ph: ',cto.phone,'<br>fx: ',cto.fax) AS oterm_popup, ctd.code AS d_terminal,
		CONCAT(ctd.name,'<br>',ctd.address1,' ',ctd.address2,'<br>',ctd.city,', ',ctd.state,' ',ctd.zip,'<br>',ctd.contact,'<br>ph: ',ctd.phone,'<br>fx: ',ctd.fax) AS dterm_popup, service.name AS service,
		CONCAT(sr.transit_days,' day transit') AS service_popup, s.shipped_tracknum AS tracking
		 FROM shipment s
		 INNER JOIN shipment_rate sr ON sr.id=s.shipped_shipment_rate_id
		 INNER JOIN service ON sr.service_id=service.id
		 LEFT JOIN carrier_terminal cto ON s.shipped_o_carrier_terminal_id=cto.id
		 LEFT JOIN carrier_terminal ctd ON s.shipped_d_carrier_terminal_id=ctd.id
		 INNER JOIN carrier car ON s.shipped_carrier_id=car.id
		 LEFT JOIN customer cus ON s.customer_id=cus.id
		 LEFT JOIN invoice i ON s.invoice_id=i.id
		 WHERE s.status != 'quote' AND s.status != 'cancelled' AND s.status != 'invoiced' AND s.status != 'tobeinvoiced' ORDER BY $orderby";
		$list_array_raw = $db->query($sql);

		$shipped_color="#ff0000";

		//CREATE AN ARRAY WITH ALL ATTRIBUTES, SO WE DON'T HAVE TO ITERATE FOR THE SAME THINGS BELOW.
		if($list_array_raw) {
			foreach($list_array_raw AS $k => $l){
				$shipment_ids[]=$l["id"];
				$carrier_ids[$l["shipped_carrier_id"]]=$l["shipped_carrier_id"];
			}

			// get main contacts
			$carrier_info = $db->query("SELECT * FROM carrier_contact WHERE type='main' AND carrier_id IN ('".implode("','",$carrier_ids)."')");
			if($carrier_info){
				foreach($carrier_info as $v){
					$carrier_contact[$v["carrier_id"]]=$v["contact"]." (".$v["phone"].")";
				}
			}

			// tracking
			$tracking_arr=$db->query("SELECT * FROM shipment_tracking WHERE shipment_id IN (".implode(",",$shipment_ids).") ORDER BY date_show DESC");
			if($tracking_arr){
				foreach($tracking_arr as $XX){
					$tracking[$XX["shipment_id"]][]=$XX;
				}
			}

			// commodities
			$commodity_arr=$db->query("SELECT shipment_commodity.*,class_list.class,packaging_list.packaging FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id LEFT JOIN packaging_list ON shipment_commodity.packaging_list_id=packaging_list.id WHERE shipment_commodity.shipment_id IN (".implode(",",$shipment_ids).")");
			if($commodity_arr){
				foreach($commodity_arr as $XX){
					$commodity[$XX["shipment_id"]][]=$XX;
				}
			}

			// costs
			$shipment_rate_arr=$db->query("SELECT shipment_rate.* FROM shipment INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id WHERE shipment.id IN (".implode(",",$shipment_ids).") ");
			if($shipment_rate_arr){
				foreach($shipment_rate_arr as $XX){
					$shipment_rate[$XX["shipment_id"]]=$XX;
					$shipment_id_map[$XX["id"]]=$XX["shipment_id"];
				}
			}
			$shipment_rate_accessorial_arr=$db->query("SELECT shipment_rate_accessorial.*,accessorial.name AS accessorial FROM shipment INNER JOIN shipment_rate_accessorial ON shipment.shipped_shipment_rate_id=shipment_rate_accessorial.shipment_rate_id INNER JOIN accessorial ON shipment_rate_accessorial.accessorial_id=accessorial.id WHERE shipment.id IN (".implode(",",$shipment_ids).") ");
			if($shipment_rate_accessorial_arr){
				foreach($shipment_rate_accessorial_arr as $XX){
					if($shipment_id_map[$XX["shipment_rate_id"]]){
						$shipment_rate_accessorial[$shipment_id_map[$XX["shipment_rate_id"]]][]=$XX;
					}
				}
			}


			foreach($list_array_raw AS $k => $l) {
                //echo "<pre>";print_r($k);echo "</pre>";
               // echo "<pre>";print_r($l);echo "</pre>";
				foreach($l AS $o=>$p) {
					$list_array[$k][$o] = $p;
                   // print_r("k =".$k);echo "\n";
                   // print_r("o =".$o);
				}
				if(substr($l["shipped_pickup_date"],0,4) != "0000") {
					$list_array[$k]["req_pickup_date"] = formatDateOnly($l["shipped_pickup_date"]);
					$list_array[$k]["shipped_set"] = TRUE;
				} else {
					$list_array[$k]["req_pickup_date"] = formatDateOnly($l["req_pickup_date"]);
					$list_array[$k]["shipped_set"] = FALSE;
				}
				$list_array[$k]["req_pickup_time_start"] = formatTimeOnly($l["req_pickup_time_start"])." - ".formatTimeOnly($l["req_pickup_time_end"]);

				if(substr($l["req_delivery_date"],0,4) != "0000") {
					$list_array[$k]["req_delivery_date"] = "<font color=red>".formatDateOnly($l["req_delivery_date"])."</font>";
				}else{
					// attempt to figure it out
					if(substr($l["shipped_pickup_date"],0,4) != "0000"){
						list($y,$m,$d)=explode("-",$l["shipped_pickup_date"]);
					}else{
						list($y,$m,$d)=explode("-",$l["req_pickup_date"]);
					}

					$DAYS=$l["transit_days"];
					if($DAYS){
						switch($l["service_id"]){
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

						$list_array[$k]["req_delivery_date"] = formatDateOnly(date("Y-m-d",mktime(0,0,0,$m,$d+$DAYS,$y)));
					}else{
						$list_array[$k]["req_delivery_date"] = "";
					}
				}
				$list_array[$k]["req_delivery_time_start"] = formatTimeOnly($l["req_delivery_time_start"])." - ".formatTimeOnly($l["req_delivery_time_end"]);

                //echo "<pre>";echo "ship=". $l[id];echo "</pre>";
                //echo "<pre>";echo "cost=". $l[cost];echo "</pre>";
				$list_array[$k]["cost"] = "\$$l[cost]";
				if($l["cost_verified"] == "1"){
					$list_array[$k]["cost"] = "<b>".$list_array[$k]["cost"]."</b>";
				}

				$list_array[$k]["profit"] = "\$$l[profit]";
				$list_array[$k]["total"] = "\$$l[total]";
				$list_array[$k]["origin"] = "$l[o_city] $l[o_state], $l[o_zip]";
				$list_array[$k]["destination"] = "$l[d_city] $l[d_state], $l[d_zip]";


				$descarray[]='<div align=left>'.$l["cus_popup"].'</div>';
				$list_array[$k]["customer_layer"]=count($descarray)-1;

				$l["car_popup"] = replace(array("main_contact"=>$carrier_contact[$l["shipped_carrier_id"]]),$l["car_popup"]);
				$descarray[]='<div align=left>'.$l["car_popup"].'</div>';
				$list_array[$k]["carrier_layer"]=count($descarray)-1;

				$descarray[]='<div align=left>'.$l["oterm_popup"].'</div>';
				$list_array[$k]["oterm_layer"]=count($descarray)-1;

				$descarray[]='<div align=left>'.$l["dterm_popup"].'</div>';
				$list_array[$k]["dterm_layer"]=count($descarray)-1;

				$descarray[]='<div align=left>'.$l["service_popup"].'</div>';
				$list_array[$k]["service_layer"]=count($descarray)-1;

				$descarray[]='<div align=left>'.$l["o_popup"].'</div>';
				$list_array[$k]["o_layer"]=count($descarray)-1;

				$descarray[]='<div align=left>'.$l["d_popup"].'</div>';
				$list_array[$k]["d_layer"]=count($descarray)-1;

				$track_info="";
				if($tracking[$l["id"]]){
					foreach($tracking[$l["id"]] as $XX){
						$track_info.='<tr><td nowrap><b>'.sysDateTime($XX["date_show"]).'</b></td><td>'.$XX["tracking"].'</td></tr>';
					}
				}
				$descarray[]='<table cellpadding=1>'.$track_info.'</table>';
				$list_array[$k]["track_layer"]=count($descarray)-1;

				$bol_info="";
				if($commodity[$l["id"]]){
					foreach($commodity[$l["id"]] as $XX){
						$bol_info.='<tr><td>'.$XX["pieces"].'</td><td>'.$XX["packaging"].'</td><td>'.$XX["dim_d"].'x'.$XX["dim_w"].'x'.$XX["dim_h"].'</td><td>'.$XX["class"].' / '.$XX["nmfc"].'</td><td>'.$XX["description"].'</td><td align=right>'.$XX["weight"].'</td></tr>';
					}
				}
				$descarray[]='<table cellpadding=1><tr><td><b>pcs</b></td><td><b>pack</b></td><td><b>dims</b></td><td><b>class / nmfc</b></td><td><b>description</b></td><td align=right><b>weight</b></td></tr>'.$bol_info.'</table>';
				$list_array[$k]["bol_layer"]=count($descarray)-1;

				$cost_info="";
				$total_info="";
				$margin_info="";

                //anh
                $shipmentCost=0;
                $shipmentRate=0;
                $shipmentMargin=0;
                //
				if($shipment_rate[$l["id"]]){
                    $shipmentCost = $shipment_rate[$l["id"]]["cost"];
                    $shipmentRate= $shipment_rate[$l["id"]]["rate"];
                    $shipmentMargin= $shipment_rate[$l["id"]]["rate"]-$shipment_rate[$l["id"]]["cost"];

					$cost_info.='<tr><td>Freight Charges</td><td align=right>$'.number_format($shipment_rate[$l["id"]]["cost"],2).'</td></tr>';
                    $total_info.='<tr><td>Shipment Charge</td><td align=right>$'.number_format($shipment_rate[$l["id"]]["rate"],2).'</td></tr>';
					$margin_info.='<tr><td>Shipment Charge</td><td align=right>$'.(number_format($shipment_rate[$l["id"]]["rate"]-$shipment_rate[$l["id"]]["cost"],2)).'</td></tr>';

				}
                //anh
                $cost_accessorial =0;
                $total_accessorial =0;
                $margin_accessorial =0;
                //
				if($shipment_rate_accessorial[$l["id"]]){
					foreach($shipment_rate_accessorial[$l["id"]] as $XX){
                        $cost_accessorial +=$XX["cost"];
                        $total_accessorial +=$XX["rate"];
                        $margin_accessorial +=($XX["rate"]-$XX["cost"]);
						$cost_info.='<tr><td>'.$XX["accessorial"].'</td><td align=right>$'.number_format($XX["cost"],2).'</td></tr>';
						$total_info.='<tr><td>'.$XX["accessorial"].'</td><td align=right>$'.number_format($XX["rate"],2).'</td></tr>';
						$margin_info.='<tr><td>'.$XX["accessorial"].'</td><td align=right>$'.number_format(($XX["rate"]-$XX["cost"]),2).'</td></tr>';
					}
				}
                //anh
                $cost_info.='<tr><td>Container Cost</td><td align=right>$'.number_format($l["container_cost"],2).'</td></tr>';
                $totalCost = $shipmentCost + $cost_accessorial + $l["container_cost"];
                $cost_info.='<tr><td>Total</td><td align=right>$'.number_format($totalCost,2).'</td></tr>';

                $total_info.='<tr><td>Container Price</td><td align=right>$'.number_format($l["container_price"],2).'</td></tr>';
                $totalPrice = $shipmentRate + $total_accessorial + $l["container_price"];
                $total_info.='<tr><td>Total</td><td align=right>$'.number_format($totalPrice,2).'</td></tr>';

                $margin_info.='<tr><td>Container Charge</td><td align=right>$'.number_format(($l["container_price"] - $l["container_cost"]),2).'</td></tr>';
                $margin_info.='<tr><td>Total</td><td align=right>$'.number_format($totalPrice-$totalCost,2).'</td></tr>';
                //
				$descarray[]='<table cellpadding=1><tr><td><b>description</b></td><td align=right><b>cost</b></td></tr>'.$cost_info.'</table>';
				$list_array[$k]["cost_layer"]=count($descarray)-1;


				$descarray[]='<table cellpadding=1><tr><td><b>description</b></td><td align=right><b>rate</b></td></tr>'.$total_info.'</table>';
				$list_array[$k]["total_layer"]=count($descarray)-1;

				$descarray[]='<table cellpadding=1><tr><td><b>description</b></td><td align=right><b>margin</b></td></tr>'.$margin_info.'</table>';
				$list_array[$k]["margin_layer"]=count($descarray)-1;
			}
		}
    //echo "<pre>";print_r($descarray);echo "</pre>";

		$v["pickup_date"] = ($_GET["sort"] == "req_pickup_date") ? "<u>Pickup Date</u>" : "Pickup Date";
		$v["pickup_time"] = ($_GET["sort"] == "req_pickup_time_start") ? "<u>Pickup Time</u>" : "Pickup Time";
		$v["customer"] = ($_GET["sort"] == "company") ? "<u>Customer</u>" : "Customer";
		$v["carrier"] = ($_GET["sort"] == "carrier") ? "<u>Carrier</u>" : "Carrier";
		$v["terminal"] = ($_GET["sort"] == "terminal") ? "<u>Terminal</u>" : "Terminal";
		$v["service"] = ($_GET["sort"] == "service") ? "<u>Service</u>" : "Service";
		$v["origin"] = ($_GET["sort"] == "o_city") ? "<u>Origin</u>" : "Origin";
		$v["destination"] = ($_GET["sort"] == "d_city") ? "<u>Destination</u>" : "Destination";
		$v["tracking"] = ($_GET["sort"] == "tracking") ? "<u>Tracking</u>" : "Tracking";
		$v["cost"] = ($_GET["sort"] == "cost") ? "<u>Container cost</u>" : "Container cost";
		$v["invoice"] = ($_GET["sort"] == "total") ? "<u>Invoice</u>" : "Invoice";
		$v["margin"] = ($_GET["sort"] == "profit") ? "<u>Margin</u>" : "Margin";
		$v["est_delivery_date"] = ($_GET["sort"] == "req_delivery_date") ? "<u>Est. delivery date</u>" : "Est. delivery date";
		$v["est_delivery_time"] = ($_GET["sort"] == "req_delivery_time_start") ? "<u>Est. delivery time</u>" : "Est. delivery time";


		if(!empty($status_tobescheduled)) {
			if($list_array) {
				foreach($list_array AS $vv) {
					if($vv["status"] == "tobescheduled") {
						if($vv["shipped_set"]) {
							$vv["req_pickup_date"] = "<span style='color:$shipped_color'>$vv[req_pickup_date]</span>";
						}
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}
                        //print_r($vv["service"]);
                        $vv_not_serviceandtermial = array();
                        $vv_not_serviceandtermial = $vv;
                        unset($vv_not_serviceandtermial["service"]);
                        unset($vv_not_serviceandtermial["d_terminal"]);
                        //print_r($vv_not_serviceandtermial);
                        //print_r("----------------------------------");
						$v["tobescheduled_row"] .= replace_shipment_waiting_realse($vv_not_serviceandtermial,rf_waiting_release($htmlpath."shipments_list_scheduled_row.html"));
					}
				}
			}

            //print_r($v);
			if(!$v["tobescheduled_row"]) $v["tobescheduled_row"] = "<tr><td colspan='3'>Nothing to be scheduled</td></tr>";
			$v["status_options"] = "";
			
		
			
			foreach($status AS $status_value) {
				$selected = ($status_value == "scheduled") ? " SELECTED" : "";
				
				$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}

            $v_not_serviceandtermial = array();
            $v_not_serviceandtermial = $v;
            if(isset($v_not_serviceandtermial["service"])){
                unset($v_not_serviceandtermial["service"]);
            }
            if(isset($v_not_serviceandtermial["terminal"])){
                unset($v_not_serviceandtermial["terminal"]);
            }
            /*if(isset($v_not_serviceandtermial["asc_service"])){
                unset($v_not_serviceandtermial["asc_service"]);
            }
            if(isset($v_not_serviceandtermial["asc_o_terminal"])){
                unset($v_not_serviceandtermial["asc_o_terminal"]);
            }
            if(isset($v_not_serviceandtermial["asc_terminal"])){
                unset($v_not_serviceandtermial["asc_terminal"]);
            }*/

			$vars["tobescheduled"]=replace($v_not_serviceandtermial,rf($htmlpath."shipments_list_tobescheduled.html"));
		} else {
			$vars["tobescheduled"]="";
		}

		if(!empty($status_scheduled)) {
			if($list_array) {
				foreach($list_array AS $vv) {
					if($vv["status"] == "scheduled") {
						if($vv["shipped_set"]) {
							$vv["req_pickup_date"] = "<span style='color:$shipped_color'>$vv[req_pickup_date]</span>";
						}
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}

                        $vv_not_service_termial = array();
                        $vv_not_service_termial = $vv;
                        unset($vv_not_service_termial["service"]);
                        unset($vv_not_service_termial["d_terminal"]);

						$v["scheduled_row"] .= replace($vv_not_service_termial,rf($htmlpath."shipments_list_scheduled_row.html"));
					}
				}
			}
			if(!$v["scheduled_row"]) $v["scheduled_row"] = "<tr><td colspan='3'>Nothing scheduled</td></tr>";
			$v["status_options"] = "";
			foreach($status AS $status_value) {
				$selected = ($status_value == "intransit") ? " SELECTED" : "";
				
				$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}

            $v_not_service_termial = array();
            $v_not_service_termial = $v;
            if(isset($v_not_service_termial["service"])){
                unset($v_not_service_termial["service"]);
            }
            if(isset($v_not_service_termial["terminal"])){
                unset($v_not_service_termial["terminal"]);
            }

              //  print_r($v_not_service_termial);
           // echo "</br>";

			$vars["scheduled"]=replace($v_not_service_termial,rf($htmlpath."shipments_list_scheduled.html"));
		} else {
			$vars["scheduled"]="";
		}

		if(!empty($status_delivered)) {
			if($list_array) {
				foreach($list_array AS $vv) {
					if($vv["status"] == "delivered") {
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}

                        $vv_not_serviceandtermial = array();
                        $vv_not_serviceandtermial = $vv;
                        unset($vv_not_serviceandtermial["service"]);
                        unset($vv_not_serviceandtermial["d_terminal"]);
						$v["delivered_row"] .= replace($vv_not_serviceandtermial,rf($htmlpath."shipments_list_delivered_row.html"));
					}
				}
                //print_r($list_array);
			}
			if(!$v["delivered_row"]) $v["delivered_row"] = "<tr><td colspan='3'>Nothing delivered</td></tr>";
			$v["status_options"] = "";
			foreach($status AS $status_value) {
				$selected = ($status_value == "tobeinvoiced") ? " SELECTED" : "";
				
				$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}
            $v_not_service_termial = array();
            $v_not_service_termial = $v;
            unset($v_not_service_termial["service"]);
            unset($v_not_service_termial["terminal"]);
			$vars["delivered"]=replace($v_not_service_termial,rf($htmlpath."shipments_list_delivered.html"));
		} else {
			$vars["delivered"]="";
		}

		if(!empty($status_problem)) {
			if($list_array) {
				foreach($list_array AS $vv) {
					if($vv["status"] == "problem") {
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}

                        $vv_not_service_termial = array();
                        $vv_not_service_termial = $vv;
                        unset($vv_not_service_termial["service"]);
                        unset($vv_not_service_termial["d_terminal"]);

						$v["problem_row"] .= replace($vv_not_service_termial,rf($htmlpath."shipments_list_problem_row.html"));
					}
				}
			}
			if(!$v["problem_row"]) $v["problem_row"] = "<tr><td colspan='3'>Nothing has problems</td></tr>";
			$v["status_options"] = "";
			foreach($status AS $status_value) {
				$selected = ($status_value == "outfordelivery") ? " SELECTED" : "";
				
					$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}
            $v_not_service_termial = array();
            $v_not_service_termial = $v;
            if(isset($v_not_service_termial["service"])){
                unset($v_not_service_termial["service"]);
            }
            if(isset($v_not_service_termial["terminal"])){
                unset($v_not_service_termial["terminal"]);
            }

			$vars["problem"]=replace($v_not_service_termial,rf($htmlpath."shipments_list_problem.html"));
		} else {
			$vars["problem"]="";
		}

		if(!empty($status_outfordelivery)) {
			if($list_array) {
				foreach($list_array AS $vv) {
					if($vv["status"] == "outfordelivery") {
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}

                        $vv_not_serviceandtermial = array();
                        $vv_not_serviceandtermial = $vv;
                        unset($vv_not_serviceandtermial["service"]);
                        unset($vv_not_serviceandtermial["d_terminal"]);

						$v["outfordelivery_row"] .= replace($vv_not_serviceandtermial,rf($htmlpath."shipments_list_outfordelivery_row.html"));
					}
				}
			}
			if(!$v["outfordelivery_row"]) $v["outfordelivery_row"] = "<tr><td colspan='3'>Nothing is currently out for delivery</td></tr>";
			$v["status_options"] = "";
			foreach($status AS $status_value) {
				$selected = ($status_value == "delivered") ? " SELECTED" : "";
				
				$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}

            $v_not_service_termial = array();
            $v_not_service_termial = $v;
            unset($v_not_service_termial["service"]);
            unset($v_not_service_termial["terminal"]);

			$vars["outfordelivery"]=replace($v_not_service_termial,rf($htmlpath."shipments_list_outfordelivery.html"));
		} else {
			$vars["outfordelivery"]="";
		}

		if(!empty($status_intransit)) {
			if($list_array) {
				foreach($list_array AS $vv) {

					if($vv["status"] == "intransit") {
						$vv["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						if($vv["roll_off"] == "1") {
							$vv["bg"] = $rolloffbg;
						}
						if($vv["expedited"] == "1") {
							$vv["bg"] = $expeditedbg;
						}

                        $vv_not_serviceandtermial = array();
                        $vv_not_serviceandtermial = $vv;
                        unset($vv_not_serviceandtermial["service"]);
                        unset($vv_not_serviceandtermial["d_terminal"]);


						$v["intransit_row"] .= replace($vv,rf($htmlpath."shipments_list_intransit_row.html"));
					}
				}
			}
			if(!$v["intransit_row"]) $v["intransit_row"] = "<tr><td colspan='3'>Nothing is in transit</td></tr>";
			$v["status_options"] = "";
			foreach($status AS $status_value) {
				$selected = ($status_value == "outfordelivery") ? " SELECTED" : "";
				
				$status_label_new = $status_value;
                if($status_value=="tobescheduled"){
                    $status_label_new ="waitingforrelease";
                }elseif($status_value=="scheduled"){
                    $status_label_new = "realeseready";
                }elseif($status_value=="intransit"){
                    $status_label_new = "scheduled";
                }elseif($status_value=="outfordelivery"){
                    $status_label_new = "pickedup";
                }
				
				$v["status_options"] .="<option value='$status_value'$selected>$status_label_new</option>\n";
			}
            $v_not_service_termial = array();
            $v_not_service_termial = $v;
            unset($v_not_service_termial["service"]);
            unset($v_not_service_termial["terminal"]);

			$vars["intransit"]=replace($v_not_service_termial,rf($htmlpath."shipments_list_intransit.html"));
		} else {
			$vars["intransit"]="";
		}

		$html["BODY"]=replace($vars,rf($htmlpath."shipments_list.html"));
}


?>