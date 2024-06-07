<?php
//print_r('testttt'); die();
//$info=$db->query("SELECT marketing_letters.*, customer.main_contact_name, customer_login.username, customer_login.password FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
if($action=="dest_request"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password, shipment.d_to, shipment.d_address1, shipment.d_address2, shipment.d_city, shipment.d_state, shipment.d_zip FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id INNER JOIN shipment ON shipment.id=marketing_letters.shipment_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="org_request"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password, shipment.o_to, shipment.o_address1, shipment.o_address2, shipment.o_city, shipment.o_state, shipment.o_zip FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id INNER JOIN shipment ON shipment.id=marketing_letters.shipment_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="ns"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}else if($action=="inactivity"){
	$info=$db->query("SELECT marketing_letters.*, marketing_type.subject, marketing_type.file, customer.main_contact_name, customer_login.username, customer_login.password FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id INNER JOIN customer_login ON customer_login.customer_id=customer.id INNER JOIN marketing_type ON marketing_type.id=marketing_letters.marketing_type_id WHERE marketing_letters.id IN ('".implode("','",$items)."') AND customer_login.admin='1'");
}

if($info){
	$itemArr[]="f_name=date,company,msg";
	$itemArr[]="f_x=625,125,125";
	$itemArr[]="f_y=90,170,300";
	$itemArr[]="f_w=100,150,650";
	$itemArr[]="f_h=50,75,650";

	ini_set('memory_limit', '32M');
	
	// debug
	$debug=false;
	
	// get rid of gzip
	ini_set('zlib.output_compression', '0');
	
	// get ready
	define('FPDF_FONTPATH','font/');
    require_once('fpdi1.6.2/vendor/autoload.php');
    $pdf = new FPDI("P","in","Letter");
	//require("fpdf/fpdi.php");

	// setup the object

	//$pdf= new fpdi("P","in","Letter");
    //print_r('ai');
    //print_r($info); die();
	$pdf->SetAutoPageBreak(false);
	$pdf->SetDisplayMode("real");
	$pdf->SetMargins(0,0,0);
	$pdf->SetFont('Arial','',9);
	//if($debug){$pdf->SetDrawColor(51,255,51);}
   
	// loop through each invoice
	$pslip_pages=0;
	foreach($info as $vv){
		$page=0;
		$pslip_pages++;
	
		// start out with top 
		//if($type=="fax"){
			$pdf->setSourceFile($pdfpath."norway_marketing.pdf");
		//}else{
		//	$pdf->setSourceFile($pdfpath."norway_blank.pdf");
		//}
		$tplidx = $pdf->ImportPage(1);	
		$pdf->addPage();
		$pdf->useTemplate($tplidx);
		$pdf->SetXY(0,0);	
	
		$pdf->SetFont('Arial','B',12);
		setItem("date",date("m/d/Y"));
		$pdf->SetFont('Arial','',10);

		if($action=="dest_request"){
			setItem("company",$vv["d_to"]."\n".$vv["d_address1"]." ".$vv["d_address2"]."\n".$vv["d_city"].", ".$vv["d_state"]." ".$vv["d_zip"]);
			$vv["lead_company"]=$vv["d_to"];
			
			setItem("msg","Dear ".$vv["main_contact_name"].",\n\n".$vv["company"]." is one of our most important customers here at Norway Freight.\n\nWe have moved freight with your facility for ".$vv["company"]." recently.  We would simply like to let you\nknow that we would love to be of service to ".$vv["lead_company"]." as well! We offer nationwide\nAir and Ground freight services at discounted rates.\n\nOur ultimate goal is to save you time and money on all of your shipments moving within the United\nStates and Canada.\n\nAt Norway Freight, we believe that:\n\n  - Customer Service\n  - Faster discounted freight quotes\n  - Quick Transit times\n  - Instant insurance coverage\n  - Solutions to many issues you might be having with your current Air and Ground shipments\n\n......is the NorWAY!\n\nPlease call us to setup and account, get a rate quote, or schedule a pickup at your convenience.\n\nThanks,\n\nNorway Freight\n801-657-9826\nwww.norwayfreight.com");
		}else if($action=="org_request"){
			setItem("company",$vv["o_to"]."\n".$vv["o_address1"]." ".$vv["o_address2"]."\n".$vv["o_city"].", ".$vv["o_state"]." ".$vv["o_zip"]);
			
			$vv["lead_company"]=$vv["o_to"];
			
			setItem("msg","Dear ".$vv["main_contact_name"].",\n\n".$vv["company"]." is one of our most important customers here at Norway Freight.\n\nWe have moved freight with your facility for ".$vv["company"]." recently.  We would simply like to let you\nknow that we would love to be of service to ".$vv["lead_company"]." as well! We offer nationwide Air and\nGroundfreight services at discounted rates.\n\nOur ultimate goal is to save you time and money on all of your shipments moving within the United\nStates and Canada.\n\nAt Norway Freight, we believe that:\n\n  - Customer Service\n  - Faster discounted freight quotes\n  - Quick Transit times\n  - Instant insurance coverage\n  - Solutions to many issues you might be having with your current Air and Ground shipments\n\n......is the NorWAY!\n\nPlease call us to setup and account, get a rate quote, or schedule a pickup at your convenience.\n\nThanks,\n\nNorway Freight\n801-657-9826\nwww.norwayfreight.com");
		}else if($action=="ns"){
			setItem("company",$vv["company"]."\n".$vv["addr"]." ".$vv["addr2"]."\n".$vv["city"].", ".$vv["state"]." ".$vv["zip"]);
			//setItem("msg","Dear ".$vv["main_contact_name"].",\n\nWe simply want to thank you for shipping with Norway Freight!\n\nOur ultimate objective is to save you time and money on all of your Airfreight, LTL, Full Truckload, and Partial\nload domestic shipping needs. We will do whatever it takes to ensure that you know how much your business\nmeans to us. Our goal is to save you money on shipping costs, while also extending the arm of service\nto you personally. We will save you time by helping you manage your freight needs. We don't just do quotes and pickups.\nWe will also track your freight until it delivers, ensure that it is billed properly, help you with any issues that arise,\nand we'll give you full visibility of all of this over the web!\n\nAs you become more familiar with our services, we hope that you enjoy working with us here at\nNorway Freight.\n\nHere is your login information for norwayfreight.com\n\nUsername: ".$vv["username"]."\nPassword: ".$vv["password"]."\n\n\nThanks,\n\nNorway Freight\n801-657-9826");
            setItem("msg","Dear ".$vv["main_contact_name"].",\n\nWe simply want to thank you for shipping with Norway Freight!\n\nOur ultimate objective is to save you time and money on all of your Airfreight, LTL, Full Truckload, and Partial\nload domestic shipping needs. We will do whatever it takes to ensure that you know how much your business\nmeans to us. Our goal is to save you money on shipping costs, while also extending the arm of service\nto you personally. We will save you time by helping you manage your freight needs. We don't just do quotes and pickups.\nWe will also track your freight until it delivers, ensure that it is billed properly, help you with any issues that arise,\nand we'll give you full visibility of all of this over the web!\n\nAs you become more familiar with our services, we hope that you enjoy working with us here at\nNorway Freight.\n\nThanks,\n\nNorway Freight\n801-657-9826");
		}else if($action=="inactivity"){
			setItem("company",$vv["company"]."\n".$vv["addr"]." ".$vv["addr2"]."\n".$vv["city"].", ".$vv["state"]." ".$vv["zip"]);

			$sel_last = $db->query("SELECT MAX(shipment.date_shipment) AS last_shipped FROM shipment INNER JOIN customer ON customer.id = shipment.customer_id WHERE shipment.shipped = '1' AND customer.id='".$vv["customer_id"]."' GROUP BY customer_id LIMIT 1");
			$vv["last_shipment"] = date("m/d/Y",strtotime($sel_last[0]["last_shipped"]));
			
			//setItem("msg","Dear ".$vv["main_contact_name"].",\n\nWe Vikings have been sitting in our ship waiting for a call from you and our phone has simply not rang.\nWe noticed that you haven't shipped anything with us since ".$vv["last_shipment"].".  We are concerned and would like\nto know if there is anything that we might be able to do to continue moving your freight? If you simply\nhaven't had any shipments since the last shipment, we must just be insecure and we ask your\nforgiveness. We sit in our ships, rowing all day long waiting for shipments.  When a customer stops shipping,\nwe get worried about what might have gone wrong.  We also worry about how we are going to pay\nfor our various Viking supplies.\n\nOur Goal is to save you time and money on shipping. We can't live without you. We hunger for shipments.\nWe are ok with begging for your shipments.\n\nPlease call us to quote and schedule your next shipment.\n\nVisit us online at www.norwayfreight.com\n\nHere is your login information for norwayfreight.com\n\nUsername: ".$vv["username"]."\nPassword: ".$vv["password"]."\n\n\nThanks,\n\nNorway Freight\n801-657-9826\nwww.norwayfreight.com");
            setItem("msg","Dear ".$vv["main_contact_name"].",\n\nWe Vikings have been sitting in our ship waiting for a call from you and our phone has simply not rang.\nWe noticed that you haven't shipped anything with us since ".$vv["last_shipment"].".  We are concerned and would like\nto know if there is anything that we might be able to do to continue moving your freight? If you simply\nhaven't had any shipments since the last shipment, we must just be insecure and we ask your\nforgiveness. We sit in our ships, rowing all day long waiting for shipments.  When a customer stops shipping,\nwe get worried about what might have gone wrong.  We also worry about how we are going to pay\nfor our various Viking supplies.\n\nOur Goal is to save you time and money on shipping. We can't live without you. We hunger for shipments.\nWe are ok with begging for your shipments.\n\nPlease call us to quote and schedule your next shipment.\n\nVisit us online at www.norwayfreight.com\n\nThanks,\n\nNorway Freight\n801-657-9826\nwww.norwayfreight.com");
		}

		// add pages
		//$pages=$pdf->GetNumPages();
		//for($page=1;$page<=$pages;$page++){
		//	$pdf->SetCurPage($page);
		//	$pdf->SetXY(0,10.55);
		//	$pdf->MultiCell(8.15,.15,"Page $page of $pages",0,"R",0);
		//}
	}

	if($type=="fax"){
		//save it
		$pdf->Output($pdfpath."generated/".$vv["id"].".pdf","F");
	}else{
		// send it out
		$pdf->Output("letters.pdf","I");
		die();
	}
	
	$pdf->closeParsers();
}else{
	echo "No Letter Selected.";
	die();
}	

?>