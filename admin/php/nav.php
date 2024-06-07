<?php

ob_start();
if($login){
    switch($_LOGIN["access_level"]){
        case "admin":
            ?>
            <a href="index.php?action=home" class='toptab_<?=(($page_action=="home")?"on":"off")?>'>Home</a>&nbsp;
            <a href="index.php?action=calendar" class='toptab_<?=(($page_action=="calendar")?"on":"off")?>'>Calendar</a>&nbsp;
            <a href="index.php?action=customers" class='toptab_<?=(($page_action=="customers")?"on":"off")?>'>Customers</a>&nbsp;
            <a href="index.php?action=quotes" class='toptab_<?=(($page_action=="quotes")?"on":"off")?>'>Quotes</a>&nbsp;
            <a href="index.php?action=shipments" class='toptab_<?=(($page_action=="shipments")?"on":"off")?>'>Shipments</a>&nbsp;
            <a href="index.php?action=accounting" class='toptab_<?=(($page_action=="accounting")?"on":"off")?>'>Accounting</a>&nbsp;
            <a href="index.php?action=reports" class='toptab_<?=(($page_action=="reports")?"on":"off")?>'>Reports</a>&nbsp;
            <a href="index.php?action=marketing" class='toptab_<?=(($page_action=="marketing")?"on":"off")?>'>Marketing</a>&nbsp;
            <a href="index.php?action=carriers" class='toptab_<?=(($page_action=="carriers")?"on":"off")?>'>Carriers</a>&nbsp;
            <a href="index.php?action=settings" class='toptab_<?=(($page_action=="settings")?"on":"off")?>'>Settings</a>&nbsp;
            <a href="index.php?action=volumeloads" class='toptab_<?=(($page_action=="volumeloads")?"on":"off")?>'>Sales</a>
            <?php
            break;
        case "sales":
            ?>
            <a href="index.php?action=home" class='toptab_<?=(($page_action=="home")?"on":"off")?>'>Home</a>&nbsp;
            <a href="index.php?action=calendar" class='toptab_<?=(($page_action=="calendar")?"on":"off")?>'>Calendar</a>&nbsp;
            <a href="index.php?action=customers" class='toptab_<?=(($page_action=="customers")?"on":"off")?>'>Customers</a>&nbsp;
            <a href="index.php?action=quotes" class='toptab_<?=(($page_action=="quotes")?"on":"off")?>'>Quotes</a>&nbsp;
            <a href="index.php?action=shipments" class='toptab_<?=(($page_action=="shipments")?"on":"off")?>'>Shipments</a>&nbsp;
            <a href="index.php?action=reports" class='toptab_<?=(($page_action=="reports")?"on":"off")?>'>Reports</a>&nbsp;
            <a href="index.php?action=carriers" class='toptab_<?=(($page_action=="carriers")?"on":"off")?>'>Carriers</a>
            <?php
            break;
    }
}else{echo "";}
$buffer = ob_get_contents();
$html["TOP_NAV"]=$buffer;
ob_end_clean();



