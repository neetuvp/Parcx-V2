<?php
$json= file_get_contents("php://input");
$case=$_GET["type"];
$data=json_decode($json,true);
$response = "";
switch($case)
{
    case 1:  
    {
        $id = "";
        $response = GetVMSSettings("");
        break;
    }
    case 2:  
    {
        
        $response = json_encode(EditVMSSettings($data));
        break;
    }
    case 3:
    {
        $response = PostVMSSettings($data);
        break;
    }
    
}
echo $response;
?>