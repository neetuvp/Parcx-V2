<?php
$json='{"edit":1,"delete":1,"task":"10"}';
$data= json_decode($json,TRUE); 
$response=parcxV2Settings($data);
if(is_array($response))
    {
    echo json_encode($response);    
    }
else
    echo $response;

?>