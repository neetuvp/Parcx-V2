<?php 
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
$response=parcxViewLogs($data);
if($data["task"]==3 && $data["download"]==0)
	$response = preg_replace("/\r\n|\r|\n/", '<br/>', $response);	
echo $response;

?>
