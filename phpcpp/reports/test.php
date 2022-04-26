<?php            	        
$json='{"shift_id":"8120210618112424","task":16,"language":"English"}';
$data = json_decode($json,true);
$r=parcxV2Report($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>
