<?php
// $data["option-type"]="13";
// $data["validator_id"]="1";
$json='{"user_id":17,"status":"0","option-type":4,"activity_message":"Disable validation user ICDBP"}';
$data=json_encode($json,TRUE);
$response=parcxValidation($data);      
if(is_array($response))  
    echo json_encode($response);
else
    echo "Response: ".$response;

?>
