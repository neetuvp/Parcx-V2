<?php
$json='{"uuid":"cbc326d345af29bf297f7947af82e252","task":"1"}';
echo $json."\n";
$data= json_decode($json,TRUE); 
$response=parcxCloudIOT($data);
if(is_array($response))
    {
    echo json_encode($response);    
    }
else
    echo $response;

?>