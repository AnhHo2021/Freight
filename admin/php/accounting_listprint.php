<?php
//print_r($_POST);
//break;

if(!empty($_POST)) {
	foreach($_POST AS $k=>$pv) {
		if(strstr($k,"print")) {
			$k = explode("__",$k);
			$print_array[] = $k[1];
		}
	}

	if($print_array){
		if(count($print_array)==1){
			header("Location: index.php?action=invoices_print&id=".$print_array[0]);
			die();
		}else{
			$ids=implode(",",$print_array);
			header("Location: index.php?action=invoices_print-main&ids=$ids");	
			die();
		}
	}
} elseif(isset($_GET["id"])) {
	header("Location:index.php?action=invoices_print&id=".$_GET["id"]);
	die();
}

$page = ($action == "listview") ? "listview" : "ageview";
echo"
<script>
function closeWindow(){
	window.opener.location.href='index.php?action=accounting_$page&noneselected';
	window.close();
}
</script>
<body onLoad='closeWindow()'>
</body>
";

?>