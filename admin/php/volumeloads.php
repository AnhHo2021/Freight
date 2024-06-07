<?php
$html["LOCATION"] = "<h1>SALES</h1>";

$vars["loads"] = '';
$count = 0;
switch($action)
{
    case "edit":
        // Find out is this is a single update.
        $single = FALSE;
        foreach($_POST as $key=>$value)
        {
            if(strpos($key, "update") !== FALSE)
            {
                // Get the load id from the key name
                $pieces = explode("_", $key);
                $vload_id = $pieces[1];
                $single = TRUE;
                $sql = sprintf("UPDATE volume_loads SET o_city = '%s', o_state = '%s', d_city = '%s',
                    d_state = '%s', pallets = '%s', space = '%s', weight = %d, rate = %f,
                    partner = '%s' WHERE id = %d", mysqli_real_escape_string($db->conn, ${'o_city_'.$vload_id}),
                            ${'o_state_'.$vload_id}, mysqli_real_escape_string($db->conn, ${'d_city_'.$vload_id}),
                            ${'d_state_'.$vload_id}, mysqli_real_escape_string($db->conn, ${'pallets_'.$vload_id}),
                            mysqli_real_escape_string($db->conn, ${'space_'.$vload_id}), (int)str_replace(",","",${'weight_'.$vload_id}), floatval(${'rate_'.$vload_id}),
                            mysqli_real_escape_string($db->conn, ${'partner_'.$vload_id}), (int)$vload_id);
                $success = $db->query($sql);
                break;
            }
        }
        if(!$single)
        {
            // This is a multi-row update. Walk the list of ids and update them all.
            $vload_ids = explode(",",$edit_ids);
            foreach ($vload_ids as $vload_id)
            {
                $sql = sprintf("UPDATE volume_loads SET o_city = '%s', o_state = '%s', d_city = '%s',
                    d_state = '%s', pallets = '%s', space = '%s', weight = %d, rate = %f,
                    partner = '%s' WHERE id = %d", mysqli_real_escape_string($db->conn, ${'o_city_'.$vload_id}),
                            ${'o_state_'.$vload_id}, mysqli_real_escape_string($db->conn, ${'d_city_'.$vload_id}),
                            ${'d_state_'.$vload_id}, mysqli_real_escape_string($db->conn, ${'pallets_'.$vload_id}),
                            mysqli_real_escape_string($db->conn, ${'space_'.$vload_id}), (int)str_replace(",","",${'weight_'.$vload_id}), floatval(${'rate_'.$vload_id}),
                            mysqli_real_escape_string($db->conn, ${'partner_'.$vload_id}), (int)$vload_id);
                $success = $db->query($sql);
            }
        }
        break;
    case "add":
        if(isset ($_POST["o_city"]))
        {
            $sql = sprintf("INSERT INTO volume_loads SET o_city = '%s', o_state = '%s', d_city = '%s',
                    d_state = '%s', pallets = '%s', space = '%s', weight = %d, rate = %f,
                    partner = '%s'", mysqli_real_escape_string($db->conn, $o_city), $o_state, mysqli_real_escape_string($db->conn, $d_city),
                            $d_state, mysqli_real_escape_string($db->conn, $pallets), mysqli_real_escape_string($db->conn, $space),
                            (int)str_replace(",", "", $weight), floatval($rate), mysqli_real_escape_string($db->conn, $partner));
            $success = $db->query($sql);
        }
        break;
}
 
//REMOVE A LOAD.
if(isset($_GET["remove"])) {
    $sql = "DELETE FROM volume_loads WHERE id = $_GET[remove]";
    $success = $db->query($sql);
}

// Get all the current loads.
$ids = array();
$sql = "SELECT id, o_city, o_state, d_city, d_state, pallets, space, weight, rate, partner
    FROM volume_loads ORDER BY partner";
$volume_loads = $db->query($sql);
if($volume_loads)
{
    foreach ($volume_loads as $v)
    {
        $id = $v["id"];
        array_push($ids, $id);

        //GETTING STATES FOR SELECT STATEMENTS.
        getStateCodes($v["o_state"], $v["d_state"]);

        $vars["loads"] .= '<tr bgcolor='.($count++ % 2 == 0 ? $altbg1 : $altbg2).'>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="25%" name="o_city_'.$id.'" value="'.$v["o_city"].'"></td>';
        $vars["loads"] .= '<td NOWRAP><select name="o_state_'.$id.'">'.$vars["o_states"].'</select></td>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="25%" name="d_city_'.$id.'" value="'.$v["d_city"].'"></td>';
        $vars["loads"] .= '<td NOWRAP><select name="d_state_'.$id.'">'.$vars["d_states"].'</select></td>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="15%" name="pallets_'.$id.'" value="'.$v["pallets"].'"></td>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="10%" name="space_'.$id.'" value="'.$v["space"].'"></td>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="10%" name="weight_'.$id.'" value="'.number_format($v["weight"]).'"> lbs</td>';
        $vars["loads"] .= '<td NOWRAP>$<input type="text" size="10%" name="rate_'.$id.'" value="'.number_format($v["rate"], 2).'"></td>';
        $vars["loads"] .= '<td NOWRAP><input type="text" size="20%" name="partner_'.$id.'" value="'.$v["partner"].'"></td>';        
        $vars["loads"] .= '<td NOWRAP><input type="image" name="update_'.$id.'" src="images/icon-edit.gif" border="0" title="Update Load" width="16" height="16"> &nbsp; <a href="javascript:confirm_load_delete('.$id.');"><img src="images/icon-remove.gif" border="0" title="Remove Load" width="16" height="16"></a></td>';
        $vars["loads"] .= '</tr>';
    }

    $vars["ids"] = implode(",", $ids);
    $vars["loads"] .= "<input type='hidden' name='edit_ids' value='".$vars["ids"]."'>";
}
else
{
    $vars["loads"] = '<tr><td colspan="10" align="center">There are no volumes loads in the database. Please add a new load below.</td></tr>';
}

getStateCodes("","");

$vars["new_load"] .= '<tr bgcolor='.($count++ % 2 == 0 ? $altbg1 : $altbg2).'>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="25%" name="o_city" value=""></td>';
$vars["new_load"] .= '<td NOWRAP><select name="o_state">'.$vars["o_states"].'</select></td>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="25%" name="d_city" value=""></td>';
$vars["new_load"] .= '<td NOWRAP><select name="d_state">'.$vars["d_states"].'</select></td>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="15%" name="pallets" value=""></td>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="10%" name="space" value=""></td>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="10%" name="weight" value=""> lbs</td>';
$vars["new_load"] .= '<td NOWRAP>$<input type="text" size="10%" name="rate" value=""></td>';
$vars["new_load"] .= '<td NOWRAP><input type="text" size="20%" name="partner" value=""></td>';
$vars["new_load"] .= '</tr>';



$html["BODY"]=replace($vars,rf($htmlpath."volume_loads.html"));

function getStateCodes($o_state, $d_state) {
    global $db, $vars;

    $sql = "SELECT * FROM state ORDER BY state";
    $states = $db->query($sql);
    $vars["o_states"] = "";
    $vars["d_states"] = "";
    foreach($states AS $state) {
        $s=($o_state == $state["code"])?" selected":"";
        $vars["o_states"] .= "<option value='$state[code]'$s>$state[code]</option>\n";
    }
    foreach($states AS $state) {
        $s=($d_state == $state["code"])?" selected":"";
        $vars["d_states"] .= "<option value='$state[code]'$s>$state[code]</option>\n";
    }
}
?>
