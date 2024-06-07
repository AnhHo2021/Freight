#!/usr/bin/php -q
<?php

set_time_limit(2700);

// grabs tracking updates from saia.com
include("/mnt/web/transport/transport/freight/admin/php/db.php");
$db = new DB("root","troll6");

// xml parsing
include("/opt/www/htdocs/admin.norwayfreight.com/php/xml2array.php");


// allowed for updates
$allowed_status = array('tobescheduled','scheduled','intransit','outfordelivery','problem');

// update log table
$shipments = $db->query("SELECT id,status,shipped_tracknum,shipped_carrier_pronum FROM shipment WHERE shipped='1' AND shipped_carrier_id='9' AND status IN ('".implode("','",$allowed_status)."') AND shipped_carrier_pronum != ''");
if($shipments){
	foreach($shipments as $v){
		$return = getTrackData($v["shipped_carrier_pronum"]);
		if($return){
			$pro_history = getProHistory($return);
			if($pro_history){
				$add = true;
				$cur_history = $db->query("SELECT md5(pro_history) AS hash FROM saia_track WHERE shipment_id='$v[id]' ORDER BY date_request DESC LIMIT 1");
				if($cur_history	&& $cur_history[0]["hash"] == md5($pro_history)){
					$add = false;
				}

				if($add){
					$db->query("INSERT INTO saia_track SET shipment_id='$v[id]',date_request=NOW(),pro_history='$pro_history'");
				}
			}


			// see if it is delivered
			$status_data = getStatusDelivered($return);
			if(is_array($status_data) && $status_data[0]=="Delivered"){
				if(!$status_data[1]){$status_data[1]=date("Y-m-d");}
				if(!$status_data[2]){$status_data[2]=date("H:i:s");}

//				$sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($status_data[1]." ".$status_data[2]))."',tracking='DELIVERED TO: ".mysql_escape_string($status_data[3])."'";
				$sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($status_data[1]." ".$status_data[2]))."',tracking='DELIVERED TO: ".mysqli_escape_string($db->conn,$status_data[3])."'";
				$db->query($sql);

				$sql = "UPDATE shipment SET status='delivered' WHERE id='$v[id]'";
				$db->query($sql);
			}
		}
	}
}


// save tracking updates
$saia_track = $db->query("SELECT * FROM saia_track WHERE completed='0' ORDER BY date_request DESC");
if($saia_track){
	foreach($saia_track as $v){
		$track_updates[$v["shipment_id"]][]=$v;
		$shipment_ids[$v["shipment_id"]]=$v["shipment_id"];
	}

	// past saved
	$completed = $db->query("SELECT * FROM saia_track WHERE shipment_id IN (".implode(",",$shipment_ids).") AND completed='1' ORDER BY date_request DESC");
	if($completed){
		foreach($completed as $v){
			$track_history[$v["shipment_id"]][]=$v;
		}
	}

	// cur status
	$shipments = $db->query("SELECT id,status FROM shipment WHERE id IN (".implode(",",$shipment_ids).")");
	if($shipments){
		foreach($shipments as $v){
			$shipment_status[$v["id"]]=$v["status"];
		}
	}

	// last tracking entry
	$shipment_tracking = $db->query("SELECT shipment_id,date_show FROM shipment_tracking WHERE shipment_id IN (".implode(",",$shipment_ids).") AND created_admin_login_id <> '0' ORDER BY date_show DESC");
	if($shipment_tracking){
		foreach($shipment_tracking as $v){
			$already_tracked[$v["shipment_id"]][]=$v["date_show"];
		}
	}

	foreach($track_updates as $shipment_id=>$arr){
		if(in_array($shipment_status[$shipment_id],$allowed_status)){
			$update=extractProHistory($arr[0]["pro_history"]);
			$history=extractProHistory(($track_history[$shipment_id])?$track_history[$shipment_id][0]["pro_history"]:"");

			$diff = array_diff($update,$history);
			if($diff){
				foreach($diff as $v){
					list($datetime,$tracking)=explode(" - ",$v);

					// check last track entry
					$addit=true;
					if($already_tracked[$shipment_id]){
						$day1=date("Ymd",strtotime($already_tracked[$shipment_id][0]));
						$day2=date("Ymd",strtotime($datetime));
						if($day1 >= $day2){
							$addit=false;
						}
					}

					if($addit){
						// add the tracking update
//						$sql = "INSERT INTO shipment_tracking SET shipment_id='$shipment_id',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($datetime))."',tracking='".mysql_escape_string($tracking)."'";
						$sql = "INSERT INTO shipment_tracking SET shipment_id='$shipment_id',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($datetime))."',tracking='".mysqli_escape_string($db->conn, $tracking)."'";
						$db->query($sql);

						// check for status changes
						if($shipment_status[$shipment_id] == "tobescheduled" || $shipment_status[$shipment_id] == "scheduled"){
							$db->query("UPDATE shipment SET status='intransit' WHERE id='$shipment_id'");
						}
						if(eregi("delayed",$tracking)){
							$db->query("UPDATE shipment SET status='problem' WHERE id='$shipment_id'");
						}
						if(eregi("lost",$tracking)){
							$db->query("UPDATE shipment SET status='problem' WHERE id='$shipment_id'");
						}
						if(eregi("delivery appointment",$tracking)){
							$db->query("UPDATE shipment SET status='outfordelivery' WHERE id='$shipment_id'");
						}
						if(eregi("out for delivery",$tracking)){
							$db->query("UPDATE shipment SET status='outfordelivery' WHERE id='$shipment_id'");
						}
						if(eregi("OFD",$tracking)){
							$db->query("UPDATE shipment SET status='outfordelivery' WHERE id='$shipment_id'");
						}
					}
				}
			}
		}

		foreach($arr as $v){
			$db->query("UPDATE saia_track SET completed='1' WHERE id='".$v["id"]."'");
		}
	}
}



