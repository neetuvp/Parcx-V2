<?php

require_once '../classes/user.php';
$obj=new User();
$json= file_get_contents("php://input");
$case=$_GET["type"];
switch($case)
{
    case 1:
        $response=$obj->login($json);        
        break;  
    case 2:
        $response=$obj->get_active_users($json);
        break;
    case 3:
        $response=$obj->get_disabled_users($json);
        break;
    case 4:
        $response=$obj->add_user($json);
        break;
    case 5:
        $response=$obj->enable_user($json);
        break;
    case 6:
        $response=$obj->disable_user($json);
        break;
    case 7:
        $response=$obj->change_password($json);
        break;
    case 8:
        $response=$obj->update_user($json);
        break;
}
echo $response;
?>

