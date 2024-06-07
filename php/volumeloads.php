<?php
$vars["loads"] = '';

$sql = "SELECT o_city, o_state, d_city, d_state, pallets, space, weight, rate, partner
    FROM volume_loads ORDER BY partner";
$volume_loads = $db->query($sql);
if($volume_loads)
{
    $count = 0;
    foreach ($volume_loads as $v)
    {
        $vars["loads"] .= '<tr bgcolor='.($count % 2 == 0 ? 'white' : '#ec3027').'>';
        $vars["loads"] .= '<td>'.$v["o_city"].', '.$v["o_state"].'</td>';
        $vars["loads"] .= '<td>'.$v["d_city"].', '.$v["d_state"].'</td>';
        $vars["loads"] .= '<td>'.$v["pallets"].'</td>';
        $vars["loads"] .= '<td>'.$v["space"].'</td>';
        $vars["loads"] .= '<td>'.number_format($v["weight"]).' lbs</td>';
        $vars["loads"] .= '<td>$'.number_format($v["rate"], 2).'</td>';
        $vars["loads"] .= '<td>'.$v["partner"].'</td>';
        $vars["loads"] .= '</tr>';
        $count++;
    }
}

?>
