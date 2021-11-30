<?php
session_start();
$inactive=time()-$_SESSION["last_login_timestamp"];  
if($inactive>=60*5)    
    {
    if($_SESSION["dashboard"]==0)
        {
        addParcxUserActivity("Logout",$_SESSION["user"],$_SESSION['userId'],"");
        unset($_SESSION['username'], $_SESSION['password'], $_SESSION['last_login_timestamp']);
        session_destroy();                 
        echo 1;
        }
    else                     
        echo 0;
    }
else 
    echo 0;
          
?>