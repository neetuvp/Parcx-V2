<?php
if($_FILES["file"]["name"]!='')
{
    $data=array();
    $data["from"]=$_FILES["file"]["tmp_name"];
    $data["name"]=$_FILES["file"]["name"];
    $data["size"]=$_FILES["file"]["size"];
    $data["type"]=$_FILES["file"]["type"];
    $data["error"]=$_FILES["file"]["error"];
    $data["duration"]= number_format($_GET["duration"],2);
    $data["task"]=9;
    $json = json_encode($data);
    WriteLog($json);
    WriteLog("duration:".$_GET["duration"]);
    $response=parcxContentManagement($data);
    if(is_array($response))
	echo json_encode($response);
    else
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
//$response=parcxContentManagement($data);


/*
$allowedExts = array("mp4", "avi");
$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);


//echo "Size limit: ".(200*1024*1024);

if (($_FILES["file"]["size"] > 200 * 1024 * 1024))
    echo "File size is >200 MB <br/>";
else if ((($_FILES["file"]["type"] == "video/mp4") || (($_FILES["file"]["type"] == "video/avi"))) && in_array($extension, $allowedExts)) {
    if ($_FILES["file"]["error"] > 0) {
        echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    } else {
        echo "Upload: " . $_FILES["file"]["name"] . "<br />";
      //  echo "Type: " . $_FILES["file"]["type"] . "<br />";
       // echo "Size: " . ($_FILES["file"]["size"] / (1024 * 1024)) . " MB<br />";       
        $name = $_FILES["file"]["name"];
        $target = "../../Media/Videos/";
        $location = $target . $name;
        
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $location))
            echo "Uploaded successfully";
        else
            echo "Upload failed";
        
    }
} else {
    echo "Invalid file";
}*/
?>
