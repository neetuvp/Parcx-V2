<?php
ini_set('display_errors',1);



define("GATECODE","GS3112121");
date_default_timezone_set('Asia/Dubai');
//local variables 
define ("DB_HOST", "localhost");
define ("DB_USER", "parcxservice");
define ("DB_PASSWORD","1fromParcx!19514");
define ("DB_NAME_SERVER","parcx_server");
define ("DB_NAME_REPORTING","parcx_reporting");
define ("DB_NAME_DASHBOARD","parcx_dashboard");
//codes
define("GATE_NOT_FOUND","The gate not found");
define("GATE_PURPOSE_NOT_FOUND","The gate purpose not found");
define("APPOINTMENT_NOT_FOUND","The truck appointment not found");
define("TOO_EARLY_ARRIVAL","Too early arrival");
define("TOO_LATE_ARRIVAL","Too late arrival");
define("INVALID_APPOINTMENT_STATUS","Invalid truck appointment status");
define("INVALID_PORT_GATE","Invalid port gate");
define("MULTIPLE_APPOINTMENT","There are multiple appointment for this truck");
define("NO_APPOINTMENT_ON_TIME","There are no appointment on time");
define("NO_ACCESS","No Access");
?>
