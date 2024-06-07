<?php
switch($action){
	case "bol-top":
		$set_template  = "template.topframe.html";

		$sql = "SELECT carrier.company AS carrier_name,
		carrier.fax AS carrier_fax,
		carrier.email AS carrier_email,
		shipment.o_to,shipment.o_contact_name,
		shipment.o_contact_fax,shipment.o_contact_email,
		shipment.d_to,shipment.d_contact_name,
		shipment.d_contact_fax,shipment.d_contact_email,
		customer.company AS c_to,
		customer.main_contact_name AS c_contact_name,
		customer.main_contact_fax AS c_contact_fax,
		customer.main_contact_email AS c_contact_email
		FROM shipment
		LEFT JOIN customer ON shipment.customer_id=customer.id
		INNER JOIN carrier ON shipment.shipped_carrier_id=carrier.id
		WHERE shipment.id = '$id'";
		$shipment_data = $db->query($sql);
		if($shipment_data){
			$shipment=$shipment_data[0];
		}

		if($contact_type=="" || $contact_type=="customer"){
			$vars["email1"]=$shipment["c_contact_email"];
			$vars["email2"]=$shipment["o_contact_email"];
			$vars["email3"]=$shipment["d_contact_email"];
			$vars["emailname1"]=$shipment["c_contact_name"];
			$vars["emailname2"]=$shipment["o_contact_name"];
			$vars["emailname3"]=$shipment["d_contact_name"];
			$vars["fax1"]=$shipment["c_contact_fax"];
			$vars["fax2"]=$shipment["o_contact_fax"];
			$vars["fax3"]=$shipment["d_contact_fax"];
			$vars["faxname1"]=$shipment["c_contact_name"];
			$vars["faxname2"]=$shipment["o_contact_name"];
			$vars["faxname3"]=$shipment["d_contact_name"];
		}else if($contact_type=="carrier"){
			$vars["email1"]=$shipment["carrier_email"];
			$vars["email2"]="";
			$vars["email3"]="";
			$vars["emailname1"]=$shipment["carrier_name"];
			$vars["emailname2"]="";
			$vars["emailname3"]="";
			$vars["fax1"]=$shipment["carrier_fax"];
			$vars["fax2"]="";
			$vars["fax3"]="";
			$vars["faxname1"]=$shipment["carrier_name"];
			$vars["faxname2"]="";
			$vars["faxname3"]="";
		}
		$vars["id"]=$id;

		$vars["email_subject"]=$email_subject ? $email_subject : "Transport Container BOL #".$shipment["shipped_tracknum"]." to ".$shipment["d_to"];

		$vars["contact_types"].='<option value="customer"'.(($contact_type=="customer")?" selected":"").'>Customer / BOL Contacts</option>';
		$vars["contact_types"].='<option value="carrier"'.(($contact_type=="carrier")?" selected":"").'>Carrier Contacts</option>';

		$html["BODY"]=replace($vars,rf($htmlpath."shipments_bol-top.html"));
		break;
	case "bol-main":
        $send = $_POST;
		$sql = "SELECT carrier.company AS carrier,shipment.*,customer.company AS customer,al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname FROM shipment INNER JOIN carrier ON shipment.shipped_carrier_id=carrier.id LEFT JOIN customer ON shipment.customer_id=customer.id LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id WHERE shipment.id = '$id'";
		$shipment_data = $db->query($sql);
		if($shipment_data){
			$shipment=$shipment_data[0];
		}else{die("No shipment");}
		if($shipment["shipped_pickup_date"] == "0000-00-00"){
			$shipment["shipped_pickup_date"]=$shipment["req_pickup_date"];
		}

		ini_set('memory_limit', '32M');

		// debug
		$debug=false;

		// get rid of gzip
		ini_set('zlib.output_compression', '0');

		// get ready
		define('FPDF_FONTPATH','font/');
		//require("fpdf/fpdi.php");
		// setup the object
		//$pdf= new fpdi("P","in","Letter");
        require_once('fpdi1.6.2/vendor/autoload.php');
        $pdf = new FPDI("P","in","Letter");
		$pdf->SetAutoPageBreak(false);
		$pdf->SetDisplayMode("real");
		$pdf->SetMargins(0,0,0);
		if($debug){$pdf->SetDrawColor(51,255,51);}


		// rate confirmation
		if($do_rateconfirm){
			$pdf->setSourceFile($pdfpath."truck_rateconfirm.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
			$pdf->SetXY(0,0);
			$pdf->SetFont('Arial','B',14);

			$itemArr[]="f_name=o_city,o_state,d_city,d_state,pallets,weight,rate";
			$itemArr[]="f_x=196.2,196.2,463.2,464.2,264.7,270.2,246.7";
			$itemArr[]="f_y=662.204,691.256,661.202,690.255,742.349,768.897,794.444";
			$itemArr[]="f_w=228.999,228.999,200.9,200.9,74,104,103";
			$itemArr[]="f_h=28.051,28.051,28.051,29.052,19.034,19.535,18.533";

			setItem("o_city",$shipment["o_city"],"C");
			setItem("o_state",$shipment["o_state"]." ".$shipment["o_zip"],"C");
			setItem("d_city",$shipment["d_city"],"C");
			setItem("d_state",$shipment["d_state"]." ".$shipment["d_zip"],"C");

			$pallets=0;
			$weight=0;
			$sql = "SELECT shipment_commodity.*,class_list.class, packaging_list.packaging AS pack FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id LEFT JOIN packaging_list ON shipment_commodity.packaging_list_id=packaging_list.id WHERE shipment_id = '$id' ORDER BY id ASC";
			$commodities = $db->query($sql);
			if($commodities){
				foreach($commodities as $cv){
					$pallets+=$cv["pieces"];
					$weight+=$cv["weight"];
				}
			}
			setItem("pallets",number_format($pallets,0),"L");
			setItem("weight",number_format($weight,0).' lbs',"L");


			$rate=0;
			$shipment_rate = $db->query("SELECT shipment_rate.id,shipment_rate.cost FROM shipment INNER JOIN shipment_rate ON shipment.shipped_shipment_rate_id=shipment_rate.id WHERE shipment.id='$id'");
			$rate=($shipment_rate)?$shipment_rate[0]["cost"]:0;

			if($shipment_rate){
				$accessorials = $db->query("SELECT SUM(cost) AS cost FROM shipment_rate_accessorial WHERE shipment_rate_id='".$shipment_rate[0]["id"]."'");
				if($accessorials){
					$rate += $accessorials[0]["cost"];
				}
			}

			setItem("rate",'$'.number_format($rate,2),"L");
		}

		// w9
		if($do_w9){
			$pdf->setSourceFile($pdfpath."truck_w9.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
		}


		// BOL
        shell_exec( "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=".$pdfpath."ST_BillofLading.pdf"." ".$pdfpath."bol2.pdf"."");//anh modify
			// start out with top
			$pdf->setSourceFile($pdfpath."ST_BillofLading.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
			$pdf->SetXY(0,0);

			$pdf->SetFont('Arial','B',10);

			if($itemArr){unset($itemArr);}
			//$itemArr[]="f_name=carrier,pickup,tracking_num,insurance,shipper,consignee,o_contact,o_phone,o_ref,pickup_window,d_contact,d_phone,d_ref,delivery_window,billto1,billto2,billto3,accessorials";
			//$itemArr[]="f_x=80,198,198,198,80,418,198,198,198,198,570,570,570,570,80,80,80,418";
			//$itemArr[]="f_y=100,171,186,236,305,305,399,415,432,450,399,415,432,450,489,504,519,490";
			//$itemArr[]="f_w=335,215,215,215,335,350,215,215,215,215,200,200,200,200,335,335,335,350";
			//$itemArr[]="f_h=40,15,30,15,92,92,15,15,15,15,15,15,15,15,15,15,15,75";

			$itemArr[]="f_name=d_to,d_address,d_city,d_state,d_zip,d_po,o_to,o_address,o_city,o_state,o_zip,o_po,o_contact,o_phone,d_contact,d_phone,pickup_start,pickup_end,delivery_start,delivery_end,b_company,b_address,b_city,b_state,b_zip,special_instructions,ship_day,ship_month,ship_year,tracking_num,mawb,service_1,service_2,service_3,service_4,service_5,insurance,carrier";
			$itemArr[]="f_x=357.2,358.2,359.2,503.2,557.2,359.2,63.199,63.199,64.199,209.2,261.2,63.199,64.199,212.2,359.2,508.199,63.199,199.2,358.2,494.199,358.2,358.2,359.2,503.2,555.2,49.199,688.1,723.1,762.1,651.1,651.1,778.1,779.1,777.1,778.1,778.1,665.1,645.1";
			$itemArr[]="f_y=193.351,231.42,267.485,268.487,269.489,343.624,194.353,231.42,269.489,269.489,268.487,343.624,305.555,305.555,306.557,306.557,380.692,380.692,381.693,381.693,417.759,454.826,493.897,493.897,493.897,420.765,115.209,115.209,115.209,201.366,249.453,361.657,424.772,488.888,554.007,617.122,781.42,152.276";
			$itemArr[]="f_w=265.9,264.9,137.999,47,65.9,262.9,265,266,141,46,68,266,141,119,141,115.9,128,130,127.999,127.9,265.9,264.9,141,46,67.9,274,28,29,39,155,155,16,14,17,16,15,96,166";
			$itemArr[]="f_h=20.036,20.036,20.036,20.036,19.034,19.034,19.034,19.034,19.034,19.034,20.036,19.034,19.034,19.034,18.032,19.034,18.032,18.032,18.032,18.032,20.036,20.036,18.032,19.034,19.034,84.153,17,17,17,27.049,24.043,14.025,16.029,15.027,14.025,15.027,17.03,22.04";


			// variables
			$pdf->SetFont('Arial','B',10);
			setItem("carrier",$shipment["carrier"]);
			setItem("mawb",$shipment["shipped_carrier_pronum"]);
			setItem("ship_day",date("m",strtotime($shipment["req_pickup_date"])));
			setItem("ship_month",date("d",strtotime($shipment["req_pickup_date"])));
			setItem("ship_year",date("Y",strtotime($shipment["req_pickup_date"])));
			$pdf->SetFont('Arial','B',16);
			setItem("tracking_num",$shipment["shipped_tracknum"]);
			$pdf->SetFont('Arial','B',10);

			$pdf->SetFont('Arial','B',16);
			switch($shipment["service_id"]){
				case "1":
				case "2":setItem("service_1","X");break;
				case "3":
				case "4":setItem("service_2","X");break;
				case "5":
				case "6":setItem("service_3","X");break;
				case "7":setItem("service_4","X");break;
				case "8":setItem("service_5","X");break;
			}
			$pdf->SetFont('Arial','B',10);
			setItem("insurance",number_format($shipment["insurance"],2),"L");

			setItem("o_to",$shipment["o_to"],"L");
			setItem("o_address",$shipment["o_address1"]." ".(($shipment["o_address1"])?$shipment["o_address2"]."\n":""),"L");
			setItem("o_city",$shipment["o_city"],"L");
			setItem("o_state",$shipment["o_state"],"L");
			setItem("o_zip",$shipment["o_zip"],"L");
			setItem("o_contact",$shipment["o_contact_name"],"L");
			setItem("o_phone",$shipment["o_contact_phone"],"L");
			setItem("o_po",$shipment["o_po"],"L");

			setItem("pickup_start",date("g:i a",strtotime($shipment["req_pickup_time_start"])),"L");
			setItem("pickup_end",date("g:i a",strtotime($shipment["req_pickup_time_end"])),"L");

			setItem("d_to",$shipment["d_to"],"L");
			setItem("d_address",$shipment["d_address1"]." ".(($shipment["d_address1"])?$shipment["d_address2"]."\n":""),"L");
			setItem("d_city",$shipment["d_city"],"L");
			setItem("d_state",$shipment["d_state"],"L");
			setItem("d_zip",$shipment["d_zip"],"L");
			setItem("d_contact",$shipment["d_contact_name"],"L");
			setItem("d_phone",$shipment["d_contact_phone"],"L");
			setItem("d_po",$shipment["d_po"],"L");

			setItem("delivery_start",date("g:i a",strtotime($shipment["req_delivery_time_start"])),"L");
			setItem("delivery_end",date("g:i a",strtotime($shipment["req_delivery_time_end"])),"L");

			$billto_temp=getBillingId($id);
			$billto=$billto_temp[0];
			setItem("b_company",$billto["company"],"L");
			setItem("b_address",$billto["address"],"L");
			setItem("b_city",$billto["city"],"L");
			setItem("b_state",$billto["state"],"L");
			setItem("b_zip",$billto["zip"],"L");

			$sql = "SELECT accessorial.name FROM shipment_accessorial INNER JOIN accessorial ON shipment_accessorial.accessorial_id=accessorial.id WHERE shipment_accessorial.shipment_id = '$id' AND accessorial.type IN ('pickup','delivery','other')";
			$accessorial_data = $db->query($sql);
			if($accessorial_data){
				$accessorials1=array();
				$accessorials2=array();
				foreach($accessorial_data as $v){
					if($y++%2){
						$accessorials2[]=$v["name"];
					}else{
						$accessorials1[]=$v["name"];
					}
				}
				$special_instructions = implode(", ",$accessorials1)."\n".implode(", ",$accessorials2);
			}
			if($shipment["special_instructions"]){
				$special_instructions=wordwrap($shipment["special_instructions"],40,"\n",0)."\n\n".$special_instructions;
			}
			setItem("special_instructions",$special_instructions);

			// services
			$group="air";
			$shipment_rate = $db->query("SELECT service.group,service.name AS service,shipment_rate.* FROM shipment_rate INNER JOIN service ON shipment_rate.service_id=service.id WHERE shipment_rate.id='".$shipment["shipped_shipment_rate_id"]."'");
			if($shipment_rate){
				$group=$shipment_rate[0]["group"];
			}

			// commodities
			$sql = "SELECT shipment_commodity.*,class_list.class, packaging_list.packaging AS pack FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id LEFT JOIN packaging_list ON shipment_commodity.packaging_list_id=packaging_list.id WHERE shipment_id = '$id' ORDER BY id ASC";
			$commodities = $db->query($sql);
			if($commodities){
				for($x=1,$i=0;$x<=10;$x++,$i++){
					$setY=507 + ($x * 26);

					unset($itemArr);
					//$itemArr[]="f_name=pcs,pack,classdims,description,weight";
					//$itemArr[]="f_x=82,140,198,417,652";
					//$itemArr[]="f_y=$setY,$setY,$setY,$setY,$setY";
					//$itemArr[]="f_w=40,55,214,232,117";
					//$itemArr[]="f_h=15,15,15,15,15";

					$itemArr[]="f_name=pieces,classdims,description,weight";
					$itemArr[]="f_x=39.199,112.199,357.2,550.2";
					$itemArr[]="f_y=$setY,$setY,$setY,$setY";
					$itemArr[]="f_w=72,244,192,74.9";
					$itemArr[]="f_h=25.045,24.043,25.045,25.045";


					setItem("pieces",$commodities[$i]["pieces"]);
					//setItem("pack",$commodities[$i]["pack"]);

					if($group == "air" || !$group || $group == "truck"){
						if($commodities[$i]["dim_d"]  || $commodities[$i]["dim_w"] || $commodities[$i]["dim_h"]){
							setItem("classdims",$commodities[$i]["dim_d"]." x ".$commodities[$i]["dim_w"] ." x ". $commodities[$i]["dim_h"]);
						}
					}else{
						if($commodities[$i]["class"]){
							setItem("classdims",$commodities[$i]["class"]." / ".$commodities[$i]["nmfc"]);
						}
					}
					setItem("description",$commodities[$i]["description"]);
					setItem("weight",$commodities[$i]["weight"]);
				}
			}



		// truckload packet end
		if($do_mc){
			$pdf->setSourceFile($pdfpath."truck_mc.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
		}

		// bond
		if($do_bond){
			$pdf->setSourceFile($pdfpath."truck_bond1.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);

			$pdf->setSourceFile($pdfpath."truck_bond2.pdf");
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
		}


		if($set_outgoing_subject){
			$email_subject = $set_outgoing_subject;
			$fax_subject = $set_outgoing_subject;
		}

		if(count($send)<1){
			// send it out
			$pdf->Output("ST_BillofLading.pdf","I");
			//$pdf->closeParsers();
			die();
		}else{
			$file = $pdfpath."generated/bol.".$id.".pdf";
            //$pdf->Close();
			$pdf->Output($file,"F");
            $file_name = "bol.".$id.".pdf";
			//$pdf->closeParsers();
            $sends =array();
			if(is_file($file)){
				chmod($file,0777);
                $send_email = $send["send_email"];
				if($send_email){
                    $email = $send["email"];
                    $emailname = $send["emailname"];
					$email_subject = ($email_subject)?$email_subject:"Strong Container BOL #".$shipment["shipped_tracknum"]." to ".$shipment["d_to"];
					$x=0;
					foreach($email as $v){
                        if(filter_var($v, FILTER_VALIDATE_EMAIL)){
                            $name=$emailname[$x];
                            $sends[]=array("name"=>$name,"email"=>$v,"subject"=>$email_subject,"body"=>"Your BOL is attached.");
                        }
						/*if(eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$",$v)){
							$name=$emailname[$x];
							$sends[]=array("name"=>$name,"email"=>$v,"subject"=>$email_subject,"body"=>"Your BOL is attached.");
						}*/
						$x++;
					}
				}
                $send_fax = $send["send_fax"];
				if($send_fax){
                    $fax = $send["fax"];
                    $faxname = $send["faxname"];
					$fax_subject = ($fax_subject)?$fax_subject:"Strong Container BOL #".$shipment["shipped_tracknum"]." to ".$shipment["d_to"];
					$x=0;
					foreach($fax as $v){
						$v = preg_replace('/[^0-9]+/','',$v);
						if(preg_match("/^[0-9]{10}$/",$v)){
							$name=$faxname[$x];
							$sends[]=array("name"=>$name,"email"=>"1".$v."@efaxsend.com","subject"=>$fax_subject,"body"=>"");
						}
						$x++;
					}
				}

				$coversheet_message="See attached.\n\n\n\n\n*If you are receiving a Bill of Lading, please give it to the driver at the time of pickup.";
				if($do_rateconfirm || $do_w9 || $do_mc || $do_bond){
					$coversheet_message="Enclosed are the following:\n\n";
					if($do_rateconfirm){$counter++;
						$coversheet_message.=$counter.") Rate confirmation\n";
					}

					if($do_w9){$counter++;
						$coversheet_message.=$counter.") W-9\n";
					}

					$counter++;
					$coversheet_message.=$counter.") Bill of lading\n";

					if($do_mc){$counter++;
						$coversheet_message.=$counter.") Authority\n";
					}

					if($do_bond){$counter++;
						$coversheet_message.=$counter.") Bond\n";
					}

					$coversheet_message.="\n\n\nPayment terms are 20 days from receipt of invoice unless otherwise agreed upon.\n\nAny questions, please call.";
				}

				if(count($sends) >0){
					include("emailpdf.php");
					$email_from_name = ($email_from_name)?$email_from_name:"Strong Container";
//					$email_from = ($email_from)?$email_from:"support@norwayfreight.com";
					$email_from = ($email_from)?$email_from:"clint@myminicasa.com";

					foreach($sends as $v){
						$emailpdf = new emailPDF($v["name"],$v["email"],"Transport","sales@strongcontainers.com",$v["subject"],$v["body"],$file,$file_name);
                        //echo "<pre>";print_r($v["subject"]); echo "</pre>";
						echo "Sent to ".$v["name"]." - ".$v["email"]."<br>";
					}
				}else{
					echo "No addresses specified";
				}
				@unlink($pdfpath."generated/bol.".$id.".pdf");
			}else{
				echo "Cannot write PDF File";
			}
			die();
		}
		break;
	case "bol":
		$vars["id"]=$id;
		echo replace($vars,rf($htmlpath."shipments_bol.html"));
		die();
		break;
}


function getBillingId($id){
	global $db;

    // get the billing id for the shipping carrier.
    $billing_id = $db->query("SELECT billing_id FROM carrier INNER JOIN shipment ON carrier.id = shipment.shipped_carrier_id WHERE shipment.id = '$id'");
    $bid = $billing_id[0]["billing_id"];

    // get the billing address for the billing id.
    $billing = $db->query("SELECT * FROM billing WHERE id='$bid'");
    //$billing = $db->query("SELECT * FROM billing WHERE `default`='1'");

	// check for the fak address for this shipment
	$shipment = $db->query("SELECT * FROM shipment WHERE id='$id'");
	if($shipment){
		$KEY1=$shipment[0]["o_to"].$shipment[0]["o_city"].$shipment[0]["o_state"].$shipment[0]["o_zip"];
		$KEY2=$shipment[0]["d_to"].$shipment[0]["d_city"].$shipment[0]["d_state"].$shipment[0]["d_zip"];
		$carrier_fak = $db->query("SELECT billing.* FROM carrier_fak_address INNER JOIN billing ON carrier_fak_address.billing_id=billing.id WHERE carrier_id='".$shipment[0]["shipped_carrier_id"]."' AND CONCAT(carrier_fak_address.company,carrier_fak_address.city,carrier_fak_address.state,carrier_fak_address.zip) IN ('$KEY1','$KEY2')");
		if($carrier_fak){
			$billing = $carrier_fak;
		}
	}

	return $billing;
}

?>
