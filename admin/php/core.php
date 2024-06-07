<?php


$DEV_MODE=false;
$html["VERSION"]="4.7";
$html["DEV_MODE"]=($DEV_MODE)?"DEVELOPMENT MODE":"";


header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// includes
include("functions.php");
include("db.php");

// connect to db
// connect to db
if($DEV_MODE){
    //$db = new DB("cnordahl_freight","F{LrTku6i7p;");
    $db = new DB("1wire_phone","Trouble54321!");

}else{
    //$db = new DB("cnordahl_freight","F{LrTku6i7p;");
    $db = new DB("1wire_phone","Trouble54321!");
}

// env vars
if($_POST){extract($_POST);}
if($_GET){extract($_GET);}
if($_COOKIE){extract($_COOKIE);}


// paths
if($DEV_MODE){
	/*$htmlpath = "/home/cnordahl/public_html/admin/html/";
	$phppath = "/home/cnordahl/public_html/admin/php/";
	$htpath = "/home/cnordahl/public_html/admin/htdocs/";
	$pdfpath = "/home/cnordahl/public_html/admin/pdf/";
	*/
    $htmlpath = "/mnt/web/transport/transport/freight/admin/html/";
    $phppath = "/mnt/web/transport/transport/freight/admin/php/";
    $htpath = "/mnt/web/transport/transport/freight/admin/htdocs/";
    $pdfpath = "/mnt/web/transport/transport/freight/admin/pdf/";

}else{
	/*$htmlpath = "/home/cnordahl/public_html/admin/html/";
	$phppath = "/home/cnordahl/public_html/admin/php/";
	$htpath = "/home/cnordahl/public_html/admin/htdocs/";
	$pdfpath = "/home/cnordahl/public_html/admin/pdf/";
	*/

    $htmlpath = "/mnt/web/transport/transport/freight/admin/html/";
    $phppath = "/mnt/web/transport/transport/freight/admin/php/";
    $htpath = "/mnt/web/transport/transport/freight/admin/htdocs/";
    $pdfpath = "/mnt/web/transport/transport/freight/admin/pdf/";
	
}


// template variables
$html["BODY"]="";
$html["TOP_NAV"]="";
$html["SUB_NAV"]="";
$html["LOGIN"]="";
$html["LOCATION"]="";
//anh comment

//misc vars
$vars["msg"] = ($msg)?$msg:"";
echo "test 1";
include("login.php");
echo "test 2";
if($login){
    echo "test 3";
	$full_action=$action;
	$pos=strpos($action,"_");
	if($pos){
        echo "test 4";
		$page_action=substr($action,0,$pos);
		$action=substr($action,$pos+1);
	}else{
        echo "test 5";
		$page_action=$action;
		$action="";
	}

	// build nav
	include("nav.php");
    echo "test 6";
    die();
	$html["LOGIN"]='Logged In As <b>'.$_LOGIN["fname"]." ".$_LOGIN["lname"].'</b> - <a href="index.php?action=logout"><small>Logout</small></a>';

	$altbg1 ="#d7d7d2"; //"#C4C384";
	$altbg2 = "#b2b2af";//"#EBEBD5";
	$rolloffbg = "#FFFF00";
	$expeditedbg = "#FF8C00";

	$descarray=array();
	switch($page_action){
		default:
			if(is_file($phppath.$page_action.".php")){
				include($phppath.$page_action.".php");
			}else{
				header("Location: index.php?action=home");
				die();
			}
	}

	// template
   // echo "test =";
   // echo $_GET["action"];
	switch($_GET["action"]) {
		case "accounting_statements-print":
		case "accounting_listprint":
		case "customers_classes":
		case "customers_addresses":
		case "customers_addrbook":
		case "customers_logins":
		case "customers_notes":
		case "customers_rate":
		case "customers_accessorials":
		case "carriers_login":
		case "carriers_contacts":
		case "carriers_terminals":
		case "carriers_addresses":
		case "carriers_cost":
		case "carriers_accessorials":
		case "carriers_classes":
		case "calendar_add":
		case "calendar_update":
		case "addressfinder":
		case "zipfinder":
		case "customerfinder":
		case "quotefinder":
		case "trackingfinder":
		case "trackingfinder_log":
		case "trackingfinder_notes":
		case "carrierfinder":
		case "terminalfinder":
		case "quotes_notes":
		case "quotes_rates":
		case "quotes_viewwindow":
		case "shipmentfinder":
		case "shipments_notes":
		case "shipments_bol":
		case "marketing_ns":
		case "marketing_org_request":
		case "marketing_dest_request":
		case "marketing_inactivity":
		case "invoicefinder":
		case "marketing_edit":
			$template = rf($htmlpath."template.blank.html");
		break;
		default:
			$template = rf($htmlpath."template.html");
	}

	if($descarray){
		foreach($descarray as $item){
			$newdescarray[]=fixDescArray($item);
		}
		$html["descarray"]='"'.implode('","',$newdescarray).'"';
	}else{
		$html["descarray"]="";
	}

	if($set_template){$template=rf($htmlpath.$set_template);}
	echo replace($html,$template);
}else{
    echo "test 0"; die();
	$vars["login_mssg"]="";
	if($action == "login"){
		$vars["login_mssg"]="Username or Password Invalid";
	}
	
	//echo $htmlpath."login.html   ----  ";
	echo replace($vars,rf($htmlpath."login.html"));
}
echo "test 0"; die();
?>