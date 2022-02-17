<?php            	        
$json='{"from":"2022-02-01 00:00:00","to":"2022-02-16 23:59:59","carpark":"1","device":"51,52,53,81,82,91","payment-category":["1"],"payment-type":"","discount":"1,2,3,4,5,6,7,8,9,10,11","validation":"0,1,2,7,8,9,10,11,13","showvoid":0,"task":8,"language":"English"}';
$data = json_decode($json,true);
$r=parcxV2Report($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>
