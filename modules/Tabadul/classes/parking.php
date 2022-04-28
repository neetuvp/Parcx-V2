<?php
ini_set("display_errors","1");
require_once("config.php");
class parking
{

function log($message)
{
	date_default_timezone_set('Asia/Dubai');
    $myfile=fopen("/opt/parcx/Logs/WebApplication/ApplicationLogs/parking-".date("Y-m-d").".log","a");//fopen("../log/log-".date("Y-m-d").".log","a");
    fwrite($myfile, date("H:i:s")."\t".$message."\n");
    fclose($myfile);
}

function DBConnection_MySQL($databaseName)
{
    $serverName = MYSQLSERVER;
    $uid = USER;
    $pwd = PASSWORD;
    //$databaseName = DB;
    $conn = new mysqli($serverName, $uid, $pwd,$databaseName);
    // Check connection
    if($conn->connect_error)
    {
            die("Mysql Connection failed: " . $conn->connect_error);
            $this->log("Mysql Connection Failed:". $conn->connect_error);
    }               
    //$this->log("Mysql Connected successfully");
    return $conn;
}

function get_device_name($id)
    {
    $con=$this->DBConnection_MySQL(DB_SERVER);
    if($con)
       {
        $sql="select device_name from system_devices where device_number='".$id."'";
        $result = $con->query($sql);
            //echo $sql;
       if($data = mysqli_fetch_array($result))
         {
            $device=$data["device_name"];
            mysqli_close($con);
            return $device;
         }
       }
    }

function get_devices()
    {
    $con=$this->DBConnection_MySQL(DB_SERVER);
    $html_data="<option value=0>Select Device</option>";
    if($con)
        {
        $query_string = "Select device_name,device_number from system_devices where device_category in(1,2)";
        $result = $con->query($query_string);
        while($data = mysqli_fetch_array($result))
        {         
            $html_data .= "<option value = '".$data['device_number']."'>".$data['device_name']."</option>";  
        }
        mysqli_close($con);
        echo $html_data;
        }
    }
  function get_camera_devices()
  {
      
    $this->log("get_camera_devices"); 
    $con=$this->DBConnection_MySQL(DB_SERVER);
    $html_data="<option value=0>Select Device</option>";
    if($con)
        {
        $this->log("here");
        $query_string = "Select device_name,device_number from system_devices where device_category = 5";
        $result = $con->query($query_string);
        while($data = mysqli_fetch_array($result))
        {                
            $html_data .= "<option value = '".$data['device_number']."'>".$data['device_name']."</option>";  
        }
        mysqli_close($con);
        echo $html_data;
        }
    }


function report_plates_captured($json)
    {
        $this->log("Called Plates Captured report,input:".$json);                
        $con=$this->DBConnection_MySQL(DB_REPORTING);
            $json=json_decode($json);
            $sql="select *,Date(capture_date_time) as capture_date from plates_captured where  capture_date_time >='".$json->{'fromDate'}."' and capture_date_time <= '".$json->{'toDate'}."'";
            if($json->{'device'}>0)
                $sql.=" and camera_device_number='".$json->{'device'}."'";

            if($json->{'plate'}!="")
                $sql.=" and plate_number like '%".$json->{'plate'}."%'";
            

            $sql.=" order by capture_date_time desc";
            //$this->log("Called Plates Captured report,input:".$sql);            

            $html_data="";
            $html_data.='<table id="RecordsTable" class="table table-blue table-bordered table-striped">';
            $html_data.='<thead><tr>';
	    $html_data.='<th>#</th>';
            $html_data.='<th>CAMERA NAME</th>';
            $html_data.='<th>DATE TIME</th>';
            $html_data.='<th>PLATE NUMBER</th>';
            $html_data.='<th>PLATE CATEGORY</th>'; 
            $html_data.='<th>CROPPED IMAGE</th>'; 
            $html_data.='<th>FULL IMAGE</th>';          
            $html_data.='</tr></thead>';
	    $html_data.='<tbody>';
            $i=1;
            $result = $con->query($sql);
            //echo $sql;
            while($data = mysqli_fetch_array($result))
            {             
                $html_data.= '<tr><input type="hidden" id="plate_image'.$i.'" value="'.$data['plate_image_name'].'">';
		$html_data.= '<td>' . $i. '</td>';
                $html_data.= '<td>' . $data['camera_name']. '</td>';
                //$html_data.= '<div class="col">' . $this->get_device_name($data['camera_device_number']). '</div>';
                $html_data.= '<td>' . $data['capture_date_time']. '</td>';
                $html_data.= '<td>' . $data['plate_number']. '</td>';
                $html_data.= '<td>' . $data['plate_type']. '</td>';
                //$html_data.= '<div class="col"><img src ="'.ANPRImageURL.'/default.jpg" width="100"; height="50";></div>';         
                $html_data.= '<td><img
                        src="'.ANPRImageURL.'\\'.$data["camera_device_number"].'\\'.$data["capture_date"].'\\Crop_'.$data['plate_image_name'].'"
                        width="100" ; height="50" ;></center>
                </td>';
                $html_data.= '<td>
                <input type="button" data-value = "'.$i.'" data-toggle="modal" data-target="#image-modal"
                    value="View" class="btn btn-link">
                </td>';
                //$html_data.= '<div class="col"><a target="_blank" href = "'.ANPRImageURL.'/Scene/'.$data['CAMERA_DEVICE_NUMBER'].'/Scene_'.$data['PLATE_IMAGE_NAME'].'">View</a></center></div>';
                $html_data.= '</tr>';
                $i = $i + 1;
            }
		$html_data.='</tbody></table>';
            mysqli_close($con);
            echo $html_data;
            
    }


