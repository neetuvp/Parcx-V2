<?php 
ini_set("display_errors",0);
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
$data["request_ip"] = $_SERVER['REMOTE_ADDR'];
if($data["task"]!=12)
    {
    session_start();    
    $data["login_user_id"] = $_SESSION['userId'];
    $data["login_user_name"] = $_SESSION['user'];
    }
$response=parcxV2UserManagement($data);
if(is_array($response))
    {    
    if($data["task"]==12)
        {    
        $result["message"]=$response["message"];
        
        if($response["status"]==200)
            {
            setcookie("user_name", $response["operator_name"]);
            $result["message"]="Success";
            $result["home"]=$response["home"];
            session_start();
            $_SESSION['user'] = $response["operator_name"];
            $_SESSION['userRollName'] = $response["user_role_name"];
            $_SESSION['userRollId'] = $response["user_role_id"]; 
            $_SESSION['userId'] = $response["user_id"];            
            $_SESSION["language"] = ucfirst($response["language"]) ;               
            $_SESSION['last_login_timestamp'] = time();              
            }
         echo json_encode($result);
        }
    else 
        echo json_encode($response);
    }
else
    echo $response;
?>
