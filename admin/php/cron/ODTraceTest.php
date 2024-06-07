<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// grabs tracking updates from odfl.com
require_once 'TraceService.php';
$trace = new TraceService();
$traceResult = getTraceData(8215225254);
print_r($traceResult);

function getTraceData($pro){
    global $trace;

    $traceData = new getTraceData();
    $traceData->pro = $pro;
    $traceData->type = 'P';

    $response = $trace->getTraceData($traceData);
    return $response->getTraceDataReturn;
}
?>
