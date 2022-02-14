<?php
$page_title = "Device Settings";

include('../../includes/header.php');
$access = checkPageAccess("devices");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Device Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <?php
        if ($access["add"] == 1)
            echo '<div class="tab-link" data-target="form">Add device</div>';
        ?>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update form --> 
            <form class="block-data" data-status="form" style="display:none;" id="form"> 
                <input type="hidden" id="user_id" value="<?php echo $_SESSION['userId']; ?>" />
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-body">
                            <div id="alert-div" class="alert bg-danger d-none">
                                                                      
                            </div> 
                            <div class="row">
                                <div class="col form-group mb-4">
                                    <label for="">Device Category</label>
                                    <select name = "device_category" id ="device_category" class="form-control">
                                        <option value = "1" selected>Entry</option>
                                        <option value = "2">Exit</option>
                                        <option value = "3">Cashier</option>
                                        <option value = "4">Auto Payment Machine</option>
                                        <option value = "5">Handheld POS</option>
                                        <option value="6">Entry Controller</option>
                                        <option value="7">Exit Controller</option> 
                                        <option value="8">Camera</option>
                                        <option value="9">Valet Handheld</option>
                                        <option value="10">Key/Podium Manager</option> 
                                        <option value="11">Validator</option> 
                                    </select>
                                    <small class="form-text text-muted">Device category</small>
                                </div>
                                <div class="col form-group mb-4">
                                    <label for="">Device name</label>
                                    <input type="text" class="form-control" id="device_name" placeholder=""  required >
                                    <small class="form-text text-muted">Name of the device</small>
                                </div>
                            </div>
                            <div class="row">	
                                <div class="col form-group mb-4">
                                    <label for="">Device number</label>
                                    <input type="number" class="form-control acceptdigit" id="device_number" value="0" min="0" required >
                                    <small class="form-text text-muted">Device number</small>
                                </div> 
                                <div class="col form-group mb-4">
                                    <label>Device IP</label>                  
                                    <input type="text" class="form-control" id="device_ip" required >  
                                    <small class="form-text text-muted">IP address of device</small>
                                </div> 
                            </div>  
                            <div class="row">	
                                <div class="col form-group mb-4">
                                    <label for="">Carpark Name</label>
                                    <select id="carpark">                  
                                        <?php echo parcxV2Configuration(array("task" => "1")); ?>
                                    </select>
                                    <small class="form-text text-muted">Name of carpark</small>
                                </div> 
                                <div class="col form-group mb-4 ">
                                    <label for="">Facility Name</label>
                                    <select id="facility">                  
                                        <?php echo parcxV2Configuration(array("task" => "2")); ?>
                                    </select>
                                    <small class="form-text text-muted">Name of facility</small>
                                </div> 
                            </div> 

                            <div class="row div-device div-camera">	
                                <div class="col form-group mb-4">
                                    <label for="">Camera Index</label>
                                    <select id="camera_index">  
                                        <option value="0">Select camera index</option>                
                                        <?php
                                        for ($i = 1; $i <= 10; $i++)
                                            echo "<option value='" . $i . "'>Camera " . $i . "</option>";
                                        ?>
                                    </select>
                                    <small class="form-text text-muted">Camera index using for camera daemon</small>
                                </div> 
                            </div> 
                            
                                <div class="row">	
                                    <div class="col form-group mb-4 div-device div-rate div-validator">
                                        <label >Device function</label>
                                        <select id="device_function"> 
                                            <option value="1">QR Code</option>
                                            <option value="2">Chipcoin</option>
                                            <option value="3">Ticketless</option>
                                        </select>
                                        <small class="form-text text-muted">Device function</small>
                                    </div>  
                                    <div class="col form-group mb-4 div-device div-rate">
                                        <label >Network interface</label>
                                        <input type="text" class="form-control" id="network_interface" value="eth0"  required >
                                        <small class="form-text text-muted">Network interface name</small>
                                    </div> 

                                </div> 
                            <div class="div-device div-rate">
                                <div class="row d-none">
                                    <div class="col form-group mb-4 ">
                                        <label for="">ANPR Image Path</label>
                                        <input type="text" class="form-control" id="anpr_image_path" placeholder=""  value="/opt/lampp/htdocs/ANPR/Images" required >
                                        <small class="form-text text-muted"> </small>
                                    </div>
                                    <div class="col form-group mb-4 ">
                                        <label for="">ANPR COM port</label>
                                        <input type="number" class="form-control acceptdigit" id="anpr_com_port" placeholder=""  value="8091" required >
                                        <small class="form-text text-muted"> </small>
                                    </div>                                  
                                </div>
                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Camera</label>
                                        <select id="camera_id"> 
                                            <option value="0">Select Camera</option>                 
                                            <?php echo parcxV2Configuration(array("task" => "3", "type" => "8")); ?>
                                        </select>
                                        <small class="form-text text-muted">Camera connected to device</small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label for="">Server handshake interval</label>
                                        <input type="number" class="form-control acceptdigit" id="server_handshake_interval" value="90"  required >                    
                                        <small class="form-text text-muted">Time interval for server handshake from device in seconds</small>
                                    </div>

                                </div>
                                <div class="row d-none"> 
                                    <div class="col form-group mb-4">
                                        <label for="">Plate capturing wait delay</label>
                                        <input type="number" class="form-control acceptdigit" id="plate_capturing_wait_delay" value="7"  required >
                                        <small class="form-text text-muted"></small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label for="">RFID Port</label>
                                        <input type="text" class="form-control" id="rfid_port" value="/dev/ttyUSB0"  required >
                                        <small class="form-text text-muted"> </small>
                                    </div> 

                                    <div class="col form-group mb-4">
                                        <label for="">Local Media Path</label>
                                        <input type="text" class="form-control" id="media_path" value="/home/pi/parcx/Terminal/Media/"  required >
                                        <small class="form-text text-muted"> </small>
                                    </div> 
                                </div>                           
                                <div class="row ">	                        
                                    <div class="col form-group mb-4">
                                        <label >Screen saver time out</label>
                                        <input type="number" class="form-control acceptdigit" id="screensaver_timeout" value="30"  required >
                                        <small class="form-text text-muted">Screen saver time out in seconds</small>
                                    </div> 
                                    <div class="col form-group mb-4">
                                        <label >Upload to server delay</label>
                                        <input type="number" class="form-control acceptdigit" id="upload_to_server_delay" value="10"  required >
                                        <small class="form-text text-muted">Upload to server delay in seconds</small>
                                    </div> 
                                </div>
                            </div> 
                            <div class="div-device div-terminal">
                                <div class="row">	
                                    <div class="col form-group mb-4">
                                        <label >Mode of operation</label>
                                        <select id="mode_of_operation"> 
                                            <option value="0">Normal</option>
                                            <option value="1">Free Passage</option>
                                            <option value="2">Lane Closed</option>
                                        </select>                                
                                        <small class="form-text text-muted">Mode of operation in terminal</small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label >Barrier open status type</label>
                                        <select id="barrier_open_status_type"> 
                                            <option value="1">NO Barrier Open</option>
                                            <option value="0">NC Barrier open</option>
                                                                  
                                        </select>
                                        <small class="form-text text-muted">Barrier open status type</small>
                                    </div> 
                                </div>
                                <div class="row ">
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier open time limit</label>
                                        <input type="number" class="form-contro acceptdigit" id="barrier_open_time_limit" value="10"  required >
                                        <small class="form-text text-muted">Time limit in seconds for barrier to stay open</small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label for="">Backout blacklist delay</label>
                                        <input type="number" class="form-control acceptdigit" id="backout_blacklist_delay" value="0"  required >
                                        <small class="form-text text-muted">Time limit in seconds for blacklist ticket after backout</small>
                                    </div>
                                    
                                </div>
                                <div class="row ">
                                    <div class="col form-group mb-4">
                                        <label for="">Output pulse width</label>
                                        <input type="number" class="form-control acceptdigit" id="output_pulse_length" value="150"  required >
                                        <small class="form-text text-muted">Output pulse width</small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label >Mobile qr code validity limit</label>
                                        <input type="number" class="form-control acceptdigit" id="mobile_qrcode_time_limit" value="10"  required >
                                        <small class="form-text text-muted">Mobile qr code validity in seconds</small>
                                    </div> 
                                </div>
                            </div>
                            <div class="div-device div-manual-cashier">
                                <div class="row">                         
                                    <div class="col form-group mb-4">
                                        <label for="">Receipt printer type</label>
                                        <select id="receipt_printer_type"> 
                                            <option value="0">Generic printer</option>
                                            <option value="1">Star Printer</option>                                
                                        </select>
                                        <small class="form-text text-muted">Type of receipt printer</small>
                                    </div>   
                                    <div class="col form-group mb-4">
                                        <label for="">Entry printer type</label>
                                        <select id="entry_printer_type"> 
                                            <option value="0">Generic printer</option>
                                            <option value="1">Star Printer</option>                                
                                        </select>
                                        <small class="form-text text-muted">Type of entry ticket printer</small>
                                    </div>  
                                </div>
                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Entry printer port name</label>
                                        <input type="text" class="form-control" id="entry_printer_port_name" value="lp0"  required >
                                        <small class="form-text text-muted">Port for entry ticket printer</small>
                                    </div> 
                                    <div class="col form-group mb-4">
                                        <label for="">Receipt printer port name</label>
                                        <input type="text" class="form-control" id="receipt_printer_port_name" value="lp0"  required >
                                        <small class="form-text text-muted">Port for receipt printer</small>
                                    </div> 
                                </div> 

                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier open command</label>
                                        <input type="text" class="form-control" id="barrier_open_command" value="opena"  required >
                                        <small class="form-text text-muted">Command for barrier open</small>
                                    </div> 
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier close command</label>
                                        <input type="text" class="form-control" id="barrier_close_command" value="openb"  required >
                                        <small class="form-text text-muted">Command for barrier close</small>
                                    </div> 
                                </div> 
                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Receipt Primary language</label>
                                        <select id="receipt_primary_language"> 
                                            <option value="english">English</option>
                                            <option value="arabic">Arabic</option>
                                            <option value="spanish">Spanish</option>
                                        </select>
                                        <small class="form-text text-muted">Primary language of the receipt</small>
                                    </div> 
                                    <div class="col form-group mb-4">
                                        <label for="">Receipt Secondary language</label>
                                        <select id="receipt_secondary_language"> 
                                            <option value="english">English</option>
                                            <option value="arabic">Arabic</option>
                                            <option value="spanish">Spanish</option>
                                        </select>
                                        <small class="form-text text-muted">Secondary language of the receipt</small>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Plate captured interval</label>
                                        <input type="number" class="form-control acceptdigit" id="plate_captured_interval" value="10"  required >
                                        <small class="form-text text-muted">Plate captured delay for ticket check</small>
                                    </div>

                                    <div class="col form-group mb-4">
                                        <label for="">Transaction time out</label>
                                        <input type="number" class="form-control acceptdigit" id="transaction_time_out" value="120"  required >
                                        <small class="form-text text-muted">Time limit in seconds to cancel the transaction in cashier </small>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier communication</label>
                                        <select id="barrier_communication"> 
                                            <option value="url">url</option>
                                            <option value="port">port</option>                                
                                        </select>
                                        <small class="form-text text-muted">Barrier communication method</small>
                                    </div>
                                    <div class="col form-group mb-4">
                                        <label for="">Controller IP</label>
                                        <input type="text" class="form-control" id="controller_ip" value="http://10.195.14.101/?BAR="  required >
                                        <small class="form-text text-muted">Controller IP</small>
                                    </div>
                                </div>
                                <div class="row">                                 
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier port IP</label>
                                        <input type="text" class="form-control" id="barrier_port_ip" value="localhost"  required >
                                        <small class="form-text text-muted">IP address of barrier</small>
                                    </div> 
                                    <div class="col form-group mb-4">
                                        <label for="">Barrier port number</label>                            
                                        <input type="number" class="form-control acceptdigit" id="barrier_port_number" value="1234"  required >
                                        <small class="form-text text-muted">Port number barrier</small>
                                    </div> 
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-body">
                            <div class="row div-device div-camera">                        
                                <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                    <input type="checkbox" class="custom-control-input" id="cropped_picture_required">
                                    <label class="custom-control-label" for="cropped_picture_required">Cropped picture required</label>
                                    <small class="form-text text-muted">Cropped image required for Camera</small>
                                </div>

                            </div>
                            <div class="div-device div-terminal">
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_enabled">
                                        <label class="custom-control-label" for="wiegand_enabled">Enable Wiegand</label>
                                        <small class="form-text text-muted">Enable wiegand operation</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand2_enabled">
                                        <label class="custom-control-label" for="wiegand2_enabled">Enable Wiegand2</label>
                                        <small class="form-text text-muted">Enable wiegand2 operation</small>
                                    </div>                               
                                </div> 
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_whitelist_check_enabled">
                                        <label class="custom-control-label" for="wiegand_whitelist_check_enabled">Enable wiegand whitelist check</label>
                                        <small class="form-text text-muted">Enable whitelist check for wiegand</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_whitelist_parking_zone_check_enabled">
                                        <label class="custom-control-label" for="wiegand_whitelist_parking_zone_check_enabled">Enable wiegand parking zone check</label>
                                        <small class="form-text text-muted">Enable parking zone check for wiegand</small>
                                    </div>                               
                                </div>
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_whitelist_expiry_check_enabled">
                                        <label class="custom-control-label" for="wiegand_whitelist_expiry_check_enabled">Enable wiegand whitelist expiry check</label>
                                        <small class="form-text text-muted">Enable whitelist expiry check for wiegand</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_whitelist_carpark_check_enabled">
                                        <label class="custom-control-label" for="wiegand_whitelist_carpark_check_enabled">Enable wiegand carpark check</label>
                                        <small class="form-text text-muted">Enable whitelist carpark check for wiegand</small>
                                    </div>                               
                                </div> 
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="wiegand_whitelist_facility_check_enabled">
                                        <label class="custom-control-label" for="wiegand_whitelist_facility_check_enabled">Wiegand whitelist facility check Enabled</label>
                                        <small class="form-text text-muted">Enable whitelist facility check for wiegand</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="send_wiegand_response_to_port">
                                        <label class="custom-control-label" for="send_wiegand_response_to_port">Send wiegand response to device</label>
                                        <small class="form-text text-muted">Send response of weigand to device</small>
                                    </div>                               
                                </div> 
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="barrier_status_monitoring">
                                        <label class="custom-control-label" for="barrier_status_monitoring">Enable Barrier status monitoring</label>
                                        <small class="form-text text-muted">Enable barrier status monitoring</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="bms_status_enabled">
                                        <label class="custom-control-label" for="bms_status_enabled">Enable BMS status</label>
                                        <small class="form-text text-muted">Enable BMS status</small>
                                    </div>                               
                                </div> 
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="quick_barrier_close">
                                        <label class="custom-control-label" for="quick_barrier_close">Quick barrier close</label>
                                        <small class="form-text text-muted">Close barrier quickly after operation</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4 div-device div-entry">
                                        <input type="checkbox" class="custom-control-input" id="short_term_ticket_enabled">
                                        <label class="custom-control-label" for="short_term_ticket_enabled">Enable short term ticket</label>
                                        <small class="form-text text-muted">Enable short term ticket</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4 div-device div-exit">
                                        <input type="checkbox" class="custom-control-input" id="payment_enabled_exit">
                                        <label class="custom-control-label" for="payment_enabled_exit">Payment Enabled exit</label>
                                        <small class="form-text text-muted">Exit with payment</small>
                                    </div>  

                                </div> 
                            </div>
                            <div class="div-device div-rate">
                                <div class="row">	                        
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="anpr_enabled">
                                        <label class="custom-control-label" for="anpr_enabled">Enable ANPR</label>
                                        <small class="form-text text-muted">Enable ANPR Camera</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="display_anpr_image">
                                        <label class="custom-control-label" for="display_anpr_image">Display ANPR Image</label>
                                        <small class="form-text text-muted">Display vehicle image in device</small>
                                    </div>                                    
                                </div> 


                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="access_enabled">
                                        <label class="custom-control-label" for="access_enabled">Enable Access</label>
                                        <small class="form-text text-muted">Enable Access whitelist</small>
                                    </div>   
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="reservation_enabled">
                                        <label class="custom-control-label" for="reservation_enabled">Enable Reservation</label>
                                        <small class="form-text text-muted">Enable reservation</small>
                                    </div>                              
                                </div> 


                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="synch_whitelist">
                                        <label class="custom-control-label" for="synch_whitelist">Synch access whitelist</label>
                                        <small class="form-text text-muted">Synch whitelist from server to device</small>
                                    </div>   

                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="review_mode">
                                        <label class="custom-control-label" for="review_mode">Review mode</label>
                                        <small class="form-text text-muted">Review mode-for getting detailed logs</small>
                                    </div> 

                                </div>        
                                <div class="row div-device div-exit">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="full_max_exit_grace_enabled">
                                        <label class="custom-control-label" for="full_max_exit_grace_enabled">Enable full Max exit grace</label>
                                        <small class="form-text text-muted">Max exit grace period for full paid hour</small>
                                    </div>    
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="coupon_enabled">
                                        <label class="custom-control-label" for="coupon_enabled">Enable Coupon</label>
                                        <small class="form-text text-muted">Enable coupon operation</small>
                                    </div>    
                                </div> 
                                <div class="row div-device div-exit">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="validation_enabled">
                                        <label class="custom-control-label" for="validation_enabled">Enable Validation</label>
                                        <small class="form-text text-muted">Enable validation</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="anpr_mismatch_check">
                                        <label class="custom-control-label" for="anpr_mismatch_check">ANPR mismatch check</label>
                                        <small class="form-text text-muted">Check ANPR plate mismatch on exit</small>
                                    </div> 
                                </div>
                            </div>
                            <div class="div-device div-cashier div-apm">
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="print_operator_logo">
                                        <label class="custom-control-label" for="print_operator_logo">Print operator logo</label>
                                        <small class="form-text text-muted">Print operator logo in the receipt</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="print_client_logo">
                                        <label class="custom-control-label" for="print_client_logo">Print client logo</label>
                                        <small class="form-text text-muted">Print client logo in the receipt</small>
                                    </div>                       
                                </div>
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="customer_receipt_mandatory">
                                        <label class="custom-control-label" for="customer_receipt_mandatory">Customer Receipt Mandatory</label>
                                        <small class="form-text text-muted">Enable/Disable print receipt</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="issue_lost">
                                        <label class="custom-control-label" for="issue_lost">Issue lost</label>
                                        <small class="form-text text-muted">Issue lost ticket from the device</small>
                                    </div>                                                                 
                                </div> 
                                </div> 
                            <div class="div-device div-manual-cashier div-handheld">
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="physical_cash_count">
                                        <label class="custom-control-label" for="physical_cash_count">Shift Physical Cash Count Required</label>
                                        <small class="form-text text-muted">Physical cash count after shiftout</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4" >
                                        <input type="checkbox" class="custom-control-input" id="shift_receipt_mandatory">
                                        <label class="custom-control-label" for="shift_receipt_mandatory">Shift Receipt Mandatory</label>
                                        <small class="form-text text-muted">Print receipt after shift out</small>
                                    </div>                               
                                </div>           
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="allow_manual_open_close">
                                        <label class="custom-control-label" for="allow_manual_open_close">Enable manual barrier operation</label>
                                        <small class="form-text text-muted">Enable manual barrier operation</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="activate_product_sale">
                                        <label class="custom-control-label" for="activate_product_sale">Enable product sale</label>
                                        <small class="form-text text-muted">Enable product sale from the cashier</small>
                                    </div> 
                                </div>
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="activate_subscription">
                                        <label class="custom-control-label" for="activate_subscription">Allow Subscription</label>
                                        <small class="form-text text-muted">Allow parking subscriptions</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="print_entry_ticket">
                                        <label class="custom-control-label" for="print_entry_ticket">Print entry ticket</label>
                                        <small class="form-text text-muted">Print entry ticket</small>
                                    </div> 
                                </div>
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="lost_including_parkingfee">
                                        <label class="custom-control-label" for="lost_including_parkingfee">Lost including parking fee</label>
                                        <small class="form-text text-muted">Lost ticket with parking fee</small>
                                    </div> 
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="float_amount_required">
                                        <label class="custom-control-label" for="float_amount_required">Float amount required for shift opening</label>
                                        <small class="form-text text-muted">Float amount required for shift opening</small>
                                    </div> 
                                </div>
                                <div class="row">	
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="enable_port_communication">
                                        <label class="custom-control-label" for="enable_port_communication">Enable port communication</label>
                                        <small class="form-text text-muted">Enable port communication in cashier</small>
                                    </div>   
                                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                                        <input type="checkbox" class="custom-control-input" id="central_cashier">
                                        <label class="custom-control-label" for="central_cashier">Central cashier</label>
                                        <small class="form-text text-muted">Central cashier or exit cashier</small>
                                    </div> 
                                </div>                  
                            </div>
                            <input type="submit" class=" btn-info mt-2 btn-lg col-md-4" value="Submit" id="add-edit-button">
                        </div>
                    </div>
                </div>  
                                
            </form>

            <!--table -->
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table table-bordered">                    
                            <?php echo parcxV2Configuration(array("task" => "5","edit"=>$access["edit"],"delete"=>$access["delete"])); ?>
                        </table>
                    </div>                                                  
                </div>             
            </div>           

        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>
