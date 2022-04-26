<?php
date_default_timezone_set('Asia/Dubai');


class General_Oprations 
    {
    public function write_logs($file_name, $function_name, $message) 
        {
        $log_file_path = "/opt/parcx/Logs/Services/ApplicationLogs/".$file_name. date("d-m-Y") . "_log.log";
        if (file_exists($log_file_path)) 
            {
            $myfile = fopen($log_file_path, 'a');
            shell_exec('chmod -R 777 '.$log_file_path);            
            } 
        else 
            {
            $myfile = fopen($log_file_path, 'w');
            shell_exec('chmod -R 777 '.$log_file_path);
            }        
        fwrite($myfile, date("d-m-Y H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
    }

    public function write_log($file_name, $function_name, $message) 
        {
        $log_file_path = "/opt/parcx/Logs/Services/".$file_name. date("Y-m-d") . ".log";
        if (file_exists($log_file_path)) 
            {
            $myfile = fopen($log_file_path, 'a');
            shell_exec('chmod -R 777 '.$log_file_path);            
            } 
        else 
            {
            $myfile = fopen($log_file_path, 'w');
            shell_exec('chmod -R 777 '.$log_file_path);
            }        
        fwrite($myfile, date("Y-m-d H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
    }
    
    
    public function write_exception($file_name, $function_name, $message) 
        {
        $error_file_path = "/opt/parcx/Logs/Services/ExceptionLogs/" .$file_name. date("d-m-Y") . "_exception.log";  
        if (file_exists($error_file_path)) 
            {
            $myfile = fopen($error_file_path, 'a');
            shell_exec('chmod -R 777 '.$error_file_path);
            } 
        else 
            {
            $myfile = fopen($error_file_path, 'w');
            shell_exec('chmod -R 777 '.$error_file_path);
            }        
        fwrite($myfile, date("d-m-Y H:i:s") . "\t" . $function_name . ":" . $message . "\r\n\n");
        fclose($myfile);
        }
    }
?>

