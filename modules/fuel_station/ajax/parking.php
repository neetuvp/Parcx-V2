<?php 
include('../classes/parking.php');
$json= file_get_contents("php://input");
$parking=new parking();
$json_data = json_decode($json,true);
$task=$json_data["task"];
switch($task)
    {
    case 1:  
        $parking->get_transaction_details($json_data);
        break;
    case 2:  
        $parking->transaction_report_live();
        break;
    case 3:
        $parking->get_daily_report($json_data);
        break;
    case 4:$parking->get_access_request_report($json_data);
        break;
    case 5:$parking->transaction_report($json_data);
        break;
    case 6:  
        $parking->show_pump_details();
        break;
    case 7:$parking->get_pump_details();
        break;
    case 8:$parking->get_pump_status();
        break;    
    case 9:$parking->get_interfaces();
        break;
    

    } // End Switch 

?>
