<?php

$info=$db->query("SELECT marketing_letters.* FROM marketing_letters WHERE id IN ('".implode("','",$items)."')");

if($info){
	$itemArr[]="f_name=date,company";
	$itemArr[]="f_x=625,130";
	$itemArr[]="f_y=90,290";
	$itemArr[]="f_w=100,150";
	$itemArr[]="f_h=50,75";
	
	ini_set('memory_limit', '32M');
	
	// debug
	$debug=false;
	
	// get rid of gzip
	ini_set('zlib.output_compression', '0');
	
	// get ready
	define('FPDF_FONTPATH','font/');
	require("fpdf/fpdi.php");
	
	// setup the object
	$pdf= new fpdi("P","in","Letter");
	$pdf->SetAutoPageBreak(false);
	$pdf->SetDisplayMode("real");
	$pdf->SetMargins(0,0,0);
	$pdf->SetFont('Arial','',9);
	if($debug){$pdf->SetDrawColor(51,255,51);}
		
	// loop through each invoice
	$pslip_pages=0;
	foreach($info as $vv){
		$page=0;
		$pslip_pages++;
	
		// start out with top 
		$pdf->setSourceFile($pdfpath."new_shipper.pdf");
		$tplidx = $pdf->ImportPage(1);	
		$pdf->addPage();
		$pdf->useTemplate($tplidx);
		$pdf->SetXY(0,0);	
	
		$pdf->SetFont('Arial','B',12);
		setItem("date",date("m/d/Y"));
		$pdf->SetFont('Arial','',10);
		setItem("company",$vv["company"]."\n".$vv["addr"]." ".$vv["addr2"]."\n".$vv["city"].", ".$vv["state"]." ".$vv["zip"]);
		
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