<?php
define ("DB_HOST", "DBServer");
define ("DB_USER", "parcxservice");
define ("DB_PASSWORD","1fromParcx!19514");
define ("DB_NAME","parcx_server");
define ("DB_NAME_REPORTING","parcx_reporting");
define ("DB_NAME_DASHBOARD","parcx_dashboard");

class dashboard {        
    
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
        $log_file_path = "/opt/parcx/Logs/WebApplication/ApplicationLogs/dashboard-". date("Y-m-d") . ".log";
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
    
    function interface_request_dashboard($json)
        {
        $json_data = json_decode($json,true);
        $request = array();
        $con = $this->db_connect(DB_NAME_REPORTING);   
        $sql="select * from interface_access_request where id>0";
        if($json_data["pump"]!="0")
        {
            $sql.=" and (request like '%PumpID\":\"".$json_data["pump"]."\"%' or request like '%pump_id\":\"".$json_data["pump"]."\"%')";
        }
        $sql.=" order by id desc limit 250";
        //echo $sql;
        $i=0;
        $html_data="";
	$html_data.='<table id="RecordsTable1" class="table table-blue table-bordered table-striped">';
            $html_data.='<thead>';
            $html_data.='<tr>';
            $html_data.='<th>#</th>';
            $html_data.='<th>Interface Name</th>';
            $html_data.='<th>Request Date Time</th>';
            $html_data.='<th>Pump Id</th>';
            $html_data.='<th>Description</th>';
	    $html_data.='<th>Response Date Time</th>';
            //$html_data.='<th>Response</th>';
            $html_data.='<th>Request Type</th>';
            $html_data.='<th>Request IP</th>';
            $html_data.='<th>Status</th>';
            $html_data.='</tr>';
            $html_data.='</thead>';

            // table data
            $html_data.='<tbody>';
        $result = mysqli_query($con, $sql) or die(mysqli_error($con));
        while ($data = mysqli_fetch_assoc($result)){  
            $i++;
            $html_data.='<tr data-toggle="modal" data-target="#request-details-modal" id="access_record" access_id='.$data['id'].'>';
            $html_data.='<td>' .$i. '</td>';
            $html_data.='<td>' . $data['interface_name']. '</td>';
            $html_data.='<td>' . $data['request_date_time']. '</td>';
            if($data['request_type']==0)
            {
                $request = json_decode($data['request'],true);
                $html_data.='<td>' . $request["PumpID"]. '</td>';  
                $html_data.='<td>' . $request["Description"]. '</td>';
                    
                
            }
            else {
                $request = json_decode($data['request'],true);
                $html_data.='<td>' . $request["pump_id"]. '</td>';
                $html_data.='<td>' . $request["message"]. '</td>';  
                
            }
            
            $html_data.='<td>' . $data['response_date_time']. '</td>';
            //$html_data.='<td>' . $data['response']. '</td>';
            if($data['request_type']==0)
                $html_data.='<td>Incoming</td>';
            else
                $html_data.='<td>Outgoing</td>'; 
            $html_data.='<td>' . $data['client_ip']. '</td>';
            if($data['response_code']==200)
                $html_data.='<td>Success</td>';
            else
                $html_data.='<td>Failed</td>'; 

            $html_data.='</tr>';
        }
        $html_data.='</tbody>';
        $html_data.='</table>';
        mysqli_close($con);    
        echo $html_data;
        }
        
    function interface_request_detail($json)
    {      
        $json_data = json_decode($json,true);
        $id = $json_data["id"];
        $con = $this->db_connect(DB_NAME_REPORTING); 
        if($con!=NULL)
        {

            $sql = "select * from interface_access_request where id = ".$id;

            $result = $con->query($sql);
            $row = $result -> fetch_assoc();

            echo (json_encode($row));	


            mysqli_close($con);
        }
    }
    
    function get_controller_devices()
        {        
        $con=$this->db_connect(DB_NAME);   
        $html_data="";
        if($con)
            {  
            $query_string="select device_ip,device_name from system_devices where device_category=13";
            $result=mysqli_query($con,$query_string) or die(mysqli_error($con));         
            while($data=mysqli_fetch_assoc($result))        
                $html_data.="<option value=".$data['device_ip'].">".$data['device_name']."</option>";                   
                
            echo $html_data;
            }
        else
            echo "<option>Not Available</option>";                
        mysqli_close($con);        
        }// End FUnction 
    
    }
?>