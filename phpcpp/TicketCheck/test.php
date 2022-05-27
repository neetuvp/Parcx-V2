<?php
//$json='{"transaction_id":"123456789","device_number":31,"plate_number":"","ticket_id":"000001010b00583b1f28","coupon_ticket_id":"200001010b015aa14061","plate_captured_id":0,"coupon_id":"000001010b0057dae8f6"}';
$json='{"transaction_id":"123456789","device_number":31,"plate_number":"","ticket_id":"200001010b015aa14f8d","plate_captured_id":0,"coupon_id":"000001010b0057dae8f6,000001010b00583b1f28"}';
$json='{"transaction_id":"2000010151075aa53597","online_validation":0,"anpr_enable":0,"carpark_number":1,"anpr_check_only":0,"reservation_enabled":0,"camera_id":71,"device_type":3,"ticket_id":"200001010b015aa14f87","central_cashier":1,"device_ip":"192.168.1.164","device_name":"Cashier 81","app_key":"PXCashier","app_user_id":30,"device_function":1,"facility_number":200001,"device_number":81,"plate_captured_interval":0,"qrcode_access_enabled":1,"parking_zone":0}';
$data=json_decode($json);
$response=parcxTicketCheck($data);
print_r($response);
//echo json_encode($response);	
?>