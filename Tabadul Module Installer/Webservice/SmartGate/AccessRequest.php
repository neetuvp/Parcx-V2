<?php

include('common.php');
include('DBOperations.php');
ini_set("display_errors", 0);
header("Content-Type: application/json; charset=UTF-8");
$plate_image_name = "";
$plate_image_name_secondary = "";
$token_url="";
$api_url="";
$settings = array();
createLog("*************************************Log**********************************************************");
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
    $json = file_get_contents("php://input");
    createLog("Request:" . $json);
    $json_request = json_decode($json, true);
    if ($json_request["plate_match"] == "plate_matching" || ($json_request["qrcode"] > "" && $json_request["plate_number"] == "")) 
        {
        $response = CallAPI($json_request);               
        } 
    else 
        {
        try 
            {
            $conn = DBConnect(DB_NAME_REPORTING);
            $plate_capture_id1 = $json_request["plate_captured_id1"];
            $plate_capture_id2 = $json_request["plate_captured_id2"];
            $plate_number = $json_request["plate_number"];
            $qrcode=$json_request["qrcode"];
            $device_number=$json_request["device_number"];
            $device_name=$json_request["device_name"];
            
            if ($plate_capture_id1 == "")
                $plate_capture_id1 = "0";
            if ($plate_capture_id2 == "")
                $plate_capture_id2 = "0";

            if ($json_request["plate_match"] == "both_plates_not_received") 
                {
                $description_line1 = "both_plates_not_received";
                $description_line2 = "Both plates not received";
                } 
            else if ($json_request["plate_match"] == "plates_not_matching") 
                {
                $description_line1 = "plates_not_matching";
                $description_line2 = "Plates do not match";
                }

            $sql = "Insert into smartgate_request(device_number,device_name,reference_number,plate_number,gate_code,plate_capture_id,plate_capture_id_secondary,plates_match,response_status,fault_string,fault_code_text,api_datetime_request,api_datetime_response)values('".$device_number."','".$device_name."','" . $qrcode . "','" . $plate_number . "','" . $gatecode . "','" . $plate_capture_id1 . "','" . $plate_capture_id2 . "',0,'Failed','" . $description_line1 . "','" . $description_line2 . "',NOW(),NOW())";
            //createLog("sql:".$sql);
            DBOperation($conn, $sql);
            CloseDBConnection($conn);
            $response["access_allowed"] = "false";
            $response["plate_capture_id"] = $plate_capture_id1;            
            $response["plate_capture_id_secondary"] = $plate_capture_id2;            
            $response["description_line1"] = $description_line1;
            $response["description_line2"] = $description_line2;
            $response["plate_number"] = $plate_number;
            $response["reference_type"] = "PlateNumber";
            } 
        catch (exception $e) 
            {
            createLog("Exception:" . $ex);
            }
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
    
createLog("Response:" . json_encode($response));
echo json_encode($response);


function getSettings()
{
    
   $conn = DBConnect(DB_NAME_SERVER); 
   $sql = "Select setting_name,setting_value from system_settings where setting_status=1";
   $rs = GetData($conn, $sql);
    if (mysqli_num_rows($rs) > 0) {
        
        while($row = mysqli_fetch_array($rs))
        {
            $settings[$row["setting_name"]]=$row["setting_value"];
        }
    }
    return $settings;
    //print_r($settings);
    CloseDBConnection($conn);
}


function CallAPI($json_request) 
    {
    $settings = getSettings();
    $token_url = $settings["token_url"];
    $api_url = $settings["api_url"];
    $client_id = $settings["client_id"];
    $client_secret = $settings["client_secret"];
    $gatecode = $settings["gate_code"]; 
    //$gatecode = $json_request["gate_code"];
    $plate_capture_id1 = $json_request["plate_captured_id1"];
    $plate_capture_id2 = $json_request["plate_captured_id2"];
    if ($plate_capture_id1 == "")
        $plate_capture_id1 = 0;
    if ($plate_capture_id2 == "")
        $plate_capture_id2 = 0;
    $plate_number = $json_request["plate_number"];
    $qrcode = $json_request["qrcode"];
    $device_number=$json_request["device_number"];
    $device_name=$json_request["device_name"];
            
   
    $description = "Description";
    $description_line1 = "";
    $description_line2 = "";
    $conn = DBConnect(DB_NAME_REPORTING);
    $sql = "Insert into smartgate_request(device_number,device_name,reference_number,plate_number,gate_code,plate_capture_id,plate_capture_id_secondary)values('".$device_number."','".$device_name."','" . $qrcode . "','" . $plate_number . "','" . $gatecode . "','" . $plate_capture_id1 . "','" . $plate_capture_id2 . "')";
    //createLog("sql:".$sql);
    DBOperation($conn, $sql);   

    $reference_number = "";
    $reference_type = "";
    if ($plate_number > "") 
        {
        $reference_number = $plate_number;
        $reference_type = "PlateNumber";
        } 
    else if ($qrcode > "") 
        {
        $reference_number = $qrcode;
        $reference_type = "BookRefNumber";
        }

//Token
    $token_request = array();
    $token_request['username'] = $settings["token_user"];
    $token_request['password'] = $settings["token_password"];
    $token_header = array(
        'Accept: application/json',
        'Content-Type: application/json;  charset=utf-8',
        'x-ibm-client-id: ' . $client_id,
        'x-ibm-client-secret: ' . $client_secret
    );

    createLog("Token,URL:" . $token_url);
    createLog("Token,Request:" . json_encode($token_request));

    $curl_do = InitializeCurl($token_url, $token_header, $token_request);
    $output = str_replace("'", "", curl_exec($curl_do));
    createLog("Token,Response:" . $output);
    $response = array();
    $response["reference_type"] = $reference_type;
    $response["plate_capture_id"] = $plate_capture_id1;
   // $response["plate_image_name"] = $plate_image_name;
    $response["plate_capture_id_secondary"] = $plate_capture_id2;
    //$response["plate_image_name_secondary"] = $plate_image_name_secondary;
    $response["description_line1"] = "access_allowed";
    if (curl_errno($curl_do)) 
        {
        createLog("Token Curl Error:" . curl_error($curl_do));
        $error_message = curl_error($curl_do);
        $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($token_request) . "',fault_string ='" . str_replace("'", "", $error_message) . "',token_datetime_response=NOW(),fault_code_text='Token Curl Error' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
        //createLog("sql:".$sql);
        $result = DBOperation($conn, $sql);
        $response["access_allowed"] = "false";
        $response["description_line1"] = "technical_error";
        $response["description_line2"] = "Token Error";
        $response["plate_number"] = $plate_number;
        } 
    else 
        {
        $token_response = json_decode($output, true);
        $auth_token = $token_response["token"];
        if ($auth_token > "")   
            {
            $sql = "Update smartgate_request set token_datetime_response=NOW(),request='" . json_encode($token_request) . "',api_datetime_request=NOW(),token_response ='" . $output . "' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
            //createLog("sql:".$sql);
            $result = DBOperation($conn, $sql);

            //Smartgate API
            $api_request = array();
            $api_request['gateCode'] = $gatecode;
            $api_request['referenceNumber'] = $reference_number;
            $api_request['referenceType'] = $reference_type;
            $api_header = array(
                'Accept: application/json',
                'Content-Type: application/json;  charset=utf-8',
                'X-IBM-Client-Id: ' . $client_id,
                'Authorization: ' . $auth_token,
                'Accept-Language:en'
            );

            createLog("CallAPI,URL:" . $api_url);
            createLog("CallAPI,Request:" . json_encode($api_request));
            $curl_do = InitializeCurl($api_url, $api_header, $api_request);


            $output = str_replace("'", "", curl_exec($curl_do));
            createLog("CallAPI,Response:" . $output);

            if (curl_errno($curl_do)) 
                {
                createLog("CallAPI Curl Error:" . curl_error($curl_do));
                $error_message = curl_error($curl_do);
                $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($api_request) . "',fault_string ='" . str_replace("'", "", $error_message) . "',api_datetime_response=NOW(),fault_code_text='API Curl Error' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
                //createLog("sql:".$sql);
                $result = DBOperation($conn, $sql);
                $response["access_allowed"] = "false";
                $response["description_line1"] = "technical_error";
                $response["description_line2"] = "API Error";
                $response["plate_number"] = $plate_number;
                } 
            else 
                {
                if ($output > "") 
                    {
                    $curl_response = json_decode($output, true);
                    if ($curl_response["success"] === true) 
                        {                        
                        if ($plate_capture_id1 == "")
                            $plate_capture_id1 = "0";
                        if ($plate_capture_id2 == "")
                            $plate_capture_id2 = "0";
                        $response["access_allowed"] = "true";
                        $response["description_line1"] = "access_allowed";
                        $response["description_line2"] = "Open barriers,Driver move forward";
                        $response["plate_number"] = $plate_number;
                        $sql = "Update smartgate_request set response_status = 'Success',request='" . json_encode($api_request) . "',api_response ='" . $output . "',api_datetime_response=NOW() where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
                        $result = DBOperation($conn, $sql);
                        } 
                    else if ($curl_response["success"] === false) 
                        {
                        $code = $curl_response["code"];
                        createLog("code:" . $code);
                        if ($code == "GATE_NOT_FOUND") {
                            $code_text = GATE_NOT_FOUND;
                            $description_line1 = "gate_not_found";
                            $description_line2 = "The gate not found";
                        } else if ($code == "GATE_PURPOSE_NOT_FOUND") {
                            $code_text = GATE_PURPOSE_NOT_FOUND;
                            $description_line1 = "gate_purpose_not_found";
                            $description_line2 = "Gate purpose not found";
                        } else if ($code == "APPOINTMENT_NOT_FOUND") {
                            $code_text = APPOINTMENT_NOT_FOUND;
                            $description_line1 = "appointment_not_found";
                            $description_line2 = "No Appointment,Driver turn back after first barrier";
                        } else if ($code == "TOO_EARLY_ARRIVAL") {
                            $code_text = TOO_EARLY_ARRIVAL;
                            $description_line1 = "too_early_arrival";
                            $description_line2 = "Too early arrival,Contact operator and scan QR";
                        } else if ($code == "TOO_LATE_ARRIVAL") {
                            $code_text = TOO_LATE_ARRIVAL;
                            $description_line1 = "too_late_arrival";
                            $description_line2 = "Too late arrival,Contact operator and scan QR";
                        } else if ($code == "INVALID_APPOINTMENT_STATUS") {
                            $code_text = INVALID_APPOINTMENT_STATUS;
                            $description_line1 = "invalid_appointment_status";
                            $description_line2 = "Invalid appointment status";
                        } else if ($code == "INVALID_PORT_GATE") {
                            $code_text = INVALID_PORT_GATE;
                            $description_line1 = "invalid_port_gate";
                            $description_line2 = "Invalid port gate";
                        } else if ($code == "MULTIPLE_APPOINTMENT") {
                            $code_text = MULTIPLE_APPOINTMENT;
                            $description_line1 = "multiple_appointment";
                            $description_line2 = "Multiple appointment for the vehicle,contact operator and scan QR";
                        }else if ($code == "NO_APPOINTMENT_ON_TIME") {
                            $code_text = NO_APPOINTMENT_ON_TIME;
                            $description_line1 = "no_appointment_on_time";
                            $description_line2 = "No appointment on time";
                        }
			/*else if ($code == "USER_NOT_FOUND") {
                            $code_text = USER_NOT_FOUND;
                            $description_line1 = "user_not_found";
                            $description_line2 = "The user not found";
                        }else if ($code == "USER_NOT_AUTHORIZED") {
                            $code_text = USER_NOT_AUTHORIZED;
                            $description_line1 = "user_not_authorized";
                            $description_line2 = "The user not authorized";
                        }else if ($code == "SUCCESS_ENTRY") {
                            $code_text = SUCCESS_ENTRY;
                            $description_line1 = "success_entry";
                            $description_line2 = "Truck checked in Successfully";
                        }else if ($code == "SUCCESS_EXIT") {
                            $code_text = SUCCESS_EXIT;
                            $description_line1 = "success_exit";
                            $description_line2 = "Truck checked out Successfully";
                        }else if ($code == "SUCCESS_CHECK_OUT_WITHOUT_APPT") {
                            $code_text = SUCCESS_CHECK_OUT_WITHOUT_APPT;
                            $description_line1 = "success_check_out_without_appt";
                            $description_line2 = "Truck Checked out without Appointment";
                        }else if ($code == "SUCCESS_INDR_ENTRY") {
                            $code_text = SUCCESS_INDR_ENTRY;
                            $description_line1 = "success_indr_entry";
                            $description_line2 = "Truck indirect checked in Successfully";
                        }else if ($code == "SENT_TO_PARK") {
                            $code_text = SENT_TO_PARK;
                            $description_line1 = "sent_to_park";
                            $description_line2 = "Sent to Parking";
                        }else if ($code == "RETURNED") {
                            $code_text = RETURNED;
                            $description_line1 = "returned";
                            $description_line2 = "Truck Has been Returned";
                        }*/
			else
			{
			    $code_text = NO_ACCESS;
                            $description_line1 = "no_access";
                            $description_line2 = "No access";
			}
                        
                        createLog("codetext:" . $code_text);

                        $response["access_allowed"] = "false";
                        $response["description_line1"] = $description_line1;
                        $response["description_line2"] = $description_line2;
                        $response["plate_number"] = $plate_number;
                        $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($api_request) . "',fault_string ='" . $output . "',api_datetime_response=NOW(),fault_code_text='" . $code_text . "' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
                        //createLog("sql:".$sql);
                        $result = DBOperation($conn, $sql);
                    } else {
                        $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($api_request) . "',fault_string ='" . $output . "',api_datetime_response=NOW(),fault_code_text='API Technical Error' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
                        //createLog("sql:".$sql);
                        $result = DBOperation($conn, $sql);
                        $response["access_allowed"] = "false";
                        $response["description_line1"] = "technical_error";
                        $response["description_line2"] = "API Technical Error";
                        $response["plate_number"] = $plate_number;
                    }
                } else {
                    $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($api_request) . "',fault_string ='" . $output . "',api_datetime_response=NOW(),fault_code_text='API Technical Error' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
                    //createLog("sql:".$sql);
                    $result = DBOperation($conn, $sql);
                    $response["access_allowed"] = "false";
                    $response["description_line1"] = "technical_error";
                    $response["description_line2"] = "API Technical Error,Empty Response";
                    $response["plate_number"] = $plate_number;
                }
            }
        } else {
            $sql = "Update smartgate_request set response_status = 'Failed',request='" . json_encode($api_request) . "',request='" . json_encode($token_request) . "',fault_string ='" . $output . "',token_datetime_response=NOW(),fault_code_text='Token Technical Error' where plate_number = '" . $plate_number . "' and reference_number = '" . $qrcode . "' order by id desc limit 1";
            //createLog("sql:".$sql);
            $result = DBOperation($conn, $sql);
            $response["access_allowed"] = "false";
            $response["description_line1"] = "technical_error";
            $response["description_line2"] = "Token Error";
            $response["plate_number"] = $plate_number;
        }
    }    
    CloseDBConnection($conn);
    return $response;
}

function InitializeCurl($url, $header, $request) {
    $curl_do = curl_init();
    curl_setopt($curl_do, CURLOPT_URL, $url);
    curl_setopt($curl_do, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl_do, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl_do, CURLOPT_POST, true);
    curl_setopt($curl_do, CURLOPT_POSTFIELDS, json_encode($request));
    curl_setopt($curl_do, CURLOPT_HTTPHEADER, $header);
    return $curl_do;
}

function CheckWhitelist($json_request) {
    $plate_capture_id1 = $json_request["plate_captured_id1"];
    $plate_capture_id2 = $json_request["plate_captured_id2"];
    $plate_number = $json_request["plate_number"];
    $qrcode = $json_request["qrcode"];
    $description1 = "access_allowed";
    $description2 = "Access Allowed";
    $conn1 = DBConnect(DB_NAME_SERVER); 
    $plate_image_name = "";
    $plate_image_name_secondary = "";
    $rs = GetData($conn1, "Select access_id from access_whitelist where plate_number like '%" . $plate_number . "'");
    if (mysqli_num_rows($rs) > 0) {
        $response["access_allowed"] = "true";
        $response["plate_capture_id"] = $plate_capture_id1;        
        $response["plate_capture_id_secondary"] = $plate_capture_id2;        
        $response["description_line1"] = $description1;
        $response["description_line2"] = $description2;
        $response["plate_number"] = $plate_number;
        $response["reference_type"] = "PlateNumber";
        $sql = "Insert into smartgate_request(device_number,device_name,reference_number,plate_number,gate_code,request,plate_capture_id,plate_capture_id_secondary,response_status,api_datetime_request,api_datetime_response,fault_code_text)values('1','Device','" . $qrcode . "','" . $plate_number . "','Access Whitelist','" . json_encode($json_request) . "','" . $plate_capture_id1 . "','" . $plate_capture_id2 . "','Success',NOW(),NOW(),'')";
    } else {
        $response["access_allowed"] = "false";
        $response["description_line1"] = "technical_error";
        $response["description_line2"] = "Plate number not found in Access Whitelist";
        $response["plate_number"] = $plate_number;
        $response["reference_type"] = "PlateNumber";
        $response["plate_capture_id"] = $plate_capture_id1;      
        $response["plate_capture_id_secondary"] = $plate_capture_id2;      
        $sql = "Insert into smartgate_request(device_number,device_name,reference_number,plate_number,gate_code,request,plate_capture_id,plate_capture_id_secondary,response_status,api_datetime_request,api_datetime_response,fault_code_text)values('1','Device','" . $qrcode . "','" . $plate_number . "','Access Whitelist','" . json_encode($json_request) . "','" . $plate_capture_id1 . "','" . $plate_capture_id2 . "','Failed',NOW(),NOW(),'Technical Error')";
    }
    CloseDBConnection($conn1);
    $conn = DBConnect(DB_NAME_REPORTING);
    DBOperation($conn, $sql);
    CloseDBConnection($conn);
    return $response;
}

//Create Log file
function createLog($data) {
    date_default_timezone_set('Asia/Riyadh');
    if (!file_exists('Logs')) {
        mkdir('Logs', 777, true);
    }
    $file = "Logs/Log-" . date('Y-m-d');
    $fh = fopen($file, 'a') or die("can't open file");
    fwrite($fh, "\n");
    fwrite($fh, "Date :" . date('Y-m-d H:i:s') . " ");
    fwrite($fh, $data);
    fclose($fh);
}
?>

