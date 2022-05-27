<?php  
require_once('../../includes/common.php');
$task=$_GET['task'];
$data_obj=json_decode(file_get_contents("php://input"));
switch($task)
{    
    case 1: 
        $description=$data_obj->{'description'};
        $movement_type=$data_obj->{'movement_type'};   
        $reason=$data_obj->{'reason'}; 
        $device_number=$data_obj->{'device_number'};
        $device_ip=$data_obj->{'device_ip'};
        $device_name=$data_obj->{'device_name'};
        $carpark_number=$data_obj->{'carpark_number'};
        $operator=$data_obj->{'operator'};
        $task=$data_obj->{'task'};
	$device_type=$data_obj->{'device_type'};
	
	if($device_type=="1"||$device_type=="2")
		{		
		$message=sendDataToPort($device_ip,8091,$task);		
		if($message!="Error")
                    {
                    $result=InsertManualMovements($description,$movement_type,$reason,$device_number,$device_name,$carpark_number,$operator);
                    $operationMode=$data_obj->{'operation_mode'};
                    if($operationMode!=-1)
                        changeOperationMode($device_number,$operationMode);
                    
                    }
                $result=$message;
		}
	else
		{					
		$result=InsertManualMovements($description,$movement_type,$reason,$device_number,$device_name,$carpark_number,$operator);
		}
        echo $result;
    break;
    case 2:
        $language = $data_obj->{'language'};
        listManualOperation($language);
    break;
} // End Switch 
?>
