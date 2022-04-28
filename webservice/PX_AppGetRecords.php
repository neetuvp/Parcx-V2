<?php
ini_set("display_errors","1");
header("Content-Type: application/json; charset=UTF-8");
if($_SERVER['REQUEST_METHOD']=='POST')
	{
	$request_ip_address=$_SERVER['REMOTE_ADDR']; 
	//$request_proxy_ip_address=$_SERVER['HTTP_X_FORWARDED_FOR'];// To be logged 	

    $json= file_get_contents("php://input");
    writeAppGetRecordsLog("Input: ".$json);
    writeAppGetRecordsLog("Request IP:".$request_ip_address);			
	$data= json_decode($json,TRUE); 		
	$result=parcxAppGetRecords($data);
	$response["data"]=$result;
	$response['status_code']=200;
	$response['status_message']="OK";	
	$response['transaction_id']=$data['transaction_id'];
	http_response_code(200);
	}
else
	{		
	http_response_code(405); //405 = Method Not Allowed ie if POST is used instead of GET 
   	$response['status_code']=405;
   	$response['status_message']="Method not allowed";
   	$response['result_description']="The method is incorrect";
   	$response['result']="failed";
    }
$response=json_encode($response);
writeAppGetRecordsLog("Response: ".$response."\n");
echo $response;
?>

