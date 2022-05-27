<?php 
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
if($data["task"]==9)
	$response=parcxTicketCheck($data);
else
	$response=parcxV2Report($data);

if(is_array($response))
	echo json_encode($response);
else
	echo $response;
?>