    function get_anpr_image_platescaptured($json)
    {
        //echo "hello";
        $this->log("Called ANPR Image ,input:".$json);
        $json=json_decode($json);
        $html_data="";
        $plateImage = $json->{'plate_image'};
        //$camera_id = $json->{'camera_no'};
        $html_data.='<div>';
        //$html_data.='<img src ="'.ANPRImageURL.'/default.jpg" width="100%"; height="500";>';
       // $html_data.='<img src ="'.ANPRImageURL.'\\'.$plateImage.'" width="700"; height="700";>';
	$html_data.='<img src ="'.ANPRImageURL.'\\'.$data["camera_device_number"].'\\'.$data["capture_date"].'\\Scene'.$data['plate_image_name'].'" width="700"; height="700";>';
        $html_data.='</div>';
        echo $html_data;
    }

function report_access_request($json)
    {
      $this->log("Called Access Request report,input:".$json);
      $con=$this->DBConnection_MySQL(DB_REPORTING);
      if($con!=NULL)
      {
      $json=json_decode($json);
      $sql="select * from smartgate_request where api_datetime_request>= '".$json->{'fromDate'}."' and api_datetime_request<='".$json->{'toDate'}."'";
      //if($json->{'device'}>0)
        //$sql.=" and device_number='".$json->{'device'}."'";

        if($json->{'plate'}!="")
            $sql.=" and plate_number like '%".$json->{'plate'}."%'";
	if($json->{'qrcode'}!="")
            $sql.=" and reference_number like '%".$json->{'qrcode'}."%'";
        if($json->{'type'}!="0")
        {
            if($json->{'type'}=="Access Allowed")
            {
                $sql.=" and response_status = 'Success'";
            }
            else {
                $sql.=" and fault_code_text like '%".$json->{'type'}."%'";
            }
        }
            $sql.=" order by api_datetime_request desc";
            //echo $sql;
           //$this->log("Called Access Request report,input:".$sql);
            // nd $html_data.='';

            $html_data="";

            // open table
          
            //$html_data.='<table width="100%" class="table table-blue jspdf-table">';
	$html_data.='<table id="RecordsTable" class="table table-blue table-bordered table-striped">';
            // table header
            $html_data.='<thead>';
            $html_data.='<tr>';
            //$html_data.='<th>DEVICE NAME</th>';
	    $html_data.='<th>#</th>';
            $html_data.='<th>QR CODE</th>';
            $html_data.='<th>PLATE NUMBER</th>';
            $html_data.='<th>DATE TIME</th>';
	    $html_data.='<th>REQUEST</th>';
            $html_data.='<th>RESPONSE STATUS</th>';
            $html_data.='<th>RESPONSE</th>';
            $html_data.='<th>FAULT</th>';
            //$html_data.='<th>IMAGE</th>';
            $html_data.='</tr>';
            $html_data.='</thead>';

            // table data
            $html_data.='<tbody>';

            $i = 1;

            $result = $con->query($sql);
            while($data = mysqli_fetch_array($result))
            {                
                $html_data.='<tr data-toggle="modal" data-target="#plate-details-modal" id="access_record" access_id='.$data['id'].'>';
                //$html_data.='<td>' . $this->get_device_name($data['device_number']). '<input type="hidden" id="device_no'.$i.'" value="'.$data['device_number'].'"></td>';
		$html_data.='<td>' . $i. '</td>';
                $html_data.='<td class="tkt">' .$data['reference_number']. '</td>';
                $html_data.='<td id="plate_number'.$i.'">' . $data['plate_number']. '</td>';
                $html_data.='<td>' . $data['api_datetime_request']. '</td>';
		$html_data.='<td>' . $data['request']. '</td>';
                $html_data.='<td>' . $data['response_status']. '</td>';
                $html_data.='<td>' . $data['api_response']. '</td>';
                $html_data.='<td>' . $data['fault_string']. '</td>';
                //$html_data.='<td class="tkt" data-value="'.$i.'" data-toggle="modal" data-target="#image-modal">View</td>';
                $html_data.='</tr>';
                $i = $i + 1;
            }

            $html_data.='</tbody>';

            // close table
            $html_data.='</table>';


            //

            mysqli_close($con);
            echo $html_data;
        } else
            echo "No server connection";
    }
    
