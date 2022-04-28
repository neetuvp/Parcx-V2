<?php
require_once("config.php");
class User
{
    function log($message)
    {
        $myfile=fopen("../log/log-".date("Y-m-d").".log","a");
        fwrite($myfile, date("H-i-s")."\t".$message."\n");
        fclose($myfile);
    }
    
    function DBConnection_MySQL()
    {
        $serverName = MYSQLSERVER;
        $uid = USER;
        $pwd = PASSWORD;
        $databaseName = DB;
        $conn = new mysqli($serverName, $uid, $pwd,$databaseName);
        // Check connection
        if($conn->connect_error)
        {
                die("Mysql Connection failed: " . $conn->connect_error);
                $this->log("Mysql Connection Failed:". $conn->connect_error);
        }               
        $this->log("Mysql Connected successfully");
        return $conn;
    }


function response($status,$message)
    {
    $this->log($message);
    header("HTTP/1.1".$status);
    $response['status']=$status;
    $response['message']=$message;
    $json_response=json_encode($response);   
    return $json_response;
    }//end of .response

function login($json)
        { 

        $json=json_decode($json);
        $this->log("Login, username:".$json->{'username'});
        $con=$this->DBConnection_MySQL();
        if($con!=NULL)
            {            
            $sql="select * from system_users where username='".$json->{'username'}."' and password=md5('".$json->{'password'}."') ".
                    "and validity_from_date<=CURRENT_DATE and validity_to_date>=CURRENT_DATE";                                      
                    $result = $con->query($sql);
            if($data = mysqli_fetch_array($result))
                {
                $status=$data['account_status'];
                mysqli_close($con);
                if($status==1)
                    {
                    $_SESSION['user']=$json->{'username'};
                    $_SESSION['userid']=$data['user_id'];
		    $_SESSION['last_login_timestamp'] = time();
                    return $this->response(200,'login success');           
                    }
                else
                    {
                    return $this->response(500,'Your In Disable Mode. Please Contact Admin');
                    }
                }
            else
                {
                    mysqli_close($con);
                    return $this->response(500,'Wrong Username / Password Combination Or Validity expired');
                }
            }
        else
            {
            return $this->response(500,'No Server connection');
            }
        }
function get_active_users()
    {    
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {
        $sql="select * from system_users where account_status=1";
        $result = $con->query($sql);

        $html_data="";
        $html_data.='<div class="card-header">';
        $html_data.='<div class="d-flex justify-content-between align-items-center">';                 
        $html_data.='<div class="col">USER NAME</div>';
        $html_data.='<div class="col">NAME</div>';       
        $html_data.='<div class="col">ROLE</div>';
        $html_data.='<div class="col">PHONE</div>';
        $html_data.='<div class="col">VALIDITY</div>';        
        $html_data.='<div class="col"></div>';
        $html_data.='<div class="col"></div>';
        $html_data.='<div class="col"></div>';
        $html_data.='</div>';
        $html_data.='</div>';
        while($data = mysqli_fetch_array($result))
            {
            $id = $data['user_id'];
            $html_data.= '<div class="table-view">';
            $html_data.= '<div class="card-text">';
            $html_data.= '<div class="d-flex justify-content-between align-items-center">';
            $html_data.= '<div class="col" id="username-' .$id . '">' . $data['username']. '</div>';             
            $html_data.= '<div class="col" id="name-' .$id . '">' . $data['operator_name'] . '</div>';
            if($data['user_role_id']=="102")
                $role="Cashier";
            else
                $role="Admin";
            $html_data.= '<div class="col" id="role-' .$id . '">' . $role . '</div>';
            $html_data.= '<div class="col" id="phone-' .$id . '">' . $data['phone'] . '</div>';
            $html_data.= '<div class="col" id="from-' . $id . '">'
                .$data['validity_from_date']." to ".$data['validity_to_date']. '</div>';
            
            $html_data.="<div class='col'>";
            $html_data.= '<input type="submit" id="' . $id . '" class="btn btn-danger btn-block btn-disable-user" name="Add" value="Disable">';
            $html_data.="</div> ";
            $html_data.= "<div class='col'>";           
            $html_data.= '<input type="submit" id="edit' . $id . '" class="btn btn-info btn-block edit-user-button" name="Add" value="Edit">';
            $html_data.= "</div> ";
            $html_data.= "<div class='col'>";           
            $html_data.= '<input type="submit" id="password' . $id . '" class="btn btn-info btn-block change_password_button" data-toggle="modal" name="Add" data-target="#changePasswordModal" value="Change Password">';
            $html_data.= "</div> ";

            $html_data.= '</div>';
            $html_data.= '</div>';
            $html_data.= '</div>';
            }
       
        mysqli_close($con);
        echo $html_data;
        }
    else
        echo "No server connection";
    }
function get_disabled_users()
    {    
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {
        $sql="select * from system_users where account_status=0";
        $result = $con->query($sql);


        $html_data="";
        $html_data.='<div class="card-header">';
        $html_data.='<div class="d-flex justify-content-between align-items-center">';
        $html_data.='<div class="col">USER NAME</div>';
        $html_data.='<div class="col">NAME</div>'; 
        $html_data.='<div class="col">ROLE</div>';
        $html_data.='<div class="col">PHONE</div>';
        $html_data.='<div class="col">FROM</div>';        
        $html_data.='<div class="col"></div>';
        $html_data.='</div>';
        $html_data.='</div>';
        while($data=mysqli_fetch_array($result))
        {
        $html_data.= '<div class="table-view">';
        $html_data.= '<div class="card-text">';
        $html_data.= '<div class="d-flex justify-content-between align-items-center">';                    
        $html_data.= '<div class="col">' . $data['username']. '</div>';
        $html_data.= '<div class="col">' . $data['operator_name']. '</div>';
        if($data['user_role_id']=="102")
            $role="Cashier";
        else
            $role="Admin";

        $html_data.= '<div class="col">' .$role. '</div>';
        $html_data.= '<div class="col">' . $data['phone']. '</div>';        
        $html_data.= '<div class="col">' . $data['validity_from_date']." to ".$data['validity_to_date']. '</div>';        
        $html_data.= "<div class='col'>";
        $id = $data['user_id'];
        $html_data.= '<input type="submit" id="' . $id . '" class="btn btn-success btn-block btn-enable-user" name="Add" value="Enable">';
        $html_data.= "</div> ";
        $html_data.= "</div> ";
        $html_data.= "</div> ";
        }
        mysqli_close($con);
        echo $html_data;
        }
    else
        echo "No server connection";
    }
function add_user($json)
    {
    $this->log("Called Add user");
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {
        $json=json_decode($json);
        $sql="select * from system_users where username='".$json->{'userName'}."'";
        $result = $con->query($sql);

        if($data=mysqli_fetch_array($result))
            {
            mysqli_close($con);
            return $this->response(404,"Username already exist,change username");
            }
        else
            {
            $password=MD5($json->{'password'});
            $sql="insert into system_users(operator_name,username,phone,password,user_role_id,validity_from_date,validity_to_date,account_status)VALUES('".
            $json->{'fullName'}."','".$json->{'userName'}."','".$json->{'phone'}."','".$password."','".
            $json->{'userType'}."','".$json->{'userFromDate'}."','".$json->{'userToDate'}."',1)";        
            $result = $con->query($sql);
            
            if($result)
                {
                    mysqli_close($con);
                    return $this->response(200,"User Inserted Successfully");        
                }
            else        
                {
                    $this->error($result);
                    mysqli_close($con);
                    return $this->response(404,"Failed user insert");        
                }
            }
        }
     else
        {
        return $this->response(500,'No Server connection');
        }
    }
function enable_user($json)
    {
    $json=json_decode($json);
    $this->log("Called Enable user, uid=".$json->{'id'});
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {        
        $sql="update system_users set account_status=1 where user_id='".$json->{'id'}."'";
        $result = $con->query($sql);
        if($result)
            {
            mysqli_close($con);
            return $this->response(200,"Enabled user");
            }
        else
            {
            $this->error($Con);
            mysqli_close($con);
            return $this->response(404,"Failed Enable user");
            }
        }
    else
        {
        return $this->response(500,'No Server connection');
        }
    }
function disable_user($json)
    {
    $json=json_decode($json);
    $this->log("Called Disable user, uid=".$json->{'id'});
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {
        $sql="update system_users set account_status=0 where user_id='".$json->{'id'}."'";
        $result = $con->query($sql);
       
        if($result)
            {
                mysqli_close($con);
                return $this->response(200,"Disabled user");
            }
        else
            {
                mysqli_close($con);
                return $this->response(404,"Failed disable user");
            }
        }
    else
        {
        return $this->response(500,'No Server connection');
        }
    }

function change_password($json)
    {     
    $json=json_decode($json);
    $this->log("Called change_password, uid=".$json->{'id'});
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {
        $password=MD5($json->{'currentPassword'});
        $sql="select * from system_users where user_id='".$json->{'id'}."' and password='".$password."'";
        $result = $con->query($sql);
       if($data=mysqli_fetch_array($result))
            {
            $password=MD5($json->{'newPassword'});
            $sql="update system_users set password='".$password."' where user_id='".$json->{'id'}."'";
            $result = $con->query($sql);
            if($result)
                {
                    mysqli_close($con);
                    return $this->response(200,"Password updated");
                }
            else
                {
                    mysqli_close($con);
                    return $this->response(404,"Failed Update Password");
                }
            }
        else
            {
                mysqli_close($con);
                return $this->response(404,"Current Password is wrong");
            }
        }
    else
        {
        return $this->response(500,'No Server connection');
        }
    }

function update_user($json)
    {
    $data=json_decode($json);
    $this->log("Called update_user, uid=".$data->{'id'});
    $con=$this->DBConnection_MySQL();
    if($con!=NULL)
        {                                
        $sql="UPDATE system_users SET operator_name='".$data->{"name"}."',phone='".$data->{'phone'}."',user_role_id='".$data->{'type'}."',validity_from_date='".$data->{'fromDate'}."',validity_to_date='".$data->{'toDate'}."' where user_id='".$data->{'id'}."'";
        $this->log($sql);
        $result = $con->query($sql);
        if($result)
            {
                mysqli_close($con);
                return $this->response(200,"User details updated");
            }
        else
            {
                mysqli_close($con);
                return $this->response(404,"Failed Update User details");
            }
        }
    else
        {
            mysqli_close($con);
            return $this->response(404,"Current Password is wrong");
        }
    }

}
?>
