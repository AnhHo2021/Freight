<?php

switch($action){
	case "logins":
		//REMOVE A USER.
		if(isset($_GET["remove"])) {
			$sql = "UPDATE admin_login SET removed = '1' WHERE id = '$_GET[remove]'";
			$success = $db->query($sql);
		}
		$vars["asc_lname"] = $_GET["asc_lname"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_fname"] = $_GET["asc_fname"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_username"] = $_GET["asc_username"] == "DESC" ? "ASC" : "DESC"; 
		$vars["asc_access"] = $_GET["asc_access"] == "DESC" ? "ASC" : "DESC"; 
		switch($_GET["sort"]){
			case "lname";
			$orderby = "lname";
			if($vars["asc_lname"] == "DESC") $orderby .=" DESC";
			break;
			case "username";
			$orderby = "username";
			if($vars["asc_username"] == "DESC") $orderby .=" DESC";
			break;
			case "access_level";
			$orderby = "access_level";
			if($vars["asc_access"] == "DESC") $orderby .=" DESC";
			break;
			default:
			$orderby = "fname";
			if($vars["asc_fname"] == "DESC") $orderby .=" DESC";
		}

		$sql = "SELECT * FROM admin_login WHERE removed = '0' ORDER BY $orderby";
		$logins = $db->query($sql);
		if($logins) {
			$row = rf($htmlpath."settings_logins_row.html");
			foreach($logins AS $v) {
				$v["bg"] = ($x++ % 2) ? $altbg1 : $altbg2;
				if($v["active"]) {
					$vars["logins_active"] .= replace($v, $row);
				} else {
					$vars["logins_inactive"] .= replace($v, $row);
				}
			}
		} else {
			$vars["logins_active"] = "<tr><th colspan='5'>No active users found.</th></tr>";
			$vars["logins_inactive"] = "<tr><th colspan='5'>No inactive users found.</th></tr>";
		}
		if(!$vars["logins_active"]) $vars["logins_active"] = "<tr><th colspan='5'>No active users found.</th></tr>";
		if(!$vars["logins_inactive"]) $vars["logins_inactive"] = "<tr><th colspan='5'>No inactive users found.</th></tr>";
		$html["BODY"]=replace($vars,rf($htmlpath."settings_logins.html"));
		break;
	case "logins_update";
		if($_POST) {
			if(!$_POST["fname"] || !$_POST["lname"] || !$_POST["user_username"] || !$_POST["user_password"]){
				$error_messages[] = "Please complete all fields";
			}
			//MAKE SURE NOBODY ELSE HAS THE USERNAME POSTED.
			$sql = "SELECT id FROM admin_login WHERE username = '$_POST[user_username]' AND id != '$_GET[id]'";
			$login_check = $db->query($sql);
			if($login_check) {
				$error_messages[] = "We're sorry, someone is using that username.  Please choose another one.";
			}

			if(!$error_messages){
				if(!empty($_GET["id"])) {
					$sql_prefix = "UPDATE admin_login SET ";
					$sql_suffix = "WHERE id = '$_GET[id]'";
				} else {
					$sql_prefix = "INSERT INTO admin_login SET ";
					$sql_suffix = "";
				}
				$sql = "$sql_prefix
				active = '$_POST[active]',
				fname = '$_POST[fname]',
				lname = '$_POST[lname]',
				username = '$_POST[user_username]',
				password = '$_POST[user_password]',
				access_level = '$_POST[access_level]'
				$sql_suffix";
				$success = $db->query($sql);
				header("Location: index.php?action=settings_logins");
				exit();
			}
		}
	case "logins_edit":
		if($_GET["id"]){
			$sql = "SELECT * FROM admin_login WHERE id = '$_GET[id]'";
			$admin_login = $db->query($sql);
			if($admin_login){
				$vars = $admin_login[0];
			}
		}
		if(!$_GET[id]){
			$vars["id"]=$_POST["id"];
			$vars["fname"]=$_POST["fname"];
			$vars["lname"]=$_POST["lname"];
			$vars["username"]=$_POST["user_username"];
			$vars["password"]=$_POST["user_password"];
			if(!$_POST["active"]){$_POST["active"]="1";}
			$vars["active"]=$_POST["active"];			
			if(!$_POST["access_level"]){$_POST["access_level"]="sales";}
			$vars["access_level"]=$_POST["access_level"];
		}
		if($vars["active"]) {
			$vars["active_selected_yes"] = " CHECKED";
			$vars["active_selected_no"] = "";
		} else {
			$vars["active_selected_no"] = " CHECKED";
			$vars["active_selected_yes"] = "";
		}

		// ACCESS LEVELS
		$access_levels=getEnum("admin_login","access_level");
		foreach($access_levels AS $value) {
			$selected = $vars["access_level"] == $value ? " SELECTED" : "";
			$vars["access_levels"] .="<option value='$value'$selected>$value</option>\n";
		}

		$vars["error_message"]=($error_messages)?implode("<br>",$error_messages)."<br><br>":"";
		$html["BODY"]=replace($vars,rf($htmlpath."settings_logins_edit.html"));
		break;
	default:
		header("Location: index.php?action=settings");
}

?>