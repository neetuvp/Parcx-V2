<?php            	        
$json='{"user_role_id":151,"user_role_name":"Manager","activity_message":"Edit user role 151","menu":[{"id":"111","view":0,"add":0,"edit":0,"delete":0},{"id":"110","view":0,"add":0,"edit":0,"delete":0},{"id":"23","view":0,"add":0,"edit":0,"delete":0},{"id":"81","view":0,"add":0,"edit":0,"delete":0},{"id":"86","view":0,"add":0,"edit":0,"delete":0},{"id":"92","view":0,"add":0,"edit":0,"delete":0},{"id":"93","view":0,"add":0,"edit":0,"delete":0},{"id":"94","view":0,"add":0,"edit":0,"delete":0},{"id":"102","view":0,"add":0,"edit":0,"delete":0},{"id":"103","view":0,"add":0,"edit":0,"delete":0},{"id":"106","view":0,"add":0,"edit":0,"delete":0},{"id":"107","view":0,"add":0,"edit":0,"delete":0},{"id":"109","view":0,"add":0,"edit":0,"delete":0},{"id":"78","view":0,"add":0,"edit":0,"delete":0},{"id":"49","view":1,"add":0,"edit":0,"delete":1},{"id":"64","view":0,"add":0,"edit":0,"delete":0},{"id":"66","view":0,"add":0,"edit":0,"delete":0},{"id":"69","view":0,"add":0,"edit":0,"delete":0},{"id":"89","view":0,"add":0,"edit":0,"delete":0},{"id":"99","view":0,"add":0,"edit":0,"delete":0},{"id":"100","view":0,"add":0,"edit":0,"delete":0},{"id":"84","view":0,"add":0,"edit":0,"delete":0},{"id":"105","view":0,"add":0,"edit":0,"delete":0},{"id":"47","view":0,"add":0,"edit":0,"delete":0},{"id":"48","view":0,"add":0,"edit":0,"delete":0},{"id":"45","view":0,"add":0,"edit":0,"delete":0},{"id":"54","view":0,"add":0,"edit":0,"delete":0},{"id":"56","view":0,"add":0,"edit":0,"delete":0},{"id":"57","view":0,"add":0,"edit":0,"delete":0},{"id":"58","view":0,"add":0,"edit":0,"delete":0},{"id":"67","view":0,"add":0,"edit":0,"delete":0},{"id":"68","view":0,"add":0,"edit":0,"delete":0},{"id":"73","view":0,"add":0,"edit":0,"delete":0},{"id":"90","view":0,"add":0,"edit":0,"delete":0},{"id":"98","view":0,"add":0,"edit":0,"delete":0},{"id":"35","view":0,"add":0,"edit":0,"delete":0},{"id":"37","view":0,"add":0,"edit":0,"delete":0},{"id":"46","view":0,"add":0,"edit":0,"delete":0},{"id":"71","view":0,"add":0,"edit":0,"delete":0},{"id":"72","view":0,"add":0,"edit":0,"delete":0},{"id":"82","view":0,"add":0,"edit":0,"delete":0},{"id":"85","view":0,"add":0,"edit":0,"delete":0},{"id":"91","view":0,"add":0,"edit":0,"delete":0},{"id":"33","view":0,"add":0,"edit":0,"delete":0},{"id":"34","view":0,"add":0,"edit":0,"delete":0},{"id":"61","view":0,"add":0,"edit":0,"delete":0},{"id":"88","view":0,"add":0,"edit":0,"delete":0},{"id":"111","view":0,"add":0,"edit":0,"delete":0},{"id":"110","view":0,"add":0,"edit":0,"delete":0},{"id":"23","view":0,"add":0,"edit":0,"delete":0},{"id":"81","view":0,"add":0,"edit":0,"delete":0},{"id":"86","view":0,"add":0,"edit":0,"delete":0},{"id":"92","view":0,"add":0,"edit":0,"delete":0},{"id":"93","view":0,"add":0,"edit":0,"delete":0},{"id":"94","view":0,"add":0,"edit":0,"delete":0},{"id":"102","view":0,"add":0,"edit":0,"delete":0},{"id":"103","view":0,"add":0,"edit":0,"delete":0},{"id":"106","view":0,"add":0,"edit":0,"delete":0},{"id":"107","view":0,"add":0,"edit":0,"delete":0},{"id":"109","view":0,"add":0,"edit":0,"delete":0},{"id":"78","view":0,"add":0,"edit":0,"delete":0},{"id":"49","view":1,"add":0,"edit":0,"delete":1},{"id":"64","view":0,"add":0,"edit":0,"delete":0},{"id":"66","view":0,"add":0,"edit":0,"delete":0},{"id":"69","view":0,"add":0,"edit":0,"delete":0},{"id":"89","view":0,"add":0,"edit":0,"delete":0},{"id":"99","view":0,"add":0,"edit":0,"delete":0},{"id":"100","view":0,"add":0,"edit":0,"delete":0},{"id":"84","view":0,"add":0,"edit":0,"delete":0},{"id":"105","view":0,"add":0,"edit":0,"delete":0},{"id":"47","view":0,"add":0,"edit":0,"delete":0},{"id":"48","view":0,"add":0,"edit":0,"delete":0},{"id":"45","view":0,"add":0,"edit":0,"delete":0},{"id":"54","view":0,"add":0,"edit":0,"delete":0},{"id":"56","view":0,"add":0,"edit":0,"delete":0},{"id":"57","view":0,"add":0,"edit":0,"delete":0},{"id":"58","view":0,"add":0,"edit":0,"delete":0},{"id":"67","view":0,"add":0,"edit":0,"delete":0},{"id":"68","view":0,"add":0,"edit":0,"delete":0},{"id":"73","view":0,"add":0,"edit":0,"delete":0},{"id":"90","view":0,"add":0,"edit":0,"delete":0},{"id":"98","view":0,"add":0,"edit":0,"delete":0},{"id":"35","view":0,"add":0,"edit":0,"delete":0},{"id":"37","view":0,"add":0,"edit":0,"delete":0},{"id":"46","view":0,"add":0,"edit":0,"delete":0},{"id":"71","view":0,"add":0,"edit":0,"delete":0},{"id":"72","view":0,"add":0,"edit":0,"delete":0},{"id":"82","view":0,"add":0,"edit":0,"delete":0},{"id":"85","view":0,"add":0,"edit":0,"delete":0},{"id":"91","view":0,"add":0,"edit":0,"delete":0},{"id":"33","view":0,"add":0,"edit":0,"delete":0},{"id":"34","view":0,"add":0,"edit":0,"delete":0},{"id":"61","view":0,"add":0,"edit":0,"delete":0},{"id":"88","view":0,"add":0,"edit":0,"delete":0}],"task":5}';
$data = json_decode($json,true);
$r=parcxV2UserManagement($data);
if(is_array($r))
print_r($r);
else
echo $r;
             

?>