    function access_request_live()
    {
        $this->log("Called Access Request report live");
        $con=$this->DBConnection_MySQL(DB_REPORTING);
        if($con!=NULL)
        {        
        $sql="select * from smartgate_request order by api_datetime_request desc";
        //$this->log("Called Access Request report,input:".$sql);
            // nd $html_data.='';

        $html_data="";

        // open table

        //$html_data.='<table width="100%" class="table table-blue jspdf-table">';
	$html_data.='<table id="RecordsTable" class="table table-blue table-bordered table-striped">';
        // table header
        $html_data.='<thead>';
        $html_data.='<tr>';
        $html_data.='<th>DATE TIME</th>';
        $html_data.='<th>QR CODE</th>';
        $html_data.='<th>PLATE NUMBER</th>';
        $html_data.='<th>STATUS</th>';
        $html_data.='</tr>';
        $html_data.='</thead>';

        // table data
        $html_data.='<tbody>';

        $i = 1;

        $result = $con->query($sql);
        while($data = mysqli_fetch_array($result))
        {                
            $html_data.='<tr data-toggle="modal" data-target="#plate-details-modal" id="access_record" access_id='.$data['id'].'>';
            $html_data.='<td>' . $data['api_datetime_request']. '</td>';
            $html_data.='<td class="tkt">' .$data['reference_number']. '</td>';
            $html_data.='<td id="plate_number'.$i.'">' . $data['plate_number']. '</td>';
            if($data['response_status']=="Success")
                $html_data.='<td>Access Allowed</td>';  
            else
                $html_data.='<td>Access Denied,'.$data['fault_code_text'].'</td>';  
            //$html_data.='<td class="tkt" data-value="'.$i.'" data-toggle="modal" data-target="#image-modal">View</td>';
            $html_data.='</tr>';
            $i = $i + 1;
        }

        $html_data.='</tbody>';

        // close table
        $html_data.='</table>';


        //

        mysqli_close($con);
        echo $html_data;
        } else
            echo "No server connection";
    }



function report_access_whitelist()
    {
      $edit = 0;
      $con=$this->DBConnection_MySQL(DB_SERVER);
      if($con!=NULL)
      {
            //$json=json_decode($json);
            $sql="select b.* from system_menu a join system_user_role_rights b on a.menu_id = b.menu_id where a.menu_name='access_whitelist' and b.user_role_id=".$_SESSION["userRollId"];
            $result = $con->query($sql);
            if($data = mysqli_fetch_array($result))
            {
                if($data["rr_edit"]==1)
                {
                   $edit = 1; 
                }
            }
            if($_SESSION["userRollId"]==100)
            {  
               $edit = 1;  
            }
            $sql="select * from access_whitelist order by access_whitelist_id desc";
      
            //echo $sql;
            // $this->log("Called Access Request report,input:".$sql);
            // nd $html_data.='';

            $html_data="";

          

            // table header
            $html_data.='<thead>';
            $html_data.='<tr>';
            //$html_data.='<th>DEVICE NAME</th>';
	    $html_data.='<th>#</th>';
            $html_data.='<th>Customer Name</th>';
            $html_data.='<th>Plate Number</th>';
	    $html_data.='<th>Ticket Id</th>';
	    $html_data.='<th>Tag</th>';
            $html_data.='<th>Validity From</th>';
	    $html_data.='<th>Validity To</th>';
            if($edit ==1 )
                $html_data.='<th></th>';
            $html_data.='</tr>';
            $html_data.='</thead>';

            // table data
            $html_data.='<tbody>';

            $i = 1;

            $result = $con->query($sql);
            while($data = mysqli_fetch_array($result))
            {                
                $html_data.="<tr data-id='".$data["access_whitelist_id"]."'>";
                //$html_data.='<td>' . $this->get_device_name($data['device_number']). '<input type="hidden" id="device_no'.$i.'" value="'.$data['device_number'].'"></td>';
		$html_data.='<td>' . $i. '</td>';
                $html_data.='<td class="tkt">' .$data['customer_name']. '</td>';
                $html_data.='<td id="plate_number'.$i.'">' . $data['plate_number']. '</td>';
		$html_data.='<td>' . $data['ticket_id']. '</td>';
		$html_data.='<td>' . $data['tag']. '</td>';
                $html_data.='<td>' . $data['validity_start_date']. '</td>';
		$html_data.='<td>' . $data['validity_expiry_date']. '</td>';
                if($edit ==1 )
                    $html_data.='<td><button type="button" class="col btn btn-info whitelist-edit"><i class="fas fa-edit"></i>Edit</button></td>';
                $html_data.='</tr>';
                $i = $i + 1;
            }

            $html_data.='</tbody>';

            
            //

            mysqli_close($con);
            echo $html_data;
        } else
            echo "No server connection";
    }
function get_details($json)
{      
      $json_data = json_decode($json,true);

      $con=$this->DBConnection_MySQL(DB_SERVER);
      if($con!=NULL)
      {
	$result = $con->query("SELECT * FROM access_whitelist where access_whitelist_id=".$json_data["id"]);
	$row = $result -> fetch_assoc();
	echo (json_encode($row));
	
      }
	mysqli_close($con);
}


function update_plate_captured($json)
    {
    $result="0";          
    $json_data = json_decode($json,true);    
    $con=$this->DBConnection_MySQL(DB_REPORTING);
    if($con!=NULL)
        {
        $plate_number=$json_data["plate_number"];        
        $id = $json_data["plate_captured_id"];
        if($id>0)
            {                
            $initial_plate=$json_data["initial_plate_number1"];    
            if($json_data["plate1_already_corrected"]==0)    
                $sql = "update plates_captured  set plate_number='".$plate_number."',initial_plate_number='".$initial_plate."',plate_corrected_date_time=CURRENT_TIMESTAMP,user_name='".$_SESSION['username']."' where plate_captured_id = '".$id."'";                    
            else
                $sql = "update plates_captured  set plate_number='".$plate_number."',plate_corrected_date_time=CURRENT_TIMESTAMP,user_name='".$_SESSION['username']."' where plate_captured_id = '".$id."'";             

		//this->log("sql".$sql);        
            if($con->query($sql)==TRUE) 
                $result="1";
            }
	    else
	    {
		$this->log("Plate Correction Error".$con->error);
            }
        $id = $json_data["plate_captured_id_secondary"];
        if($id>0)
            {                
            $initial_plate=$json_data["initial_plate_number2"];  
            if($json_data["plate2_already_corrected"]==0)    
                $sql = "update plates_captured  set plate_number='".$plate_number."',initial_plate_number='".$initial_plate."',plate_corrected_date_time=CURRENT_TIMESTAMP,user_name='".$_SESSION['username']."' where plate_captured_id = '".$id."'";                              
            else
                $sql = "update plates_captured  set plate_number='".$plate_number."',plate_corrected_date_time=CURRENT_TIMESTAMP,user_name='".$_SESSION['username']."' where plate_captured_id = '".$id."'";               
		
		//$this->log("sql".$sql);                          
            if($con->query($sql)==TRUE) 
                $result="1";
            }
	    else
	    {
		$this->log("Plate Correction Error".$con->error);
            }
        mysqli_close($con);          
        if($result=="1")        
            $result="MP0".$plate_number."P1C0".$json_data["plate_captured_id"]."C1C2".$json_data["plate_captured_id_secondary"]."C3" ;                 
        }
    
    return $result  ;  
    }
    

function get_access_request_details($json)
{      
      $json_data = json_decode($json,true);
      $id = $json_data["id"];
      $con=$this->DBConnection_MySQL(DB_REPORTING);
      if($con!=NULL)
      {
        if($id!="")
            $sql = "SELECT a.*,b.plate_number as plate,b.initial_plate_number,b.plate_image_name,b.camera_device_number,b.capture_date_time,b.plate_type,b.plate_country,b.confidence_rate,Date(b.capture_date_time) as capture_date FROM smartgate_request as a left join plates_captured as b on a.plate_capture_id = b.plate_captured_id  where a.id = ".$id;
        else
            $sql = "SELECT a.*,b.plate_number as plate,b.initial_plate_number,b.plate_image_name,b.camera_device_number,b.capture_date_time,b.plate_type,b.plate_country,b.confidence_rate,Date(b.capture_date_time) as capture_date FROM smartgate_request as a left join plates_captured as b on a.plate_capture_id = b.plate_captured_id  order by a.api_datetime_request desc limit 1";
        
	$result = $con->query($sql);
    $row1 = $result -> fetch_assoc();
    $id=$row1["plate_capture_id_secondary"];
    if($id>0)    
        {
       
        $sql = "SELECT initial_plate_number initial_plate_number2,plate_number plate_number2,plate_image_name plate_image_name2,camera_device_number camera_device_number2,capture_date_time capture_date_time2,plate_type plate_type2,plate_country plate_country2,confidence_rate confidence_rate2,Date(capture_date_time) as capture_date2 FROM plates_captured  where plate_captured_id = '".$id."'";

        $result = $con->query($sql);
        $row2 = $result -> fetch_assoc();

        $row = array_merge($row1, $row2);
        echo (json_encode($row));	
        }
    else
        echo (json_encode($row1));	
      }
	mysqli_close($con);
}

function get_secondary_plate_details($json)
{      
      $json_data = json_decode($json,true);
      $id = $json_data["plate_capture_id"];
      $con=$this->DBConnection_MySQL(DB_REPORTING);
      if($con!=NULL)
      {
        if($id!=""){
            $sql = "SELECT plate_number,plate_image_name,camera_device_number,capture_date_time,plate_type,plate_country,confidence_rate FROM plates_captured  where plate_captured_id = ".$id;
            $result = $con->query($sql);
            $row = $result -> fetch_assoc();
            echo (json_encode($row));
        }
        else {
            echo "";
        }
      }
	mysqli_close($con);
}

function accessID()
{

	$timeFirst  = strtotime('2020-01-01 00:00:00');
	$timeSecond = strtotime(date("Y-m-d h:i:s"));
	$differenceInSeconds = $timeSecond - $timeFirst;
	return $differenceInSeconds;				

}

function add_update_access_whitelist($json)
{
	$this->log("Called add_access_whitelist,input:".$json);
	$json_data = json_decode($json,TRUE);
	$id = $json_data["id"];
try{
	$conn=$this->DBConnection_MySQL(DB_SERVER);
	$result = "Failed";
	if($id=="")
	{
		$sql = "insert into access_whitelist(facility_number,carpark_number,access_zones,ticket_id,plate_number,tag,tag_serial,tag_tid,country,validity_start_date,validity_expiry_date,customer_name,antipassback_enabled,season_card,wallet_payment,corporate_parker,personalized_message_line1,personalized_message_line2,access_id,status) values(".$json_data["facility_number"].",'".$json_data["carpark_number"]."','".$json_data["access_zones"]."','".$json_data["ticket_id"]."','".$json_data["plate_number"]."','".$json_data["tag"]."','".$json_data["tag_serial"]."','".$json_data["tag_tid"]."','".$json_data["country"]."','".$json_data["start_date"]."','".$json_data["expiry_date"]."','".$json_data["customer_name"]."',".$json_data["anti_passback"].",".$json_data["season_card"].",".$json_data["wallet_payment"].",".$json_data["corporate_parker"].",'".$json_data["message1"]."','".$json_data["message2"]."','".$this->accessId()."',1)";
		if($conn->query($sql)==TRUE)
		{
			$this->log("Access Whitelist record created successfully");
			$result = "Successful";
		}
		else
		{
			$this->log("Access Whitelist Error: ".$conn->error );
		}

	}
	else
	{
		$sql = "Update access_whitelist set ticket_id = '".$json_data["ticket_id"]."',plate_number='".$json_data["plate_number"]."',tag='".$json_data["tag"]."',tag_serial='".$json_data["tag_serial"]."',tag_tid='".$json_data["tag_tid"]."',country='".$json_data["country"]."',validity_start_date='".$json_data["start_date"]."',validity_expiry_date='".$json_data["expiry_date"]."',customer_name='".$json_data["customer_name"]."',antipassback_enabled=".$json_data["anti_passback"].",season_card=".$json_data["season_card"].",wallet_payment=".$json_data["wallet_payment"].",corporate_parker=".$json_data["corporate_parker"].",personalized_message_line1='".$json_data["message1"]."',personalized_message_line2='".$json_data["message2"]."' where access_whitelist_id=".$id;
		if($conn->query($sql)==TRUE)
		{
			$this->log("Access Whitelist record updated successfully");
			$result = "Successful";
		}
		else
		{
			$this->log("Access Whitelist Update Error: ".$conn->error);
		}

	}
	
	$conn->close();
}
catch(exception $e)
{
	$this->log("Exception:".$ex);
}
	echo $result;
}

function checkPageAccess($request)
{
    
    $access=array();
    $access["view"] = 0;
    $access["add"] = 0;
    $access["edit"] = 0;
    $access["delete"] = 0;
    try {
        $page = $request["page"];
        $user_role_id = $_SESSION["userRollId"];

        if ($user_role_id == 100) {
            $access["view"] = 1;
            $access["add"] = 1;
            $access["edit"] = 1;
            $access["delete"] = 1;
            return $access;
        }

        $menu_add=0;
        $menu_delete=0;
        $menu_edit=0;
        $menu_id;
        $conn=$this->DBConnection_MySQL(DB_SERVER);
        
        $sql = "SELECT menu_id,menu_add,menu_edit,menu_delete from  system_menu where menu_name='".$page."' and menu_status>0";
        $result = $conn->query($sql);
        if($row = $result -> fetch_assoc())
        {
            $menu_id = $row["menu_id"];
            $menu_add = $row["menu_add"];
            $menu_edit = $row["menu_edit"];
            $menu_delete = $row["menu_delete"];

        


            $sql = "SELECT rr_view,rr_add,rr_edit,rr_delete FROM system_user_role_rights where menu_id=" .$menu_id." and user_role_id=".$user_role_id;
            $result = $conn->query($sql);
            if($row = $result -> fetch_assoc())
            {
                $access["view"] = $row["rr_view"];

                if($menu_add==1)
                    $access["add"] = $row["rr_add"];
                if($menu_edit==1)
                    $access["edit"] = $row["rr_edit"];
                if($menu_delete==1)
                    $access["delete"] = $row["rr_delete"];
            }
            
        }

        
        $conn->close();
    }    
    catch(exception $e)
    {
            $this->log("Exception:".$ex);
    }
    return $access;
//print_r($access);
}
   
/*function get_anpr_image_accessrequest($json)
{
    //echo "hello";
    $this->log("Called ANPR Image ,input:".$json);
    $html_data="";
    $con=$this->DBConnection_MySQL();
    
      if($con!=NULL)
      {
        $json=json_decode($json);
        $sql="select camera_id from system_devices where device_number = ".$json->{'device_no'};
        //echo $sql;
        $stmt=oci_parse($con,$sql);
        if(!$stmt)
        $this->error($con);
        oci_execute($stmt);
        if($row = oci_fetch_assoc($stmt))
        {
            $camera_id = $row["CAMERA_ID"];
        }
        else{
            $camera_id = 0;
        }
		
		$plate = $json->{'plate_number'};
		if(strpos($plate,'POL')!==false)
		{
			$plate = substr($plate,3);
		}
		else if ((strpos($plate,'CC')!==false)||(strpos($plate,'CD')!==false))
		{
			$plate = substr($plate,2);
		}
        $sql = "Select plate_image_name from (select * from plates_captured where plate_number = '".$plate."' order by id desc) where camera_device_number =".$camera_id." and rownum=1";
		$stmt=oci_parse($con,$sql);
        if(!$stmt)
        $this->error($con);
        oci_execute($stmt);
        
        if($row = oci_fetch_assoc($stmt))
        {

            $plateImage = $row["PLATE_IMAGE_NAME"];
            $html_data.='<div>';
                //$html_data.='<img src ="'.ImageURL.'/default.jpg" width="100%"; height="500";>';
                $html_data.='<img src ="'.ANPRImageURL.'/Scene/'.$camera_id.'/Scene_'.$plateImage.'" width="100%"; height="500";>';
                $html_data.='</div>';
                echo $html_data;
        }
      }
      else{
        echo "No server connection";
      }
}*/
    
}
?>

