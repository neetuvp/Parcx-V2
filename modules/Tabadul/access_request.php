<?php

$page_title="Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header">Access Request</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
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
                            <p><strong>Plate Number: </strong><span id="plate_number_modal"></span></p>
                             <p><strong>Country: </strong><span id="plate_country_modal"></span></p>
                              <p><strong>Category: </strong><span id="plate_type_modal"></span></p>
                               <p><strong>Confidence Rate: </strong><span id="confidence_rate_modal"></span></p>
                            
                            <p><strong>Date: </strong><span id="date_modal"></span></p>
                            <p><strong>QR Code: </strong><span id="qrcode_modal"></span></p>
                            <p><strong>Gate Code: </strong><span id="gate_code_modal"></span></p>
                            <p id="reason-p"><strong>Reason: </strong><span id="reason_modal"></span></p>
                            
                            <p><strong>Access Allowed: </strong><span id="access_allowed_modal"></span></p>
                            <p style="word-wrap: break-word;"><strong>API Request: </strong><span id="api_request_modal"></span></p>
                            <p style="word-wrap: break-word;"><strong>API Response: </strong><span id="api_response_modal"></span></p>
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

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">

                
		<div class="col-md-2">                    
                    <input type = "text" class="form-control" id="qrcode" placeholder="QR Code" >
                </div>
                <div class="col-md-2">                    
                    <input type = "text" class="form-control" id="plate" placeholder="Plate Number" >
                </div>
                
                <div class="col-md-2">
                    <select class="form-control" id="fault_type" placeholder="Type">
                        <option value='0'>All</option>
                        <option value = 'Access Allowed'>Access Allowed</option>
                        <option value = 'The gate not found'>The gate not found</option>
                        <option value = 'The gate purpose not found'>The gate purpose not found</option>
                        <option value = 'The truck appointment not found'>Appointment not found</option>
                        <option value = 'Too early arrival'>Too early arrival</option>
                        <option value = 'Too late arrival'>Too late arrival</option>
                        <option value = 'Invalid truck appointment status'>Invalid appointment status</option>
                        <option value = 'Invalid port gate'>Invalid port gate</option>
                        <option value = 'There are multiple appointment for this truck'>Multiple appointment</option>
                        <option value = 'API'>API Error</option>
                        <option value = 'Token'>Token Error</option>
                    </select>
                </div>

                <!-- date and time -->
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                        <input type="text" class="form-control float-right" id="reservationtime" autocomplete="off"
                            placeholder="Choose Date and Time Range">
                    </div>
                </div>

                <!-- search -->
                <div class="col-md-1">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button">Search</button>
                </div>

                <!-- loader -->
                <div class='col-1' id='loader'>
                    <img src='../../dist/img/loading.gif'>
                </div>

            </div>

            <div class="additional-menu-right">
                <div id="action-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" id="export_pdf_report">Export PDF</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end / additional menu -->

    <section class="content">
        <div class="container-wide">

            <!-- begin report -->
            <div class="card">
                <div class="card-body" id="access-request-report-content">
			No Records Found
                </div>
            </div>
            <!-- / end report -->

        </div>

</div>
</section>
</div>



<script>
$('body').on('click', '#access_record', function () 
  {
$('#plate_image_modal').html('<img src ="" width="100%"; height="400";>');
$('#plate_number_modal').html("");
$('#plate_country_modal').html("");
$('#plate_type_modal').html("");
$('#confidence_rate_modal').html("");
$('#access_allowed_modal').html("");
$('#date_modal').html("");
$('#qrcode_modal').html("");
$('#gate_code_modal').html("");
$('#api_request_modal').html("");
$('#reason_modal').html("");
$('#api_response_modal').html("");
  var data={};
  data["id"]=$(this).attr('access_id');
  data["task"]=14;   
  data["language"] = $("#language").val();
  var jsondata = JSON.stringify(data);      
  //console.log(jsondata);
  $.post("ajax/parking.php?task=14",jsondata,function(data)
    {	
        var response=JSON.parse(data);
        var image_name = response["plate_image_name"];
        var camera_id = response["camera_device_number"];
        var image_url = "<?php echo ANPRImageURL?>";
        var date_captured = response["capture_date"];
        //date_captured = date_captured.substring(0,10);
        //$('#plate_image_modal').html('<img src ="'+image_url+'/'+image_name+'" width="100%"; height="400";>');
	image_url = image_url+"/"+camera_id+"/"+date_captured
	$('#plate_image_modal').html('<img src ="'+image_url+'/Scene_'+image_name+'" width="100%"; height="400";>');
        $('#plate_number_modal').html(response["plate_number"]);
        $('#plate_country_modal').html(response["plate_country"]);
        $('#plate_type_modal').html(response["plate_type"]);
        $('#confidence_rate_modal').html(response["confidence_rate"]);
        $('#access_allowed_modal').html(response["response_status"]);
        $('#date_modal').html(response["api_datetime_response"]);
        $('#qrcode_modal').html(response["reference_number"]);
        $('#gate_code_modal').html(response["gate_code"]);
        $('#api_request_modal').html(response["request"]);
        
        if(response["response_status"]=="Failed")
        {
            $('#reason-p').show();
            $('#reason_modal').html(response["fault_code_text"]);
	    $('#api_response_modal').html(response["fault_string"]);
        }
        else
        {
            $('#reason-p').hide();
            $('#reason_modal').html("");
	   $('#api_response_modal').html(response["api_response"]);
        }
    })
  .fail(function(jqxhr,status,error)
    {
        alert("Error: "+error);
    }); 
 });
 
    $('#view-report-button').click(function (event) {
        callReport();
    });
    
    function callReport()
    {
        var device = $("#device").val();
        var daterange = $("#reservationtime").val();
        var from = daterange.substring(0, 19);
        var to = daterange.substring(22, 41);
        var plate = $("#plate").val();
	var qrcode = $("#qrcode").val();
        var type = $("#fault_type").val();
        if ((!daterange)) {
            alert("choose date range");
        } else {
            var data = {
                device: device,
                toDate: to,
                fromDate: from,
                plate: plate,
		qrcode:qrcode,
                type:type
            };
            var temp = JSON.stringify(data);
            //alert(temp);
            $.post("ajax/parking.php?task=3", temp)
                .done(function (result) {
                    $("#access-request-report-content").html(result);
                    reportSuccess();
		    loadDataTable();
                }, "json");
        } // end if 

        event.preventDefault();
    }
   


     /* Generate Modal Data */

    $('body').on('click', "[data-target='#error-log-modal']", function () {

        var temp = $(this).text();
        console.log(temp);
        var data = {ticket_number:temp};
        var json = JSON.stringify(data)
         $.post("ajax/parking.php?task=5", json)
         .done(function (result) {

         $("#barrier-action-report-content-modal").html(result);
         }, "json");
    });

    $('body').on('click', "[data-target='#image-modal']", function () {
    var id = $(this).data('value');
    var device_no = $('#device_no'+id).val();
    var plate_number = $('#plate_number'+id).text();
    console.log(plate_number);
    var data = {
        device_no:device_no,
        plate_number:plate_number
    };
    var json = JSON.stringify(data)
    console.log(json);
    $.post("ajax/parking.php?task=9", json)
    .done(function (result) {
        console.log(result)
    $("#image-content-modal").html(result);
    }, "json");
});

$("#language").change(function ()
{
    update_session();
    callReport();
    /*loadReportLabels();
    */

});
</script>
<?php include('../../includes/footer.php'); ?>