ob_start();
if($login){
    switch($_LOGIN["access_level"]){
        case "admin":
            switch($page_action){
                case "settings":
                    ?>
                    <a href="index.php?action=settings_logins" class='subtab_<?=(($full_action=="settings_logins")?"on":"off")?>'>Login List</a> |
                    <a href="index.php?action=settings_logins_edit" class='subtab_<?=(($full_action=="settings_logins_edit")?"on":"off")?>'>Add Login</a>
                    <?php
                    break;
                case "customers":
                    ?>
                    <a href="index.php?action=customers_list" class='subtab_<?=(($full_action=="customers_list" || $full_action == "customers")?"on":"off")?>'>Customer List</a> |
                    <a href="index.php?action=customers_edit" class='subtab_<?=(($full_action=="customers_edit")?"on":"off")?>'>Add Customer</a> |
                    <a href="index.php?action=customers_defaults" class='subtab_<?=(($full_action=="customers_defaults" || $full_action == "customers_defaultsprocess")?"on":"off")?>'>Edit Customer Quote Defaults</a> |
                    <a href="index.php?action=customers_prodbook" class='subtab_<?=(($full_action=="customers_prodbook")?"on":"off")?>'>Edit Customer Product Books</a>
                    <?php
                    break;
                case "carriers":
                    ?>
                    <a href="index.php?action=carriers_list" class='subtab_<?=(($full_action=="carriers_list" || $full_action == "carriers")?"on":"off")?>'>Carrier List</a> |
                    <a href="index.php?action=carriers_edit" class='subtab_<?=(($full_action=="carriers_edit")?"on":"off")?>'>Add Carrier</a>
                    <?php
                    break;
                case "quotes":
                    ?>
                    <a href="index.php?action=quotes_list" class='subtab_<?=(($full_action=="quotes_list" || $full_action == "quotes")?"on":"off")?>'>Quotes List</a> |
                    <a href="index.php?action=quotes_edit" class='subtab_<?=(($full_action=="quotes_edit")?"on":"off")?>'>Add Quote</a>
                    <?php
                    break;
                case "shipments":
                    ?>
                    <a href="index.php?action=shipments_list" class='subtab_<?=(($full_action=="shipments_list" || $full_action == "shipments")?"on":"off")?>'>Shipments List</a>
                    <?php
                    break;
                case "accounting":
                    ?>
                    <a href="index.php?action=accounting_tobeinvoiced" class='subtab_<?=(($full_action=="accounting_tobeinvoiced" || $full_action == "accounting")?"on":"off")?>'>To Be Invoiced</a>
                    <a href="index.php?action=accounting_listview" class='subtab_<?=(($full_action=="accounting_listview")?"on":"off")?>'>List View</a>
                    <a href="index.php?action=accounting_ageview" class='subtab_<?=(($full_action=="accounting_ageview")?"on":"off")?>'>Age View</a>
                    <a href="index.php?action=accounting_customerbalances" class='subtab_<?=(($full_action=="accounting_customerbalances")?"on":"off")?>'>Customer Balances</a>
                    <a href="index.php?action=accounting_payments" class='subtab_<?=(($full_action=="accounting_payments")?"on":"off")?>'>Receive Payments</a>
                    <a href="index.php?action=accounting_billing" class='subtab_<?=(($full_action=="accounting_billing")?"on":"off")?>'>Billing Report</a>
                    <?php
                    break;
                case "reports":
                    ?>
                    <a href="index.php?action=reports_snapshot" class='subtab_<?=(($full_action=="reports_snapshot" || $full_action == "reports")?"on":"off")?>'>Company Snapshot</a>
                    <a href="index.php?action=reports_ardetail" class='subtab_<?=(($full_action=="reports_ardetail")?"on":"off")?>'>A/R Detail</a>
                    <a href="index.php?action=reports_revenue" class='subtab_<?=(($full_action=="reports_revenue")?"on":"off")?>'>Revenue</a>
                    <a href="index.php?action=reports_carriervolume" class='subtab_<?=(($full_action=="reports_carriervolume")?"on":"off")?>'>Carrier Volume</a>
                    <a href="index.php?action=reports_quoteship" class='subtab_<?=(($full_action=="reports_quoteship")?"on":"off")?>'>Quotes vs Shipments</a>
                    <a href="index.php?action=reports_shipmentservices" class='subtab_<?=(($full_action=="reports_shipmentservices")?"on":"off")?>'>Shipment Services</a>
                    <a href="index.php?action=reports_customerlist" class='subtab_<?=(($full_action=="reports_customerlist")?"on":"off")?>'>Customer List</a>
                    <?php
                    break;
            }
            break;
        case "sales":
            switch($page_action){
                case "customers":
                    ?>
                    <a href="index.php?action=customers_list" class='subtab_<?=(($full_action=="customers_list" || $full_action == "customers")?"on":"off")?>'>Customer List</a> |
                    <a href="index.php?action=customers_edit" class='subtab_<?=(($full_action=="customers_edit")?"on":"off")?>'>Add Customer</a> |
                    <a href="index.php?action=customers_defaults" class='subtab_<?=(($full_action=="customers_defaults" || $full_action == "customers_defaultsprocess")?"on":"off")?>'>Edit Customer Quote Defaults</a> |
                    <a href="index.php?action=customers_prodbook" class='subtab_<?=(($full_action=="customers_prodbook")?"on":"off")?>'>Edit Customer Product Books</a>
                    <?php
                    break;
                case "carriers":
                    ?>
                    <a href="index.php?action=carriers_list" class='subtab_<?=(($full_action=="carriers_list" || $full_action == "carriers")?"on":"off")?>'>Carrier List</a> |
                    <a href="index.php?action=carriers_edit" class='subtab_<?=(($full_action=="carriers_edit")?"on":"off")?>'>Add Carrier</a>
                    <?php
                    break;
                case "quotes":
                    ?>
                    <a href="index.php?action=quotes_list" class='subtab_<?=(($full_action=="quotes_list" || $full_action == "quotes")?"on":"off")?>'>Quotes List</a> |
                    <a href="index.php?action=quotes_edit" class='subtab_<?=(($full_action=="quotes_edit")?"on":"off")?>'>Add Quote</a>
                    <?php
                    break;
                case "shipments":
                    ?>
                    <a href="index.php?action=shipments_list" class='subtab_<?=(($full_action=="shipments_list" || $full_action == "shipments")?"on":"off")?>'>Shipments List</a>
                    <?php
                    break;
                case "reports":
                    ?>
                    <a href="index.php?action=reports_revenue" class='subtab_<?=(($full_action=="reports_revenue")?"on":"off")?>'>Revenue</a>
                    <a href="index.php?action=reports_quoteship" class='subtab_<?=(($full_action=="reports_quoteship")?"on":"off")?>'>Quotes vs Shipments</a>
                    <a href="index.php?action=reports_customerlist" class='subtab_<?=(($full_action=="reports_customerlist")?"on":"off")?>'>Customer List</a>
                    <?php
                    break;
            }
            break;
    }
}else{echo "";}
$buffer = ob_get_contents();
$html["SUB_NAV"]=$buffer;
ob_end_clean();


?>