<?php 
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
/*if(isset($data["activity_message"]))    
    {
    session_start();
    addParcxUserActivity($data["activity_message"],$_SESSION["user"],$_SESSION['userId'],$json);    
    }*/
$response=parcxV2Settings($data);
if(is_array($response))
	echo json_encode($response);
else
	echo $response;
?>
