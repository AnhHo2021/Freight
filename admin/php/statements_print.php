<?php

switch($action){
	case "print-top":
		$set_template  = "template.topframe.html";

		$sql = "SELECT customer.main_contact_name AS c_contact_name,customer.main_contact_fax AS c_contact_fax,customer.main_contact_email AS c_contact_email,customer.bill_contact_name AS b_contact_name,customer.bill_contact_fax AS b_contact_fax,customer.bill_contact_email AS b_contact_email FROM customer WHERE customer.id = '$id'";
		$customer_data = $db->query($sql);
		if($customer_data){
			$customer=$customer_data[0];
		}

		$vars["email1"]=$customer["c_contact_email"];
		$vars["email2"]=$customer["b_contact_email"];
		$vars["email3"]="";
		$vars["emailname1"]=$customer["c_contact_name"];
		$vars["emailname2"]=$customer["b_contact_name"];
		$vars["emailname3"]="";
		$vars["fax1"]=$customer["c_contact_fax"];
		$vars["fax2"]=$customer["b_contact_fax"];
		$vars["fax3"]="";
		$vars["faxname1"]=$customer["c_contact_name"];
		$vars["faxname2"]=$customer["b_contact_name"];
		$vars["faxname3"]="";

		$vars["id"]=$id;

		$html["BODY"]=replace($vars,rf($htmlpath."statements_print-top.html"));
		break;
	case "print-main":
        $send = $_POST;
		if($ids){$ids=explode(",",$ids);}else{$ids[]=$id;}

		$sql = "SELECT customer.* FROM customer WHERE customer.id IN (".implode(",",$ids).")";
		$customer_data = $db->query($sql);
		if($customer_data){
			foreach($customer_data as $v){
				$customers[$v["id"]]=$v;
			}
		}

		$sql = "SELECT c.id,CASE WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -90 THEN '90' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -60 THEN '60' WHEN TO_DAYS(i.due_date)-TO_DAYS(NOW()) < -30 THEN '30' ELSE 'current' END AS filter,SUM(i.total_due) AS total FROM shipment s INNER JOIN invoice i ON s.invoice_id=i.id INNER JOIN customer c ON s.customer_id=c.id WHERE c.id IN (".implode(",",$ids).") AND s.status='invoiced' AND i.status='open' GROUP BY c.id,filter";
		$balances_temp = $db->query($sql);
		if($balances_temp){
			foreach($balances_temp as $v){
				$balances[$v["id"]][$v["filter"]]=$v["total"];
			}
		}

		$sql = "SELECT shipment.customer_id,invoice.id AS invoice_id,invoice.due_date,invoice.invoice_date,shipment.o_to,shipment.d_to,invoice.total_due FROM invoice INNER JOIN shipment ON invoice.shipment_id=shipment.id WHERE shipment.invoice_final='1' AND shipment.customer_id IN (".implode(",",$ids).") AND shipment.status='invoiced' AND invoice.status='open' ORDER BY invoice.due_date ASC";
		$invoice_data = $db->query($sql);
		if($invoice_data){
			foreach($invoice_data as $v){
				$invoices[$v["customer_id"]][$v["invoice_id"]]=$v;
			}
		}

		//$statement="statement.pdf";
		//$statement_np="statement_np.pdf";
		//if(!$send){
			$statement="statement_blank.pdf";
			$statement_np="statement_blank_np.pdf";
		//}

		ini_set('memory_limit', '32M');

		// debug
		$debug=false;

		// get rid of gzip
		ini_set('zlib.output_compression', '0');

		// get ready
		define('FPDF_FONTPATH','font/');
		
		class_exists('TCPDF', true); // trigger Composers autoloader to load the TCPDF class

        // setup the autoload function
        require_once('fpdi1.6.2/vendor/autoload.php');
        //require_once('fpdi1.6.2/vendor/setasign/fpdi/fpdi.php');
       //require_once('fpdi1.6.2/vendor/setasign/fpdi/pdf_parser.php');
       // require_once('fpdi1.6.2/vendor/setasign/fpdi/fpdi_pdf_parser.php');
        // initiate FPDI
        $pdf = new FPDI("P","in","Letter");
		
		//require("fpdf/fpdi.php"); //anh

		// setup the object
	//	$pdf= new fpdi("P","in","Letter"); //anh
		$pdf->SetAutoPageBreak(false);
		$pdf->SetDisplayMode("real");
		$pdf->SetMargins(0,0,0);
		if($debug){$pdf->SetDrawColor(51,255,51);}

		foreach($ids as $id){
			if($customers[$id] && $balances[$id] && $invoices[$id]){
				$customer=$customers[$id];

				// update print log
				$db->query("INSERT INTO statement_printlog SET customer_id='$id',date_printed=NOW(),printed_admin_login_id='".$_LOGIN["id"]."'");

				// start out with top
				$pdf->setSourceFile($pdfpath.$statement);
				$tplidx = $pdf->ImportPage(1);
				$pdf->addPage();
				$pdf->useTemplate($tplidx);
				$pdf->SetXY(0,0);

				$pdf->SetFont('Arial','B',10);

				if($itemArr){unset($itemArr);}
				$itemArr[]="f_name=date,billto,age_90,age_60,age_30,age_current";
				$itemArr[]="f_x=600,80,600,600,600,600";
				$itemArr[]="f_y=103,200,173,190,207,225";
				$itemArr[]="f_w=140,325,140,140,140,140";
				$itemArr[]="f_h=15,100,15,15,15,15";

				// variables
				$pdf->SetFont('Arial','B',10);
				setItem("date",sysDate(date("Y-m-d")));
				setItem("billto",$customer["company"]."\n".(($customer["bill_contact_name"])?$customer["bill_contact_name"]."\n":"").$customer["bill_address1"]."\n".(($customer["bill_address2"])?$customer["bill_address2"]."\n":"").$customer["bill_city"].", ".$customer["bill_state"]." ".$customer["bill_zip"]);

				setItem("age_90","$".number_format($balances[$id]["90"],2));
				setItem("age_60","$".number_format($balances[$id]["60"],2));
				setItem("age_30","$".number_format($balances[$id]["30"],2));
				setItem("age_current","$".number_format($balances[$id]["current"],2));


				// main invoice body
				$pdf->SetFillColor(186,180,118);
				$pdf->SetXY(.5,3.25);
				$pdf->MultiCell(.75,.15,"Invoice",1,"C",1);
				$pdf->SetXY(1.25,3.25);
				$pdf->MultiCell(1,.15,"Due Date",1,"C",1);
				$pdf->SetXY(2.25,3.25);
				$pdf->MultiCell(2.5,.15,"Origin Company",1,"L",1);
				$pdf->SetXY(4.75,3.25);
				$pdf->MultiCell(2.5,.15,"Destination Company",1,"L",1);
				$pdf->SetXY(7.25,3.25);
				$pdf->MultiCell(.75,.15,"Total Due",1,"R",1);
				$pdf->SetFillColor(0,0,0);

				// loop through the invoices creating pages as needed
				$curY=3.25+.15;
				foreach($invoices[$id] as $v){
					$testY=$curY + .15;
					if($testY > 10){
						$pdf->setSourceFile($pdfpath.$statement_np);
						$tplidx = $pdf->ImportPage(1);
						$pdf->addPage();
						$pdf->useTemplate($tplidx);
						$pdf->SetXY(0,0);

						$curY=1.5+.15;

						// main statement body
						$pdf->SetFillColor(186,180,118);
						$pdf->SetXY(.5,1.5);
						$pdf->MultiCell(.75,.15,"Invoice",1,"C",1);
						$pdf->SetXY(1.25,1.5);
						$pdf->MultiCell(1,.15,"Due Date",1,"C",1);
						$pdf->SetXY(2.25,1.5);
						$pdf->MultiCell(2.5,.15,"Origin Company",1,"L",1);
						$pdf->SetXY(4.75,1.5);
						$pdf->MultiCell(2.5,.15,"Destination Company",1,"L",1);
						$pdf->SetXY(7.25,1.5);
						$pdf->MultiCell(.75,.15,"Total Due",1,"R",1);
						$pdf->SetFillColor(0,0,0);
					}

					// invoice #
					$pdf->Rect(.5,$curY,.75,.15,1);
					$pdf->SetXY(.5,$curY);
					$pdf->MultiCell(.75,.15,$v["invoice_id"],0,"C",0);

					// due date
					$pdf->Rect(1.25,$curY,1,.15,1);
					$pdf->SetXY(1.25,$curY);
					$pdf->MultiCell(1,.15,sysDate($v["due_date"]),0,"C",0);

					// origin
					if(strlen($v["o_to"]) > 30){
						$v["o_to"]=substr($v["o_to"],0,30)."...";
					}
					$pdf->Rect(2.25,$curY,2.5,.15,1);
					$pdf->SetXY(2.25,$curY);
					$pdf->MultiCell(2.5,.15,$v["o_to"],0,"L",0);

					// destination
					if(strlen($v["d_to"]) > 30){
						$v["d_to"]=substr($v["d_to"],0,30)."...";
					}
					$pdf->Rect(4.75,$curY,2.5,.15,1);
					$pdf->SetXY(4.75,$curY+$x);
					$pdf->MultiCell(2.5,.15,$v["d_to"],0,"L",0);

					// remain
					$pdf->Rect(7.25,$curY,.75,.15,1);
					$pdf->SetXY(7.25,$curY);
					$pdf->MultiCell(.75,.15,"$".number_format($v["total_due"],2),0,"R",0);

					$curY+=.15;
				}

				// add the footer
				$pdf->SetFont('Arial','',8);
				$pdf->SetXY(0,10.5);
				$pdf->MultiCell(8.5,.15,"Norway Freight     |     P.O. Box 94546 South Jordan, UT 84095     |     Phone 801-657-9826     |     Fax 801-905-6054",0,"C",0);
			}
		}

		if(count($send)<1){
			// send it out
			$pdf->Output("statement.pdf","I");
			$pdf->_closeParsers();
			die();
		}else{
			$file = $pdfpath."generated/statement.".$id.".pdf";
            //$pdf->Close();
			$pdf->Output($file,"F");
            $file_name = "statement.".$id.".pdf";
			//$pdf->closeParsers();
            //$pdf->_closeParsers();
            $sends =array();
			if(is_file($file)){
				chmod($file,0777);
                $send_email = $send["send_email"];
				if($send_email){
                    $email = $send["email"];
                    $emailname = $send["emailname"];
					$x=0;
					foreach($email as $v){
                        if(filter_var($v, FILTER_VALIDATE_EMAIL)){
                            $name=$emailname[$x];
                            $sends[]=array("name"=>$name,"email"=>$v,"subject"=>"Norway Freight Statement","body"=>"Your Statement is attached.");
                        }
						/*if(eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$",$v)){
							$name=$emailname[$x];
							$sends[]=array("name"=>$name,"email"=>$v,"subject"=>"Norway Freight Statement","body"=>"Your Statement is attached.");
						}*/
						$x++;
					}
				}
                $send_fax = $send["send_fax"];
				if($send_fax){
                    $fax = $send["fax"];
                    $faxname = $send["faxname"];
					$x=0;
					foreach($fax as $v){
						$v = preg_replace('/[^0-9]+/','',$v);
						if(preg_match("/^[0-9]{10}$/",$v)){
							$name=$faxname[$x];
							$sends[]=array("name"=>$name,"email"=>"1".$v."@efaxsend.com","subject"=>"Norway Freight Statement","body"=>"");
						}
						$x++;
					}
				}
				if(count($sends) >0){
					include("emailpdf.php");

					foreach($sends as $v){
//						$emailpdf = new emailPDF($v["name"],$v["email"],"Norway Freight","support@norwayfreight.com",$v["subject"],$v["body"],$file);
						$emailpdf = new emailPDF($v["name"],$v["email"],"Transport","sales@strongcontainers.com",$v["subject"],$v["body"],$file,$file_name);

						echo "Sent to ".$v["name"]." - ".$v["email"]."<br>";
					}
				}else{
					echo "No addresses specified";
				}
				@unlink($pdfpath."generated/statement.".$id.".pdf");
			}else{
				echo "Cannot write PDF File";
			}
			die();
		}
		break;
	case "print":
		$vars["id"]=$id;
		echo replace($vars,rf($htmlpath."statements_print.html"));
		die();
		break;
}

?>