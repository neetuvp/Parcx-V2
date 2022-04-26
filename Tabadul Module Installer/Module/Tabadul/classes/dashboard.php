<?php
require_once("config.php");

class dashboard
{    
function log($message)
    {
        $myfile=fopen("/opt/parcx/Logs/WebApplication/ApplicationLogs/dashboard-".date("Y-m-d").".log","a");
        fwrite($myfile, date("H-i-s")."\t".$message."\n");
        fclose($myfile);
    }
    


function db_connect($databaseName)
    {
    
        $serverName = MYSQLSERVER;
        $uid = USER;
        $pwd = PASSWORD;
       // $databaseName = DB;
        $conn = new mysqli($serverName, $uid, $pwd,$databaseName);
        // Check connection
        if($conn->connect_error)
        {
                die("Mysql Connection failed: " . $conn->connect_error);
                $this->log("Mysql Connection Failed:". $conn->connect_error);
        }                       
        return $conn;
    } // end

function get_device_status_block_view()
    {       
    $con = $this -> db_connect(DB_DASHBOARD);
    if($con!=NULL)
        {
        
        
        $html_data = "";
        

        $html_data .= '<div  data-status="block-view">';
        $html_data .= '<div class="row">';
                
        $query_string="select * from watchdog_device_status where device_type=1" ;      
        $result = $con->query($query_string);
        
        while($data = mysqli_fetch_array($result))            
            {       
            $barrier = "false";
            $category = "";
            if($data["device_type"]==1 || $data["device_type"]==2){             
                $image_url=IMAGEURL."column.png" ;
                $data_img="column" ;
                $barrier = "true";
            }   
            else if ($data["device_type"] == 3) 
                {
                $data_status = "payment_machines";
                $image_url = IMAGEURL."cashier-pos.png";
                $data_img = "cashier-pos";
                }
            else if ($data["device_type"] == 5) 
                {
                $data_status = "payment_machines";
                $image_url = IMAGEURL."handheld-pos.png";
                $data_img = "handheld-pos";
                } 
            else if ($data["device_type"] == 4) 
                {
                $data_status = "payment_machines";
                $image_url = IMAGEURL."payment-machine.png";
                $data_img = "payment-machine";
                } 
            else if ($data["device_type"] == 6||$data["device_type"] == 7) 
                {
                $data_status = "controllers";
                $image_url = IMAGEURL."controller.png";
                $data_img = "controller";
                }
            else if ($data["device_type"] == 8) 
                {
                $data_status = "camera";
                $image_url = IMAGEURL."cctv.png";
                $data_img = "cctv";
                }
            else if ($data["device_type"] == 9) 
                {
                $data_status = "vms";
                $image_url = IMAGEURL."vms.png";
                $data_img = "vms";
                }
            $html_data.='<div class="col-md-5 block-data device-block" data-number="' .$data["device_number"].'" barrier="'.$barrier.'" data-img="' .$data_img.'" data-target="#error-log-modal">';            

            $html_data .= '<div class="card p-0">';
            $html_data .= '<div class="card-header">';
            $html_data .= '<div class="nav-item d-flex justify-content-between align-items-center">';
            $html_data .= '<h3 class="card-title">'.$data["device_name"].'</h3>';
            
            
            if ($data["device_network_status"] == 1)
                {
                $html_data .= '<span class="float-right ml-3 header-icon">';
                $html_data .= '<i class="fas fa-server text-success" data-toggle="tooltip" data-placement="top" title="Online"></i>';
                $html_data .= '</span>';
                
                } 
            else 
                {
                $html_data .= '<span class="float-right ml-3 header-icon">';
                $html_data .= '<i class="fas fa-server text-danger" data-toggle="tooltip" data-placement="top" title="Offline"></i>';
                $html_data .= '</span>';
                
                }                    

                $html_data .= '</div>';
                $html_data .= '</div>';

                $html_data .= '<div class="card-body p-0">';
                $html_data .= '<div class="row no-gutters">';

                $html_data .= '<div class="col-4 block-view-img my-auto text-center"><img class="img-fluid" src="'. $image_url .'"></div>';

                $html_data .= '<div class="col-8">';
                $html_data .= '<ul class="nav flex-column">';

                
                $html_data .= '<li class="nav-item">';
                $html_data .= '<span class="nav-link">IP Address';
                $html_data .= '<span class="float-right device_ip">'.$data["device_ip"].'</span>';
                $html_data .= '</span>';
                $html_data .= '</li>';
                
                
                if($data["device_type"]==1 || $data["device_type"]==2){
                    $html_data .= '<li class="nav-item">';
                    $html_data .= '<span class="nav-link">Presence Loop Status';
                    if($data["presence_loop_status"]==1)
                        $html_data .= '<span class="float-right presence_loop_status"><div class="dot-indicator bg-success-gradient" data-toggle="tooltip" data-placement="top" title="Active"></div></span>';
                    else
                    $html_data .= '<span class="float-right presence_loop_status"><div class="dot-indicator bg-danger-gradient" data-toggle="tooltip" data-placement="top" title="Deactive"></div></span>';
                    $html_data .= '</span>';
                    $html_data .= '</li>';
                }
               
                if($data["device_type"]==1 || $data["device_type"]==2){
                    $html_data .= '<li class="nav-item">';
                    // $html_data .= '<span class="nav-link">Safety Loop Status';
                    // if($data["safety_loop_status"]==1)
                    //     $html_data .= '<span class="float-right safety_loop_status"><div class="dot-indicator bg-success-gradient" data-toggle="tooltip" data-placement="top" title="Active"></div></span>';
                    // else
                    // $html_data .= '<span class="float-right safety_loop_status"><div class="dot-indicator bg-danger-gradient" data-toggle="tooltip" data-placement="top" title="Deactive"></div></span>';
                    // $html_data .= '</span>';
                    $html_data.=' <div class="d-flex justify-content-between align-items-center mb-4 mt-4">                    
                    <div class="col"><input type="submit" class="btn btn-outline-info btn-open-barrier btn-block" value="Open Barrier1" ></div>
                    <div class="col"><input type="submit" class="btn btn-outline-info btn-open-barrier btn-block" value="Open Barrier2" ></div>                   
                    </div>';
                    $html_data .= '</li>';
                }

                $html_data .= '</ul>';
                $html_data .= '</div>';

                $html_data .= '</div>'; // close row
                $html_data .= '</div>'; // close card-body

                $html_data .= '</div>';
                $html_data .= '</div>';


                }
            
            //access failure reason    
                $query_string = "SELECT count(*) as count,fault_code_text FROM ".DB_REPORTING.".smartgate_request where date(api_datetime_response)=current_date and (fault_code_text is not null and fault_code_text!='') group by fault_code_text";
                $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
                    
                if (mysqli_num_rows($result)) {
                $html_data .= '<div class="col-md-7">';
                $html_data .= '<div class="card ">';
                $html_data .= '<div class="card-body p-0">';
                $html_data .= '<table class="table table-blue table-bordered">';
                $html_data .= '  <thead><tr>';
                $html_data .= '   <th>ACCESS FAILURE</th>';
                $html_data .= '   <th>COUNT</th>';        
                $html_data .= '  </tr></thead>';
                
        
                while($data = mysqli_fetch_array($result)) 
                    {                                            
                        $html_data .= '  <tr>';               
                        $html_data .= '   <td>' . $data['fault_code_text'] . '</td>';
                        $html_data .= '   <td>' . $data['count'] . '</td>';
                        $html_data .= '  </tr>';
                        $html_data .= '  <tr>';                          
                    }
               
        
              
        
                $html_data .= '  </table>';
                $html_data .= ' </div>';
        
                $html_data .= '  </div>';
                $html_data .= '  </div>'; //col md 4     
                }
                
                            
            
                

            $html_data .= '</div>';
            $html_data .= '</div>';
            
            
                
            
                mysqli_close($con);
                echo $html_data;
        }    
    }

function report_watchdog_device_log($device_number)
    {          
        $con=$this->db_connect(DB_DASHBOARD);
        if($con!=NULL)
            {
            $json=json_decode($json,TRUE);
            $sql="select * from watchdog_network_logs where device_number=".$device_number."  order by id desc limit 5";                                                  
            $result = $con->query($sql);
            $html_data='';
 
	    $html_data.='<thead>';
            $html_data.='<tr>';            
            $html_data.='<th>MESSAGE</th>';
            $html_data.='<th>CONNECTION FAILURE</th>';
            $html_data.='<th>CONNECTION RESTORED</th>';
            $html_data.='</tr>';
            $html_data.='</thead>';

            // table data
            $html_data.='<tbody>'; 
            if (mysqli_num_rows($result)) {
            
		    while($data = mysqli_fetch_array($result)) 
		        {
		        $html_data.='<tr>';                
		        $html_data.='<td>' . $data['comment']. '</td>';
		        $html_data.='<td>' . $data['connection_failure']. '</td>';
		        $html_data.='<td>' . $data['connection_restored']. '</td>';
		        $html_data.='</tr>';
		    }           
            }
	    else
	    {
			$html_data.='<tr>';                
		        $html_data.='<td colspan=3>No Network Logs</td>';
		        $html_data.='</tr>';
	    }
	    $html_data.='</tbody>';

            mysqli_close($con);
            echo $html_data;
        }
    else
        echo "No server connection";
    }   
    
function device_details($device_number)
    {          
        $con=$this->db_connect(DB_DASHBOARD);
        if($con!=NULL)
            {            
            $sql="select * from watchdog_device_status where device_number=".$device_number."";                                                              
            $result = $con->query($sql);


            $html_data='';

            if($data = mysqli_fetch_array($result)) 
                {                                   
                $html_data.='<h4 class="mb-3">'.$data["device_name"].'</h4>';
                $html_data.='<p><strong>Category: </strong>Entry column</p>';
                $html_data.='<p><strong>IP Address: </strong>'.$data["device_ip"].'</p>'; 
                if($data["device_type"]==1 || $data["device_type"]==2){
                    if($data["presence_loop_status"]==1)
                        $html_data.='<p><strong>Presence loop status: </strong><span class="dot-indicator bg-success-gradient" data-toggle="tooltip" data-placement="top" title="Active"></span></p>';
                    else
                    $html_data.='<p><strong>Presence loop status: </strong><span class="dot-indicator bg-danger-gradient" data-toggle="tooltip" data-placement="top" title="Deactive"></span></p>';

                    $html_data.='<p><strong>Last presence loop active: </strong>'.$data["last_presence_loop_active"].'</p>';
                    $html_data.='<p><strong>Last presence loop deactive: </strong>'.$data["last_presence_loop_deactive"].'</p>';
                    if($data["safety_loop_status"]==1)
                        $html_data.='<p><strong>Safety loop status: </strong><span class="dot-indicator bg-success-gradient" data-toggle="tooltip" data-placement="top" title="Active"></span></p>';
                    else
                        $html_data.='<p><strong>Safety loop status: </strong><span class="dot-indicator bg-danger-gradient" data-toggle="tooltip" data-placement="top" title="Deactive"></span></p>';

                    $html_data.='<p><strong>Last safety loop active: </strong>'.$data["last_safety_loop_active"].'</p>';
                    $html_data.='<p><strong>Last safety loop deactive: </strong>'.$data["last_safety_loop_deactive"].'</p>';
                }
            }
            mysqli_close($con);
            echo $html_data;
        }
    else
        echo "No server connection";
    }
    
function hourly_entry()
    {
    $con=$this->db_connect(DB_REPORTING);
    if($con!=NULL)
        {
        $entry=array();
        for($i=0;$i<24;$i++) 
            $entry[$i]=0;               
        $sql="SELECT HOUR(api_datetime_response) as hour, COUNT(*) as num_rows FROM smartgate_request where date(api_datetime_response)=current_date and response_status='success' GROUP BY HOUR(api_datetime_response)";                                                              
        $result = $con->query($sql);
        

        while($data = mysqli_fetch_array($result)) 
            {    
            $i=$data["hour"];
            $entry[$i]=$data["num_rows"];              
            }
            mysqli_close($con);
        return $entry;
        }
    else
        echo "No server connection" ;     

    }

function hourly_entry_avg($type)
    {
    $con=$this->db_connect(DB_REPORTING);
    if($con!=NULL)
        {
        $entry=array();
        for($i=0;$i<24;$i++) 
            $entry[$i]=0;      
        
        $sql="SELECT HOUR(api_datetime_response) as hour, COUNT(*) as num_rows FROM smartgate_request  where response_status='success' ";
        if($type==0)   
            $sql.=" and date(api_datetime_response)=(DATE(NOW()) + INTERVAL -7 DAY)";            
        if($type==1)
            $sql.=" and date(api_datetime_response) between (DATE(NOW()) + INTERVAL -7 DAY) and CURRENT_DATE";                   
        
        $sql.=" GROUP BY HOUR(api_datetime_response)";
        $result = $con->query($sql);
        

        while($data = mysqli_fetch_array($result)) 
            {    
            $i=$data["hour"];
            $entry[$i]=$data["num_rows"];              
            }
            mysqli_close($con);
        return $entry;
        }
    else
        echo "No server connection" ;     

    }

function log_manual_movement($json,$message,$device_number,$device_name)
{
    try{
            $conn=$this->db_connect(DB_REPORTING);
            $result = "Failed";
            if($message!="Error")  
            {
                $result = "Success";
            }
            $sql = "insert into smartgate_request(device_number,device_name,api_datetime_request,api_datetime_response,request,api_response,response_status,gate_code) values(".$device_number.",'".$device_name."',NOW(),NOW(),'".$json."','".$message."','".$result."','Manual Entry')";
            if($conn->query($sql)==TRUE)
            {
                $this->log("Manual Movement logged into Smartgate Request table");
                    
            }
            else
            {
                $this->log("Manual Movement log error: ".$conn->error );
            }           
            $conn->close();
    }
    catch(exception $e)
    {
            $this->log("Exception:".$ex);
    }  
}

}
?>


