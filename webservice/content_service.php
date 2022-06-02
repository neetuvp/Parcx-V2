<?php
ini_set("display_errors","1");
header("Content-Type: application/json; charset=UTF-8");
$response = "";
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$request_ip_address=$_SERVER['REMOTE_ADDR']; 	
	WriteLog("Request IP Address: ".$request_ip_address);
	$data=array();
	$data["task"]=13;
	$response=parcxGreetingScreen($data);
	if(is_array($response))
	    $response=json_encode($response);
	
}
WriteLog("Response: ".$response);
echo $response;

function WriteLog($data)
{
    date_default_timezone_set('Asia/Dubai');
    $file = "/opt/parcx/Logs/Services/ApplicationLogs/PX-GreetingScreen-".date('Y-m-d');
    $fh = fopen($file, 'a') or die("can't open file");
    fwrite($fh,"\n");
    fwrite($fh,"Date :".date('Y-m-d H:i:s'). " ");
    fwrite($fh,$data);
    fclose($fh);
}

?>

