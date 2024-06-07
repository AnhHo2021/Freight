<?php

$html["LOCATION"] = "<h1>CUSTOMERS</h1>";
switch($action){
  case "defaults":
  case "defaultsprocess":
    include("customers_defaults.php");
    break;
    
  case "prodbook":
    include("customers_prodbook.php");
    break;
    
	case "addresslist":
		// get rid of options
		if($id){
			// main customer address
			$sql = "SELECT CONCAT(company,'|','','|',mail_address1,'|',mail_address2,'|',mail_city,'|',mail_state,'|',mail_zip,'|',main_contact_name,'|',main_contact_phone,'|',main_contact_fax,'|',main_contact_email) AS address FROM customer WHERE id='$id'";
			$customer_address_temp = $db->query($sql);
			if($customer_address_temp){
				$addresses[]=$customer_address_temp[0]["address"];
			}

			$type = ($select=="origin_address")?"origin":"destination";
            if(isset($_GET["quote_id"])){
                $sql = "SELECT CONCAT(`to`,'|','','|',address1,'|',address2,'|',city,'|',state,'|',zip,'|',contact_name,'|',contact_phone,'|',contact_fax,'|',contact_email) AS address FROM customer_addressbook WHERE customer_id='$id' AND type IN ('both','$type') ORDER BY `to`";

            }else{
                $sql = "SELECT CONCAT(`to`,'|','','|',address1,'|',address2,'|',city,'|',state,'|',zip,'|',contact_name,'|',contact_phone,'|',contact_fax,'|',contact_email) AS address
                FROM customer_addressbook
                WHERE customer_id='$id' AND type IN ('both','$type') LIMIT 1 ORDER BY `to`";

            }
            $customer_address_temp = $db->query($sql);

			if($customer_address_temp){
				foreach($customer_address_temp as $v){
					$addresses[]=$v["address"];
				}
			}

			if($addresses){
				echo "var elem = document.getElementById('".$select."');";

				// addressbook entries
				foreach($addresses as $v){
					$v=str_replace("'","\'",$v);
					$texts=explode("|",$v);
					$text=$texts[0]." - ".$texts[4].", ".$texts[5]." ".$texts[6];
					echo "var elOptNew = document.createElement('option');";
					echo "elOptNew.text = '".$text."';";
					echo "elOptNew.value = '".$v."';";
					echo "if(document.all){";
						echo "elem.add(elOptNew);";
					echo "}else{";
						echo "elem.appendChild(elOptNew);";
					echo "}";
				}
			}
		}
		die();
		break;
	case "classes":
		/*GETTING FAK CLASS LIST*/
		//GET MAIN LIST
		$sql = "SELECT * FROM class_list ORDER BY id";
		$class_array = $db->query($sql);
		//GET SAVED LIST
		$sql = "SELECT cfa.*,cl1.class AS class1, cl2.class AS class2 FROM carrier_fak_address_class cfa LEFT JOIN class_list cl1 ON cfa.class_list_id1=cl1.id LEFT JOIN class_list cl2 ON cfa.class_list_id2=cl2.id WHERE carrier_fak_address_id = '$_GET[id]' AND carrier_id = '$_GET[carrier_id]'";
		$saved_array = $db->query($sql);
		$u["class_list"] = "<form method='POST'>";
		if($saved_array) {
			foreach($saved_array AS $x) {
				$u["class_list"] .="<tr><td align=center>$x[class1]</td>\n<td align=center>$x[class2]</td></tr>";
			}
		} else {
			foreach($class_array AS $f) {
				$u["class_list"] .="<tr><td align=center>$f[class]</td>\n<td align=center>$f[class]</td></tr>";
			}
		}
		$html["BODY"]=replace($u,rf($htmlpath."carriers_fak_classes.html"));
		break;
	case "addrbook":
		if($customer_id){
			$vars["customer_id"] = $customer_id;

			if($mode=="remove") {
				$db->query("DELETE FROM customer_addressbook WHERE id = '$id'");
				header("Location: /?action=customers_addrbook&customer_id=$customer_id");
				die();
			}
			if($HTTP_POST_VARS){
				if($id=="NEW"){
					$sql = "INSERT INTO customer_addressbook SET customer_id='$customer_id', type='$type', `to`='$to', address1='$address1', address2='$address2', city='$city', state='$state', zip='$zip', contact_name='$contact_name', contact_phone='$contact_phone', contact_fax='$contact_fax', contact_email='$contact_email'";
					$db->query($sql);
				}else{
					$sql = "UPDATE customer_addressbook SET customer_id='$customer_id', type='$type', `to`='$to', address1='$address1', address2='$address2', city='$city', state='$state', zip='$zip', contact_name='$contact_name', contact_phone='$contact_phone', contact_fax='$contact_fax', contact_email='$contact_email' WHERE id = '$id'";
					$db->query($sql);
				}
				header("Location: /?action=customers_addrbook&customer_id=$customer_id");
				die();
			}
			
			if($id){
				$vars["to"] = "";
				$vars["contact_name"] = "";
				$vars["contact_phone"] = "";
				$vars["contact_fax"] = "";
				$vars["contact_email"] = "";
				$vars["address1"] = "";
				$vars["address2"] = "";
				$vars["city"] = "";
				$vars["zip"] = "";
				$vars["id"] = $id;
				if($id != "NEW"){
					$sql = "SELECT * FROM customer_addressbook WHERE id = '$id'";
					$sel_data = $db->query($sql);
					if($sel_data){
						foreach($sel_data[0] as $n=>$v){
							$vars[$n] = $v;
						}
					}
				}
				if(!$vars["type"]){$vars["type"]="both";}
				$vars["origin_selected"] = ($vars["type"]=="origin")?" selected":"";
				$vars["destination_selected"] = ($vars["type"]=="destination")?" selected":"";
				$vars["both_selected"] = ($vars["type"]=="both")?" selected":"";

				$sql = "SELECT code,state FROM state ORDER BY state";
				$sel_states = $db->query($sql);
				if($sel_states){
					foreach($sel_states as $v){
						$s = ($vars["state"]==$v["code"])?" selected":"";
						$vars["states"] .= '<option value="'.$v["code"].'"'.$s.'>'.$v["state"].'</option>';
					}
				}
				
				$html["BODY"] = replace($vars, rf($htmlpath."customers_addrbook_edit.html"));
			}else{
				$sql = "SELECT customer_addressbook.* FROM customer_addressbook WHERE customer_addressbook.customer_id = '$customer_id' ORDER BY `to`";
				$address_array = $db->query($sql);
				if($address_array) {
					foreach($address_array AS $v) {
						$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
						$vars["customers_addrbook_list_row"] .= replace($v, rf($htmlpath."customers_addrbook_list_row.html"));
					}
				} else {
					$vars["customers_addrbook_list_row"] = "<tr><td colspan='3' nowrap><b>No Results</b></td></tr>";
				}
				$customers_addresses_list = replace($vars, rf($htmlpath."customers_addrbook_list.html"));
				$html["BODY"]=replace($vars, $customers_addresses_list);
			}
		}
		break;
	case "addresses":
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM customer_fak_address WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["carrier"])) {
			$sql = "INSERT INTO customer_fak_address SET ";
			
			foreach($_POST AS $k=>$v) {
				if($k != "id" && $k != 'submitme') {
					$sql .= "$k = '$v',";
				}
			}
			$sql .= "customer_id='$_GET[id]'".$post_sql;
			$success = $db->query($sql);
		}
		$sql = "SELECT car_f.*,cus_f.*,car.company AS carrier_company FROM customer_fak_address cus_f, carrier_fak_address car_f, carrier car WHERE car_f.carrier_id=car.id AND cus_f.carrier_fak_address_id=car_f.id AND cus_f.customer_id = '$_GET[id]' ORDER BY company";
		$address_array = $db->query($sql);
		if($address_array) {
			foreach($address_array AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["customers_addresses_list_row"] .= replace($v, rf($htmlpath."customers_addresses_list_row.html"));
			}
		} else {
			$vars["customers_addresses_list_row"] = "<tr><td colspan='3' nowrap><b>No Results</b></td></tr>";
		}
		$vars["customer_id"] = $_GET["id"];
		$vars["action_id"] = $_GET["id"];

		$customers_addresses_list = replace($vars, rf($htmlpath."customers_addresses_list.html"));
		$html["BODY"]=replace($vars, $customers_addresses_list);

		break;
	case "accessorials":
		if(!empty($_POST)) {
			//print_r($_POST);
			//GET RID OF ALL OF THE EXISTING ROWS SO WE CAN REPLACE THEM EASILY.
			$sql="DELETE FROM customer_accessorial WHERE customer_id = '$_GET[id]'";
			$success = $db->query($sql);
			//THESE POSTS AREN'T THE SAME AS THE DATABASE, SO THIS IS WEIRD.
			//BUILDING THE MEGA-ARRAY OF ALL THE VALUES WE NEED.
			foreach($_POST AS $k=>$v) {
				$temp = explode("__",$k);
				$carrier_id = $temp[0];
				$id = $temp[1];
				$name = $temp[2];
				$name_array[$carrier_id][$id][$name] = $v;
			}
			//print_r($name_array);
			//CREATING THE SQL STATEMENTS FOR EACH OF THE ITEMS ENTERED.
			foreach($name_array AS $carrier_id => $carrier_loop) {
				foreach($carrier_loop AS $v) {
					$sql = "INSERT INTO customer_accessorial SET \n";
					foreach($v AS $k=>$value) {
						if($k != "a_id") {
							$sql .= "$k = '$value',\n";
						} else {
							$sql .= "accessorial_id = '$value',\n";
						}
					}
					$sql .= "carrier_id = '$carrier_id',\n";
					$sql .= "customer_id = '$_GET[id]'\n\n";
					$success = $db->query($sql);
				}
				//print_r($sql);
			}
		}


		// start testing
			$sql = "SELECT carrier.id, carrier.company FROM carrier WHERE type IN ('air','ltl')";
			$carriers_a=$db->query($sql);
			if($carriers_a){
				foreach($carriers_a as $v){
					$carriers[]=$v;
					$c_ids[]=$v["id"];
				}
			}else{$c_ids=array();}

			$sql = "SELECT accessorial.* FROM accessorial ORDER BY accessorial.name";
			$accessorials_a=$db->query($sql);
			if($accessorials_a){
				foreach($accessorials_a as $v){
					$accessorials[]=$v;
				}
			}else{$accessorials=array();}
			

			// customer rates
			$sql = "SELECT a.name,a.id AS a_id,ca.* FROM accessorial a LEFT JOIN customer_accessorial ca ON (a.id=ca.accessorial_id AND ca.carrier_id IN (".implode(",",$c_ids).") AND ca.customer_id='$_GET[id]') ORDER BY a.name";
			$customer_accessorial_a=$db->query($sql);
			if($customer_accessorial_a){
				foreach($customer_accessorial_a as $v){
					$customer_accessorial[$v["carrier_id"]][$v["a_id"]]=$v;
				}
			}else{
				$customer_accessorial = array();
			}

			// carrier costs
			$sql = "SELECT a.name,a.id AS a_id,ca.* FROM accessorial a LEFT JOIN carrier_accessorial ca ON a.id=ca.accessorial_id AND ca.carrier_id IN (".implode(",",$c_ids).") ORDER BY a.name";
			$carrier_accessorial_a=$db->query($sql);
			if($carrier_accessorial_a){
				foreach($carrier_accessorial_a as $v){
					if($v["type"]=="percent"){
						$v["cost"]=$v["cost"]."%";
					}else{
						$v["cost"]='$'.$v["cost"];	
					}
					$carrier_accessorial[$v["carrier_id"]][$v["a_id"]]=$v;

					$accessorial_types[$v["carrier_id"]][$v["a_id"]]=$v["type"];
				}
			}else{
				$customer_accessorial = array();
			}

			// now loop through carriers
			foreach($carriers as $v){
				foreach($accessorials as $vv){
					$out_array[$v["id"]][$vv["id"]]["company"]=$v["company"];
					$out_array[$v["id"]][$vv["id"]]["name"]=$vv["name"];
					$out_array[$v["id"]][$vv["id"]]["cost"]=($carrier_accessorial[$v["id"]][$vv["id"]])?$carrier_accessorial[$v["id"]][$vv["id"]]["cost"]:"";
					$out_array[$v["id"]][$vv["id"]]["cost_min"]=($carrier_accessorial[$v["id"]][$vv["id"]])?$carrier_accessorial[$v["id"]][$vv["id"]]["cost_min"]:"";
					$out_array[$v["id"]][$vv["id"]]["cost_max"]=($carrier_accessorial[$v["id"]][$vv["id"]])?$carrier_accessorial[$v["id"]][$vv["id"]]["cost_max"]:"";
					$out_array[$v["id"]][$vv["id"]]["rate"]=($customer_accessorial[$v["id"]][$vv["id"]])?$customer_accessorial[$v["id"]][$vv["id"]]["rate"]:"";
					$out_array[$v["id"]][$vv["id"]]["rate_min"]=($customer_accessorial[$v["id"]][$vv["id"]])?$customer_accessorial[$v["id"]][$vv["id"]]["rate_min"]:"";
					$out_array[$v["id"]][$vv["id"]]["rate_max"]=($customer_accessorial[$v["id"]][$vv["id"]])?$customer_accessorial[$v["id"]][$vv["id"]]["rate_max"]:"";
				}
			}
