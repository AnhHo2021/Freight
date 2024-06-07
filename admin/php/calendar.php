<?php
switch($action){
	case "update":
		if(!empty($_POST["description"])) {
			$sql = "INSERT INTO calendar SET
			date_created = NOW(),
			created_admin_login_id = '$_LOGIN[id]',
			date = '$_POST[date]',
			admin_login_id = '$_POST[for]',
			description = '$_POST[description]'";
			$success = $db->query($sql);
		}
		$_GET["date"] = $_POST["date"];
	case "add":
		//GETTING "FOR" FIELD
		$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
		$admins = $db->query($sql);
		$vars["for_options"] = "";
		foreach($admins AS $v) {
			$s=($_LOGIN["id"] == $v["id"])?" selected":"";
			$vars["for_options"] .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}
		$vars["date"] = $_GET["date"];
		$vars["ym"] = substr($_GET["date"],0,7);
		$html["BODY"]=replace($vars,rf($htmlpath."calendar_add.html"));
		break;
	default:
	$calendar_view_logins = array("0",$_LOGIN["id"]);
	$filter_display = $_LOGIN["id"];
	if($_LOGIN["access_level"] == "admin") {
		if(isset($_GET["filter"])) {
			$calendar_view_logins = array("0",$_GET["filter"]);
			$filter_display = $_GET["filter"];
		}
		//SETTING FILTER OPTIONS.
		$sql = "SELECT id, access_level, fname, lname FROM admin_login WHERE removed = '0' ORDER BY lname";
		$admins = $db->query($sql);
		foreach($admins AS $v) {
			$s=($filter_display == $v["id"]) ? " selected":"";
			$filter_options .= "<option value='$v[id]'$s>$v[lname], $v[fname] ($v[access_level])</option>\n";
		}
		$vars["admin_filter"] = "<br>Filter : <form method='GET'><input type='hidden' name='ym' value='$_GET[ym]'><input type='hidden' name='action' value='$_GET[action]'><select name='filter'>$filter_options</select><input type='submit' value='go'></form><br>";
	} else {
		$vars["admin_filter"] = "";
	}
	$days=array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	$vars["CALENDAR"]='<table cellpadding=3 border=1 bordercolor=black>';
		$ym=($ym)?$ym:date("Y-m");
		$dow=date("w",mktime(0,0,0,substr($ym,5,2),1,substr($ym,0,4)));
		if($dow > 0){
			for($x=0;$x<$dow;$x++){
				$events[]="";
			}
		}
		$day=$ym."-01";
		while(substr($day,0,7)==$ym){
			list($y,$m,$d)=explode("-",$day);
			$events[]=$day;
			$display[$day]="";
			$day=date("Y-m-d",mktime(0,0,0,$m,$d+1,$y));
		}
		$vars["CALENDAR"].='<tr>';
		$vars["CALENDAR"].='<td height=50 align=center colspan=7><big><b><a href="/?filter='.$filter_display.'&action=calendar&ym='.date("Y-m",mktime(0,0,0,$m-1,1,$y)).'"><<</a> &nbsp;'.date("M Y",strtotime($y."-".$m."-01")).'&nbsp; <a href="/?filter='.$filter_display.'&action=calendar&ym='.date("Y-m",mktime(0,0,0,$m+1,1,$y)).'">>></a></b></big></td>';
		$vars["CALENDAR"].='</tr>';
		$vars["CALENDAR"].='<tr bgcolor=black>';
		for($x=0;$x<7;$x++){
			$vars["CALENDAR"].='<td align=center width=100><b><font color=white>'.$days[$x].'</font></b></td>';
		}
		$vars["CALENDAR"].='</tr>';
		$sql = "SELECT * FROM calendar WHERE date LIKE '$ym%' AND admin_login_id IN (".implode(",",$calendar_view_logins).")";
		$items=$db->query($sql);
		if($items){
			foreach($items as $v){
				if($display[$v["date"]] != ""){$display[$v["date"]].="<hr style=\"color:black;height:1px\">";}
				if($v["link"]){$v["description"]='<a href="'.$v["link"].'"><u>'.$v["description"].'</u></a>';}
				$display[$v["date"]].="<li>".$v["description"]."</li>";
			}
		}	
	
		$vars["CALENDAR"].='<tr>';
		foreach($events as $n=>$day){
			if($n > 0 && $n % 7 == 0){$vars["CALENDAR"].='</tr><tr>';}
			if($day){
				$bg="#FFFFFF";
				if($day==date("Y-m-d")){$bg="#91918e";}
				if(str_replace("-","",$day) < date("Ymd")){$bg="#E2E2E2";}
				$vars["CALENDAR"].='<td bgcolor="'.$bg.'" valign=top><table border=0 cellpadding=0 cellspacing=0 width=100><tr><td valign=top align=left><b>'.date("j",strtotime($day)).')</b></td><td align=right><a href="javascript:addEvent(\''.$day.'\')"><img src="images/icon-add.gif" border="0"></a></td></tr><td colspan=2 valign=top height=84><ul>'.$display[$day].'</ul></td></tr></table></td>';
			}else{
				$vars["CALENDAR"].='<td></td>';
			}
		}
		$vars["CALENDAR"].='</tr>';	
		
		
	$vars["CALENDAR"].='</table>';
	$html["BODY"]=replace($vars,rf($htmlpath."calendar.html"));
}


?>