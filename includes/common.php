<?php
define("URL","http://".$_SERVER['SERVER_NAME']."/parcx-V02-2021/");
ini_set('display_errors',0);
define("ImageURL","http://".$_SERVER['SERVER_NAME']."/ANPR/Images");
session_start();

if (isset($_GET['logout']))
    {
    if(isset($_SESSION['user']))
        logParcxUserActivity("Logout","Logout by ".$_SESSION["user"],$_SESSION['userId'],$_SESSION["user"],"success",$_SERVER['REMOTE_ADDR'],"","");

    unset($_SESSION['username'], $_SESSION['password'], $_SESSION['last_login_timestamp']);
    session_destroy();                          
    header("Location:".URL."index.php");
    }
        
?>