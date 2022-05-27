

<?php
$response = array();
if($_FILES["file"]["name"]!='')
    {
    $data=array();
    $data["from"]=$_FILES["file"]["tmp_name"];
    $data["name"]=$_FILES["file"]["name"];
    $data["size"]=$_FILES["file"]["size"];
    $data["type"]=strtolower($_FILES["file"]["type"]);
    $data["error"]=$_FILES["file"]["error"];
    $data["task"]=1;

    WriteLog("Upload File Request: ".json_encode($data));

    $response=parcxGreetingScreen($data);        
    $response=json_encode($response);
    WriteLog("Upload File Response: ".$response);
    echo $response;
    }
      
function WriteLog($data)
    {
    date_default_timezone_set('Asia/Dubai');
    if (!file_exists('Logs')) {
        mkdir('Logs', 0777, true);
    }
    $file = "Logs/".date('Y-m-d');
    $fh = fopen($file, 'a') or die("can't open file");
    fwrite($fh,"\n");
    fwrite($fh,"Date :".date('Y-m-d H:i:s'). " ");
    fwrite($fh,$data);
    fclose($fh);
    }

?>
