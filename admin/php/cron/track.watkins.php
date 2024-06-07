#!/usr/bin/php -q
<?php

set_time_limit(2700);

// grabs tracking updates from watkins.com
include("/mnt/web/transport/transport/freight/admin/php/db.php");
$db = new DB("root","troll6");

// allowed for updates
$allowed_status = array('tobescheduled','scheduled','intransit','outfordelivery','problem');

// update log table
$shipments = $db->query("SELECT id,status,shipped_tracknum,shipped_carrier_pronum FROM shipment WHERE shipped='1' AND shipped_carrier_id='8' AND status IN ('".implode("','",$allowed_status)."') AND shipped_carrier_pronum != ''");
if($shipments){
	foreach($shipments as $v){
		$return = getTrackData($v["shipped_carrier_pronum"]);
		if($return){
			$pro_history = getProHistory($return);
			if($pro_history){
				$add = true;
				$cur_history = $db->query("SELECT md5(pro_history) AS hash FROM watkins_track WHERE shipment_id='$v[id]' ORDER BY date_request DESC LIMIT 1");
				if($cur_history	&& $cur_history[0]["hash"] == md5($pro_history)){
					$add = false;
				}

				if($add){
					$db->query("INSERT INTO watkins_track SET shipment_id='$v[id]',date_request=NOW(),pro_history='$pro_history'");
				}
			}


			// see if it is delivered
			$status_data = getStatusDelivered($return);
			if(is_array($status_data) && ($status_data[0]=="DELIVERED" || $status_data[0]=="DELIVERED (LATE)")){
				if(!$status_data[1]){$status_data[1]=date("Y-m-d");}
				if(!$status_data[2]){$status_data[2]=date("H:i:s");}

//				$sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($status_data[1]." ".$status_data[2]))."',tracking='DELIVERED TO: ".mysql_escape_string($status_data[3])."'";
				$sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',date_created=NOW(),date_show='".date("Y-m-d H:i:s",strtotime($status_data[1]." ".$status_data[2]))."',tracking='DELIVERED TO: ".mysqli_escape_string($db->conn, $status_data[3])."'";
				$db->query($sql);

				$sql = "UPDATE shipment SET status='delivered' WHERE id='$v[id]'";
				$db->query($sql);
			}

			// see if it is late
			$status_data = getStatusLate($return);
			if(is_array($status_data) && $status_data[0]=="LATE"){
				$check = $db->query("SELECT status FROM shipment WHERE id='$v[id]'");
				if($check[0]["status"] != "problem"){
					$sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',date_created=NOW(),date_show=NOW(),tracking='LATE'";
					$db->query($sql);

					$sql = "UPDATE shipment SET status='problem' WHERE id='$v[id]'";
					$db->query($sql);
				}
			}
		}
	}
}

// save tracking updates
$watkins_track = $db->query("SELECT * FROM watkins_track WHERE completed='0' ORDER BY date_request DESC");
if($watkins_track){
	foreach($watkins_track as $v){
		$track_updates[$v["shipment_id"]][]=$v;
		$shipment_ids[$v["shipment_id"]]=$v["shipment_id"];
	}

	// past saved
	$completed = $db->query("SELECT * FROM watkins_track WHERE shipment_id IN (".implode(",",$shipment_ids).") AND completed='1' ORDER BY date_request DESC");
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
						//if(eregi("LATE",$tracking)){
						//	$db->query("UPDATE shipment SET status='problem' WHERE id='$shipment_id'");
						//}
						if(eregi("OUT FOR DLVRY",$tracking)){
							$db->query("UPDATE shipment SET status='outfordelivery' WHERE id='$shipment_id'");
						}
					}
				}
			}
		}

		foreach($arr as $v){
			$db->query("UPDATE watkins_track SET completed='1' WHERE id='".$v["id"]."'");
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


function getProHistory($data){
	$data=preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $data);

	$regex1 =	"/Pro History<br>(.+)<br><br><br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$records = explode("<br>",$match1[0][1]);
		if($records){
			return implode("\n",$records);
		}
	}
}

function getStatusDelivered($data){
	$data=preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $data);

	$regex1 =   "/Status: (.+)&nbsp;<br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$status = $match1[0][1];
	}

	$regex1 =   "/Delivered Date: (.+)<br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$del_date = $match1[0][1];
	}

	$regex1 =   "/Delivered Time: (.+)<br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$del_time = $match1[0][1];
	}

	$regex1 =   "/Signed For By: (.+)<br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$signed_by = $match1[0][1];
	}
	return array($status,$del_date,$del_time,$signed_by);
}

function getStatusLate($data){
	$data=preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $data);

	$regex1 =   "/Status: (.+)&nbsp;<br>/Uis";
	preg_match_all($regex1,$data,$match1,PREG_SET_ORDER);
	if($match1[0][1]){
		$status = $match1[0][1];
	}
	return array($status);
}

function getTrackData($pro){
	$GET = "/autosub/a_trac_pro_dtl.asp?pro=".$pro;

	$fp = fsockopen("www.watkins.com", 80, $errno, $errstr, 30);
	if($fp){
		$req = "GET $GET HTTP/1.1\r\n";
		$req .= "Accept: */*\r\n";
		$req .= "User-Agent: TrollCompany 1.0\r\n";
		$req .= "Host: www.watkins.com\r\n";
		$req .= "Connection: Close\r\n\r\n";

		fwrite($fp, $req);
		while(!feof($fp)) {
		   $rets[] = fgets($fp, 4096);
		}
		fclose($fp);
		if($rets){
			$empty=false;
			foreach($rets as $v){
				if(trim($v) && $empty==true){
					$ret.=$v;
				}
				if(!trim($v)){
					$empty=true;
				}
			}

			return $ret;
		}
	}
}

?>