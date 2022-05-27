<?php
define ("DB_HOST", "DBServer");
define ("DB_USER", "parcxservice");
define ("DB_PASSWORD","1fromParcx!19514");
define ("DB_NAME","parcx_server");
define ("DB_NAME_REPORTING","parcx_reporting");
define ("DB_NAME_DASHBOARD","parcx_dashboard");

class cms {        
    
    function db_connect($db)
        {       
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, $db);
        if (!$con)
            echo "Connection attempt failed";
        else
            return $con;
        } // end
    public function write_log($function_name, $message) 
        {
        $log_file_path = "/opt/parcx/Logs/Services/ApplicationLogs/cms-". date("Y-m-d") . ".log";
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
        fwrite($myfile, date("Y-m-d H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
    }
    
    function pump_selection()
        {
        $con = $this->db_connect(DB_NAME);   
        $sql="select pump_number,cms_controller_ip,pump_name from adnoc_pump_settings";
        $result = mysqli_query($con, $sql) or die(mysqli_error($con));
        while ($data = mysqli_fetch_assoc($result)){     
            echo "<option value='".$data["pump_number"].",".$data["cms_controller_ip"]."'>".$data["pump_name"]."</option>";
            }
        mysqli_close($con);    
        
        }
        
    function update_message($json,$request_method)
        {     
        $this->write_log("pump_status_update", "Request: ".$json);
        if($request_method=='POST')
            {
            $data= json_decode($json,TRUE); 
            $line1="";
            $line2="";
            $line3="";
            $stage="";
            $status=$data["status"];
            $pump_details=$data["pump_details"];
            $pump = explode(',', $pump_details);
            $pump_number=$pump[0];
            $cms_ip=$pump[1];
            
            switch($status)
                {               
                case "Vehicle Arrived":
                    $stage="1";
                    break;
                case "Vehicle Present":           
                    $line1=$data["customer_type"];
                    $stage="2";
                    break;
                case "Fueling Started":
                    $line1=$data["message"];
                    $line2=$data["fuel_type"];
                    $stage="4";
                    break;
                case "Rewards":
                    $line1=$data["message"];
                    $line2=$data["rewards_points_earned"];
                    $stage="2";
                    break;
                case "Fueling Completed":
                    $line1=$data["message"];
                    $line2=$data["quantity_filled"];  
                    $line3=$data["amount_to_pay"];    
                    $stage="6";
                    break;
                case "Vehicle Away":
                    $stage="9";
                    break;
       
                }
            
            $con = $this->db_connect(DB_NAME);      
            
            if ($con != NULL) {                
                $sql="update greeting_screen_messages set stage='".$stage."',line_1='".$line1."',line_2='".$line2."',line_3='".$line3."' where pump_number=".$pump_number;                
                $result = mysqli_query($con, $sql) or die(mysqli_error($con));
                mysqli_close($con);                
                } 
            $json_request["stage"]=$stage;
            $json_request["line1"]=$line1;
            $json_request["line2"]=$line2;
            $json_request["line3"]=$line3;
            $request= json_encode($json_request);
            $json_response=$this->update_message_cms_controller($cms_ip,$request);
            }
        else
            {
            $response["status"]="Bad Request";
            $json_response=json_encode($response);
            }
        
            $this->write_log("pump_status_update", "Response: ".$json_response);
        echo $json_response;
        }
        
    function update_message_cms_controller($ip,$request)
        {
        $url="http://".$ip."/PGEGreeting/update_pump_status.php";
        $ch = curl_init( $url);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
        } 
    }
?>