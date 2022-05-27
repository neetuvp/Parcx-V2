<?php
require_once("../classes/webservice.php");
$db=new webservice();
$json=file_get_contents("php://input");
//$json='{"tag_serial":"56511268","movement_type":"1","carpark_number":"1","facility_number":"1","parking_zone":""}';
$db->checkAntiPassback($json);
?>