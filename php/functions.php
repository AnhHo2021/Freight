<?php
function setItem($name,$value,$align='C'){
	global $pdf,$debug;

	$itemPos = getItemPos($name);
	if($itemPos){
		$x=$itemPos["x"]/100;
		$y=$itemPos["y"]/100;
		$w=$itemPos["w"]/100;
		$h=.15;

		//debug for positioning
		if($debug){
			$pdf->SetDrawColor(51,255,51);
			$pdf->SetXY($x,$y);
			$pdf->Cell($w,$itemPos["h"]/100,"",1,0,'C',0,'');
		}

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
	global $itemArr;

	foreach($itemArr as $myv){
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

function sysTime($time){
	return date("g:i a",strtotime(date("Y-m-d")." ".$time));
}


function sysDate($date){
	if($date){return date("m/d/Y",strtotime($date));}
}

function sysDateTime($date){
	if($date){return date("m/d/Y g:i a",strtotime($date));}
}


function getFields($table){
	global $db;

	$ret = array();

//	$tbl = mysql_query("SHOW COLUMNS FROM $table");
	$tbl = mysqli_query($db->conn, "SHOW COLUMNS FROM $table");
	if($tbl){
//		while($row=mysql_fetch_array($tbl)) {
		while($row=mysqli_fetch_array($tbl)) {
			$ret[$row["Field"]]=$_POST[$row["Field"]];
		}
	}

	return $ret;
}

function doResultTable($array,$html,$bg1="#E2E2E2",$bg2="#FCF4C3",$params=""){
	if($array && count($array)){
		$html = preg_replace('/<!--noresult_start-->.*?<!--noresult_end-->/si',"",$html);
		preg_match('/(<!--result_start-->.*?<!--result_end-->)/si',$html,$matches);
		if($matches[1]){
			$result = $matches[1];
			$html = preg_replace('/<!--result_start-->.*?<!--result_end-->/si',$result,$html);

			preg_match('/(<!--row_start-->.*?<!--row_end-->)/si',$html,$matches);
			if($matches[1]){
				$row = $matches[1];
				foreach($array as $v){
					$row=str_replace("$","DOLLAR_SIGN",$row);

					$bg1_tmp = ($v["avg_daily_color"])?$v["avg_daily_color"]:"";
					if($bg1_tmp){
						$v["bg"]=$bg1_tmp;
					}else{
						$v["bg"]=($x++%2)?$bg1:$bg2;
					}

					if($params=="skipgroupcol"){
						if($y++ > 0){$v["group_name"]="";}
					}

					$rows .= replace($v,$row);
				}
				$html = preg_replace('/<!--row_start-->.*?<!--row_end-->/si',$rows,$html);
				$html = str_replace("DOLLAR_SIGN","$",$html);
			}
		}
	}else{
		$html = preg_replace('/<!--result_start-->.*?<!--result_end-->/si',"",$html);
		preg_match('/(<!--noresult_start-->.*?<!--result_noend-->)/si',$html,$matches);
		if($matches[1]){
			$noresult = $matches[1];
			$html = preg_replace('/<!--noresult_start-->.*?<!--noresult_end-->/si',$noresult,$html);
		}
	}
	return $html;
}

function rf($file){
	if(!is_file($file)){
		fwrite($fp = fopen($file,"w"),basename($file));
		fclose($fp);
		chmod($file,0775);
	}
	return implode("",file($file));
}

function replace($array,$str){
	if(is_array($array)){
		foreach($array as $n=>$v){
			$search[]="[$n]";
			$replace[]="$v";
		}
		return str_replace($search,$replace,$str);
	}else{
		return $str;
	}
}

function removeFromQS($QUERY_STRING,$var){
	$vars=explode("&",$QUERY_STRING);
	if($vars){
		foreach($vars as $val){
			list($name,$value)=explode("=",$val);
			if(is_array($var)){
				if(!in_array($name,$var)){
					$newQS[]="$name=$value";
				}
			}else{
				if($name != $var){
					$newQS[]="$name=$value";
				}
			}
		}
	}
	if($newQS){
		$newQUERY_STRING=implode("&",$newQS);
	}else{
		$newQUERY_STRING=$QUERY_STRING;
	}

	return $newQUERY_STRING;
}


function getEnum($table,$col){
	global $db;
	$options=array();

//	$result=mysql_query("SHOW COLUMNS FROM $table LIKE '$col'");
	$result=mysqli_query($db->conn, "SHOW COLUMNS FROM $table LIKE '$col'");
//	if(mysql_num_rows($result)>0){
	if(mysqli_num_rows($result)>0){
//		$row=mysql_fetch_row($result);
		$row=mysqli_fetch_row($result);
		$options=explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
	}
	return $options;
}
function datediff($interval, $date1, $date2) {
   // Function roughly equivalent to the ASP "DateDiff" function

   //convert the dates into timestamps
  $date1 = strtotime($date1);
  $date2 = strtotime($date2);
  $seconds = $date2 - $date1;

   //if $date1 > $date2
   //change substraction order
   //convert the diff to +ve integer
   if ($seconds < 0)
   {
           $tmp = $date1;
           $date1 = $date2;
           $date2 = $tmp;
           $seconds = 0-$seconds;
   }

   //reconvert the timestamps into dates
   if ($interval =='y' || $interval=='m') {
       $date1 = date("Y-m-d h:i:s", $date1);
       $date2=  date("Y-m-d h:i:s", $date2);
   }

   switch($interval) {
       case "y":
           list($year1, $month1, $day1) = split('-', $date1);
           list($year2, $month2, $day2) = split('-', $date2);
           $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
           $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
           $diff = $year2 - $year1;

           if($month1 > $month2) {
               $diff -= 1;
           } elseif($month1 == $month2) {
               if($day1 > $day2) {
                   $diff -= 1;
               } elseif($day1 == $day2) {
                   if($time1 > $time2) {
                       $diff -= 1;
                   }
               }
           }
           break;
       case "m":
           list($year1, $month1, $day1) = split('-', $date1);
           list($year2, $month2, $day2) = split('-',$date2);
           $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
           $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
           $diff = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
           if($day1 > $day2) {
               $diff -= 1;
           } elseif($day1 == $day2) {
               if($time1 > $time2) {
                   $diff -= 1;
               }
           }
           break;
       case "w":
           // Only simple seconds calculation needed from here on
           $diff = floor($seconds / 604800);
           break;
       case "d":
           $diff = floor($seconds / 86400);
           break;
       case "h":
           $diff = floor($seconds / 3600);
           break;
       case "i":
           $diff = floor($seconds / 60);
           break;
       case "s":
           $diff = $seconds;
           break;
   }

   return $diff;
}
function lastday($month, $year){
   return date( 'd', mktime( 0, 0, 0, $month + 1, 0, $year ) );
}
?>