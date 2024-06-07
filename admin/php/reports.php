<?php

switch($action){
	case "ardetail":
		include("reports_ardetail.php");
		break;
	case "snapshot":
		include("reports_snapshot.php");
		break;
	case "revenue":
		include("reports_revenue.php");
	break;
	case "carriervolume":
		include("reports_carriervolume.php");
	break;
	case "quoteship":
		include("reports_quoteship.php");
	break;
	case "shipmentservices":
		include("reports_shipmentservices.php");
	break;
	case "customerlist":
		include("reports_customerlist.php");
		break;
	default:
		if($_LOGIN["access_level"] == "admin"){
			$html["BODY"]=replace($vars,rf($htmlpath."reports.html"));
		}else if($_LOGIN["access_level"] == "sales"){
			$html["BODY"]=replace($vars,rf($htmlpath."reports.sales.html"));
		}
}

?>