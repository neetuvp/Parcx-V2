<?php 
require_once('../../includes/common.php');
include('../../classes/test.php');
$obj=new test();

$json= file_get_contents("php://input");    
$data=json_decode($json,TRUE); 
$task=$data['task'];

switch($task)
    {
    case 1:
        $obj->create_plate_captured_send_to_device($data);
        break;
     case 2:
        $obj->show_plates();
        break;
    case 3:
        $obj->plate_capture_test($data);
        break;
   
    }

?>
