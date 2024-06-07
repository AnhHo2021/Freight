<?php
switch($action){
	case "update";
		if($_POST) {
//			print_r($_POST);
			if(!$_POST["type"] || !$_POST["company"] || !$_POST["address1"] || !$_POST["city"] || !$_POST["state"] || !$_POST["zip"] || !$_POST["phone"]){
				$error_messages[] = "Please complete all required fields";
			}
			//MAKE SURE NOBODY ELSE HAS THE COMPANY NAME POSTED.
			$sql = "SELECT id FROM carrier WHERE company = '$_POST[company]' AND id != '$_GET[id]'";
			$duplicate_check = $db->query($sql);
			if($duplicate_check) {
				$error_messages[] = "That company name is already in use (# {$duplicate_check[0]["id"]}).  Please enter another name.";
			}

			if(!$error_messages){
				if(!empty($_GET["id"])) {
					$sql = "DELETE FROM carrier_fak_class WHERE carrier_id = '$_GET[id]'";
					$success = $db->query($sql);
					$sql_prefix = "UPDATE carrier SET ";
					$sql_suffix = ", updated_admin_login_id = '$_LOGIN[id]', date_updated = NOW() WHERE id = '$_GET[id]'";
					$id_is_set = TRUE;
				} else {
					$sql_prefix = "INSERT INTO carrier SET ";
					$sql_suffix = ", created_admin_login_id = '$_LOGIN[id]', date_created = NOW()";
					$id_is_set = FALSE;
				}
				foreach($_POST as $k => $v) {
					if(!is_int($k)) {
						$inner_sql .= "$k = '$v',";
					}
				}
				$inner_sql = trim($inner_sql,",");
				$sql = "$sql_prefix $inner_sql $sql_suffix";
				$success = $db->query($sql);
				//INSERT CLASSES
				if(!$id_is_set) {
//					$_GET["id"] = mysql_insert_id();
					$_GET["id"] = mysqli_insert_id($db->conn);
				}
				foreach($_POST as $k => $v) {
					if(is_int($k)) {
						$sql = "INSERT INTO carrier_fak_class SET class_list_id1 = '$k', class_list_id2 = '$v', carrier_id = '$_GET[id]'";
						$success = $db->query($sql);
					}
				}
//				header("Location: index.php?action=carriers_list");
//				exit();
			}
		}
	case "edit":
		if($_GET["id"]) {
			$sql = "SELECT * FROM carrier WHERE id = '$_GET[id]'";
			$carrier_data = $db->query($sql);
			$vars["carriers_logins"] = replace($carrier_data[0], rf($htmlpath."carriers_login.html"));
			$vars["carriers_more_info"] = replace($carrier_data[0], rf($htmlpath."carriers_more_info.html"));
			$vars["carriers_more_info_2"] = replace($carrier_data[0], rf($htmlpath."carriers_more_info_2.html"));
			$vars["carriers_more_info_3"] = replace($carrier_data[0], rf($htmlpath."carriers_more_info_3.html"));
			$vars["carriers_more_info_4"] = replace($carrier_data[0], rf($htmlpath."carriers_more_info_4.html"));
		}
		if($carrier_data){
			$sql = "SELECT username,password FROM carrier_login WHERE carrier_id = '$_GET[id]'";
			$carrier_login = $db->query($sql);
			if($carrier_login){
				$carrier_data[0]["carrier_username"] = $carrier_login[0]["username"];
				$carrier_data[0]["carrier_password"] = $carrier_login[0]["password"];
			}else{
				$carrier_data[0]["carrier_username"] = "";
				$carrier_data[0]["carrier_password"] = "";
			}

			foreach($carrier_data[0] as $n=>$v){
				$vars[$n]=($_POST[$n])?$_POST[$n]:$v;
			}
		} else {
			$vars = getFields("carrier");
			$vars["carriers_logins"] = "";
			$vars["carriers_more_info"] = "";
			$vars["carriers_more_info_2"] = "";
			$vars["carriers_more_info_3"] = "";
			$vars["carriers_more_info_4"] = "";
		}
		$vars["error_message"]=($error_messages)?implode("<br>",$error_messages)."<br><br>":"";

		if(!$error_messages){
			$vars["error_message"] = "";
		}
		//GETTING CARRIER TYPES
		$type=getEnum("carrier","type");
		foreach($type AS $value) {
			$selected = $vars["type"] == $value ? " SELECTED" : "";
			$vars["carrier_type"] .="<option value='$value'$selected>$value</option>\n";
		}
		//GETTING STATES FOR SELECT STATEMENTS.
		$sql = "SELECT * FROM state ORDER BY state";
		$states = $db->query($sql);
		$vars["states"] = "";
		foreach($states AS $v) {
			$s=($vars["state"] == $v["code"])?" selected":"";
			$vars["states"] .= "<option value='$v[code]'$s>$v[state]</option>\n";
		}

		if(!empty($_GET["id"])) {
			/*GETTING FAK CLASS LIST*/
			//GET MAIN LIST
			$sql = "SELECT * FROM class_list ORDER BY id";
			$class_array = $db->query($sql);
			//GET SAVED LIST
			$sql = "SELECT * FROM carrier_fak_class WHERE carrier_id = '$_GET[id]'";
			$saved_array = $db->query($sql);
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
			$vars["carriers_fak_classes"]=replace($u,rf($htmlpath."carriers_fak_classes.html"));
			$u["id"] = $_GET["id"];
			$vars["carriers_fak_addresses"] = replace($u,rf($htmlpath."carriers_fak_addresses.html"));
		} else {
			$vars["carriers_fak_classes"]="";
			$vars["carriers_fak_addresses"] = "";
		}


		//GETTING BILLING STUFF
		$sql = "SELECT id, company, address FROM billing ORDER BY company";
		$company_array = $db->query($sql);
		foreach($company_array AS $billing_company) {
			$selected = $vars["billing_id"] == $billing_company["id"] ? " SELECTED" : "";
			$vars["billing_options"] .="<option value='$billing_company[id]'$selected>$billing_company[company] $billing_company[address]</option>\n";
		}
		$html["BODY"]=replace($vars,rf($htmlpath."carriers_edit.html"));
		break;
}
?>