<script>
    var status;
    var id;
    var previous_carpark;
    var previous_facility;
    var previous_device;
    var cat;
    var table;    
    var page=0;
    var device_name;
    $('.div-device').hide(); 
    $('.div-terminal').show();
    $('.div-rate').show();
    $('.div-entry').show();
    $("#device_category").change(function ()
    {
        cat = $("#device_category").val();
        $(".div-device").hide();
        $('#device_function option[value=3]').text('Ticketless');
        if (cat == 1 || cat == 2)
        {
            $("#media_path").val("/home/pi/parcx/Terminal/Media/");
            $('.div-terminal').show();
            $('.div-rate').show();
            if (cat == 1)
            {
                $('.div-exit').hide();
                $('.div-entry').show();
            } else
            {
                $('.div-entry').hide();
                $('.div-exit').show();
            }
        } else if (cat == 3 || cat == 4 || cat == 5)
        {
            $('.div-rate').show();
            $('.div-cashier').show();  
            if (cat == 3)            
                $('.div-manual-cashier').show();   
            
            if (cat == 5)            
                $('.div-handheld').show();   
            
            if (cat == 4)
                {
                $('.div-apm').show();     
                $("#media_path").val("/opt/parcx/APM/Media/");
                }
        } else if (cat == 8)
        {
            $('.div-camera').show();
        } else if (cat == 11)
        {
            $('.div-validator').show();
            $('#device_function option[value=3]').text('Valet');
        }
    });

    function loadTable()
    {
        page=table.page();
        var data = {};
        data["task"] = "5";
        data["edit"] = <?php echo $access["edit"]; ?>;
        data["delete"] = <?php echo $access["delete"]; ?>;
        var jsondata = JSON.stringify(data);
        $.post("../ajax/settings.php", jsondata, function (data) {
            $("#div-table").html("<table id='RecordsTable' class='table  table-bordered'>" + data + "</table>");
            table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});
            table.page(page).draw(false); 
        });
    }
    $(document).ready(function ()
    {
        table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});
        $("#device_category").change();
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $("#alert-div").addClass("d-none");    
                $("#add-edit-button").val("Submit");
                $("#device_category").change();
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });



        //FormSubmit
        var formElement = $("#form");
        var rules_set = {};

        formElement.find('input[type=text]').each(function ()
        {
            var name = $(this).attr('id');
            rules_set[name] = 'required';
        });

        formElement.validate({
            rules: rules_set,
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                error.insertAfter(element);
            },
            submitHandler: function ()
            {
                var data = {};
                if ($("#add-edit-button").val() == "Submit")
                {
                    data["id"] = "";
                    data["previous_carpark"] = $("#carpark").val();
                    data["previous_facility"] = $("#facility").val();
                    data["previous_device"] = $("#device_number").val();
                    data["activity_message"]="Add device "+$("#device_name").val();
                } else
                {
                    data["id"] = id;
                    data["previous_carpark"] = previous_carpark;
                    data["previous_facility"] = previous_facility;
                    data["previous_device"] = previous_device;
                    data["activity_message"]="Edit device "+device_name;
                }
                data["device_category"] = $("#device_category").val();
                data["device_category_name"] = $("#device_category option:selected").text();
                data["carpark_number"] = $("#carpark").val();
                data["carpark_name"] = $("#carpark option:selected").text();
                data["facility_number"] = $("#facility").val();
                data["facility_name"] = $("#facility option:selected").text();
                data["device_number"] = $("#device_number").val();
                data["device_name"] = $("#device_name").val();
                data["device_ip"] = $("#device_ip").val();
                if ($("#camera_id").val().length != 0)
                    data["camera_id"] = $("#camera_id").val();
                else
                    data["camera_id"] = "0";

                data["mode_of_operation"] = $("#mode_of_operation").val();

                if ($("#camera_index").val().length != 0)
                    data["camera_index"] = $("#camera_index").val();
                else
                    data["camera_index"] = "0";
                data["server_handshake_interval"] = $("#server_handshake_interval").val();
                data["device_function"] = $("#device_function").val();
                data["barrier_open_time_limit"] = $("#barrier_open_time_limit").val();
                data["backout_blacklist_delay"] = $("#backout_blacklist_delay").val();
                data["output_pulse_length"] = $("#output_pulse_length").val();
                data["plate_capturing_wait_delay"] = $("#plate_capturing_wait_delay").val();
                data["barrier_open_status_type"] = $("#barrier_open_status_type").val();
                data["upload_to_server_delay"] = $("#upload_to_server_delay").val();
                data["entry_printer_port_name"] = $("#entry_printer_port_name").val();
                data["receipt_printer_port_name"] = $("#receipt_printer_port_name").val();
                data["barrier_open_command"] = $("#barrier_open_command").val();
                data["barrier_close_command"] = $("#barrier_close_command").val();
                data["receipt_secondary_language"] = $("#receipt_secondary_language").val();
                data["screensaver_timeout"] = $("#screensaver_timeout").val();
                data["media_path"] = $("#media_path").val();
                data["mobile_qrcode_time_limit"] = $('#mobile_qrcode_time_limit').val();


                if ($('#coupon_enabled').is(":checked"))
                    data["coupon_enabled"] = "1";
                else
                    data["coupon_enabled"] = "0";

                if ($('#quick_barrier_close').is(":checked"))
                    data["quick_barrier_close"] = "1";
                else
                    data["quick_barrier_close"] = "0";

                if ($('#cropped_picture_required').is(":checked"))
                    data["cropped_picture_required"] = "1";
                else
                    data["cropped_picture_required"] = "0";

                if ($('#payment_enabled_exit').is(":checked"))
                    data["payment_enabled_exit"] = "1";
                else
                    data["payment_enabled_exit"] = "0";

                if ($('#anpr_enabled').is(":checked"))
                    data["anpr_enabled"] = "1";
                else
                    data["anpr_enabled"] = "0";

                if ($('#wiegand_enabled').is(":checked"))
                    data["wiegand_enabled"] = "1";
                else
                    data["wiegand_enabled"] = "0";

                if ($('#wiegand2_enabled').is(":checked"))
                    data["wiegand2_enabled"] = "1";
                else
                    data["wiegand2_enabled"] = "0";

                if ($('#access_enabled').is(":checked"))
                    data["access_enabled"] = "1";
                else
                    data["access_enabled"] = "0";

                if ($('#reservation_enabled').is(":checked"))
                    data["reservation_enabled"] = "1";
                else
                    data["reservation_enabled"] = "0";

                if ($('#review_mode').is(":checked"))
                    data["review_mode"] = "1";
                else
                    data["review_mode"] = "0";

                if ($('#display_anpr_image').is(":checked"))
                    data["display_anpr_image"] = "1";
                else
                    data["display_anpr_image"] = "0";

                if ($('#bms_status_enabled').is(":checked"))
                    data["bms_status_enabled"] = "1";
                else
                    data["bms_status_enabled"] = "0";

                if ($('#barrier_status_monitoring').is(":checked"))
                    data["barrier_status_monitoring"] = "1";
                else
                    data["barrier_status_monitoring"] = "0";



                if ($('#synch_whitelist').is(":checked"))
                    data["synch_whitelist"] = "1";
                else
                    data["synch_whitelist"] = "0";

                if ($('#customer_receipt_mandatory').is(":checked"))
                    data["customer_receipt_mandatory"] = "1";
                else
                    data["customer_receipt_mandatory"] = "0";

                if ($('#shift_receipt_mandatory').is(":checked"))
                    data["shift_receipt_mandatory"] = "1";
                else
                    data["shift_receipt_mandatory"] = "0";

                if ($('#physical_cash_count').is(":checked"))
                    data["physical_cash_count"] = "1";
                else
                    data["physical_cash_count"] = "0";

                if ($('#issue_lost').is(":checked"))
                    data["issue_lost"] = "1";
                else
                    data["issue_lost"] = "0";

                data["plate_captured_interval"] = $("#plate_captured_interval").val();
                data["entry_printer_port_name"] = $("#entry_printer_port_name").val();
                data["receipt_printer_port_name"] = $("#receipt_printer_port_name").val();
                data["receipt_printer_type"] = $("#receipt_printer_type").val();
                data["entry_printer_type"] = $("#entry_printer_type").val();
                data["barrier_open_command"] = $("#barrier_open_command").val();
                data["barrier_close_command"] = $("#barrier_close_command").val();
                data["receipt_primary_language"] = $("#receipt_primary_language").val();
                data["barrier_communication"] = $("#barrier_communication").val();
                data["transaction_time_out"] = $("#transaction_time_out").val();
                data["rfid_port"] = $("#rfid_port").val();
                data["controller_ip"] = $("#controller_ip").val();
                data["enable_port_communication"] = $("#enable_port_communication").val();
                data["barrier_port_number"] = $("#barrier_port_number").val();
                data["barrier_port_ip"] = $("#barrier_port_ip").val();
                data["anpr_image_path"] = $("#anpr_image_path").val();
                data["anpr_com_port"] = $("#anpr_com_port").val();
                data["network_interface"] = $("#network_interface").val();




                if ($('#allow_manual_open_close').is(":checked"))
                    data["allow_manual_open_close"] = "1";
                else
                    data["allow_manual_open_close"] = "0";

                if ($('#full_max_exit_grace_enabled').is(":checked"))
                    data["full_max_exit_grace_enabled"] = "1";
                else
                    data["full_max_exit_grace_enabled"] = "0";

                if ($('#activate_product_sale').is(":checked"))
                    data["activate_product_sale"] = "1";
                else
                    data["activate_product_sale"] = "0";

                if ($('#activate_subscription').is(":checked"))
                    data["activate_subscription"] = "1";
                else
                    data["activate_subscription"] = "0";

                if ($('#print_entry_ticket').is(":checked"))
                    data["print_entry_ticket"] = "1";
                else
                    data["print_entry_ticket"] = "0";

                if ($('#lost_including_parkingfee').is(":checked"))
                    data["lost_including_parkingfee"] = "1";
                else
                    data["lost_including_parkingfee"] = "0";

                if ($('#float_amount_required').is(":checked"))
                    data["float_amount_required"] = "1";
                else
                    data["float_amount_required"] = "0";

                if ($('#print_operator_logo').is(":checked"))
                    data["print_operator_logo"] = "1";
                else
                    data["print_operator_logo"] = "0";


                if ($('#central_cashier').is(":checked"))
                    data["central_cashier"] = "1";
                else
                    data["central_cashier"] = "0";

                if ($('#enable_port_communication').is(":checked"))
                    data["enable_port_communication"] = "1";
                else
                    data["enable_port_communication"] = "0";
                /////////////////////////////////////////////////////////
                if ($('#wiegand_enabled').is(":checked"))
                    data["wiegand_enabled"] = "1";
                else
                    data["wiegand_enabled"] = "0";

                if ($('#wiegand2_enabled').is(":checked"))
                    data["wiegand2_enabled"] = "1";
                else
                    data["wiegand2_enabled"] = "0";

                if ($('#wiegand_whitelist_check_enabled').is(":checked"))
                    data["wiegand_whitelist_check_enabled"] = "1";
                else
                    data["wiegand_whitelist_check_enabled"] = "0";

                if ($('#wiegand_whitelist_parking_zone_check_enabled').is(":checked"))
                    data["wiegand_whitelist_parking_zone_check_enabled"] = "1";
                else
                    data["wiegand_whitelist_parking_zone_check_enabled"] = "0";

                if ($('#wiegand_whitelist_expiry_check_enabled').is(":checked"))
                    data["wiegand_whitelist_expiry_check_enabled"] = "1";
                else
                    data["wiegand_whitelist_expiry_check_enabled"] = "0";

                if ($('#wiegand_whitelist_carpark_check_enabled').is(":checked"))
                    data["wiegand_whitelist_carpark_check_enabled"] = "1";
                else
                    data["wiegand_whitelist_carpark_check_enabled"] = "0";

                if ($('#wiegand_whitelist_facility_check_enabled').is(":checked"))
                    data["wiegand_whitelist_facility_check_enabled"] = "1";
                else
                    data["wiegand_whitelist_facility_check_enabled"] = "0";

                if ($('#send_wiegand_response_to_port').is(":checked"))
                    data["send_wiegand_response_to_port"] = "1";
                else
                    data["send_wiegand_response_to_port"] = "0";

                if ($('#print_client_logo').is(":checked"))
                    data["print_client_logo"] = "1";
                else
                    data["print_client_logo"] = "0";

                if ($('#allow_manual_open_close').is(":checked"))
                    data["allow_manual_open_close"] = "1";
                else
                    data["allow_manual_open_close"] = "0";

                if ($('#activate_product_sale').is(":checked"))
                    data["activate_product_sale"] = "1";
                else
                    data["activate_product_sale"] = "0";

                if ($('#activate_subscription').is(":checked"))
                    data["activate_subscription"] = "1";
                else
                    data["activate_subscription"] = "0";

                if ($('#print_entry_ticket').is(":checked"))
                    data["print_entry_ticket"] = "1";
                else
                    data["print_entry_ticket"] = "0";

                if ($('#lost_including_parkingfee').is(":checked"))
                    data["lost_including_parkingfee"] = "1";
                else
                    data["lost_including_parkingfee"] = "0";

                if ($('#float_amount_required').is(":checked"))
                    data["float_amount_required"] = "1";
                else
                    data["float_amount_required"] = "0";

                if ($('#enable_port_communication').is(":checked"))
                    data["enable_port_communication"] = "1";
                else
                    data["enable_port_communication"] = "0";

                if ($('#central_cashier').is(":checked"))
                    data["central_cashier"] = "1";
                else
                    data["central_cashier"] = "0";

                if ($('#anpr_mismatch_check').is(":checked"))
                    data["anpr_mismatch_check"] = "1";
                else
                    data["anpr_mismatch_check"] = "0";

                if ($('#short_term_ticket_enabled').is(":checked"))
                    data["short_term_ticket_enabled"] = "1";
                else
                    data["short_term_ticket_enabled"] = "0";

                if ($('#validation_enabled').is(":checked"))
                    data["validation_enabled"] = "1";
                else
                    data["validation_enabled"] = "0";



                data["user_id"] = $("#user_id").val();
                data["task"] = "6";
                var jsondata = JSON.stringify(data);
                //console.log(jsondata);
                $.post("../ajax/settings.php", jsondata, function (result) {
                    if (result == "Successfull")
                        location.reload();
                    else
                    {    //alert(result);
                        $("#alert-div").removeClass("d-none");    
                        $("#alert-div").html(result);    
                        
                    }        
                });
            }
        });

    });

    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".device-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        device_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text = $(this).attr("data-text");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "7";
        data["activity_message"]=status_text+" device "+device_name;
        var jsondata = JSON.stringify(data);
        $.post("../ajax/settings.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable();
            else
                alert(result);
        });
    });

    /*=====edit======*/
    $(document).on("click", ".device-edit", function ()
    {
        $("#alert-div").addClass("d-none");    
        id = $(this).parent('td').parent('tr').data('id');
        device_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = id;
        data["task"] = "8";
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("../ajax/settings.php", jsondata, function (result) {
            //console.log(result);
            $("#form").trigger('reset');
            $(":checkbox").attr("checked", false);
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
            var response = JSON.parse(result);
            $("#device_category").val(response.device_category);
            $("#device_category").change();
            $("#device_name").val(response.device_name);
            $("#device_number").val(response.device_number);
            $("#device_ip").val(response.device_ip);
            $("#facility").val(response.facility_number);
            $("#multiselect").val(response.carpark_number);
            $("#camera_id").val(response.camera_id);
            $("#camera_index").val(response.camera_index);
            $("#server_handshake_interval").val(response.server_handshake_interval);
            $("#plate_capturing_wait_delay").val(response.plate_capturing_wait_delay);
            $("#device_function").val(response.device_function);
            $("#barrier_open_status_type").val(response.barrier_open_status_type);
            $("#barrier_open_time_limit").val(response.barrier_open_time_limit);
            $("#backout_blacklist_delay").val(response.backout_blacklist_delay);
            $("#output_pulse_length").val(response.output_pulse_length);
            $("#network_interface").val(response.network_interface);
            $("#upload_to_server_delay").val(response.upload_to_server_delay);

            $("#barrier_communication").val(response.barrier_communication);
            $("#transaction_time_out").val(response.transaction_time_out);
            $("#rfid_port").val(response.rfid_port);
            $("#controller_ip").val(response.controller_ip);
            $("#enable_port_communication").val(response.enable_port_communication);
            $("#barrier_port_number").val(response.barrier_port_number);
            $("#barrier_port_ip").val(response.barrier_port_ip);
            $("#anpr_image_path").val(response.anpr_image_path);
            $("#anpr_com_port").val(response.anpr_com_port);
            $("#screensaver_timeout").val(response.screensaver_timeout);
            $("#mobile_qrcode_time_limit").val(response.mobile_qrcode_time_limit);
            $("#media_path").val(response.media_path);
            $("#mode_of_operation").val(response.mode_of_operation);
            $("#entry_printer_type").val(response.entry_printer_type);
            $("#receipt_printer_type").val(response.receipt_printer_type);
            $("#receipt_primary_language").val(response.receipt_primary_language);
            $("#receipt_secondary_language").val(response.receipt_secondary_language);
            $("#plate_captured_interval").val(response.plate_captured_interval);
            $("#barrier_open_command").val(response.barrier_open_command);
            $("#barrier_close_command").val(response.barrier_close_command);
                                        
            if (response.customer_receipt_mandatory == 1)
                $('#customer_receipt_mandatory').attr("checked", "checked");
            if (response.shift_receipt_mandatory == 1)
                $('#shift_receipt_mandatory').attr("checked", "checked");
            if (response.shift_physical_cash_count_required == 1)
                $('#physical_cash_count').attr("checked", "checked");
            if (response.synch_whitelist_flag == 1)
                $('#synch_whitelist').attr("checked", "checked");
            if (response.issue_lost == 1)
                $('#issue_lost').attr("checked", "checked");

            if (response.wiegand_enabled == 1)
                $('#wiegand_enabled').attr("checked", "checked");
            if (response.wiegand2_enabled == 1)
                $('#wiegand2_enabled').attr("checked", "checked");
            if (response.barrier_status_monitoring == 1)
                $('#barrier_status_monitoring').attr("checked", "checked");
            if (response.quick_barrier_close == 1)
                $('#quick_barrier_close').attr("checked", "checked");
            if (response.payment_enabled_exit == 1)
                $('#payment_enabled_exit').attr("checked", "checked");
            if (response.bms_status_enabled == 1)
                $('#bms_status_enabled').attr("checked", "checked");
            if (response.anpr_enabled == 1)
                $('#anpr_enabled').attr("checked", "checked");
            if (response.display_anpr_image == 1)
                $('#display_anpr_image').attr("checked", "checked");
            if (response.access_enabled == 1)
                $('#access_enabled').attr("checked", "checked");
            if (response.reservation_enabled == 1)
                $('#reservation_enabled').attr("checked", "checked");
            if (response.synch_whitelist == 1)
                $('#synch_whitelist').attr("checked", "checked");
            if (response.review_mode == 1)
                $('#review_mode').attr("checked", "checked");

            if (response.cropped_picture_required == 1)
                $('#cropped_picture_required').attr("checked", "checked");
            if (response.anpr_mismatch_check == 1)
                $('#anpr_mismatch_check').attr("checked", "checked");
            if (response.allow_manual_open_close == 1)
                $('#allow_manual_open_close').attr("checked", "checked");
            if (response.activate_product_sale == 1)
                $('#activate_product_sale').attr("checked", "checked");
            if (response.activate_subscription == 1)
                $('#activate_subscription').attr("checked", "checked");
            if (response.print_entry_ticket == 1)
                $('#print_entry_ticket').attr("checked", "checked");
            if (response.lost_including_parkingfee == 1)
                $('#lost_including_parkingfee').attr("checked", "checked");
            if (response.float_amount_required == 1)
                $('#float_amount_required').attr("checked", "checked");
            if (response.full_max_exit_grace_enabled == 1)
                $('#full_max_exit_grace_enabled').attr("checked", "checked");
            if (response.print_operator_logo == 1)
                $('#print_operator_logo').attr("checked", "checked");
            if (response.central_cashier == 1)
                $('#central_cashier').attr("checked", "checked");
            if (response.enable_port_communication == 1)
                $('#enable_port_communication').attr("checked", "checked");
            if (response.coupon_enabled == 1)
                $('#coupon_enabled').attr("checked", "checked");
            if (response.short_term_ticket_enabled == 1)
                $('#short_term_ticket_enabled').attr("checked", "checked");
            if (response.validation_enabled == 1)
                $('#validation_enabled').attr("checked", "checked");
            if (response.wiegand_whitelist_check_enabled == 1)
                $('#wiegand_whitelist_check_enabled').attr("checked", "checked");

            if (response.wiegand_whitelist_parking_zone_check_enabled == 1)
                $('#wiegand_whitelist_parking_zone_check_enabled').attr("checked", "checked");
            if (response.wiegand_whitelist_expiry_check_enabled == 1)
                $('#wiegand_whitelist_expiry_check_enabled').attr("checked", "checked");
            if (response.wiegand_whitelist_carpark_check_enabled == 1)
                $('#wiegand_whitelist_carpark_check_enabled ').attr("checked", "checked");
            if (response.wiegand_whitelist_facility_check_enabled == 1)
                $('#wiegand_whitelist_facility_check_enabled').attr("checked", "checked");
            if (response.send_wiegand_response_to_port == 1)
                $('#send_wiegand_response_to_port').attr("checked", "checked");
            if (response.print_client_logo == 1)
                $('#print_client_logo').attr("checked", "checked");



            $("#add-edit-button").val("Edit");
            previous_carpark = response.carpark_number;
            previous_facility = response.facility_number;
            previous_device = response.device_number;
        });
    });


    var invalidChars = ["-","+","e"];
    $(".acceptdigit").bind("keypress", function (e) {
        console.log("here");
        if (invalidChars.includes(e.key)) {
            e.preventDefault();
        }
    });
    
    $(".acceptdigit").bind("input", function (e) {
        console.log("hereinput");
        this.value = this.value.replace(/[e\+\-]/gi, "");
    });

</script>
