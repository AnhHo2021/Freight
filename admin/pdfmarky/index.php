<?

$vars="";
if($file){
	$f=file($file);
	foreach($f as $v){
		list($varname,$value)=explode("=",trim($v));
		${$varname}=$value;
		$vars_arr[]="$varname=$value";
	}
	$vars=implode("&",$vars_arr);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>marky</title>
</head>
<body bgcolor="#ffffff">
<!--url's used in the movie-->
<!--text used in the movie-->
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="1100" height="1100" id="marky" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="marky.swf?<?=$vars?>" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<embed src="marky.swf?<?=$vars?>" quality="high" bgcolor="#ffffff" width="1100" height="1100" name="marky" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</body>
</html>
