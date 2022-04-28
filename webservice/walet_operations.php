<?php
ini_set("display_errors","1");
function CreateLogs($data)
	{
    date_default_timezone_set('Asia/Dubai');
    $file = "/var/log/parcx/Services/ApplicationLogs/Parcx-Walet-".date("Y-m-d").".log";	
    $fh = fopen($file, 'a') or die("can't open file");    
    fwrite($fh,date('H:i:s'). "\t");
    fwrite($fh,$data);
    fwrite($fh,"\n");
    fclose($fh);
	}
if($_SERVER["REQUEST_METHOD"]=='POST')
	{
	$json= file_get_contents("php://input");
    $data=json_decode($json,TRUE);	
    $task=$data["task"];		
	CreateLogs("Task:".$task);
	CreateLogs("Request:".$json);		
	$result="";
	switch($task)
		{
		case 1://update wallet transaction
			CreateLogs("updateWaletTransaction");
			$plate_number=$data["plate_number"];
			$ticket_id=$data["ticket_id"];
			$amount=$data["amount"];
			$device_number=$data["device_number"];
			$device_name=$data["device_name"];
			$carpark_number=$data["carpark_number"];
			$carpark_name=$data["carpark_name"];
			$facility_number=$data["facility_number"];
			$facility_name=$data["facility_name"];
			$result = updateWaletTransaction($plate_number,$ticket_id,$amount,$device_number,$device_name,$carpark_number,$carpark_name,$facility_number,$facility_name);							
			break;
			
		case 2://get Wallet Details
			CreateLogs("getWalletDetails");	
			$user_id=$data["user_id"];
			$result = getWalletDetails($user_id);				
			break;
			
		case 3://get Wallet transactions
			CreateLogs("getWalletTransactions");
			$user_id=$data["user_id"];
			$result=getWalletTransactions($user_id);							
			break;
		
		case 4://updateWalletPayment
			CreateLogs("updateWalletPayment");	
			$user_id=$data["user_id"];
			$amount=$data["amount"];
			$result = updateWalletPayment($user_id,$amount);				
			break;
					
		}
$response=json_encode($result);
CreateLogs("Response: ".$response."\n");			
echo $response;	
	}	
?>