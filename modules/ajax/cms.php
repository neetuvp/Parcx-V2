<?php 
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
session_start();    
$data["request_ip"] = $_SERVER['REMOTE_ADDR'];
$data["login_user_id"] = $_SESSION['userId'];
$data["login_user_name"] = $_SESSION['user'];
/*if(isset($data["activity_message"]))    
    {
    session_start();
    addParcxUserActivity($data["activity_message"],$_SESSION["user"],$_SESSION['userId'],$json);    
    }*/
$response=parcxContentManagement($data);
if(is_array($response))
	echo json_encode($response);
else
	echo $response;
?>
