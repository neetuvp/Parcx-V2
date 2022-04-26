<?php
date_default_timezone_set('Asia/Dubai');


class General_Operations 
    {

function db_connect()
        {
        
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_REPORTING);
        
        if (!$con)
        {
            $this->error("Connection attempt failed");
            write_logs("log","ServerDBConnection","Connection Failed");
            
        }
        else
            return $con;
        
    } // end         
    
function write_logs($file_name, $function_name, $message) 
{
   $myfile = fopen("/opt/parcx/Logs/Services/ApplicationLogs/".$file_name, "a") or die("Unable to open file!");
   fwrite($myfile,date("d-m-Y H:i:s") . "\t" . $message."\n");
   fclose($myfile);
}
    
    public function write_exception($file_name, $function_name, $message) 
        {
        $error_file_path = "/opt/parcx/Logs/Services/ExceptionLogs/" .$file_name. date("d-m-Y") . "_exception.log";  
        if (file_exists($error_file_path)) 
            {
            $myfile = fopen($error_file_path, 'a');
            shell_exec('chmod -R 777 '.$error_file_path);
            } 
        else 
            {
            $myfile = fopen($error_file_path, 'w');
            shell_exec('chmod -R 777 '.$error_file_path);
            }        
        fwrite($myfile, date("d-m-Y H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
        }
        
        

    
    public function SendMessageToPort($message)
    {        
        $this->write_logs("SendMessageToPort.txt","SendMessageToPort",$message);

         // Send Message To Terminal 
        $host    = "10.195.14.141";
        $port    = 8091;

        //echo "Message To server :".$message;
        // create socket
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        // connect to server
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        // send string to server
        socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
        // get server response
        //$result = socket_read ($socket, 1024) or die("Could not read server response\n");
        //echo "Reply From Server  :".$result;
        // close socket
        socket_close($socket);

    }
        
    
    public function SaveImage($filename,$image)
    {
        
	$img = str_replace('data:image/png;base64,', '', $image);
	$img = str_replace(' ', '+', $img);
	$data = base64_decode($img);
	$file = $filename . '.jpg';
	$success = file_put_contents("images/".$file, $data);
	//print $success ? $file : 'Unable to save the file.';

        
    }// end function 
    
    public function InsertPlateCaptured($plate,$plate_image_name,$camera_id,$CameraName)
    {
    $con = $this->db_connect();
    $plate_image_name=$plate_image_name.".jpg";
    $query_string = "Insert into plates_captured(plate_number,plate_image_name,camera_device_number,camera_name) VALUES('".$plate."','".$plate_image_name."',".$camera_id.",'".$CameraName."')";
    $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
    $last_id=$con->insert_id;
    mysqli_close($con);
    $this->write_logs("log.txt","InserPlates",$query_string);
    return $last_id;
    }
    
    
    function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);
	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;
	
	$json_response = json_encode($response);
	echo $json_response;
}
    
    
    
    
    
    
    } // end class 
        
        
    
?>

