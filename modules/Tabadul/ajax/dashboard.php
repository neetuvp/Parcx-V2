<?php 
 
$task=$_GET['task'];
include('../classes/dashboard.php');
include('../classes/general.php');
$dashboard=new dashboard();
$general=new General_Operations();

switch($task)
{
case 1:   // device block view
    $dashboard->get_device_status_block_view();
    break;
case 2:
    $data_obj=json_decode(file_get_contents("php://input"));
    $device_number=$data_obj->{'device_number'};
    $dashboard->report_watchdog_device_log($device_number);
    break; 
case 3:
    $json= file_get_contents("php://input");  
    $data_obj=json_decode(file_get_contents("php://input"));
    $description=$data_obj->{'description'};
    $movement_type=$data_obj->{'movement_type'};   
    $reason=$data_obj->{'reason'}; 
    $device_number=$data_obj->{'device_number'};
    $device_ip=$data_obj->{'device_ip'};
    $device_name=$data_obj->{'device_name'};
    $carpark_number=$data_obj->{'carpark_number'};
    $task=$data_obj->{'task'};
    $device_type=$data_obj->{'device_type'};

    $result=1;
    $general->SendMessageToPort($task,$device_ip);
   	
    // $message=sendDataToPort($device_ip,8091,$task);    
    // if($message!="Error")  
    // {
    //     $result=InsertManualMovements($description,$movement_type,$reason,$device_number,$device_name,$carpark_number,$_SESSION['user']);
    // }
    $dashboard->log_manual_movement($json,$reason,$device_number,$device_name);
        
    echo $result;
    break;
case 4:
    $data_obj=json_decode(file_get_contents("php://input"));
    $device_number=$data_obj->{'device_number'};
    $dashboard->device_details($device_number);
        break;
case 5:$response=$dashboard->hourly_entry();
        echo json_encode($response);
    break;
case 6:$response=$dashboard->hourly_entry_avg($_GET['type']);
    echo json_encode($response);
break;
} // End Switch 

?>
