-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 25, 2022 at 11:42 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: parcx_reporting
--

-- --------------------------------------------------------

--
-- Table structure for table smartgate_request
--
Use parcx_reporting;
CREATE TABLE parcx_reporting.smartgate_request (
  id int(11) NOT NULL,
  device_number int(11) DEFAULT NULL,
  device_name varchar(50) DEFAULT NULL,
  api_datetime_request timestamp NULL DEFAULT NULL,
  api_datetime_response datetime DEFAULT NULL,
  token_datetime_request datetime DEFAULT current_timestamp(),
  token_datetime_response datetime DEFAULT NULL,
  token_response text DEFAULT NULL,
  plate_number varchar(15) DEFAULT NULL,
  plate_capture_id varchar(30) DEFAULT NULL,
  plate_capture_id_secondary varchar(30) DEFAULT NULL,
  gate_code varchar(50) DEFAULT NULL,
  reference_number varchar(50) DEFAULT NULL,
  request text DEFAULT NULL,
  response_status varchar(50) DEFAULT NULL,
  api_response text DEFAULT NULL,
  fault_string text DEFAULT NULL,
  fault_code_text text DEFAULT NULL,
  plates_match int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table smartgate_request
--
ALTER TABLE parcx_reporting.smartgate_request
  ADD PRIMARY KEY (id);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table smartgate_request
--
ALTER TABLE parcx_reporting.smartgate_request
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

Use parcx_server;
INSERT INTO parcx_server.system_menu_group (group_name, group_icon, group_order_index, header_id, group_expand, group_status) SELECT 'smart_gate', 'fas fa-key', '5',(Select header_id from system_menu_header where header_name='reports'), '1', '1' Where not exists (Select group_name from system_menu_group where group_name='smart_gate');
INSERT INTO parcx_server.system_menu (group_id, menu_name, menu_link, menu_icon, menu_order_index, menu_add, menu_edit, menu_delete, menu_status) SELECT (Select group_id from system_menu_group where group_name='smart_gate'), 'access_request', 'modules/Tabadul/access_request.php', 'far fa-circle', '1', '0', '0', '0', '1' WHERE NOT EXISTS (Select menu_name from system_menu where menu_name='access_request');
INSERT INTO parcx_server.system_menu (group_id, menu_name, menu_link, menu_icon, menu_order_index, menu_add, menu_edit, menu_delete, menu_status) SELECT (Select group_id from system_menu_group where group_name='smart_gate'), ' plates_captured', 'modules/Tabadul/plates_captured.php', 'far fa-circle', '2', '0', '0', '0', '1' WHERE NOT EXISTS (Select menu_name from system_menu where menu_name='plates_captured');
Update parcx_server.system_menu set group_id = (Select group_id from system_menu_group where group_name='smart_gate') where menu_name='plates_captured';
INSERT INTO parcx_server.system_menu (group_id, menu_name, menu_link, menu_icon, menu_order_index, menu_add, menu_edit, menu_delete, menu_status) SELECT (Select group_id from system_menu_group where group_name='smart_gate'), 'access_whitelist', 'modules/Tabadul/access_whitelist.php', 'far fa-circle', '3', '1', '1', '0', '1' WHERE NOT EXISTS (Select menu_name from system_menu where menu_name='access_whitelist');
Update system_menu set menu_link = 'modules/Tabadul/home.php' where menu_name='home';
INSERT INTO parcx_server.web_application_labels (message, english, arabic, spanish, status) VALUES ('smart_gate', 'Smart Gate', 'Smart Gate', 'Smart Gate', '1');
INSERT INTO parcx_server.web_application_labels (message, english, arabic, spanish, status) VALUES ('access_whitelist', 'Access Whitelist', 'الوصول إلى القائمة البيضاء', 'Acceder a la lista blanca', '1');
INSERT INTO parcx_server.web_application_labels (message, english, arabic, spanish, status) VALUES ('access_request', 'Access Request', 'مطلوب الوصول', 'Acceso requerido', '1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
