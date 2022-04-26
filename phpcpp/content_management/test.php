<?php            	        
$json='{"task":"6"}';
$data = json_decode($json,true);
$r=parcxV2ContentManagement($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>
