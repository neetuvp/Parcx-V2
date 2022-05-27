<?php            	        
//$json='{"device_id":"20","task":"3","playlists":[{"id":"1","repeat":1,"start":"2022-03-06 00:00:00","end":"2022-03-15 23:59:59","name":"Playlist 1"}]}';
$json = '{"task":"13"}';
$data = json_decode($json,true);
$r=parcxGreetingScreen($data);
if(is_array($r)){
//print_r($r);
    echo json_encode($r);
}
else
echo $r;
             

?>
