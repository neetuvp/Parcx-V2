<?php
//Tables: smartgate_request,watchdog_network_logs
//Media/Smartgate/images folder in parcx
//system_menu - home menu_link modules/Tabadul/home.php
//INSERT INTO system_menu (group_id, menu_name, menu_link, menu_icon, menu_order_index, menu_add, menu_edit, menu_delete, menu_status) VALUES ('2', 'Access Request', 'modules/Tabadul/access_request.php', 'far fa-circle', '10', '0', '0', '0', '1');
//INSERT INTO system_menu (menu_id, group_id, menu_name, menu_link, menu_icon, menu_order_index, menu_add, menu_edit, menu_delete, menu_status) VALUES (NULL, '2', 'Plates Captured', 'modules/Tabadul/plates_captured.php', 'far fa-circle', '11', '0', '0', '0', '1')
ini_set('display_errors',1);
define("MYSQLSERVER","localhost");
define("USER","parcxservice");
define("PASSWORD","1fromParcx!19514");
//define("DB","parcx_smartgate");
define("DB_SERVER","parcx_server");
define("DB_REPORTING","parcx_reporting");
define("DB_DASHBOARD","parcx_dashboard");
define("ANPRImageURL","../../../ANPR/Images");
//define("ANPRImageURL","../../../Media/SmartGate/images");
define("IMAGEURL","../../dist/img/icon/device_icons/");
?>
