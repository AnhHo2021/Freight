<?php
switch($action){
	case "print-top":
	case "print-main":
	case "print":
		include("invoices_print.php");
		break;
	break;
}
?>
