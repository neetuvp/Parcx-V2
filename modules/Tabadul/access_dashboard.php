<?php

$page_title="Application";

//# Import application layout.
include('includes/header.php');
include('includes/navbar-start.php');
require_once("classes/config.php");
?>

</ul>

<div class="header text-dark" id="pdf-report-header">Access Request Dashboard</div>

<?php
include('includes/navbar-end.php');
include('includes/sidebar.php');
include('classes/parking.php');
$obj = new parking();
?>
    <!-- Modal -->
<!--<div class="modal fade" id="plate-details-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">-->
<div class="modal fade text-dark" id="plate-details-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Access Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">

                <!-- info -->
                <div class="row mb-4">
                    <div class="col-4">
                        <div class="border-simple p-4" id="plate_image_modal">
                            
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="border-simple h-100 p-3">
                            <h4 class="mb-3" id="device_name"></h4>
                            <p><strong>Date: </strong><span id="date_modal"></span></p>
                            <p><strong>Status: </strong><span id="access_allowed_modal"></span></p>
                            <p><strong>Plate Number: </strong><span id="plate_number_modal"></span></p>                           
                             <p><strong>Country: </strong><span id="plate_country_modal"></span></p>
                              <p><strong>Category: </strong><span id="plate_type_modal"></span></p>
                               <p><strong>Confidence Rate: </strong><span id="confidence_rate_modal"></span></p>                                                    
                            <p><strong>QR Code: </strong><span id="qrcode_modal"></span></p>
                            <p><strong>Gate Code: </strong><span id="gate_code_modal"></span></p>
                            <!--<p id="reason-p"><strong>Reason: </strong><span id="reason_modal"></span></p>-->
                            
                            
                        </div>
                    </div>
                </div>
                <!-- end / info -->

                <!-- table -->
                <table width="100%" id="alarm-data">
                    
                </table>
                <!-- end / table -->

            </div>
        </div>
    </div>
</div>
<!-- / end modal -->



<div class="content-wrapper">

    

    <section class="content">
        <div class="container-wide">

            <!-----Dashboard-->
                <div>
                    <div class="row">
                        <div class="col-md-12 mt-4 block-data">
                            <div class="card p-0">
                                
                                    <div class="card-header">
                                        <div class="nav-item d-flex justify-content-between align-items-center">
                                            <h3 class="card-title">Last Transaction</h3>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="row no-gutters">
                                            <div class="col-5"  id="plate-image-content">
                                                <img src ="" width="100%"; height="340";>
                                                
                                            </div>
                                            <div class="col-7">
                                                <ul class="nav flex-column">
                                                    <li class="nav-item">
                                                        <span class="nav-link">Date
                                                            <span class="float-right response_date"></span>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="nav-link">Status
                                                            <span class="float-right access_allowed"></span>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="nav-link">Plate Number
                                                                <span class="float-right plate_number"></span>
                                                        </span>
                                                    </li>
                                                    
                                                    <li class="nav-item">
                                                        <span class="nav-link">Country
                                                            <span class="float-right plate_country"></span>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="nav-link">Category
                                                            <span class="float-right plate_type"></span>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="nav-link">Confidence Rate
                                                            <span class="float-right confidence_rate"></span>
                                                        </span>
                                                    </li>
                                                    
                                                    
                                                    <li class="nav-item">
                                                        <span class="nav-link">QR Code
                                                            <span class="float-right qr_code"></span>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="nav-link">Gate Code
                                                            <span class="float-right gate_code"></span>
                                                        </span>
                                                    </li>
                                                    <!--<li class="nav-item list-reason">
                                                        <span class="nav-link">Reason
                                                            <span class="float-right reason"></span>
                                                        </span>
                                                    </li>-->
                                                
                                            </div>
                                        </div>
                                    </div>
                                
                            </div>
                            
                        </div>
                    </div>
                </div>
            <div id="live-access-requests">
                                <div class="card">
                                    <div class="card-body" id="access-request-report-content">
                                           
                                    </div>
                                </div> <!-- End . Live Revenue Summary Content --->
                 </div>
                
                
                <!-----Dashboard-->
</div>
</section>
</div>



