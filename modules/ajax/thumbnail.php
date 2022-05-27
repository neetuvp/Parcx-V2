<?php

$thumbs_dir = '../../Media/cms/thumbnail';
if( $_POST["data"] ){
    $filename=$_POST["filename"].".png";
    $thumbnail_dir = "../../Media/cms/thumbnail";
    if (!file_exists($thumbs_dir)) {
        mkdir($thumbs_dir, 0777, true);
    }
    WriteLog("here");
    // Grab the MIME type and the data with a regex for convenience
    if (!preg_match('/data:([^;]*);base64,(.*)/', $_POST['data'], $matches)) {
        die("error");
    }

    // Decode the data
    $data = $matches[2];
    $data = str_replace(' ','+',$data);
    $data = base64_decode($data);

    file_put_contents($thumbnail_dir."/".$filename, $data);
}
function WriteLog($data)
{
    date_default_timezone_set('Asia/Dubai');
    if (!file_exists('Logs')) {
        mkdir('Logs', 0777, true);
    }
    $file = "Logs/test-".date('Y-m-d');
    $fh = fopen($file, 'a') or die("can't open file");
    fwrite($fh,"\n");
    fwrite($fh,"Date :".date('Y-m-d H:i:s'). " ");
    fwrite($fh,$data);
    fclose($fh);
}
?>
