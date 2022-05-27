<?php
ini_set("display_errors","1");
header("Content-Type:application/json");

include("../classes/general.php"); 

 $general = new General_Oprations();

if($_SERVER['REQUEST_METHOD']=='POST')
	{
	$general->write_logs("VoucherProcess","POST Method","got a post request");  
	$json= file_get_contents("php://input");	
	$general->write_logs("VoucherProcess","Json input: ",$json);    
	$response=ProcessVoucher($json);
	$general->write_logs("VoucherProcess","Json output: ",$response);    
	echo $response;
    }
    else{
		$general->write_logs("VoucherProcess","POST Method","not a post request");    
		}

	
?>
