<?php
$curl_do = curl_init();
$request = '{"task":"4","plate":"21112","confidence":"98","country":"UAE","category":"private","camera":"71","plateimage":""}';
        curl_setopt($curl_do, CURLOPT_URL,"http://10.195.15.205/Tabadul-API/MXCameraService.php?camera_id=71");
        curl_setopt($curl_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_do, CURLOPT_POST, true);
        curl_setopt($curl_do, CURLOPT_VERBOSE, 1);
        curl_setopt($curl_do, CURLOPT_POSTFIELDS, $request);
        $output = str_replace("'", "", curl_exec($curl_do));
        echo $output;
?>