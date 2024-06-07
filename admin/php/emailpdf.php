<?php

class emailPDF{
	function emailPDF($to_name,$to_email,$from_name,$from_email,$subject,$body,$file,$file_name=null){
		$this->to_name=$to_name;
		$this->to_email=$to_email;
		$this->from_name=$from_name;
		$this->from_email=$from_email;
		$this->to = ($to_name)?'"'.$to_name.'" <'.$to_email.'>':$to_email;
		$this->from = ($from_name)?'"'.$from_name.'" <'.$from_email.'>':$from_email;
		$this->subject=$subject;
		$this->body=$body;
		$this->file=$file;
        $this->file_name = $file_name;

		$this->sendEmail();
	}

	function sendEmail(){
		global $pdfpath,$pdf,$coversheet_message;

		if(!$coversheet_message){$coversheet_message="See attached.";}

		// seed it
		srand(((int)((double)microtime()*1000003)));

		// Generate a boundary string 											
		$semi_rand = md5(rand(100,99999).time()); 
		$mime_boundary = "----=_Multipart_Boundary_x{$semi_rand}x"; 
		
		//if(strpos($this->to,"efaxsend")){
        if(strpos($this->to,"anh remove_efaxsend")){
            // setup the object
			//$pdf= new fpdi("P","in","Letter");
            $pdf = new FPDI("P","in","Letter");
			$pdf->SetAutoPageBreak(false);
			$pdf->SetDisplayMode("real");
			$pdf->SetMargins(0,0,0);
			//if($debug){$pdf->SetDrawColor(51,255,51);}

			$pdf->setSourceFile($pdfpath."coversheet.pdf");
			$tplidx = $pdf->ImportPage(1);	
			$pdf->addPage();
			$pdf->useTemplate($tplidx);
			$pdf->SetXY(0,0);


			list($fax_num,$t)=explode("@",$this->to_email);
			$fax_num=substr($fax_num,1,3)."-".substr($fax_num,4,3)."-".substr($fax_num,7,4);

			$pages=1;

			// add the original file
			if(copy($this->file,"/tmp/".basename($this->file))){
				$ii = $pdf->setSourceFile("/tmp/".basename($this->file));
				for($i=1;$i<=$ii;$i++){
					$tplidx = $pdf->ImportPage($i);	
					$pdf->addPage();
					$pdf->useTemplate($tplidx);
					$pages++;
				}
			}else{
				die("Couldn't add coversheet");
			}

			$this->itemArr[]="f_name=to,from,company,date,fax,pages,re,message";
			$this->itemArr[]="f_x=95,460,135,460,145,620,100,75";
			$this->itemArr[]="f_y=308,308,362,362,411,411,465,489";
			$this->itemArr[]="f_w=270,270,270,270,270,50,325,626";
			$this->itemArr[]="f_h=15,15,15,15,15,15,15,301";
			
			$pdf->SetCurPage(1);
			$pdf->SetXY(0,0);
			$pdf->SetFont('Arial','B',10);
			$this->setItem("to",$this->to_name,"L");
			$this->setItem("from",$this->from_name,"L");
			$this->setItem("company","","L");
			$this->setItem("date",date("m/d/Y"),"L");
			$this->setItem("fax",$fax_num,"L");
			$this->setItem("pages",$pages,"L");
			$this->setItem("re",$this->subject,"L");
			$this->setItem("message",$coversheet_message,"L");	
			
			$pdf->SetCurPage($pdf->GetNumPages());

			// over write it

			$pdf->Output("/tmp/".basename($this->file),"F");
			//$pdf->closeParsers();

			$this_file = "/tmp/".basename($this->file);
		}else{
			$this_file = $this->file;
		}

        $from_name ="Transport";
        $receiver_name = $this->to_name;
        mail_to($from_name,$receiver_name,$this->to_email,$this->subject,$this->body,$this_file,$this->file_name);
		//------------------------end send mail---------------------------
		// Read the file to be attached ('rb' = read binary) 
		$fp = fopen($this_file,"rb");
		$data = fread($fp,filesize($this_file));
		fclose($fp);
		
		// Base64 encode the file data 
		$data = chunk_split(base64_encode($data));

		// Add file attachment to the message 
		$message_attachments .= "--{$mime_boundary}\n" . 
					 "Content-Type: application/pdf;\n" . 
					 " name=\"".basename($this_file)."\"\n" . 
					 "Content-Disposition: inline;\n" . 
					 " filename=\"".basename($this_file)."\"\n" . 
					 "Content-Transfer-Encoding: base64\n\n" . 
					 $data . "\n\n";

		// Add the headers for a file attachment 
		$headers =	"From: ".$this->from."\n" .
					"Return-Path: ".$this->from."\n" .
					"MIME-Version: 1.0\n" .
					"Content-Type: multipart/mixed;\n" .
					" boundary=\"{$mime_boundary}\"";

		// Add a multipart boundary above the plain message 
		$message = "This is a multi-part message in MIME format.\n\n".  
					$message_attachments ."--{$mime_boundary}--\n";

		//mail($this->to, $this->subject, $message, $headers);

		if(is_file("/tmp/".basename($this->file))){
			unlink("/tmp/".basename($this->file));
		}
	}

	function setItem($name,$value,$align='C'){
		global $pdf;

		$itemPos = $this->getItemPos($name);
		if($itemPos){
			$x=$itemPos["x"]/100;
			$y=$itemPos["y"]/100;
			$w=$itemPos["w"]/100;
			$h=.15;

			$pdf->ClippingRect($itemPos["x"]/100,$itemPos["y"]/100,$itemPos["w"]/100,$itemPos["h"]/100,$debug);				

			$DATAs=explode("\n",$value);
			if(count($DATAs)==1){
				$TEXT=$DATAs[0];
				$pdf->SetXY($x,$y);
				$pdf->Cell($itemPos["w"]/100,$itemPos["h"]/100,$TEXT,0,0,$align,0,'');
			}else{
				foreach($DATAs as $WRITE){
					$pdf->SetXY($x,$y);
					if($WRITE){
						$pdf->CellFitScale($w,$h,$WRITE,0);
					}
					$y+=$h;
				}
			}
			$pdf->UnsetClipping();
		}
	}

	function getItemPos($name){
		foreach($this->itemArr as $myv){
			list($field_name,$myv)=explode("=",trim($myv));
			$vv=explode(",",$myv);		
			$fields_out[$field_name]=$vv;
		}

		foreach($fields_out["f_name"] as $key=>$v){
			if($v==$name){
				$ret["x"]=$fields_out["f_x"][$key];
				$ret["y"]=$fields_out["f_y"][$key];
				$ret["w"]=$fields_out["f_w"][$key];
				$ret["h"]=$fields_out["f_h"][$key];
			}
		}
		return $ret;
	}
}

?>