<script>
  $('body').on('click', '#access_record', function () 
  {
$('#plate_image_modal').html('<img src ="" width="100%"; height="350";>');
$('#plate_number_modal').html("");
$('#plate_country_modal').html("");
$('#plate_type_modal').html("");
$('#confidence_rate_modal').html("");
$('#access_allowed_modal').html("");
$('#date_modal').html("");
$('#qrcode_modal').html("");
$('#gate_code_modal').html("");
$('#reason_modal').html("");
  var data={};
  data["id"]=$(this).attr('access_id');
  data["task"]=14;   
  data["language"] = $("#language").val();
  var jsondata = JSON.stringify(data);  
  $.post("ajax/parking.php?task=14",jsondata,function(data)
    {	
        var response=JSON.parse(data);
        var image_name = response["plate_image_name"];
        var camera_id = response["camera_device_number"];
        var image_url = "<?php echo ANPRImageURL?>";
        var date_captured = response["capture_date_time"];
	if(date_captured!=null)
        	date_captured = date_captured.substring(0,10);
        //$('#plate_image_modal').html('<img src ="'+image_url+'/'+camera_id+'/'+date_captured+'/Scene_'+image_name+'" width="100%"; height="350";>');
	$('#plate_image_modal').html('<img src ="'+image_url+'/'+image_name+'" width="100%"; height="350";>');
        $('#plate_number_modal').html(response["plate_number"]);
        $('#plate_country_modal').html(response["plate_country"]);
        $('#plate_type_modal').html(response["plate_type"]);
        $('#confidence_rate_modal').html(response["confidence_rate"]);
        //$('#access_allowed_modal').html(response["response_status"]);
        $('#date_modal').html(response["api_datetime_response"]);
        $('#qrcode_modal').html(response["reference_number"]);
        $('#gate_code_modal').html(response["gate_code"]);
        if(response["response_status"]=="Success")
        {
	    $('#reason-p').hide();
            $('#reason_modal').html("");
            $('#access_allowed_modal').html("Access Allowed");
           
        }
        else
        {
            // $('#reason-p').show();
            //$('#reason_modal').html(response["fault_code_text"]);
            var status = "Access Denied,"
            status = status +response["fault_code_text"];
            $('#access_allowed_modal').html(status);
        }
    })
  .fail(function(jqxhr,status,error)
    {
        alert("Error: "+error);
    }); 
 });
    
    function get_live_access_request()
    {
       $.get( "ajax/parking.php?task=13", function( data ) 
        {  
        $('#access-request-report-content').html(data); 
        reportSuccess();
	loadDataTable();
        });    
    }
    function get_live_access_request_details()
    {
        $('#plate-image-content').html('<img src ="default.jpg" width="100%"; height="380";>');
        $('.plate_number').html("");
	$('.plate_country').html("");
	$('.plate_type').html("");
	$('.confidence_rate').html("");
        $('.access_allowed').html("");
        $('.response_date').html("");
        $('.qr_code').html("");
        $('.gate_code').html("");
        $('.reason').html("");
        var data={};
        data["id"]="";
        var jsondata = JSON.stringify(data);
       $.post( "ajax/parking.php?task=14",jsondata, function( data ) 
        {  

            var response=JSON.parse(data);
            var image_name = response["plate_image_name"];
            var camera_id = response["camera_device_number"];
            var image_url = "<?php echo ANPRImageURL?>";
            var date_captured = response["capture_date_time"];
	    if(date_captured!=null)
             	date_captured = date_captured.substring(0,10);
            //$('#plate-image-content').html('<img src ="'+image_url+'/'+camera_id+'/'+date_captured+'/Scene_'+image_name+'" width="100%"; height="380";>');
	    $('#plate-image-content').html('<img src ="'+image_url+'/'+image_name+'" width="100%"; height="340";>');
            $('.plate_number').html(response["plate_number"]);
            $('.plate_country').html(response["plate_country"]);
            $('.plate_type').html(response["plate_type"]);
            $('.confidence_rate').html(response["confidence_rate"]);
            
            $('.response_date').html(response["api_datetime_response"]);
            $('.qr_code').html(response["reference_number"]);
            $('.gate_code').html(response["gate_code"]);
            if(response["response_status"]=="Success")
            {
		$('.access_allowed').html("Access Allowed");
                $('li.list-reason').hide();
                
            }
            else
            {
                //$('li.list-reason').show();
                var status = "Access Denied,"
                status = status +response["fault_code_text"];
                $('.access_allowed').html(status);
                //$('.reason').html(response["fault_code_text"]);
            }
        });    
    }
    $(document).ready(function () 
    {
        get_live_access_request();
        get_live_access_request_details()
        setInterval(function () 
        {             
            get_live_access_request(); 
            get_live_access_request_details()
        }, 1000*30);
    });
</script>
<?php include('includes/footer.php'); ?>
