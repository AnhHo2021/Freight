<?php
$status = ($status)?$status:"open";
$html["status"] = $status;
$html["status_underline_open"] = ($status=="open")?" style='text-decoration:underline;'":"";
$html["status_underline_sent"] = ($status=="sent")?" style='text-decoration:underline;'":"";


switch($action){
	case "remove":
		if($id){
			$db->query("UPDATE marketing_letters SET status='removed' WHERE id = '$id'");
			$msg = "The letter has been successfully removed.";
			header("Location: /?action=marketing_$mode&status=$status&msg=$msg");
		}
		break;
	case "edit":
		if($id){
			if($mode=="save"){
				$db->query("UPDATE marketing_letters SET company='$company', addr='$addr', addr2='$addr2', city='$city', state='$state', zip='$zip', email='$email', fax='$fax' WHERE id = '$id'");
				echo "<div align='center'><b>Customer letter successfully saved.</b><p><input type='button' onclick='window.opener.location.reload();window.close();' value='Close Window'></div>";
			}else{
				$sql = "SELECT * FROM marketing_letters WHERE id='$id'";
				$sel_data = $db->query($sql);
				if($sel_data){
					foreach($sel_data[0] as $n=>$v){$vars[$n] = $v;}
					
					$sql = "SELECT * FROM state ORDER BY state";
					$states = $db->query($sql);
					foreach($states as $v) {
						$s=($vars["state"] == $v["code"])?" selected":"";
						$vars["states"] .= "<option value='".$v["code"]."'".$s.">".$v["code"]."</option>\n";
					}
					$html["BODY"]=replace($vars,rf($htmlpath."marketing_edit.html"));
				}
			}
		}
		break;
	case "ns":
	case "org_request":
	case "dest_request":
	case "inactivity":
		if($action=="ns"){
			$marketing_type_id = 1;
			$vars["mode"] = "ns";
		}elseif($action=="org_request"){
			$marketing_type_id = 2;
			$vars["mode"] = "org_request";
		}elseif($action=="dest_request"){
			$marketing_type_id = 3;
			$vars["mode"] = "dest_request";
		}elseif($action=="inactivity"){
			$marketing_type_id = 4;
			$vars["mode"] = "inactivity";
		}

		if($items){
			//print, email or fax
			if($status=="open"){
				foreach($items as $v){
					//$db->query("UPDATE marketing_letters SET status='sent', date_sent=NOW() WHERE id = '".$v."' AND status='open'");
				}
			}
			if($type=="print"){
				include("marketing_print.php");
			}elseif($type=="email"){
				include("marketing_email.php");
			}elseif($type=="fax"){
				include("marketing_print.php");
				sendFax($marketing_type_id,$items);
				$msg = count($items)." fax messages have been successfully sent.";
				header("Location: /?action=marketing&msg=$msg");
			}
		}else{
			$vars["status"]=$status;
			
			if(!$orderby){$orderby="marketing_letters.id";$orderdir="DESC";}
			$vars["orderby"]=$orderby;
			$vars["orderdir"]=$orderdir;
			$vars["asc"]=($orderdir == "ASC")?"DESC":"ASC";
			
			$sql = "SELECT marketing_letters.*, customer.company as mail_company, customer.mail_address1, customer.mail_address2, customer.mail_city, customer.mail_state, customer.mail_zip FROM marketing_letters INNER JOIN customer ON customer.id=marketing_letters.customer_id WHERE marketing_letters.marketing_type_id = '".$marketing_type_id."' AND marketing_letters.status='$status' ORDER BY $orderby $orderdir";
			$sel_items = $db->query($sql);
			
			if($sel_items){
				foreach($sel_items as $v){
					$bg = ($i++ % 2) ? $altbg1 : $altbg2;
					$vars["rows"] .= '<tr bgcolor="'.$bg.'" onmouseover="style.backgroundColor=\'white\';" onmouseout="style.backgroundColor=\''.$bg.'\'">';
						$vars["rows"] .= '<td valign="top" align="center" nowrap><input type="checkbox" name="items[]" id="items" value="'.$v["id"].'"></td>';	
						$vars["rows"] .= '<td valign="top" align="center" nowrap>'.date("m/d/Y g:i a",strtotime($v["date_created"])).'</td>';
						$vars["rows"] .= '<td valign="top"><a target="_parent" href="/?action=customers_edit&id='.$v["customer_id"].'">'.$v["company"].'<br>'.$v["addr"].' '.$v["addr2"].'<br>'.$v["city"].', '.$v["state"].' '.$v["zip"].'</td>';
						$vars["rows"] .= '<td valign="top" nowrap><a target="_parent" href="/?action=customers_edit&id='.$v["customer_id"].'">'.$v["email"].'</td>';
						$vars["rows"] .= '<td valign="top" nowrap><a target="_parent" href="/?action=customers_edit&id='.$v["customer_id"].'">'.$v["fax"].'</td>';
						$vars["rows"] .= '<td valign="top" align="center" nowrap><a target="_parent" href="/?action=shipments_edit&id='.$v["shipment_id"].'">'.$v["shipment_id"].'</td>';
						$vars["rows"] .= '<td valign="top" align="center" nowrap><a onclick="editLetter('.$v["id"].');" href="#"><img src="images/icon-edit.gif" border="0"></a>&nbsp;<a target="_self" onClick="return ask_user();" href="/?action=marketing_remove&id='.$v["id"].'&mode='.$vars["mode"].'&status='.$status.'"><img src="images/icon-remove.gif" border="0"></a></td>';
					$vars["rows"] .= '</tr>';
				}
				$vars["rows"] .= '<tr>';
					$vars["rows"] .= '<td align="center"><a href="javascript:checkAll()">ALL</a></small> / <small><a href="javascript:checkNone()">NONE</a></small></td>';
					$vars["rows"] .= '<td colspan="6">&nbsp;</td>';
					$vars["rows"] .= '</tr>';
				$vars["rows"] .= '<tr>';
					$vars["rows"] .= '<td colspan="7" align="center"><input onclick="printLetters();" type="button" value="Print">&nbsp;<input onclick="faxLetters();" type="button" value="Fax">&nbsp;<input onclick="emailLetters();" type="button" value="Email"></td>';
				$vars["rows"] .= '</tr>';
			}else{
				$vars["rows"] .= '<tr><td colspan="7" align="center"><b>There are no letters to display.</b></td></tr>';
			}
			$html["BODY"]=replace($vars,rf($htmlpath."marketing_list.html"));
		}
		break;
	default:
		$html["LOCATION"] = "<h1>MARKETING</h1>";
		$html["BODY"]=replace($vars,rf($htmlpath."marketing.html"));
}
?>