//print_r($out_array);
			// end testing

		$ROW=rf($htmlpath."customers_accessorials_row.html");
		$customers_accessorials = "<form method='POST' action='./?action=customers_accessorials&id=$_GET[id]'>\n";
		foreach($out_array as $carrier_id => $array_pieces) {
			unset($vars);
			$i=0;
			$vars["customers_accessorials_row_temp"] = "";
			foreach($array_pieces AS $a_id => $value) {
				$v["name"] = $value["name"];
				$v["a_id"] = $a_id;
				$v["carrier_id"] = $carrier_id;
//				$v["save_button"] = ($i==0) ? "<td align='center' valign='middle' rowspan='12' bgcolor='white'><input type='submit' value='save'></td>" : "";
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;

				$v["dollar"]="";
				$v["percent"]="";
				if($accessorial_types[$carrier_id][$a_id]){
					if($accessorial_types[$carrier_id][$a_id]=="percent"){
						$v["percent"]='%';
					}else{						
						$v["dollar"]='$';
					}
				}

				$vars["customers_accessorials_row_temp"] .= replace($v, $ROW);
				$i++;
			}
			foreach($array_pieces AS $a_id => $value) {
				foreach($value AS $k=>$a) {
					$new_name = $carrier_id."__".$a_id."__".$k;
					$v[$new_name] = $a;
				}
			}
			$vars["company"] = $value["company"];
			$vars["customers_accessorials_row"] .= replace($v, $vars["customers_accessorials_row_temp"]);
//			$vars["action_id"] = $_GET["id"];
//			$vars["carrier_id"] = $carrier_id;
			$customers_accessorials .= replace($vars, rf($htmlpath."customers_accessorials.html"));
		}
		$customers_accessorials .= "<table width='100%'><tr><td align='center' bgcolor='white'><input type='submit' value='save'></td></tr></table></form>";
		$html["BODY"]=replace($vars, $customers_accessorials);
		
	break;
	case "rate":
		if(!empty($_POST)) {
			$db->query("DELETE FROM customer_carrier_exclude WHERE customer_id='$_GET[id]'");
			foreach($_POST as $n=>$v){
				if(substr($n,0,12)=="service_ids_"){
					list($t1,$t2,$carrier_id)=explode("_",$n);
					$service_ids=$v;
					foreach($service_ids as $service_id){
						$db->query("DELETE FROM customer_rate WHERE customer_id='$_GET[id]' AND carrier_id='$carrier_id' AND service_id='$service_id'");
						$db->query("INSERT INTO customer_rate SET customer_id='$_GET[id]',carrier_id='$carrier_id',service_id='$service_id',type='".${"type_".$carrier_id."_".$service_id}."',direction='intrastate',rate='".${"rate_".$carrier_id."_".$service_id."_intrastate"}."',min_rate='".${"min_rate_".$carrier_id."_".$service_id."_intrastate"}."'");
						$db->query("INSERT INTO customer_rate SET customer_id='$_GET[id]',carrier_id='$carrier_id',service_id='$service_id',type='".${"type_".$carrier_id."_".$service_id}."',direction='interstate',rate='".${"rate_".$carrier_id."_".$service_id."_interstate"}."',min_rate='".${"min_rate_".$carrier_id."_".$service_id."_interstate"}."'");
					}
				}

				if(substr($n,0,8)=="exclude_" && $v=="1"){
					$carrier_exclude = substr($n,8);
					$db->query("INSERT INTO customer_carrier_exclude SET customer_id='$_GET[id]',carrier_id='$carrier_exclude'");
				}
			}

		}
		$services = $db->query("SELECT carrier_services.carrier_id,carrier.company AS carrier,carrier_services.service_id,service.name AS service FROM carrier_services INNER JOIN service ON carrier_services.service_id=service.id INNER JOIN carrier ON carrier_services.carrier_id=carrier.id");
		if($services){
			$carrier_excluded=array();
			$excludes = $db->query("SELECT * FROM customer_carrier_exclude WHERE customer_id='$_GET[id]'");
			if($excludes){
				foreach($excludes as $v){
					$carrier_excluded[$v["carrier_id"]]=true;
				}
			}

			foreach($services as $v){
				$carrier_services[$v["carrier_id"]][]=$v;
			}

			// get carrier costs
			$sql = "SELECT carrier_cost.* FROM carrier_cost";
			$costs_temp = $db->query($sql);
			if($costs_temp) {
				foreach($costs_temp as $v){
					$costs[$v["carrier_id"]][$v["service_id"]][$v["direction"]]=$v;
				}
			}

			$sql = "SELECT customer_rate.* FROM customer_rate WHERE customer_rate.customer_id = '$_GET[id]'";
			$rates_temp = $db->query($sql);
			if($rates_temp) {
				foreach($rates_temp as $v){
					$rates_all[$v["carrier_id"]][$v["service_id"]][$v["direction"]]=$v;
				}			
			}

			$types=getEnum("customer_rate","type");
			$group_table = rf($htmlpath."customers_rates_group.html");
			$table = rf($htmlpath."customers_rates_table.html");

			foreach($carrier_services as $carrier_id=>$services){
				if($myvars){unset($myvars);}
				$rates=($rates_all[$carrier_id])?$rates_all[$carrier_id]:array();
				foreach($services as $v){
					$v["bg1"] = $altbg1;
					$v["bg2"] = $altbg2;
					
					foreach($types as $vv){
						$s=($vv == $rates[$v["service_id"]]["intrastate"]["type"] || $vv == $rates[$v["service_id"]]["interstate"]["type"])?" selected":"";
						$v["type"].='<option value="'.$vv.'"'.$s.'>'.$vv.'</option>';
					}

					if($rates[$v["service_id"]]){
						$vv=$rates[$v["service_id"]];
						${"cost_".$vv["direction"]}=$vv["cost"];
						${"rate_".$vv["direction"]}=$vv["rate"];
						${"min_cost_".$vv["direction"]}=$vv["min_cost"];
						${"min_rate_".$vv["direction"]}=$vv["min_rate"];
					}

					$v["cost_intrastate"]=$costs[$carrier_id][$v["service_id"]]["intrastate"]["cost"];
					$v["min_cost_intrastate"]=$costs[$carrier_id][$v["service_id"]]["intrastate"]["min_cost"];
					$v["rate_intrastate"]=$rates[$v["service_id"]]["intrastate"]["rate"];
					$v["min_rate_intrastate"]=$rates[$v["service_id"]]["intrastate"]["min_rate"];
					
					$v["cost_interstate"]=$costs[$carrier_id][$v["service_id"]]["interstate"]["cost"];
					$v["min_cost_interstate"]=$costs[$carrier_id][$v["service_id"]]["interstate"]["min_cost"];
					$v["rate_interstate"]=$rates[$v["service_id"]]["interstate"]["rate"];
					$v["min_rate_interstate"]=$rates[$v["service_id"]]["interstate"]["min_rate"];

					$myvars["company"]=$v["carrier"];
					$myvars["carrier_id"]=$carrier_id;
					$myvars["exclude"]=($carrier_excluded[$carrier_id])?" checked":"";
					$myvars["RATES"].=replace($v,$table);
				}
				$vars["ALL_RATES"].=replace($myvars,$group_table);
			}
			
			$vars["customer_id"]=$_GET[id];
			$html["BODY"]=replace($vars, rf($htmlpath."customers_rates.html"));
		}else{
			$html["BODY"] = "No Services Defined";
		}


		/*
		if(!empty($_POST)) {
			foreach($_POST AS $k=>$v) {
				$temp = explode("__",$k);
				$carriers[$temp[0]] = $temp[0];
			}
			//GET RID OF ALL OF THE 3 EXISTING ROWS SO WE CAN REPLACE THEM EASILY.
			$sql="DELETE FROM customer_rate WHERE customer_id = '$_GET[id]'";
			foreach($carriers as $carrier_id) {
				$success = $db->query($sql);
				//THESE POSTS AREN'T THE SAME AS THE DATABASE, SO THIS IS WEIRD.
				$sql = "INSERT INTO customer_rate SET
				customer_id = '$_GET[id]',
				carrier_id = '$carrier_id',
				type = '".$_POST["$carrier_id"."__type"]."',
				direction = 'na',
				rate = '".$_POST["$carrier_id"."__1__rate"]."',
				min_rate = '".$_POST["$carrier_id"."__1__min_rate"]."'
				";
				$success = $db->query($sql);
	
				$sql = "INSERT INTO customer_rate SET
				customer_id = '$_GET[id]',
				carrier_id = '$carrier_id',
				type = '".$_POST["$carrier_id"."__type"]."',
				direction = 'intrastate',
				rate = '".$_POST["$carrier_id"."__2__rate"]."',
				min_rate = '".$_POST["$carrier_id"."__2__min_rate"]."'
				";
				$success = $db->query($sql);
	
				$sql = "INSERT INTO customer_rate SET
				customer_id = '$_GET[id]',
				carrier_id = '$carrier_id',
				type = '".$_POST["$carrier_id"."__type"]."',
				direction = 'interstate',
				rate = '".$_POST["$carrier_id"."__3__rate"]."',
				min_rate = '".$_POST["$carrier_id"."__3__min_rate"]."'
				";
				$success = $db->query($sql);
			}
		}
		$sql = "SELECT carrier.id AS c_id, carrier.company, customer_rate.* FROM carrier LEFT JOIN customer_rate ON (customer_rate.carrier_id=carrier.id AND customer_rate.customer_id='$_GET[id]') WHERE carrier.type IN ('air','ltl') ORDER BY carrier.company";
		$carrier_rates = $db->query($sql);
		$customers_rates = "<form method='POST' action='./?action=customers_rate&id=$_GET[id]'>";
		if($carrier_rates) {
			//GROUP THE ARRAY BY c_id.
			foreach($carrier_rates AS $v) {
				$c_id_array["$v[c_id]"][] = $v;
			}
			//FOR THOSE CARRIERS WHICH HAVE NO DATA, IT DOES ONE THING, FOR THOSE WHO DO, IT DOES ANOTHER.
			foreach($c_id_array as $key=>$value) {
				$vars["bg1"] = $altbg1;
				$vars["bg2"] = $altbg2;
				$vars["action_id"] = $_GET["id"];
				if(count($value) > 1) {
					foreach($value AS $v) {
						switch($v["direction"]) {
							case "na":
							$v["$v[c_id]__1__rate"] = $v["rate"];
							$v["$v[c_id]__1__min_rate"] = $v["min_rate"];
							break;
							case "intrastate":
							$v["$v[c_id]__2__rate"] = $v["rate"];
							$v["$v[c_id]__2__min_rate"] = $v["min_rate"];
							break;
							case "interstate":
							$v["$v[c_id]__3__rate"] = $v["rate"];
							$v["$v[c_id]__3__min_rate"] = $v["min_rate"];
							break;
						}
						foreach($v AS $a=>$b) {
							$vars[$a] = $b;
						}
					}
					//GETTING CUSTOMER TYPES
					$vars["rate_type"] = "";
					$type=getEnum("customer_rate","type");
					foreach($type AS $type_value) {
						$selected = $vars["type"] == $type_value ? " SELECTED" : "";
						$vars["rate_type"] .="<option value='$type_value'$selected>$type_value</option>\n";
					}
					$customers_rates .= replace($vars, rf($htmlpath."customers_rates.html"));
				} else {
					foreach($value AS $v) {
						$v["$v[c_id]__1__rate"] = "";
						$v["$v[c_id]__1__min_rate"] = "";
						$v["$v[c_id]__2__rate"] = "";
						$v["$v[c_id]__2__min_rate"] = "";
						$v["$v[c_id]__3__rate"] = "";
						$v["$v[c_id]__3__min_rate"] = "";
						foreach($v AS $a=>$b) {
							$vars[$a] = $b;
						}
					}
					//GETTING CUSTOMER TYPES
					$vars["rate_type"] = "";
					$type=getEnum("customer_rate","type");
					foreach($type AS $value) {
						$selected = $vars["type"] == $value ? " SELECTED" : "";
						$vars["rate_type"] .="<option value='$value'$selected>$value</option>\n";
					}
					$customers_rates .= replace($vars, rf($htmlpath."customers_rates.html"));
				}
			}
			$customers_rates .= "<table width='100%'><tr><td align='center' bgcolor='white'><input type='submit' value='save'></td></tr></table></form>";
			$html["BODY"]=replace($vars, $customers_rates);
		}
		*/
	break;
	case "notes":
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM customer_note WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["note"])) {
			$sql = "INSERT INTO customer_note SET
			customer_id = '$_GET[id]',
			date_note = NOW(),
			admin_login_id = '$_LOGIN[id]',
			note = '$_POST[note]'";
			$success = $db->query($sql);
		}
		$sql = "SELECT * FROM customer_note WHERE customer_id = '$_GET[id]' ORDER BY date_note DESC";
		$notes = $db->query($sql);
		if($notes) {
			foreach($notes AS $v) {
				$v["date_note"] = date("m/d/Y g:i a",strtotime($v["date_note"]));
				$v["note"] = nl2br($v["note"]);
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["customers_notes_list_row"] .= replace($v, rf($htmlpath."customers_notes_list_row.html"));
			}
		} else {
			$vars["customers_notes_list_row"] = "<tr><td colspan='3'><b>Currently there are no notes</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$customers_notes_list = replace($vars, rf($htmlpath."customers_notes_list.html"));
		$html["BODY"]=replace($vars, $customers_notes_list);
		break;
	case "loginsadmin":
		$db->query("UPDATE customer_login SET admin='0' WHERE customer_id='$_GET[customer_id]'");	
		$db->query("UPDATE customer_login SET admin='1' WHERE id='$_GET[id]'");
		header("Location: ?action=customers_logins&id=$_GET[customer_id]");
		break;
	case "logins":
		$vars["error"] = "";
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM customer_login WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if($_POST["username"] && $_POST["password"]) {
			$sql = "SELECT id FROM customer_login WHERE username = '$_POST[username]'";
			$dupe_check = $db->query($sql);
			if($dupe_check) {
				$vars["error"] = "<span style='color:red'>That username is already in use.</span>";
			} else {
				$sql = "INSERT INTO customer_login SET
				customer_id = '$_GET[id]',
				admin = '0',
				username = '$_POST[username]',
				password = '$_POST[password]'";
				$success = $db->query($sql);
			}
		}
		$sql = "SELECT * FROM customer_login WHERE customer_id = '$_GET[id]' ORDER BY username";
		$logins = $db->query($sql);
		if($logins) {
			foreach($logins AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				if($v["admin"]=="1"){$v["username"]='<b>'.$v["username"].'</b>';}
				$vars["customers_logins_list_row"] .= replace($v, rf($htmlpath."customers_logins_list_row.html"));
			}
		} else {
			$vars["customers_logins_list_row"] = "<tr><td colspan='3'><b>Currently there are no customer logins</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$customers_logins_list = replace($vars, rf($htmlpath."customers_logins_list.html"));
		$html["BODY"]=replace($vars, $customers_logins_list);
	break;
	case "update";
	case "edit":
		include("customers_edit.php");
		break;
	case "list":
	default:
		//REMOVE A USER.
		if(isset($_GET["remove"])) {
			$sql = "UPDATE customer SET removed = '1' WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}

		// page logic
		$vars["prev_page"]="";
		$vars["next_page"]="";
		$numpp = 25;
		if(!$page){$page=1;}
		$_sl = ($page - 1) * $numpp;
		$_el = $numpp + 6;
	
		//ORDERING LOGIC.
		$vars["asc_id"] = $_GET["asc_id"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_company"] = $_GET["asc_company"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_address"] = $_GET["asc_address"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_city"] = $_GET["asc_city"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_state"] = $_GET["asc_state"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_zip"] = $_GET["asc_zip"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_contact_name"] = $_GET["asc_contact_name"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_contact_phone"] = $_GET["asc_contact_phone"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_contact_email"] = $_GET["asc_contact_email"] == "DESC" ? "ASC" : "DESC";
		$vars["url"] = "";
		switch($_GET["sort"]){
			case "id";
				$orderby = "id";
				if($vars["asc_id"] == "DESC") $orderby .=" DESC";
				break;
			case "state";
				$orderby = "mail_state";
				if($vars["asc_state"] == "DESC") $orderby .=" DESC";
				break;
			case "address";
				$orderby = "mail_address1";
				if($vars["asc_address"] == "DESC") $orderby .=" DESC";
				break;
			case "city";
				$orderby = "mail_city";
				if($vars["asc_city"] == "DESC") $orderby .=" DESC";
				break;
			case "zip";
				$orderby = "mail_zip";
				if($vars["asc_zip"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_name";
				$orderby = "main_contact_name";
				if($vars["asc_contact_name"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_phone";
				$orderby = "main_contact_phone";
				if($vars["asc_contact_phone"] == "DESC") $orderby .=" DESC";
				break;
			case "contact_email";
				$orderby = "main_contact_email";
				if($vars["asc_contact_email"] == "DESC") $orderby .=" DESC";
				break;
			default:
				$vars["asc_company"]="ASC";
				$orderby = "company";
				if($vars["asc_company"] == "DESC") $orderby .=" DESC";
		}

		//MAINTAINING SEARCH INTEGRITY THROUGH THE ORDERING LOGIC.
		if(isset($_GET["search"])) {
			$vars["url"] .= "&search=$_GET[search]";
		}
		if(isset($_GET["search_alpha"])) {
			$vars["url"] .= "&search_alpha=$_GET[search_alpha]";
		}
    
    $types = getEnum("customer", "type");
    $type_excludes = Array("lead", "inactive");
    
    if (!$type_filter)
      $type_filter = array_diff($types, $type_excludes);
    else
    {
      $where .= "(";
      foreach ($type_filter as $t)
        $where .= " type = '$t' OR";
      $where = trim($where,"OR");
      $where .= ") AND ";
    }
    
    // exclude lead and inactive
    foreach ($types as $t)
    {
      $count = $db->query("SELECT COUNT(*) FROM customer WHERE type = '$t' AND removed='0' $SALES_REP_FILTER GROUP BY type");
      $count = $count[0];
      $count = $count["COUNT(*)"];
      
      if (!$count)
        $count = "0";
      
      $vars["TYPE_OPTS"] .= "<option value=\"$t\"" . (in_array($t, $type_filter) ? " selected" : "") . ">$t ($count)</option>";
    }

		//SEARCH SWITCHES
		if(isset($_GET["search"])) {
			$field[] = "id";
			$field[] = "company";
			$field[] = "mail_zip";
			$field[] = "main_contact_name";
			$search_term = "$_GET[search]";
      $where .= "(";
			foreach($field AS $fld) {
				$where .= " $fld LIKE '%$search_term%' OR";
			}
			$where = trim($where,"OR");
      $where .= ") ";
			$where .= "ORDER BY $orderby";
		} elseif(isset($_GET["search_alpha"])) {
			$field = "company";
			switch($_GET["search_alpha"]) {
				case 0:
					$search_term =  "'0%' OR ".
											"company LIKE '1%' OR ".
											"company LIKE '2%' OR ".
											"company LIKE '3%' OR ".
											"company LIKE '4%' OR ".
											"company LIKE '5%' OR ".
											"company LIKE '6%' OR ".
											"company LIKE '7%' OR ".
											"company LIKE '8%' OR ".
											"company LIKE '9%'";
					break;
				case 1:
					$search_term = "'a%' OR company LIKE 'b%' OR company LIKE 'c%'";
					break;
				case 2:
					$search_term = "'d%' OR company LIKE 'e%' OR company LIKE 'f%'";
					break;
				case 3:
					$search_term = "'g%' OR company LIKE 'h%' OR company LIKE 'i%'";
					break;
				case 4:
					$search_term = "'j%' OR company LIKE 'k%' OR company LIKE 'l%'";
					break;
				case 5:
					$search_term = "'m%' OR company LIKE 'n%' OR company LIKE 'o%'";
					break;
				case 6:
					$search_term = "'p%' OR company LIKE 'q%' OR company LIKE 'r%'";
					break;
				case 7:
					$search_term = "'s%' OR company LIKE 't%' OR company LIKE 'u%'";
					break;
				case 8:
					$search_term = "'v%' OR company LIKE 'w%' OR company LIKE 'x%' OR company LIKE 'y%' OR company LIKE 'z%'";
					break;
			}
			$where = "($field LIKE $search_term) ORDER BY $orderby";
		} else {
			$where = "id  ='1219' and company LIKE '%%' ORDER BY $orderby";
		}
		
		$where = "removed = '0' $SALES_REP_FILTER AND ".$where;


		$sql = "SELECT id, company, mail_address1, mail_address2, mail_city, mail_state, mail_zip, main_contact_name, main_contact_phone, main_contact_email FROM customer WHERE $where LIMIT $_sl, $_el";

        //die($sql);
        $customer_list = $db->query($sql);
		if($customer_list) {
			$num_res = count($customer_list);
			$next_page = ($num_res > $numpp && $num_res == $_el) ? true : false;

			$row = rf($htmlpath."customers_list_row.html");
			foreach($customer_list AS $v) {$num++;
				if($num <= $numpp || !$next_page){
					$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
					$v["address"] = "$v[mail_address1] $v[mail_address2]";
					$vars["customers_list"] .= replace($v, $row);
				}
			}
			
			if($next_page){
				$npage = $page + 1;
				$vars["next_page"]='<a href="./?'.removeFromQS("page").'&page='.$npage.'">Next Page ></a>';			
			}
			if($page > 1){
				$ppage = $page - 1;
				$vars["prev_page"]='<a href="./?'.removeFromQS("page").'&page='.$ppage.'">< Previous Page</a>';
			}
		} else {
			$vars["customers_list"] = "<tr><th colspan='9'>No customers found.</th></tr>";
		}

		$html["LOCATION"] = "<h1>CUSTOMERS : LIST</h1>";

		$html["BODY"]=replace($vars,rf($htmlpath."customers_list.html"));	
}


?>