function extractProHistory($data){
	$arr = explode("\n",$data);
	if($arr){
		foreach($arr as $v){
			$new[]=trim($v);
		}
	}else{
		$new=array();
	}
	return $new;
}

function getStatusDelivered($data){
	$xml = new xml2Array();
	$xml_array = $xml->parse($data);
	if($xml_array){
		if($xml_array["RESPONSE"]["CURRENTSTATUS"]){
			$status = $xml_array["RESPONSE"]["CURRENTSTATUS"]["DATA"];
		}
		if($xml_array["RESPONSE"]["DELIVERYDATETIME"]){
			list($del_date,$del_time)=explode(" ",$xml_array["RESPONSE"]["DELIVERYDATETIME"]["DATA"]);
		}
		if($xml_array["RESPONSE"]["SIGNATURE"]){
			$signed_by = $xml_array["RESPONSE"]["SIGNATURE"]["DATA"];
		}
	}
	return array($status,$del_date,$del_time,$signed_by);
}

function getProHistory($data){
	$xml = new xml2Array();
	$xml_array = $xml->parse($data);
	if($xml_array){
		if($xml_array["RESPONSE"]["HISTORY"]["HISTORYITEM"]){
			foreach($xml_array["RESPONSE"]["HISTORY"]["HISTORYITEM"] as $v){
				$ret .= $v["ACTIVITYDATETIME"]["DATA"]." - ".$v["ACTIVITY"]["DATA"]." ".$v["CITY"]["DATA"]." ".$v["STATE"]["DATA"]."\n";
			}
		}
	}
	return $ret;
}

function getTrackData($pro){
	$request =	"<GetByProNumber>\n".
				"	<UserID>cnordahl</UserID>\n".
				"	<Password>norge10</Password>\n".
				"	<TestMode>N</TestMode>\n".
				"	<ProNumber>$pro</ProNumber>\n".
				"</GetByProNumber>";
	$fp = fsockopen("www.saiasecure.com", 80, $errno, $errstr, 30);
	if($fp){
		$req =  "POST /webservice/shipment/xml.aspx HTTP/1.1\r\n";
		$req .= "Host: www.saiasecure.com\r\n";
		$req .= "User-Agent: Norway Freight\r\n";
		$req .= "Content-Type: text/xml\r\n";
		$req .= "Content-Length: ".strlen($request)."\r\n";
		$req .= "Connection: close\r\n\r\n";
		$req .= $request;

		fwrite($fp, $req);
		while(!feof($fp)) {
		   $rets[] = fgets($fp, 4096);
		}
		fclose($fp);
		if($rets){
			foreach($rets as $v){
				if($isRes || substr($v,0,9)=="<Response"){
					$response.=trim($v);
					$isRes=true;
				}
			}
			if($response){
				return $response;
			}
		}
	}
}

?>