<?php
if(!empty($_POST)) {
	foreach($_POST AS $k=>$pv) {
		if(strstr($k,"print")) {
			$k = explode("__",$k);
			$print_array[] = $k[1];
		}
	}
	
	if($print_array){
		if(count($print_array)==1){
			header("Location: index.php?action=statements_print&id=".$print_array[0]);
			die();
		}else{
			$ids=implode(",",$print_array);
			header("Location: index.php?action=statements_print-main&ids=$ids");	
			die();
		}
	}
} elseif(isset($_GET["id"])) {
	header("Location: ./?action=statements_print&id=".$_GET["id"]);
	die();
}


echo"
<script>
function closeWindow(){
	window.opener.location.href='index.php?action=accounting_customerbalances';
	window.close();
}
</script>
<body onLoad='closeWindow()'>
</body>
";

?>