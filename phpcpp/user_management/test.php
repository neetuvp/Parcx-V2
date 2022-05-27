<?php            	        
$json='{"user_name":"parcx","password":"Parcx123!","task":"12"}';
$data = json_decode($json,true);
$r=parcxV2UserManagement($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>



