-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2020 at 03:13 PM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parcx_server`
--

-- --------------------------------------------------------

--
-- Table structure for table `vms_communication_config`
--

CREATE TABLE `vms_communication_config` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `serial_port` varchar(20) NOT NULL,
  `group_address` int(11) NOT NULL,
  `vms_address` int(11) NOT NULL DEFAULT '1',
  `vms_name` varchar(50) NOT NULL,
  `vms_width` int(11) NOT NULL,
  `vms_height` int(11) NOT NULL,
  `daemon_port_id` int(11) NOT NULL,
  `icon_font_size` int(11) NOT NULL,
  `icon_color` int(11) NOT NULL,
  `left_icon` varchar(100) NOT NULL,
  `left_icon_text` varchar(25) NOT NULL,
  `left_icon_name` varchar(25) NOT NULL,
  `left_color` int(1) NOT NULL,
  `left_font_size` int(1) NOT NULL,
  `left_character_count` int(2) NOT NULL,
  `left_counter` varchar(100) NOT NULL,
  `center_icon` varchar(100) NOT NULL,
  `center_icon_text` varchar(25) NOT NULL,
  `center_icon_name` varchar(25) NOT NULL,
  `center_color` int(11) NOT NULL,
  `center_font_size` int(11) NOT NULL,
  `center_character_count` int(2) NOT NULL,
  `center_counter` varchar(100) NOT NULL,
  `right_icon` varchar(100) NOT NULL,
  `right_icon_text` varchar(25) NOT NULL,
  `right_icon_name` varchar(25) NOT NULL,
  `right_character_count` int(2) NOT NULL,
  `right_counter` varchar(100) NOT NULL,
  `right_color` int(11) NOT NULL,
  `right_font_size` int(11) NOT NULL,
  `test` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `data_sent` text NOT NULL,
  `last_communication_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `brightness_time1` int(11) NOT NULL,
  `brightness_time2` int(11) NOT NULL,
  `brightness_time3` int(11) NOT NULL,
  `brightness_time4` int(11) NOT NULL,
  `brightness_time5` int(11) NOT NULL,
  `brightness_value1` int(11) NOT NULL,
  `brightness_value2` int(11) NOT NULL,
  `brightness_value3` int(11) NOT NULL,
  `brightness_value4` int(11) NOT NULL,
  `brightness_value5` int(11) NOT NULL,
  `wait_for_response` int(11) NOT NULL,
  `sign_type` int(11) NOT NULL COMMENT '1 tower,2 internal'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `vms_communication_config`
--

INSERT INTO `vms_communication_config` (`id`, `ip_address`, `serial_port`, `group_address`, `vms_address`, `vms_name`, `vms_width`, `vms_height`, `daemon_port_id`, `icon_font_size`, `icon_color`, `left_icon`, `left_icon_text`, `left_icon_name`, `left_color`, `left_font_size`, `left_character_count`, `left_counter`, `center_icon`, `center_icon_text`, `center_icon_name`, `center_color`, `center_font_size`, `center_character_count`, `center_counter`, `right_icon`, `right_icon_text`, `right_icon_name`, `right_character_count`, `right_counter`, `right_color`, `right_font_size`, `test`, `status`, `data_sent`, `last_communication_datetime`, `brightness_time1`, `brightness_time2`, `brightness_time3`, `brightness_time4`, `brightness_time5`, `brightness_value1`, `brightness_value2`, `brightness_value3`, `brightness_value4`, `brightness_value5`, `wait_for_response`, `sign_type`) VALUES
(1, '10.10.120.32', '/dev/ttyUSB0', 1, 2, 'VMS 1', 96, 16, 4, 0, 2, '0', '', '', 2, 4, 2, '', '', '', '', 2, 6, 4, '1470', '0', '0', '', 3, '', 0, 6, 0, 1, '{0}   [FT112][FC02]0001    {0}', '2020-03-23 11:36:08', 10, 12, 15, 18, 5, 1, 2, 3, 4, 5, 1, 1),
(2, '10.10.120.32', '/dev/ttyUSB0', 1, 3, 'VMS 2', 96, 16, 4, 0, 2, '0', '', '', 2, 4, 2, '', '', '', '', 2, 6, 4, '1470', '0', '0', '', 3, '', 0, 6, 0, 1, '{0}   [FT112][FC02]0001    {0}', '2020-03-23 11:36:09', 10, 12, 15, 18, 5, 1, 2, 3, 4, 5, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vms_communication_config`
--
ALTER TABLE `vms_communication_config`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vms_communication_config`
--
ALTER TABLE `vms_communication_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
