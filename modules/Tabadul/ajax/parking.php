<?php 
 
$task=$_GET['task'];
include('../classes/parking.php');
include('../classes/general.php');
$json= file_get_contents("php://input");
$parking=new parking();
$general=new General_Operations();
switch($task)
{

case 1:   // barrier action
    $parking->report_barrier_action($json);
    break;
case 2:
    $parking->report_plates_captured($json);
    break;
case 3:
    $parking->report_access_request($json);  
    break;
case 4:
    $parking->report_access_request_modal($json);
    break;
case 5:
    $parking->report_barrier_action_modal($json);
    break;
case 6:
    $json=json_decode($json);
    $response=clearDisplayToVms($json->{'device'});
    echo $response;
    break;
case 7:
    $json=json_decode($json);
    $response=sendStringToVms($json->{'device'},$json->{'line1'},$json->{'line2'},$json->{'line3'},$json->{'line4'},$json->{'colour'});
    if($response)
        echo "Data send to vms";
    else
        echo "Cant send data to vms";
    break;
case 8:
    $parking->report_watchdog_device_log($json);
    break;
case 9:
    $parking->get_anpr_image_accessrequest($json);
    break;
case 10:
    $parking->get_anpr_image_platescaptured($json);
    break;
case 11:
    $parking->add_update_access_whitelist($json);
    break;
case 12:
    $parking->get_details($json);
    break;
case 13:
    $parking->access_request_live();
    break;
case 14:
    $parking->get_access_request_details($json);
    break;
case 15:
    $parking->get_secondary_plate_details($json);
    break;
case 16:
    $result=$parking->update_plate_captured($json);    
    if($result!="0")                
        $general->SendMessageToPort($result);  
    echo $result;      
    break;

} // End Switch 

?>
