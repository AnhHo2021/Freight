<?php

$html["LOCATION"] = "<h1>CARRIERS</h1>";
switch($action){
	case "classes":
		if($_POST) {
			$sql = "DELETE FROM carrier_fak_address_class WHERE carrier_fak_address_id = '$_GET[id]' AND carrier_id = '$_GET[carrier_id]'";
			$success = $db->query($sql);
			foreach($_POST as $k => $v) {
				$sql = "INSERT INTO carrier_fak_address_class SET class_list_id1 = '$k', class_list_id2 = '$v', carrier_fak_address_id = '$_GET[id]', carrier_id = '$_GET[carrier_id]'";
				$success = $db->query($sql);
			}
		}
		/*GETTING FAK CLASS LIST*/
		//GET MAIN LIST
		$sql = "SELECT * FROM class_list ORDER BY id";
		$class_array = $db->query($sql);
		//GET SAVED LIST
		$sql = "SELECT * FROM carrier_fak_address_class WHERE carrier_fak_address_id = '$_GET[id]'";
		$saved_array = $db->query($sql);
		$u["class_list"] = "<form method='POST'>";
		foreach($class_array AS $f) {
			$u["class_list"] .="<tr><td>$f[class]</td>\n<td><select name='$f[id]'>";
			foreach($class_array AS $f2) {
				if($saved_array) {
					foreach($saved_array AS $x) {
						$s = "";
						if($x["class_list_id1"] == $f["id"] && $x["class_list_id2"] == $f2["id"]) {
							$s = " selected";
							break;
						}
					}
				} else {
					$s=($f["id"] == $f2["id"])?" selected":"";
				}
				$u["class_list"] .= "<option value='$f2[id]'$s>$f2[class]</option>\n";
			}
			$u["class_list"] .= "</select></td></tr>\n";
		}
		$u["class_list"] .= "<tr><td colspan='2' align='center'><input type='submit' value='save'></td></tr></form>";
		$html["BODY"]=replace($u,rf($htmlpath."carriers_fak_classes.html"));
	break;
	case "addresses":
		//GETTING BILLING STUFF
		$sql = "SELECT id, company, address FROM billing ORDER BY company";
		$company_array = $db->query($sql);
		foreach($company_array AS $billing_company) {
			$vars["billing_options"] .="<option value='$billing_company[id]'>$billing_company[company] $billing_company[address]</option>\n";
		}

		$vars["error"] = "";
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM carrier_fak_address WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
			$sql = "DELETE FROM carrier_fak_address_class WHERE carrier_fak_address_id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["company"])) {
//			$sql = "SELECT id FROM carrier_fak_address WHERE address = '$_POST[address]'";
//			$dupe_check = $db->query($sql);
//			if($dupe_check) {
//				$vars["error"] = "<span style='color:red'>That address name is already in use.</span>";
//			} else {
				if($_POST["id"]) {
					$sql = "UPDATE carrier_fak_address SET ";
					$post_sql = " WHERE id = '$_POST[id]'";
				} else {
					$sql = "INSERT INTO carrier_fak_address SET ";
					$post_sql = "";
				}
				
				foreach($_POST AS $k=>$v) {
					if($k != "id" && $k != 'submitme') {
						$sql .= "$k = '$v',";
					}
				}
				$sql .= "carrier_id='$_GET[id]'".$post_sql;
				$success = $db->query($sql);
//			}
		}
		$sql = "SELECT f.*,b.company AS billing_company FROM carrier_fak_address f LEFT JOIN billing b ON f.billing_id=b.id WHERE carrier_id = '$_GET[id]' ORDER BY company";
		$address_array = $db->query($sql);
		if($address_array) {
			foreach($address_array AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["carriers_addresses_list_row"] .= replace($v, rf($htmlpath."carriers_addresses_list_row.html"));
			}
		} else {
			$vars["carriers_addresses_list_row"] = "<tr><td colspan='3'><b>Currently there are no carrier FAK addresses</b></td></tr>";
		}
		$vars["id"] = $_GET["id"];
		$vars["action_id"] = $_GET["id"];

		//GETTING STATES FOR SELECT STATEMENTS.
		$sql = "SELECT * FROM state ORDER BY state";
		$states = $db->query($sql);
		$vars["states"] = "";
		foreach($states AS $v) {
			$s=($vars["state"] == $v["code"])?" selected":"";
			$vars["states"] .= "<option value='$v[code]'$s>$v[code]</option>\n";
		}

		$carriers_addresses_list = replace($vars, rf($htmlpath."carriers_addresses_list.html"));
		$html["BODY"]=replace($vars, $carriers_addresses_list);
	break;
	case "accessorials":
		if(!empty($_POST)) {
			//GET RID OF ALL OF THE EXISTING ROWS SO WE CAN REPLACE THEM EASILY.
			$sql="DELETE FROM carrier_accessorial WHERE carrier_id = '$_GET[id]'";
			$success = $db->query($sql);
			//THESE POSTS AREN'T THE SAME AS THE DATABASE, SO THIS IS WEIRD.
			//BUILDING THE MEGA-ARRAY OF ALL THE VALUES WE NEED.
			foreach($_POST AS $k=>$v) {
				$temp = explode("__",$k);
				$id = $temp[0];
				$name = $temp[1];
				$name_array[$id][$name] = $v;
			}
			//CREATING THE SQL STATEMENTS FOR EACH OF THE ITEMS ENTERED.
			foreach($name_array AS $v) {
				$sql = "INSERT INTO carrier_accessorial SET \n";
				foreach($v AS $k=>$value) {
					$sql .= "$k = '$value',\n";
				}
				$sql .= "carrier_id = '$_GET[id]'\n\n";
				$success = $db->query($sql);
			}
		}
		$sql = "SELECT a.name,a.id AS a_id,ca.* FROM accessorial a LEFT JOIN carrier_accessorial ca ON (a.id=ca.accessorial_id AND ca.carrier_id='$_GET[id]') ORDER BY a.name";
		$accessorials = $db->query($sql);
		if($accessorials) {
			$i=0;
			//GET THE $a_id IN TO THE HTML.
			foreach($accessorials AS $value) {
				$v["name"] = $value["name"];
				$v["a_id"] = $value["a_id"];
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["carriers_accessorials_row"] .= replace($v, rf($htmlpath."carriers_accessorials_row.html"));
				$i++;
			}
			//DO THE REST OF THE HTML CONVERSION. (PLUG IN THE VARIABLES)
			foreach($accessorials AS $value) {
				foreach($value AS $k=>$a) {
					$new_name = $value["a_id"]."__".$k;
					$v[$new_name] = $a;
				}
				//GETTING CARRIER TYPES
				$drop_type=getEnum("carrier_accessorial","type");
				foreach($drop_type AS $type_value) {
					$selected = $value["type"] == $type_value ? " SELECTED" : "";
					$v[$value["a_id"]."__accessorial_types"] .="<option value='$type_value'$selected>$type_value</option>\n";
				}
			}
			$vars["carriers_accessorials_row"] = replace($v, $vars["carriers_accessorials_row"]);
		}
		$vars["action_id"] = $_GET["id"];
		$carriers_accessorials = replace($vars, rf($htmlpath."carriers_accessorials.html"));
		$html["BODY"]=replace($vars, $carriers_accessorials);
	break;
	case "cost":
		if(!empty($_POST)) {
			foreach($service_ids as $service_id){
				$db->query("DELETE FROM carrier_cost WHERE carrier_id='$_GET[id]' AND service_id='$service_id'");
				$db->query("INSERT INTO carrier_cost SET carrier_id='$_GET[id]',service_id='$service_id',type='".${"type_".$service_id}."',direction='intrastate',cost='".${"cost_".$service_id."_intrastate"}."',rate='".${"rate_".$service_id."_intrastate"}."',min_cost='".${"min_cost_".$service_id."_intrastate"}."',min_rate='".${"min_rate_".$service_id."_intrastate"}."'");
				$db->query("INSERT INTO carrier_cost SET carrier_id='$_GET[id]',service_id='$service_id',type='".${"type_".$service_id}."',direction='interstate',cost='".${"cost_".$service_id."_interstate"}."',rate='".${"rate_".$service_id."_interstate"}."',min_cost='".${"min_cost_".$service_id."_interstate"}."',min_rate='".${"min_rate_".$service_id."_interstate"}."'");
			}
		}
		$services = $db->query("SELECT carrier_services.service_id,service.name AS service FROM carrier_services INNER JOIN service ON carrier_services.service_id=service.id WHERE carrier_services.carrier_id='$_GET[id]'");
		if($services){
			$sql = "SELECT carrier_cost.* FROM carrier_cost WHERE carrier_cost.carrier_id = '$_GET[id]'";
			$costs_temp = $db->query($sql);
			if($costs_temp) {
				foreach($costs_temp as $v){
					$costs[$v["service_id"]][$v["direction"]]=$v;
				}
			}
			$types=getEnum("carrier_cost","type");
			$table = rf($htmlpath."carriers_costs_table.html");
			foreach($services as $v){
				$v["bg1"] = $altbg1;
				$v["bg2"] = $altbg2;
				
				foreach($types as $vv){
					$s=($vv == $costs[$v["service_id"]]["intrastate"]["type"] || $vv == $costs[$v["service_id"]]["interstate"]["type"])?" selected":"";
					$v["type"].='<option value="'.$vv.'"'.$s.'>'.$vv.'</option>';
				}

				if($costs[$v["service_id"]]){
					$vv=$costs[$v["service_id"]];
					${"cost_".$vv["direction"]}=$vv["cost"];
					${"rate_".$vv["direction"]}=$vv["rate"];
					${"min_cost_".$vv["direction"]}=$vv["min_cost"];
					${"min_rate_".$vv["direction"]}=$vv["min_rate"];
				}

				$v["cost_intrastate"]=$costs[$v["service_id"]]["intrastate"]["cost"];
				$v["min_cost_intrastate"]=$costs[$v["service_id"]]["intrastate"]["min_cost"];
				$v["rate_intrastate"]=$costs[$v["service_id"]]["intrastate"]["rate"];
				$v["min_rate_intrastate"]=$costs[$v["service_id"]]["intrastate"]["min_rate"];
				
				$v["cost_interstate"]=$costs[$v["service_id"]]["interstate"]["cost"];
				$v["min_cost_interstate"]=$costs[$v["service_id"]]["interstate"]["min_cost"];
				$v["rate_interstate"]=$costs[$v["service_id"]]["interstate"]["rate"];
				$v["min_rate_interstate"]=$costs[$v["service_id"]]["interstate"]["min_rate"];

				$vars["COSTS"].=replace($v,$table);
			}

			$vars["action_id"] = $_GET["id"];
			$carriers_costs = replace($vars, rf($htmlpath."carriers_costs.html"));
			$html["BODY"]=replace($vars, $carriers_costs);
		}else{
			$html["BODY"] = "No Services Defined";
		}
	break;
	case "terminals":
		$vars["error"] = "";
		if(isset($_GET["remove"])) {
			$sql = "UPDATE carrier_terminal SET removed='1' WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["name"])) {
			if($_POST["id"]) {
				$sql = "UPDATE carrier_terminal SET ";
				$post_sql = " WHERE id = '$_POST[id]'";
			} else {
				$sql = "INSERT INTO carrier_terminal SET ";
				$post_sql = "";
			}
							
			foreach($_POST as $k => $v) {
				if($k != "id" && $k != 'submitme') {
					$sql .= "$k = '$v',";
				}
			}
			$sql .= "carrier_id='$_GET[id]'".$post_sql;
			$success = $db->query($sql);
		}
		$sql = "SELECT * FROM carrier_terminal WHERE carrier_id = '$_GET[id]' AND removed='0' ORDER BY name";
		$terminals = $db->query($sql);
		if($terminals) {
			foreach($terminals AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["carriers_terminals_list_row"] .= replace($v, rf($htmlpath."carriers_terminals_list_row.html"));
			}
		} else {
			$vars["carriers_terminals_list_row"] = "<tr><td colspan='3'><b>Currently there are no terminals for this carrier</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$carriers_terminals_list = replace($vars, rf($htmlpath."carriers_terminals_list.html"));
		$html["BODY"]=replace($vars, $carriers_terminals_list);
	break;
	case "login":
		$vars["error"] = "";
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM carrier_login WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["carrier_username"])) {
			$sql = "SELECT id FROM carrier_login WHERE username = '$_POST[carrier_username]'";
			$dupe_check = $db->query($sql);
			if($dupe_check) {
				$vars["error"] = "<span style='color:red'>That login is already in use.</span>";
			}else{
				if($_POST["id"]) {
					$db->query("UPDATE carrier_login SET fname='$fname', lname='$lname', username='$carrier_username', password='$carrier_password' WHERE id='$_POST[id]'");
				} else {
					$db->query("INSERT INTO carrier_login SET active='1', carrier_id='$_GET[id]', fname='$fname', lname='$lname', username='$carrier_username', password='$carrier_password'");
				}
			}
		}
		$sql = "SELECT id, fname, lname, username as carrier_username, password as carrier_password FROM carrier_login WHERE carrier_id = '$_GET[id]' AND active='1' AND removed='0' ORDER BY username";
		$logins = $db->query($sql);
		if($logins) {
			foreach($logins AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["carriers_login_list_row"] .= replace($v, rf($htmlpath."carriers_login_list_row.html"));
			}
		} else {
			$vars["carriers_login_list_row"] = "<tr><td colspan='3'><b>Currently there are no carrier logins</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$vars["carrier_id"] = $_GET["id"];
		$carriers_login_list = replace($vars, rf($htmlpath."carriers_login_list.html"));
		$html["BODY"]=replace($vars, $carriers_login_list);
	break;
	case "contacts":
		//GETTING CARRIER TYPES
		$type=getEnum("carrier_contact","type");
		foreach($type AS $value) {
			$vars["type"] .="<option value='$value'$selected>$value</option>\n";
		}

		$vars["error"] = "";
		if(isset($_GET["remove"])) {
			$sql = "DELETE FROM carrier_contact WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		if(isset($_POST["contact"])) {
			$sql = "SELECT id FROM carrier_contact WHERE contact = '$_POST[contact]' AND id != '$_POST[id]'";
			$dupe_check = $db->query($sql);
			if($dupe_check) {
				$vars["error"] = "<span style='color:red'>That contact name is already in use.</span>";
			}else{
				if($_POST["id"]) {
					$sql = "UPDATE carrier_contact SET ";
					$post_sql = " WHERE id = '$_POST[id]'";
				} else {
					$sql = "INSERT INTO carrier_contact SET ";
					$post_sql = "";
				}
								
				foreach($_POST as $k => $v) {
					if($k != "id" && $k != 'submitme') {
						$sql .= "$k = '$v',";
					}
				}
				$sql .= "carrier_id='$_GET[id]'".$post_sql;
				$success = $db->query($sql);
			}
		}
		$sql = "SELECT * FROM carrier_contact WHERE carrier_id = '$_GET[id]' ORDER BY contact";
		$contacts = $db->query($sql);
		if($contacts) {
			foreach($contacts AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				$vars["carriers_contacts_list_row"] .= replace($v, rf($htmlpath."carriers_contacts_list_row.html"));
			}
		} else {
			$vars["carriers_contacts_list_row"] = "<tr><td colspan='3'><b>Currently there are no carrier contacts</b></td></tr>";
		}
		$vars["action_id"] = $_GET["id"];
		$carriers_contacts_list = replace($vars, rf($htmlpath."carriers_contacts_list.html"));
		$html["BODY"]=replace($vars, $carriers_contacts_list);
	break;
	case "update";
	case "edit":
		include("carriers_edit.php");
		break;
	case "list":
	default:
		//REMOVE A USER.
		if(isset($_GET["remove"])) {
			$sql = "UPDATE carrier SET removed = '1' WHERE id = '$_GET[remove]'";
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
		$vars["asc_type"] = $_GET["asc_type"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_address"] = $_GET["asc_address"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_city"] = $_GET["asc_city"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_state"] = $_GET["asc_state"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_zip"] = $_GET["asc_zip"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_fax"] = $_GET["asc_fax"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_phone"] = $_GET["asc_phone"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_quotes_phone"] = $_GET["asc_quotes_phone"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_pickups_phone"] = $_GET["asc_pickups_phone"] == "DESC" ? "ASC" : "DESC";
		$vars["asc_account_num"] = $_GET["asc_account_num"] == "DESC" ? "ASC" : "DESC";
		$vars["url"] = "";
		switch($_GET["sort"]){
			case "id";
				$orderby = "id";
				if($vars["asc_id"] == "DESC") $orderby .=" DESC";
				break;
			case "state";
				$orderby = "state";
				if($vars["asc_state"] == "DESC") $orderby .=" DESC";
				break;
			case "address";
				$orderby = "address1";
				if($vars["asc_address"] == "DESC") $orderby .=" DESC";
				break;
			case "city";
				$orderby = "city";
				if($vars["asc_city"] == "DESC") $orderby .=" DESC";
				break;
			case "zip";
				$orderby = "zip";
				if($vars["asc_zip"] == "DESC") $orderby .=" DESC";
				break;
			case "type";
				$orderby = "type";
				if($vars["asc_type"] == "DESC") $orderby .=" DESC";
				break;
			case "phone";
				$orderby = "phone";
				if($vars["asc_phone"] == "DESC") $orderby .=" DESC";
				break;
			case "quotes_phone";
				$orderby = "quotes_phone";
				if($vars["asc_quotes_phone"] == "DESC") $orderby .=" DESC";
				break;
			case "pickups_phone";
				$orderby = "pickups_phone";
				if($vars["asc_pickups_phone"] == "DESC") $orderby .=" DESC";
				break;
			case "fax";
				$orderby = "fax";
				if($vars["asc_fax"] == "DESC") $orderby .=" DESC";
				break;
			case "account_num";
				$orderby = "account_num";
				if($vars["asc_account_num"] == "DESC") $orderby .=" DESC";
				break;
			case "company";
				$orderby = "company";
				if($vars["asc_company"] == "DESC") $orderby .=" DESC";
				break;
			default:
				$orderby = "company ASC";
		}

		//MAINTAINING SEARCH INTEGRITY THROUGH THE ORDERING LOGIC.
		if(isset($_GET["search"])) {
			$vars["url"] .= "&search=$_GET[search]";
		}
		if(isset($_GET["search_alpha"])) {
			$vars["url"] .= "&search_alpha=$_GET[search_alpha]";
		}

		//SEARCH SWITCHES
		if(isset($_GET["search"])) {
			$field[] = "id";
			$field[] = "company";
			$field[] = "zip";
			$search_term = "$_GET[search]";
			foreach($field AS $fld) {
				$where .= " $fld LIKE '%$search_term%' OR";
			}
			$where = trim($where,"OR");
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
			$where = "$field LIKE $search_term ORDER BY $orderby";
		} else {
			$where = "company LIKE '%%' ORDER BY $orderby";
		}
		
		$where = "removed = '0' AND ".$where;

		$sql = "SELECT id, type, company, address1, address2, city, state, zip, phone, fax, account_num, quotes_phone, pickups_phone FROM carrier WHERE $where LIMIT $_sl, $_el";
		$carrier_list = $db->query($sql);
		if($carrier_list) {
			$num_res = count($carrier_list);
			$next_page = ($num_res > $numpp && $num_res == $_el) ? true : false;

			$row = rf($htmlpath."carriers_list_row.html");
			foreach($carrier_list AS $v) {
				$num++;
				if($num <= $numpp || !$next_page){
					$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
					$v["address"] = "$v[address1] $v[address2]";
					$vars["carriers_list"] .= replace($v, $row);
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
			$vars["carriers_list"] = "<tr><th colspan='9'>No carriers found.</th></tr>";
		}

		$html["LOCATION"] = "<h1>CARRIERS : LIST</h1>";

		$html["BODY"]=replace($vars,rf($htmlpath."carriers_list.html"));	
}


?>