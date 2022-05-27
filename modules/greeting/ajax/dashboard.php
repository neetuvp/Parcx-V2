<?php 
ini_set("display_errors",0);
$task=$_GET['task'];
include('../classes/dashboard.php');
$dashboard=new dashboard();
$json= file_get_contents("php://input");
switch($task)
{
    case 1:   // interface_request
        $dashboard->interface_request_dashboard($json);
        break;
    case 2:   // interface_request_detail
        $dashboard->interface_request_detail($json);
        break;
    case 3:   
        $dashboard->get_controller_devices($json);
        break;
} // End Switch 

?>