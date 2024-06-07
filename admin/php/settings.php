<?php

$html["LOCATION"] = "<h1>SETTINGS</h1>";
switch($action){
	case "logins":
	case "logins_update":
	case "logins_edit":
		$html["LOCATION"] = "<h1>SETTINGS : LOGINS</h1>";
		include("settings_logins.php");
		break;
	default:
		header("Location: index.php?action=settings_logins");
		//menu when more settings
		//$html["BODY"]=replace($vars,rf($htmlpath."settings.html"));
}

?>