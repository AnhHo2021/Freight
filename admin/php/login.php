<?php

$login = false;

// logout
if($action == "logout"){
	setcookie("loginHash","");
	header("Location: index.php");
	die();
}


// check login
if($action == "login" && $username && $password){
    $login_info = $username.$password;
	$loginHash=md5($login_info);
}
if($loginHash){
    //print_r($loginHash); die();
    //print_r($loginHash); die();
    $sql = "SELECT id,fname,lname,access_level FROM admin_login WHERE username ='$username' and password = '$password' AND active='1' LIMIT 1";
	//$sql = "SELECT id,fname,lname,access_level FROM admin_login WHERE MD5(CONCAT(username,password))='$loginHash' AND active='1' LIMIT 1";
    //$sql = "SELECT * FROM norwayfreight.admin_login";

	$login_data = $db->query($sql);
    print_r($login_data); die();
	if($login_data){
		setcookie("loginHash",$loginHash);
		$_LOGIN=$login_data[0];
		$login=true;

		$SALES_REP_FILTER="";
		if($_LOGIN["access_level"] == "sales"){
			$SALES_REP_FILTER=" AND customer.sales_admin_login_id='".$_LOGIN["id"]."' ";
		}

		if($action == "login"){$action = "home";}
	}
}



?>