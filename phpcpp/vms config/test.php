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
//echo GetDaemonList("");
//$datastring['daemon'] = "px_maintenance_daemon";
//print_r(StartDaemon($datastring))
//$datastring['id'] = 1;
//echo json_encode(EditVMSSettings($datastring));
$datastring = '{"id":"","vms_name":"VMS 4","port_no":"Port555","sign_type":"1","vms_width":"1","vms_height":"1","left_font_size":"1","left_icon_text":"","left_counter_color":"0","left_character_count":"1","left_counter_id":"","left_icon":"B","center_font_size":"1","center_icon_text":"","center_counter_color":"0","center_character_count":"1","center_counter_id":"","center_icon":"B","right_font_size":"1","right_icon_text":"","right_counter_color":"0","right_character_count":"1","right_counter_id":"","right_icon":"1","brightness_time1":"0","brightness_value1":"1","brightness_time2":"0","brightness_value2":"1","brightness_time3":"0","brightness_value3":"1","brightness_time4":"0","brightness_value4":"1","brightness_time5":"2","brightness_value5":"1"}';
$data = json_decode($datastring,true);
echo PostVMSSettings($data);


/*$dir = "/dev/";
$files = scandir($dir);
//print_r($files);
for($i=0;$i<sizeof($files);$i++)
{
    //echo $files[$i];
    if(strpos($files[$i],"tty")!==false)
    {
        echo $files[$i];
    }
    
}*/
//echo GetVMSSettings("");
?>