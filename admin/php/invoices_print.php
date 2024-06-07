<?php

switch($action){
	case "print-top":
		$set_template  = "template.topframe.html";

		$sql = "SELECT customer.main_contact_name AS c_contact_name,customer.main_contact_fax AS c_contact_fax,customer.main_contact_email AS c_contact_email,customer.bill_contact_name AS b_contact_name,customer.bill_contact_fax AS b_contact_fax,customer.bill_contact_email AS b_contact_email FROM shipment INNER JOIN customer ON shipment.customer_id=customer.id WHERE shipment.invoice_id = '$id'";
		$invoice_data = $db->query($sql);
		if($invoice_data){
			$invoice=$invoice_data[0];
		}

		$vars["email1"]=$invoice["c_contact_email"];
		$vars["email2"]=$invoice["b_contact_email"];
		$vars["email3"]="";
		$vars["emailname1"]=$invoice["c_contact_name"];
		$vars["emailname2"]=$invoice["b_contact_name"];
		$vars["emailname3"]="";
		$vars["fax1"]=$invoice["c_contact_fax"];
		$vars["fax2"]=$invoice["b_contact_fax"];
		$vars["fax3"]="";
		$vars["faxname1"]=$invoice["c_contact_name"];
		$vars["faxname2"]=$invoice["b_contact_name"];
		$vars["faxname3"]="";

		$vars["id"]=$id;

		$html["BODY"]=replace($vars,rf($htmlpath."invoices_print-top.html"));
		break;
	case "print-main":
        $send = $_POST;

		if($ids){$ids=explode(",",$ids);}else{$ids[]=$id;}

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
        
        // initiate FPDI
        $pdf = new FPDI("P","in","Letter");
		
		//require("fpdf/fpdi.php");   anh

		// setup the object
		//$pdf= new fpdi("P","in","Letter"); anh
		
		$pdf->SetAutoPageBreak(false);
		$pdf->SetDisplayMode("real");
		$pdf->SetMargins(0,0,0);
		if($debug){$pdf->SetDrawColor(51,255,51);}

		foreach($ids as $id){
			$sql = "SELECT carrier.company AS carrier,shipment.*,customer.*,al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname,invoice.* FROM invoice
			INNER JOIN shipment ON invoice.shipment_id=shipment.id
			INNER JOIN carrier ON shipment.shipped_carrier_id=carrier.id
			LEFT JOIN customer ON shipment.customer_id=customer.id
			LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id
			LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id WHERE invoice.id = '$id'";
			$invoice_data = $db->query($sql);
			if($invoice_data){
				$invoice=$invoice_data[0];

				// update print log
				$db->query("INSERT INTO invoice_printlog SET invoice_id='$id',date_printed=NOW(),printed_admin_login_id='".$_LOGIN["id"]."'");

				// start out with top
                shell_exec( "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=".$pdfpath."ST_Invoice.pdf"." ".$pdfpath."invoice2.pdf"."");

				$pdf->setSourceFile($pdfpath."ST_Invoice.pdf");
				$tplidx = $pdf->ImportPage(1);
				$pdf->addPage();
				$pdf->useTemplate($tplidx);
				$pdf->SetXY(0,0);

				$pdf->SetFont('Arial','B',10);

				if($itemArr){unset($itemArr);}
				//$itemArr[]="f_name=date,invoice_num,due_mssg,billto,tracking_num,service,pickup,insurance,shipper,consignee,total,invoice_num2,due_date2,total2,due_date3,total3";
				//$itemArr[]="f_x=535,656,535,80,213,213,213,213,480,480,660,659,537,659,537,659";
				//$itemArr[]="f_y=103,103,123,200,358,375,392,409,200,340,712,847,870,870,890,890";
				//$itemArr[]="f_w=120,116,236,325,190,190,190,190,285,285,110,110,120,110,120,110";
				//$itemArr[]="f_h=15,15,15,100,15,15,15,15,110,110,20,20,20,20,20,20";

				//$itemArr[]="f_name=b_to,b_address,b_city,b_state,b_zip,o_to,o_address,o_city,o_state,o_zip,o_po,date1,invoice_num1,due_date1,total1,shipped_tracknum,service,pickup,insurance,d_to,d_address,d_city,d_state,d_zip,d_po,total2,invoice_num2,total3,total4,due_date2,date2";
				//$itemArr[]="f_x=63.199,64.199,64.699,211.7,262.2,358.7,358.7,359.2,505.7,555.2,358.7,646.1,646.1,646.1,713.1,46.199,47.199,46.199,110.199,360.2,359.2,361.2,505.199,554.2,362.2,533.2,635.1,635.1,636.1,470.2,470.2";
				//$itemArr[]="f_y=192.349,228.415,268.486,268.989,268.989,194.353,230.918,267.985,268.486,268.486,306.557,193.351,230.418,267.485,300.546,343.624,382.695,419.763,450.819,343.624,380.692,417.759,419.763,419.763,453.825,682.24,898.533,930.591,961.648,963.652,930.652";
				//$itemArr[]="f_w=265,264,140.5,41,66,263.5,264.5,140,43.5,69,264,162,161,161,94,279,277,278,216,261.899,263.9,135,44,67.9,258.899,88.9,148,147,146,125.9,125.9";
				//$itemArr[]="f_h=20.036,20.036,18.533,18.032,18.533,18.032,19.034,19.034,18.533,19.535,18.032,19.034,19.034,19.034,19.034,18.032,17.03,18.032,17.03,19.034,19.034,19.034,17.03,17.03,19.034,22.04,23.041,22.04,20.036,19.034,19.034";

				$itemArr[]="f_name=b_to,b_address,b_city,b_state,b_zip,date1,invoice_num1,due_date1,total1,d_to,d_address,d_city,d_state,d_zip,d_po,total2,invoice_num2,total3,total4,due_date2,date2,o_to,o_address,o_city,o_state,o_zip,o_po,shipped_tracknum,service,pickup,insurance";
				$itemArr[]="f_x=63.199,64.199,64.699,211.7,262.2,646.1,646.1,646.1,713.1,360.2,359.2,361.2,505.199,554.2,362.2,533.2,635.1,635.1,636.1,470.2,470.2,65.199,65.199,66.199,213.199,264.2,66.199,358.2,358.2,358.2,417.2";
				$itemArr[]="f_y=192.349,228.415,268.485,268.989,268.989,193.351,230.418,267.484,300.546,343.624,380.692,417.759,419.763,419.763,453.825,682.24,898.533,930.591,961.648,963.651,930.651,341.621,378.688,417.759,418.761,416.757,454.826,191.347,229.417,265.482,298.542";
				$itemArr[]="f_w=265,264,140.5,41,66,162,161,161,94,261.899,263.9,135,44,67.9,258.899,88.9,148,147,146,125.9,125.9,264,265,140,43,66,263,264.9,264.9,266.9,203.9";
				$itemArr[]="f_h=20.036,20.036,18.533,18.032,18.533,19.034,19.034,19.034,19.034,19.034,19.034,19.034,17.03,17.03,19.034,22.04,23.041,22.04,20.036,19.034,19.034,20.036,22.04,19.034,18.032,20.036,19.034,20.036,20.036,22.04,19.034";



				// variables
				$pdf->SetFont('Arial','B',10);
				setItem("date1",sysDate($invoice["invoice_date"]));
				setItem("date2",sysDate($invoice["due_date"]));
				setItem("invoice_num1",$invoice["invoice_id"]);
				setItem("due_date1",sysDate($invoice["due_date"]));
				setItem("due_date2",sysDate($invoice["due_date"]));

				setItem("b_to",$invoice["company"],"L");
				setItem("b_address",$invoice["bill_address1"].' '.$invoice["bill_address2"],"L");
				setItem("b_city",$invoice["bill_city"],"L");
				setItem("b_state",$invoice["bill_state"],"L");
				setItem("b_zip",$invoice["bill_zip"],"L");

				if($invoice["shipped_pickup_date"] != "0000-00-00 00:00:00"){
					setItem("pickup",date("m/d/Y",strtotime($invoice["shipped_pickup_date"])),"L");
				}else{
					setItem("pickup","N/A","L");
				}
				setItem("shipped_tracknum",$invoice["shipped_tracknum"],"L");
				setItem("insurance",number_format($invoice["insurance"],2),"L");

				$pdf->SetFont('Arial','B',13);
				setItem("total1",number_format($invoice["total"],2),'L');
				setItem("total2",number_format($invoice["total"],2),'L');
				setItem("total3",number_format($invoice["total"],2),'L');
				setItem("total4",number_format($invoice["total"]/.97,2),'L');
				setItem("invoice_num2",$invoice["invoice_id"],'L');
				$pdf->SetFont('Arial','B',10);


				setItem("o_to",$invoice["o_to"],"L");
				setItem("o_address",$invoice["o_address1"]." ".$invoice["o_address2"],"L");
				setItem("o_city",$invoice["o_city"],"L");
				setItem("o_state",$invoice["o_state"],"L");
				setItem("o_zip",$invoice["o_zip"],"L");
				setItem("o_po",$invoice["o_po"],"L");

				setItem("d_to",$invoice["d_to"],"L");
				setItem("d_address",$invoice["d_address1"]." ".$invoice["d_address2"],"L");
				setItem("d_city",$invoice["d_city"],"L");
				setItem("d_state",$invoice["d_state"],"L");
				setItem("d_zip",$invoice["d_zip"],"L");
				setItem("d_po",$invoice["d_po"],"L");

               

				/*$pdf->Code39(1.35,10,$invoice["invoice_id"],false,false,0.01,.3,true);*/ //anh

				// services
				$group="air";
				$shipment_rate = $db->query("SELECT service.group,service.name AS service,shipment_rate.* FROM shipment_rate INNER JOIN service ON shipment_rate.service_id=service.id WHERE shipment_rate.id='".$invoice["shipped_shipment_rate_id"]."'");
				if($shipment_rate){
					setItem("service",$shipment_rate[0]["service"],"L");

					$group=$shipment_rate[0]["group"];
				}

				// commodities
				$sql = "SELECT shipment_commodity.*,class_list.class, packaging_list.packaging AS pack FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id LEFT JOIN packaging_list ON shipment_commodity.packaging_list_id=packaging_list.id WHERE shipment_id = '".$invoice["shipment_id"]."' ORDER BY id ASC";
				$commodities = $db->query($sql);
				if($commodities){
					for($x=1,$i=0;$x<=5;$x++,$i++){
						$setY=472 + ($x * 25);

						unset($itemArr);
						//$itemArr[]="f_name=pcs,classdims,weight";
						//$itemArr[]="f_x=92,237,145";
						//$itemArr[]="f_y=$setY,$setY,$setY";
						//$itemArr[]="f_w=40,225,90";
						//$itemArr[]="f_h=15,15,15";

						$itemArr[]="f_name=pieces,weight,classdims,description,charges";
						$itemArr[]="f_x=40.699,114.199,177.2,333.2,519.2";
						$itemArr[]="f_y=$setY,$setY,$setY,$setY,$setY";
						$itemArr[]="f_w=71,60,153,184,104.9";
						$itemArr[]="f_h=25,25,25,25,25";

						setItem("pieces",$commodities[$i]["pieces"]);

						if($group == "air" || !$group || $group == "truck"){
							if($commodities[$i]["dim_d"]  || $commodities[$i]["dim_w"] || $commodities[$i]["dim_h"]){
								setItem("classdims",$commodities[$i]["dim_d"]." x ".$commodities[$i]["dim_w"] ." x ". $commodities[$i]["dim_h"]);
							}
						}else{
							if($commodities[$i]["class"]){
								setItem("classdims",$commodities[$i]["class"]." / ".$commodities[$i]["nmfc"]);
							}
						}
						setItem("weight",$commodities[$i]["weight"]);
					}
				}

				// rates
				//$sql = "SELECT rate FROM shipment_rate WHERE shipment_rate.id='".$invoice["shipped_shipment_rate_id"]."'";

                $sql = "SELECT rate,shipment.container_price,shipment.container_cost
				FROM shipment_rate
				LEFT JOIN shipment on shipment.id = shipment_rate.shipment_id
				WHERE
				shipment_rate.id='".$invoice["shipped_shipment_rate_id"]."'";

                $base_rates = $db->query($sql);
                $container_price = 0;
				if($base_rates){
					unset($itemArr);
                    $container_price = $base_rates[0]["container_price"];
					//$itemArr[]="f_name=description,charge";
					//$itemArr[]="f_x=465,659";
					//$itemArr[]="f_y=479,479";
					//$itemArr[]="f_w=190,110";
					//$itemArr[]="f_h=15,15";

					$itemArr[]="f_name=description,charges";
					$itemArr[]="f_x=333.2,519.2";
					$itemArr[]="f_y=495.901,495.901";
					$itemArr[]="f_w=184,104.9";
					$itemArr[]="f_h=25,25";

					setItem("description","Freight Charges",'L');
					setItem("charges","$".number_format($base_rates[0]["rate"],2),'R');
				}

                $j =495.901 +25;
				// accessorials
				$sql = "SELECT accessorial.name,accessorial.type, shipment_rate_accessorial.rate FROM shipment_rate_accessorial INNER JOIN accessorial ON shipment_rate_accessorial.accessorial_id=accessorial.id WHERE shipment_rate_accessorial.shipment_rate_id='".$invoice["shipped_shipment_rate_id"]."' ORDER BY accessorial.name";
				$accessorial = $db->query($sql);
				if($accessorial){
					$x=2;
					foreach($accessorial as $v){
						$setY=472 + ($x * 25);
						switch($v["type"]){
							case "insurance":
								$insurance_line=$v["rate"];
								break;
							case "fuel":
								$fuel_line=$v["rate"];
								break;
							default:
								unset($itemArr);
								//$itemArr[]="f_name=description,charge";
								//$itemArr[]="f_x=465,659";
								//$itemArr[]="f_y=$setY,$setY";
								//$itemArr[]="f_w=190,110";
								//$itemArr[]="f_h=15,15";

								$itemArr[]="f_name=description,charges";
								$itemArr[]="f_x=333.2,519.2";
								$itemArr[]="f_y=$setY,$setY";
								$itemArr[]="f_w=184,104.9";
								$itemArr[]="f_h=25,25";

								setItem("description",$v["name"],'L');
								setItem("charges","$".number_format($v["rate"],2),'R');
								$x++;
						}
					}
					// insurance
					$setY=472 + ($x * 25);
					unset($itemArr);
					//$itemArr[]="f_name=description,charge";
					//$itemArr[]="f_x=465,659";
					//$itemArr[]="f_y=$setY,$setY";
					//$itemArr[]="f_w=190,110";
					//$itemArr[]="f_h=15,15";

					$itemArr[]="f_name=description,charges";
					$itemArr[]="f_x=333.2,519.2";
					$itemArr[]="f_y=$setY,$setY";
					$itemArr[]="f_w=184,104.9";
					$itemArr[]="f_h=25,25";
					$x++;
					setItem("description","Insurance",'L');
					setItem("charges","$".number_format($insurance_line,2),'R');

					// fuel
					$setY=472 + ($x * 25);
					unset($itemArr);
					//$itemArr[]="f_name=description,charge";
					//$itemArr[]="f_x=465,659";
					//$itemArr[]="f_y=$setY,$setY";
					//$itemArr[]="f_w=190,110";
					//$itemArr[]="f_h=15,15";

					$itemArr[]="f_name=description,charges";
					$itemArr[]="f_x=333.2,519.2";
					$itemArr[]="f_y=$setY,$setY";
					$itemArr[]="f_w=184,104.9";
					$itemArr[]="f_h=25,25";
					$x++;
                    $j=$x;
					setItem("description","Fuel Surcharge",'L');
					setItem("charges","$".number_format($fuel_line,2),'R');
				}

                // container price
                if(is_numeric($container_price)){
                    $setY=472 + ($j * 25);
                    unset($itemArr);

                    $itemArr[]="f_name=description1,charges1";
                    $itemArr[]="f_x=333.2,519.2";
                    $itemArr[]="f_y=$setY,$setY";
                    $itemArr[]="f_w=184,104.9";
                    $itemArr[]="f_h=25,25";
                    setItem("description1","Container price",'L');
                    setItem("charges1","$".number_format($container_price,2),'R');
                }

			}
		}

		if(count($send)<1){
			// send it out
			$pdf->Output("ST_Invoice.pdf","I");
			$pdf->closeParsers();
			die();
		}else{
			$file = $pdfpath."generated/invoice.".$id.".pdf";

			$pdf->Output($file,"F");
            $file_name = "invoice.".$id.".pdf";
			//$pdf->closeParsers();
           // $pdf->_closeParsers();

            //deleting the pdf object
           // $pdf->my_destructor();

            $sends =array();
			if(is_file($file)){
				chmod($file,0777);

                $send_email = $send["send_email"];
				if($send_email){
					$x=0;
                    $email = $send["email"];
                    $emailname = $send["emailname"];
					foreach($email as $v){
                        if(filter_var($v, FILTER_VALIDATE_EMAIL)) {
                            $name=$emailname[$x];
                            $sends[]=array("name"=>$name,"email"=>$v,"subject"=>"Transport Container Invoice","body"=>"Your Invoice is attached.");
                        }
                        /*
                         if(eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$",$v)){
                                $name=$emailname[$x];
                                $sends[]=array("name"=>$name,"email"=>$v,"subject"=>"Norway Freight Invoice","body"=>"Your Invoice is attached.");
                            }
                         */
                        $x++;
					}
				}

                $send_fax = $send["send_fax"];
				if($send_fax){
					$x=0;
                    $fax = $send["fax"];
                    $faxname = $send["faxname"];
					foreach($fax as $v){
                        $v = preg_replace('/[^0-9]+/','',$v);
                        if(preg_match("/^[0-9]{10}$/",$v)){
                            $name=$faxname[$x];
                            $sends[]=array("name"=>$name,"email"=>"1".$v."@efaxsend.com","subject"=>"Transport Container Invoice","body"=>"");
                        }
						$x++;
					}
				}
               // echo "<pre>sends ="; print_r($sends); echo "</pre>";
				if(count($sends) >0 ){
					include("emailpdf.php");

					foreach($sends as $v){
                        // echo "<pre>send ="; print_r($v); echo "</pre>";
//						$emailpdf = new emailPDF($v["name"],$v["email"],"Norway Freight","support@norwayfreight.com",$v["subject"],$v["body"],$file);
						$emailpdf = new emailPDF($v["name"],$v["email"],"Transport","sales@strongcontainers.com",$v["subject"],$v["body"],$file,$file_name);

						echo "Sent to ".$v["name"]." - ".$v["email"]."<br>";
					}

				}else{
					echo "No addresses specified";
				}
				@unlink($pdfpath."generated/invoice.".$id.".pdf");
			}else{
				echo "Cannot write PDF File";
			}
			die();
		}
		break;
	case "print":
		$vars["id"]=$id;
		echo replace($vars,rf($htmlpath."invoices_print.html"));
		die();
		break;
}

?>