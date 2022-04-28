<?php

ini_set("display_errors", 1);
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents("php://input");
    $json_request = json_decode($json, true);
    if($json_request["username"]>"" && $json_request["password"]>"")
    {
		$response['token'] = "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ2eGhiZDAzOCIsIkdST1VQUyAiOiJHQVRFT1BFUkFUT1IiLCJJU19TU08gIjpmYWxzZSwiU1NPX1RPS0VOICI6IiIsIkNMSUVOVF9OQU1FIjoiRkFTQUgiLCJpc3MiOiJGQVNBSCIsImF1ZCI6IkZBU0FIIEFwcGxpY2F0aW9uIiwiZXhwIjoxNjE0NTIxMjc1fQ.PFNBicdYQ7ah5yYuiaEujd31II3WcZbvO3c0jEjrdt1qEk-EWs2E4VJ4ylBYv7RHYzCxeM8olWeBO3XNzKQy_g";
    }
    else
    {
	$response['httpCode'] = "401";
        $response['httpMessage'] = "Unauthorized";
	$response['moreInformation'] = "Invalid client id or secret.";
    }
    
    http_response_code(200);
} else {
    http_response_code(405); //405 = Method Not Allowed ie if POST is used instead of GET 
    $response['status_code'] = 405;
    $response['status_message'] = "Method not allowed";
    $response['result_description'] = "The method is incorrect";
    $response['result'] = "failed";
}
echo json_encode($response);


?>
