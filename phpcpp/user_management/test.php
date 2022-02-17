<?php            	        
$json='{"user_role_id":100,"user_id":1,"url":"http://localhost/parcx-V02-2021/","task":1,"lang":"Arabic"}';
$data = json_decode($json,true);
$r=parcxV2UserManagement($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>
