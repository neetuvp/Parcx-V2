<?php
$json= file_get_contents("php://input");
$case=$_GET["type"];
$data=json_decode($json);
switch($case)
{
    case 1:                     
        $response=json_encode(GetParkingRateSettings($data->{"rate"}));
        break; 
    case 2:  
    {
        $id = $_GET["id"];
        $rateno = $_GET["rateno"];
        $data = json_decode($json);
        $timeunit = $data -> {'timeunit'};
        $timeduration = $data -> {'timeduration'};
        $parkingrate = $data -> {'parkingrate'};
        $response = PostParkingRateSettings($id,$rateno,$timeunit,$timeduration,$parkingrate);
        break;
    }
    case 3:
        $response=json_encode(GetFixedParkingRateSettings($data->{"rate"}));
        break; 
    case 4:  
    {
        $id = $_GET["id"];
        $rateno = $_GET["rateno"];
        $data = json_decode($json);
        $ratelabel = $data -> {'ratelabel'};
        $fixedrate = $data -> {'fixedrate'};
        $response = PostFixedParkingRateSettings($id,$rateno,$ratelabel,$fixedrate);
        break;
    }
}
echo $response;
?>