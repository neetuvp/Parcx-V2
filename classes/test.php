<?php
require_once("../../includes/common.php");

define ("DB_HOST", "DBServer");
define ("DB_USER", "parcxservice");
define ("DB_PASSWORD","1fromParcx!19514");
define ("DB_NAME","parcx_server");
define ("DB_NAME_REPORTING","parcx_reporting");
define ("DB_NAME_DASHBOARD","parcx_dashboard");
class test
    {
    
    function db_connect($db)
        {       
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, $db);
        if (!$con)
            echo "Connection attempt failed";
        else
            return $con;
        } // end
    function show_plates()
        {
        $con = $this->db_connect(DB_NAME);
        $query_string = "select * from sample_plates order by id desc";                            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        echo "<option value='0'>Select plate number</option>";
        while($data = mysqli_fetch_assoc($result))
            {
            echo "<option value='".$data["plate_number"]."'>".$data["plate_number"]."</option>";
            }
        mysqli_close($con);
        
        }
        
    function plate_capture_test($data)
        {
        $device_number=$data["device_number"];
        echo "Device_number: ".$device_number;
        
        $con = $this->db_connect(DB_NAME);
        $query_string = "select * from system_devices where device_number=".$device_number;                            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        $data = mysqli_fetch_assoc($result);
        $device_ip=$data["device_ip"];
        $camera_id=$data["camera_id"];
        
        $query_string = "select * from system_devices where device_number=".$camera_id;                            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        $data = mysqli_fetch_assoc($result);
        $camera_name=$data["device_name"];
        
       
        
        //activate Presenceloop
        echo "<h5>Activate Presence loop</h5>";
        $message="S10";
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message."<br>";
        
        sleep(3);
        //capture plate
        echo "<h5>Plate capture</h5>";
        
        $query_string="select plate_number from sample_plates ORDER BY RAND() LIMIT 1;";
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        $data = mysqli_fetch_assoc($result);
        $plate_number=$data["plate_number"];
        echo "Plate: ".$plate_number."<br>";
        $image=$this->copy_image($plate_number, $camera_id);
        $confidence_rate="98";

        $query_string="insert into parcx_reporting.plates_captured(camera_device_number,camera_name,plate_number,plate_image_name,confidence_rate)values(".$camera_id.",'".$camera_name."','".$plate_number."','".$image."',".$confidence_rate.")";            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));

        $query_string = "select id from parcx_reporting.plates_captured order by id desc limit 1";                            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        $data = mysqli_fetch_assoc($result);
        $id=$data["id"];            
        $message="C0".$id."C1P0".$plate_number."P1".$image."P2";  
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message."<br>";
        
         mysqli_close($con);
        
        sleep(5);
        //activate safety;
        echo "<h5>Activate Safety loop</h5>";
        $message="S12";
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message."<br>";
        
        sleep(2);
        
        //deactivate presence;
        echo "<h5>Deactivate Presence loop</h5>";
        $message="S11";
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message."<br>";
        sleep(3);
        //deactivate safety
        echo "<h5>Deactivate Safety loop</h5>";
        $message="S13";
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message;
            
        }
        
    function copy_image($plate_number,$camera_id)
        {
        $plate_image=str_replace(' ', '', $plate_number).".jpg";

        $image=str_replace(' ', '', $plate_number);
        $image=$image."_".date("Y-m-dH:i:s").".jpg";
        

        $path = "/opt/lampp/htdocs/ANPR/Images/".$camera_id."/".date("Y-m-d");
        echo "Folder path:".$path."\n";
        if (!file_exists($path)) {
            echo "folder not exist\n";
            mkdir($path, 0777, true);
        }

        $source = "/opt/lampp/htdocs/parcx/Media/plates/Scene_".$plate_image;               
        $destination = $path."/Scene_".$image; 

        echo $source."\n";
        echo $destination."\n";

        if( !copy($source, $destination) ) {             
            if(copy("/opt/lampp/htdocs/parcx/Media/plates/default_car.jpg", $destination))
                echo "Scene File has been copied! \n"; 
            else
                echo "Scene File can't be copied! \n"; 

        } 
        else { 
            echo "Scene File has been copied! \n"; 
        } 



        $source = "/opt/lampp/htdocs/parcx/Media/plates/Crop_".$plate_image;               
        $destination = $path."/Crop_".$image; 

        echo $source."\n";
        echo $destination."\n";

        if( !copy($source, $destination) ) { 
            
            if(copy("/opt/lampp/htdocs/parcx/Media/plates/default_car.jpg", $destination))
                echo "Crop File has been copied! \n"; 
            else
                echo "Crop File can't be copied! \n"; 
        } 
        else { 
            echo "Crop File has been copied! \n"; 
        } 
        
        return $image;
        }
                   
    function create_plate_captured_send_to_device($data)
        {        
        $device_number=$data["device_number"];
        $plate_number=$data["plate_number"];
        $confidence_rate=$data["confidence_rate"];
        $option=$data["option"];
        
        $con = $this->db_connect(DB_NAME);
        $query_string = "select * from system_devices where device_number=".$device_number;                            
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
        $data = mysqli_fetch_assoc($result);
        $device_ip=$data["device_ip"];
        $camera_id=$data["camera_id"];
        
        if($option==1)
            {
            $query_string = "select * from system_devices where device_number=".$camera_id;                            
            $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
            $data = mysqli_fetch_assoc($result);
            $camera_name=$data["device_name"];
            
           
            
            $image=$this->copy_image($plate_number, $camera_id);


            $query_string="insert into parcx_reporting.plates_captured(camera_device_number,camera_name,plate_number,plate_image_name,confidence_rate)values(".$camera_id.",'".$camera_name."','".$plate_number."','".$image."',".$confidence_rate.")";            
            $result = mysqli_query($con, $query_string) or die(mysqli_error($con));

            $query_string = "select id from parcx_reporting.plates_captured order by id desc limit 1";                            
            $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
            $data = mysqli_fetch_assoc($result);
            $id=$data["id"];            
            $message="C0".$id."C1P0".$plate_number."P1".$image."P2";                       
            }
        else
            $message=$option;
        
        echo $message."\n"; 
        mysqli_close($con);
        $message=sendDataToPort($device_ip,8091,$message);   
        echo $device_ip. " : ".$message;
        }
        
    
    function send_camera_data_to_device($data)
    {
        
        $request = $data;
        $camera = $data["camera"];
        $request["image"] =file_get_contents('scene.txt',true);
        $request["plateimage"] =file_get_contents('crop.txt',true);
        $device_ip = $data["ip"];
       // echo "here".json_encode($request);
	//$this->write_logs("camera_simulator","send_camera_data_to_device","Request:".json_encode($request));
        $curl_do = curl_init();
       // curl_setopt($curl_do, CURLOPT_URL,"http://10.195.15.205/Tabadul-API/MXCameraService.php?camera_id=".$camera);
        curl_setopt($curl_do, CURLOPT_URL,"http://".$device_ip."/Tabadul-API/MXCameraService.php?camera_id=".$camera);
        curl_setopt($curl_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_do, CURLOPT_POST, true);
        curl_setopt($curl_do, CURLOPT_VERBOSE, 1);
        curl_setopt($curl_do, CURLOPT_POSTFIELDS, json_encode($request));
        $output = str_replace("'", "", curl_exec($curl_do));
        if (curl_errno($curl_do)) 
        {
            $this->write_logs("camera_simulator","send_camera_data_to_device","Response:".curl_error($curl_do));
            echo curl_error($curl_do);
        }
        else {
            $this->write_logs("camera_simulator","send_camera_data_to_device","Response:".$output);
            echo $output;
        }
	
        
    }

    function write_logs($file_name, $function_name, $message) 
        {
        $log_file_path = "/opt/parcx/Logs/WebApplication/ApplicationLogs/".$file_name. date("d-m-Y") . "_log.log";
        if (file_exists($log_file_path)) 
            {
            $myfile = fopen($log_file_path, 'a');
            shell_exec('chmod -R 777 '.$log_file_path);            
            } 
        else 
            {
            $myfile = fopen($log_file_path, 'w');
            shell_exec('chmod -R 777 '.$log_file_path);
            }        
        fwrite($myfile, date("d-m-Y H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
    }
    
    }
?>
