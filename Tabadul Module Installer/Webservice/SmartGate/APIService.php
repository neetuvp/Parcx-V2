<?php

ini_set("display_errors", 1);
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents("php://input");
    $json_request = json_decode($json, true);
    if ($json_request["gateCode"] > "" && $json_request["referenceNumber"] > "" && $json_request["referenceType"] > "") {
        if ($json_request["gateCode"] != "GS3112121") 
            {
            $response['success'] = false;
            //$response['code']="GATE_NOT_FOUND";
            //$response['code']="GATE_PURPOSE_NOT_FOUND";
            //$response['code']="APPOINTMENT_NOT_FOUND";
            //$response['code']="TOO_EARLY_ARRIVAL";
            //$response['code']="TOO_LATE_ARRIVAL";
            //$response['code']="INVALID_APPOINTMENT_STATUS";
            //$response['code']="INVALID_PORT_GATE";
            //$response['code'] = "MULTIPLE_APPOINTMENT";
		$response['code']="SOME_REASON";
            } 
        else 
            {
            $response['success'] = true;
            }
        } 
    else 
        {
        $response['httpCode'] = "401";
        $response['httpMessage'] = "Unauthorized";
        $response['moreInformation'] = "Cannot pass the security checks that are required by the target API or operation, Enable debug headers for more details.";
        }

    http_response_code(200);
    } 
else 
    {
    http_response_code(405); //405 = Method Not Allowed ie if POST is used instead of GET 
    $response['status_code'] = 405;
    $response['status_message'] = "Method not allowed";
    $response['result_description'] = "The method is incorrect";
    $response['result'] = "failed";
    }
echo json_encode($response);
?>
