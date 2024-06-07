<?php

switch($action){
	case "view-top":
		$set_template  = "template.topframe.html";

		$sql = "SELECT shipment.id,shipment.o_to,shipment.o_contact_name,shipment.o_contact_fax,shipment.o_contact_email,shipment.d_to,shipment.d_contact_name,shipment.d_contact_fax,shipment.d_contact_email,customer.company AS c_to,customer.main_contact_name AS c_contact_name,customer.main_contact_fax AS c_contact_fax,customer.main_contact_email AS c_contact_email FROM shipment LEFT JOIN customer ON shipment.customer_id=customer.id WHERE shipment.id = '$id'";
		$quote_data = $db->query($sql);
		if($quote_data){
			$quote=$quote_data[0];
		}

		$vars["email1"]=$quote["c_contact_email"];
		$vars["email2"]=$quote["o_contact_email"];
		$vars["email3"]=$quote["d_contact_email"];

		$vars["emailname1"]=$quote["c_contact_name"];
		$vars["emailname2"]=$quote["o_contact_name"];
		$vars["emailname3"]=$quote["d_contact_name"];

		$vars["fax1"]=$quote["c_contact_fax"];
		$vars["fax2"]=$quote["o_contact_fax"];
		$vars["fax3"]=$quote["d_contact_fax"];

		$vars["faxname1"]=$quote["c_contact_name"];
		$vars["faxname2"]=$quote["o_contact_name"];
		$vars["faxname3"]=$quote["d_contact_name"];

		$vars["id"]=$id;

		$vars["carrier_rates"]="";
		$shipment_rates = $db->query("SELECT shipment_rate.*,carrier.company AS carrier FROM shipment_rate INNER JOIN carrier ON shipment_rate.carrier_id=carrier.id WHERE shipment_id='$id' ORDER BY id");
		if($shipment_rates){
			$shipment_accessorials = $db->query("SELECT shipment_rate_accessorial.shipment_rate_id,SUM(shipment_rate_accessorial.rate) AS rate FROM shipment_rate_accessorial INNER JOIN shipment_rate ON shipment_rate_accessorial.shipment_rate_id=shipment_rate.id WHERE shipment_rate.shipment_id='$id' GROUP BY shipment_rate_accessorial.shipment_rate_id");
			if($shipment_accessorials){
				foreach($shipment_accessorials as $v){
					$accessorials[$v["shipment_rate_id"]]=$v["rate"];
				}
			}

			foreach($shipment_rates as $v){
				$total_rate=$v["rate"];
				if($accessorials[$v["id"]]){$total_rate+=$accessorials[$v["id"]];}
				$vars["carrier_rates"].='<option value="'.$v["id"].'">'.$v["carrier"].' - '.$v["transit_days"] .' days - $'. number_format($total_rate,2).'</option>';
			}
		}

		$vars["email_subject"]=$email_subject ? $email_subject : "Strong Container Quote #".$quote["id"];

		$html["BODY"]=replace($vars,rf($htmlpath."quotes_view-top.html"));
		break;
	case "view-main":
        $send = $_POST;
		$sql = "SELECT shipment.*,customer.*,al.fname,al.lname, au.fname AS update_fname,au.lname AS update_lname FROM shipment LEFT JOIN customer ON shipment.customer_id=customer.id LEFT JOIN admin_login au ON shipment.updated_admin_login_id=au.id LEFT JOIN admin_login al ON shipment.created_admin_login_id=al.id WHERE shipment.id = '$id'";
		$quote_data = $db->query($sql);
		if($quote_data){
			$quote=$quote_data[0];
		}else{die("No Quote");}

		if($itemArr){unset($itemArr);}
		//$itemArr[]="f_name=date,num,shipper,consignee,req_pickup,req_delivery,insurance,accessorials";
		//$itemArr[]="f_x=605,595,105,440,235,235,235,430";
		//$itemArr[]="f_y=83,100,238,238,390,408,425,405";
		//$itemArr[]="f_w=145,160,300,310,175,175,175,320";
		//$itemArr[]="f_h=15,15,120,120,15,15,15,32";

		$itemArr[]="f_name=date,o_to,o_address,o_city,o_state,o_zip,o_contact,o_po,total,company,mail_address,mail_city,mail_state,mail_zip,mail_contact,num1,service,transit,insurance,d_to,d_address,d_city,d_state,d_zip,d_po,total,notes,num2";
		$itemArr[]="f_x=695.1,357.2,358.2,359.2,503.2,557.2,359.2,359.2,696.1,63.199,63.199,64.199,209.2,261.2,65.199,107.2,106.199,107.2,108.199,359.2,359.2,359.2,503.2,556.2,359.2,534.2,65.199,695.7";
		$itemArr[]="f_y=191.347,193.351,231.42,267.486,268.488,269.489,306.557,343.624,228.415,194.353,231.42,269.489,269.489,268.488,304.553,385.701,425.774,463.843,493.897,388.706,425.774,463.843,462.841,463.843,500.91,787.431,843.433,266.985";
		$itemArr[]="f_w=115,265.9,264.9,137.999,47,65.9,263.9,262.9,113,265,266,141,46,68,263,221,222,222,219,263.9,263.9,140,46,66.9,263.9,87.9,551.9,114";
		$itemArr[]="f_h=22.04,20.036,20.036,20.036,20.036,19.034,19.034,19.034,16.029,19.034,19.034,19.034,19.034,20.036,21.038,22.04,19.034,19.034,20.036,20.036,20.036,20.036,20.036,19.034,19.034,18.032,150.273,21.539";


		ini_set('memory_limit', '32M');

		// debug
		$debug=false;

		// get rid of gzip
		ini_set('zlib.output_compression', '0');

		// get ready
		define('FPDF_FONTPATH','font/');
			// get ready
		define('FPDF_FONTPATH','font/');
		
		class_exists('TCPDF', true); // trigger Composers autoloader to load the TCPDF class

        // setup the autoload function
        require_once('fpdi1.6.2/vendor/autoload.php');
        
        // initiate FPDI
        $pdf = new FPDI("P","in","Letter");
		
		
		//require("fpdf/fpdi.php");

		// setup the object
		//$pdf= new fpdi("P","in","Letter");
		$pdf->SetAutoPageBreak(false);
		$pdf->SetDisplayMode("real");
		$pdf->SetMargins(0,0,0);
		if($debug){$pdf->SetDrawColor(51,255,51);}

		// start out with top
		$pdf->setSourceFile($pdfpath."ST_quote.pdf");
		$tplidx = $pdf->ImportPage(1);
		$pdf->addPage();
		$pdf->useTemplate($tplidx);
		$pdf->SetXY(0,0);

		$pdf->SetFont('Arial','B',10);

		// variables
		setItem("num1",$id,"L");
		setItem("num2",$id,"L");
		setItem("date",date("m/d/Y"),"L");

		setItem("company",$quote["company"],"L");
		setItem("mail_address",$quote["mail_address1"]." ".(($quote["mail_address1"])?$quote["mail_address2"]."\n":""),"L");
		setItem("mail_city",$quote["mail_city"],"L");
		setItem("mail_state",$quote["mail_state"],"L");
		setItem("mail_zip",$quote["mail_zip"],"L");
		setItem("main_contact_name",$quote["main_contact_name"],"L");

		setItem("o_to",$quote["o_to"],"L");
		setItem("o_address",$quote["o_address1"]." ".(($quote["o_address2"])?$quote["o_address2"]."\n":""),"L");
		setItem("o_city",$quote["o_city"],"L");
		setItem("o_state",$quote["o_state"],"L");
		setItem("o_zip",$quote["o_zip"],"L");
		setItem("o_contact",$quote["o_contact_name"],"L");
		setItem("o_po",$quote["o_po"],"L");

		setItem("d_to",$quote["d_to"],"L");
		setItem("d_address",$quote["d_address1"]."".(($quote["d_address2"])?$quote["d_address2"]."\n":""),"L");
		setItem("d_city",$quote["d_city"],"L");
		setItem("d_state",$quote["d_state"],"L");
		setItem("d_zip",$quote["d_zip"],"L");
		setItem("d_po",$quote["d_po"],"L");

		//setItem("req_pickup",date("m/d/y",strtotime($quote["req_pickup_date"]))." @ ".date("g:i a",strtotime($quote["req_pickup_time_start"])));
		//setItem("req_delivery",date("g:i a",strtotime($quote["req_delivery_time_start"]))." - ".date("g:i a",strtotime($quote["req_delivery_time_end"])));
		setItem("insurance",(($quote["insurance"])?number_format($quote["insurance"],2):""),"L");

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
			//setItem("accessorials",implode(", ",$accessorials1)."\n".implode(", ",$accessorials2));
		}

		// shipment rate
		$filter = ($shipment_rate_id)?"shipment_rate.id='$shipment_rate_id'":"shipment_rate.shipment_id='$id'";
		$shipment_rate = $db->query("SELECT shipment.container_price,shipment.container_cost,
		service.group,service.name AS service,
		shipment_rate.*,carrier.company AS carrier
		FROM shipment_rate
		INNER JOIN shipment ON shipment_rate.shipment_id=shipment.id
		INNER JOIN carrier ON shipment_rate.carrier_id=carrier.id
		INNER JOIN service ON shipment_rate.service_id=service.id
		WHERE $filter ORDER BY id LIMIT 1");

        $container_price=0;
		if($shipment_rate){
            $container_price= $shipment_rate[0]["container_price"];
			$group = $shipment_rate[0]["group"];
			setItem("service",$shipment_rate[0]["service"],"L");
		}

		// commodities
		$sql = "SELECT shipment_commodity.*,class_list.class, packaging_list.packaging AS pack FROM shipment_commodity LEFT JOIN class_list ON shipment_commodity.class_list_id=class_list.id LEFT JOIN packaging_list ON shipment_commodity.packaging_list_id=packaging_list.id WHERE shipment_id = '$id' ORDER BY id ASC";
		$commodities = $db->query($sql);
		if($commodities){
			for($x=1,$i=0;$x<=9;$x++,$i++){
				$setY=520 + ($x * 26);

				unset($itemArr);
				//$itemArr[]="f_name=pcs,pack,class,dims,nmfc,description,weight";
				//$itemArr[]="f_x=80,165,235,305,430,500,675";
				//$itemArr[]="f_y=$setY,$setY,$setY,$setY,$setY,$setY,$setY";
				//$itemArr[]="f_w=67,68,67,120,65,165,75";
				//$itemArr[]="f_h=24,24,24,24,24,24,24";

				$itemArr[]="f_name=pcs,weight,classdims";
				$itemArr[]="f_x=39.199,113.199,176.2";
				$itemArr[]="f_y=$setY,$setY,$setY";
				$itemArr[]="f_w=73,62,154";
				$itemArr[]="f_h=24,24,24";


				setItem("pcs",$commodities[$i]["pieces"]);
				//setItem("pack",$commodities[$i]["pack"]);
				//setItem("class",$commodities[$i]["class"]);
				//if($commodities[$i]["dim_d"] || $commodities[$i]["dim_w"] || $commodities[$i]["dim_h"]){
				//	setItem("dims",$commodities[$i]["dim_d"]." x ".$commodities[$i]["dim_w"] ." x ". $commodities[$i]["dim_h"]);
				//}
				//setItem("nmfc",$commodities[$i]["nmfc"]);

				if($group == "air" || !$group || $group == "truck"){
					if($commodities[$i]["dim_d"]  || $commodities[$i]["dim_w"] || $commodities[$i]["dim_h"]){
						setItem("classdims",$commodities[$i]["dim_d"]." x ".$commodities[$i]["dim_w"] ." x ". $commodities[$i]["dim_h"]);
					}
				}else{
					if($commodities[$i]["class"]){
						setItem("classdims",$commodities[$i]["class"]." / ".$commodities[$i]["nmfc"]);
					}
				}

				//setItem("description",$commodities[$i]["description"]);
				setItem("weight",$commodities[$i]["weight"]);
			}
		}

		// services
		if($shipment_rate){
			unset($itemArr);
			//$itemArr[]="f_name=service,carrier,item,amount";
			//$itemArr[]="f_x=95,305,500,675";
			//$itemArr[]="f_y=732,732,732,732";
			//$itemArr[]="f_w=202,185,165,75";
			//$itemArr[]="f_h=20,20,20,20";

			$itemArr[]="f_name=description,charges";
			$itemArr[]="f_x=333.2,519.2";
			$itemArr[]="f_y=546,546";
			$itemArr[]="f_w=184,104.9";
			$itemArr[]="f_h=24,24";

			setItem("description",$shipment_rate[0]["service"]);
			setItem("carrier",$shipment_rate[0]["carrier"]);
			$transit = $shipment_rate[0]["transit_days"];
			setItem("charges","$".number_format($shipment_rate[0]["rate"],2));

			$total = $shipment_rate[0]["rate"];
            $ypos=546;
			$x=1;

			$accessorials = $db->query("SELECT accessorial.name,rate
			FROM shipment_rate_accessorial
			INNER JOIN accessorial ON shipment_rate_accessorial.accessorial_id=accessorial.id
			WHERE shipment_rate_accessorial.shipment_rate_id='".$shipment_rate[0]["id"]."' AND
			rate > 0 AND
			accessorial.type NOT IN ('fuel','insurance')");
			if($accessorials){
				foreach($accessorials as $v){$x++;
					if($x <= 6){
                        $ypos = $setY=520 + ($x * 26);

						unset($itemArr);
						//$itemArr[]="f_name=service,carrier,item,amount";
						//$itemArr[]="f_x=95,305,500,675";
						//$itemArr[]="f_y=$setY,$setY,$setY,$setY";
						//$itemArr[]="f_w=202,185,165,75";
						//$itemArr[]="f_h=20,20,20,20";

						$itemArr[]="f_name=description,charges";
						$itemArr[]="f_x=333.2,519.2";
						$itemArr[]="f_y=$setY,$setY";
						$itemArr[]="f_w=184,104.9";
						$itemArr[]="f_h=24,24";

						setItem("description",$v["name"]);
						setItem("charges","$".number_format($v["rate"],2));

						$total+=$v["rate"];
					}
				}
			}


			$accessorials = $db->query("SELECT accessorial.name,rate
			FROM shipment_rate_accessorial
			INNER JOIN accessorial ON shipment_rate_accessorial.accessorial_id=accessorial.id
			WHERE shipment_rate_accessorial.shipment_rate_id='".$shipment_rate[0]["id"]."' AND
			accessorial.type IN ('fuel','insurance')");
			if($accessorials){
				foreach($accessorials as $v){$x++;
					if($x <= 8){
                        $ypos = $setY=520 + ($x * 26);

						unset($itemArr);
						//$itemArr[]="f_name=service,carrier,item,amount";
						//$itemArr[]="f_x=95,305,500,675";
						//$itemArr[]="f_y=$setY,$setY,$setY,$setY";
						//$itemArr[]="f_w=202,185,165,75";
						//$itemArr[]="f_h=20,20,20,20";

						$itemArr[]="f_name=description,charges";
						$itemArr[]="f_x=333.2,519.2";
						$itemArr[]="f_y=$setY,$setY";
						$itemArr[]="f_w=184,104.9";
						$itemArr[]="f_h=24,24";

						setItem("description",$v["name"]);
						setItem("charges","$".number_format($v["rate"],2));

						$total+=$v["rate"];
					}
				}
			}
		}

        if(is_numeric($container_price)){
            unset($itemArr);
            $ypos += 25;
            $itemArr[]="f_name=description,charges";
            $itemArr[]="f_x=333.2,519.2";
            $itemArr[]="f_y=$ypos,$ypos";
            $itemArr[]="f_w=184,104.9";
            $itemArr[]="f_h=24,24";

            setItem("description","Container price");
            setItem("charges","$".number_format($container_price,2));
            $total+= $container_price;
        }

		// totals and transit
		$itemArr[]="f_name=total1,total2,transit";
		$itemArr[]="f_x=696.1,534.2,105.199";
		$itemArr[]="f_y=228.415,787.934,462.841";
		$itemArr[]="f_w=113,88,228";
		$itemArr[]="f_h=17.03,16.53,21.038";

		setItem("total1",number_format($total,2),"L");
		setItem("total2",number_format($total,2),"L");
		setItem("transit",$transit,"L");


		if($set_outgoing_subject){
			$email_subject = $set_outgoing_subject;
			$fax_subject = $set_outgoing_subject;
		}

		//if($setY<732){$setY=732;}
		//$pdf->SetFillColor(255,255,255);
		//$pdf->SetXY(6.75,($setY/100)+.25);
		//$pdf->Cell(.75,.25,"$".number_format($total,2),1,0,'C',1,'');
       // echo "<pre>send ="; print_r($_POST); echo "</pre>";
		if(count($send)<1){
			// send it out
			$pdf->Output("ST_quote.pdf","I");
			$pdf->closeParsers();
			die();
		}else{
			$file = $pdfpath."generated/quotes_view.".$id.".pdf";
            //$pdf->Close();
			$pdf->Output($file,"F");
            $file_name = "quotes_view.".$id.".pdf";
			//$pdf->closeParsers();
            $sends =array();
			if(is_file($file)){
				chmod($file,0777);
                $send_email = $send["send_email"];
				if($send_email){
                    $email = $send["email"];
                    $emailname = $send["emailname"];

					$email_subject = ($email_subject)?$email_subject:"Strong Container Quote #".$quote["id"];
					$x=0;
					foreach($email as $v){
                        if(filter_var($v, FILTER_VALIDATE_EMAIL)){
                            $name=$emailname[$x];
                            $sends[]=array("name"=>$name,"email"=>$v,"subject"=>$email_subject,"body"=>"Your quote is attached.");
                        }
						/*
						 * if(eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$",$v)){
							$name=$emailname[$x];
							$sends[]=array("name"=>$name,"email"=>$v,"subject"=>$email_subject,"body"=>"Your quote is attached.");
						}*/
						$x++;
					}
				}
                $send_fax = $send["send_fax"];
				if($send_fax){
                    $fax = $send["fax"];
                    $faxname = $send["faxname"];
					$fax_subject = ($fax_subject)?$fax_subject:"Strong Container Quote #".$quote["id"];
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
				if(count($sends) >0){
					include("emailpdf.php");
					$email_from_name = ($email_from_name)?$email_from_name:"Strong Container";
//					$email_from = ($email_from)?$email_from:"support@norwayfreight.com";
					$email_from = ($email_from)?$email_from:"clint@myminicasa.com";

					foreach($sends as $v){
						$emailpdf = new emailPDF($v["name"],$v["email"],"Transport","sales@strongcontainers.com",$v["subject"],$v["body"],$file,$file_name);

						if(!$no_show){echo "Sent to ".$v["name"]." - ".$v["email"]."<br>";}
					}
				}
				@unlink($pdfpath."generated/quotes_view.".$id.".pdf");
			}else{
				echo "Cannot write PDF File";
			}
			if(!$no_die){die();}
		}
		break;
}

?>