<?php
require_once("../includes/common.php");
define ("DB_HOST", "DBServer");
define ("DB_USER", "parcxservice");
define ("DB_PASSWORD","1fromParcx!19514");
define ("DB_NAME","parcx_server");
define ("DB_NAME_REPORTING","parcx_reporting");
define ("DB_NAME_DASHBOARD","parcx_dashboard");
class webservice
    {
    function db_connect()
        {
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_REPORTING);
        if (!$con)
            $this->log("Connection attempt failed");
        else
            return $con;
        } // end
    function log($msg)
        {       
        $file = "/opt/parcx/Logs/Services/ApplicationLogs/antipassback.log";	
        $fh = fopen($file, 'a') or die("can't open file");    
        fwrite($fh,date('H:i:s'). "\t");
        fwrite($fh,$msg);
        fwrite($fh,"\n");
        fclose($fh);
        }
    function checkAntiPassback($json)
        {        
        $data=json_decode($json);
        $this->log("Input:".$json);
        //$tag_serial=$data->{'tag_serial'};
        $tag=$data->{'ticket_id'};
        $response["tag"]=$tag;
        $response["status"]=400;        
        $con=$this->db_connect();
        if($con)
            {            
            $movement_type=$data->{'movement_type'};           
            $facility_number=$data->{'facility_number'};
            $carpark_number=$data->{'carpark_number'};  
            $device_name= $data->{'device_name'};  
            $parking_zone=   $data->{'parking_zone'};     
            $query_string="select * from open_transactions where carpark_number='$carpark_number' and
            facility_number='$facility_number' and ticket_id='$tag' and entry_type=2";
            //echo $query_string;
            $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
            if ($data = mysqli_fetch_assoc($result))                
                {                   
                if($movement_type==1)
                    {
                    if($data["movement_type"]==1)    
                        $response["message"]="Entry Exists";
                    else
                        {
                        $response["message"]="Success";
                        $response["status"]=200;
                        }
                    }

                if($movement_type==2)
                    {
                    if($data["movement_type"]==1)    
                        {
                        $response["message"]="Success";
                        $response["status"]=200;
                        }
                    else
                        $response["message"]="Already Exited";
                    }
                   
                }
            else
                {
                if($movement_type==1)
                    {
                    $response["status"]=200;
                    $response["message"]="Success";
                    } 
                if($movement_type==2)
                    {
                    $response["message"]="Entry not Exist";
                    }                    
                }
            } 
        $this->log("Output:".json_encode($response));
        echo json_encode($response);
        }       
    }
?>