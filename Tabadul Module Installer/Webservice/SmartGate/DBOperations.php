<?php
   function DBConnect($db)
    {
	$conn=null;
	$serverName = "localhost";
	$uid = "parcxservice";
	$pwd = "1fromParcx!19514";
	$databaseName = $db;
	$conn = new mysqli($serverName, $uid, $pwd,$databaseName);
	// Check connection
	if($conn->connect_error)
	{

	     die("Mysql Connection failed: " . $conn->connect_error);
		createExceptionLog("Mysql Connection Failed:". $conn->connect_error);
	}
	
        return $conn;
    }

    function CloseDBConnection($conn)
    {
        mysqli_close($conn);
    }

    function GetData($conn,$sql)
    {
        $result = $conn->query($sql);
        return $result;
    }
    function  DBOperation($conn,$sql)
    {
	//$result = $conn->query($sql);
	//return $result;
	if($conn->query($sql)==TRUE)
	{
		return true;
	}
	else
	{
		createExceptionLog("DB Operation Failed: ".$conn->error );
		return false;
	}	
    }
 function  DBOperationMysql($sql)
 {
	try{
		$conn = DBConnect();
		if($conn){
		    $result = $conn->query($sql);
		    CloseDBConnection($conn);
		}
	}
	catch(exception $e) {
		createExceptionLog("Exception occured");
	}
 }

function createExceptionLog($data)
{
	date_default_timezone_set('Asia/Dubai');
	if (!file_exists('Logs')) {
	    mkdir('Logs', 0777, true);
	}
	$file = "Logs/ExceptionLog-".date('Y-m-d');
	$fh = fopen($file, 'a') or die("can't open file");
	fwrite($fh,"\n");
	fwrite($fh,"Date :".date('Y-m-d H:i:s'). " ");
	fwrite($fh,$data);
	fclose($fh);
}

    ?>
