#!/usr/bin/php -q
<?php
set_time_limit(2700);

// grabs tracking updates from odfl.com
require_once '/mnt/web/transport/transport/freight/admin/php/cron/TraceService.php';
$trace = new TraceService();

include("/opt/www/htdocs/admin.norwayfreight.com/php/db.php");
$db = new DB("root","troll6");

// allowed for updates
$allowed_status = array('tobescheduled','scheduled','intransit','outfordelivery',
    'problem');

// update log table
$shipments = $db->query("SELECT id,status,shipped_tracknum,shipped_carrier_pronum
    FROM shipment WHERE shipped='1' AND shipped_carrier_id='125' AND status IN
    ('".implode("','",$allowed_status)."') AND shipped_carrier_pronum != ''");
if($shipments){
	foreach($shipments as $v){
		$traceResult = getTraceData($v["shipped_carrier_pronum"]);
		if($traceResult->errorMessage == '')
        {
            //print_r($traceResult);

            $pro_history = $traceResult->status;
			if($pro_history){
				$add = true;
				$cur_history = $db->query("SELECT pro_history
                        FROM odfl_track WHERE shipment_id='$v[id]'
                        ORDER BY date_request DESC LIMIT 1");

				if($cur_history){
                    $ext_history = explode(" ", $cur_history[0]["pro_history"], 4);
                    if(md5($ext_history[3]) == md5($pro_history))
                    {
                        $add = false;
                    }
				}

				if($add){
                    $d_date = date("Y-m-d");
                    $d_time = date("H:i:s");
					$db->query("INSERT INTO odfl_track SET shipment_id='$v[id]',
                            date_request=NOW(),pro_history='".
                            date("Y-m-d H:i:s")." - ".$pro_history."'");
				}
			}

			// see if it is delivered
            if($traceResult->statusCode == 'DEL' && $traceResult->delivered =='Y')
            {
                $pro_date = $traceResult->proDate;
                if($pro_date == '')
                {
                    $pro_date = date("Y-m-d");
                }
                $pro_date = date("Y-m-d", strtotime($pro_date));
                $pro_time = date("H:i:s");
                $sql = "INSERT INTO shipment_tracking SET shipment_id='$v[id]',
                    date_created=NOW(),date_show='".date("Y-m-d H:i:s",
//                  strtotime($pro_date." ".$pro_time))."',tracking='DELIVERED TO: ".mysql_real_escape_string($traceResult->signature)."'";
					strtotime($pro_date." ".$pro_time))."',tracking='DELIVERED TO: ".mysqli_real_escape_string($db->conn,$traceResult->signature)."'";
				$db->query($sql);

				$sql = "UPDATE shipment SET status='delivered' WHERE id='$v[id]'";
				$db->query($sql);
            }
		}
	}
}

// save tracking updates
$odfl_track = $db->query("SELECT * FROM odfl_track WHERE completed='0' ORDER BY date_request DESC");
if($odfl_track){
	foreach($odfl_track as $v){
		$track_updates[$v["shipment_id"]][]=$v;
		$shipment_ids[$v["shipment_id"]]=$v["shipment_id"];
	}

	// past saved
	$completed = $db->query("SELECT * FROM odfl_track WHERE shipment_id IN (".implode(",",$shipment_ids).") AND completed='1' ORDER BY date_request DESC");
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
			$db->query("UPDATE odfl_track SET completed='1' WHERE id='".$v["id"]."'");
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

function getTraceData($pro){
    global $trace;

    $traceData = new getTraceData();
    $traceData->pro = $pro;
    $traceData->type = 'P';

    $response = $trace->getTraceData($traceData);
    return $response->getTraceDataReturn;
}
?>
