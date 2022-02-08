<?php
session_start();
$inactive=time()-$_SESSION["last_login_timestamp"];  
if($inactive>=60*15)    
    {
    if($_SESSION["dashboard"]==0)
        {
        logParcxUserActivity("Logout","Session timeout logout by ".$_SESSION["user"],$_SESSION['userId'],$_SESSION["user"],"success",$_SERVER['REMOTE_ADDR'],"","");
        unset($_SESSION['username'], $_SESSION['password'], $_SESSION['last_login_timestamp']);
        session_destroy(); 
        setcookie("user_name",time()-3600);
        echo 1;
        }
    else                     
        echo 0;
    }
else 
    echo 0;
          
?>