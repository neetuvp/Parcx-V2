<?php
ini_set("display_errors","1");
header("Content-Type: application/json; charset=UTF-8");
include("../classes/general.php"); 
$general = new General_Oprations();
$filename="PX_PlateSuggestions-Webservice";

 $general = new General_Oprations();

if($_SERVER['REQUEST_METHOD']=='POST')
	{	
	$json= file_get_contents("php://input");	
	$general->write_logs($filename,"Json input: ",$json);    
	$response=PlateSuggestions($json);
	$general->write_logs($filename,"Json output: ",$response);    
	echo $response;
    }
else
	{
	$general->write_logs($filename,"POST Method","not a post request");    
	}

	
?>
