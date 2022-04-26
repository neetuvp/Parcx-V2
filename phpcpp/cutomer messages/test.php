<?php
//echo GetParkingRateSettings('parking_rates2');
//echo GetExport();
//echo GetInterfaceName(1);
ini_set("display_errors",1);
//echo GetCustomerMessagesText();
//echo print_r(GetDeviceTypesCustomerMessages(""),true);
/*$datastring['device_type'] = "1";
$datastring['message'] = "Message";
$datastring['english_line1'] = "1";
$datastring['english_line2'] = "2";
$datastring['english_line3'] = "3";
print_r(InsertRecord($datastring));*/
//echo GetCustomerMessagesMedia("1");
//print_r(GetMessageLanguages());
/*$datastring['type'] = "1";
$datastring['language'] = "English";
echo GetMessageTextByLanguage($datastring);*/
/*$datastring['id'] = "1";
$datastring['language'] = "English";
$datastring['line1'] = "Test2";
$datastring['line2'] = "Test2";
$datastring['line3'] = "Test3";
print_r(UpdateRecord($datastring));*/
/*$json = '{"device_type":"1","message":"Testing1","media_path":"/parcx/Media/entrypushbutton2.jpg","english_line1":"1","english_line2":"","english_line3":"3","arabic_line1":"5","arabic_line2":"4","arabic_line3":"6","languages":["english","arabic"]}';
$data = json_decode($json,true);
print_r(InsertRecord($data));*/
echo json_encode(GetCustomerMessageSettings());
?>