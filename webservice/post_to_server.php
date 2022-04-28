<?php
ini_set("display_errors","0");
header("Content-Type: application/json; charset=UTF-8");
include("../classes/general.php"); 
$general = new General_Oprations();
$filename="PXDeviceUpload/ApplicationLog-PXDeviceUpload-";
if($_SERVER['REQUEST_METHOD']=='POST')
	{
	$request_ip_address=$_SERVER['REMOTE_ADDR']; 		
	$json= file_get_contents("php://input");		
	$general->write_log($filename,"PostToServer","Json input: ".$json);
	$general->write_log($filename,"PostToServer","Request IP:".$request_ip_address);
	$data= json_decode($json,TRUE); 		
	$response=uploadToServer($data);	
	}
$general->write_log($filename,"PostToServer","Json output: ".$response."\n");
echo $response;
?>