<?php
include('classes/cms.php');
$obj=new cms();
$json= file_get_contents("php://input");
$obj->update_message($json,$_SERVER['REQUEST_METHOD']);
?>
