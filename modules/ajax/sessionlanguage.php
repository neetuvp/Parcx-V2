<?php 
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
session_start();
$_SESSION["language"] = $data["language"];
isset($_SESSION["language"])?$data["language"]:"English";
echo $_SESSION["language"];
?>
