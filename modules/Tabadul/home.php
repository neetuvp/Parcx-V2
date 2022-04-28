
<?php
include('../../includes/header.php');
require_once("classes/config.php");
?>

<div class="navbar-has-tablink navbar-has-sublink">

    <?php include('../../includes/navbar-start.php'); ?>

</ul>

<div class="header text-dark" id="pdf-report-header">Overview</div>    
<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>


<!-- Modal -->
<div class="modal fade" id="error-log-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Notifications</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">

                <!-- info -->
                <div class="row mb-4">
                    <div class="col-4">
                        <div class="border-simple p-4">
                            <img src="" class="mx-auto d-block img-fluid" id="device_img">
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="border-simple h-100 p-3" id="device_details">

                        </div>
                    </div>
                </div>
                <!-- end / info -->



                <!-- table -->
                <table width="100%" id="watchdog-data" class="table table-blue table-bordered ">

                </table>
                <!-- end / table -->

            </div>
        </div>
    </div>
</div>
<!-- / end modal -->


<!-- Modal -->
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
                <div id="alert-div-access-details" class="alert bg-success">
                    <h5><i class="icon fa fa-check"></i> Alert!</h5>                                            
                </div> 
                <!-- info -->
                <div class="row no-gutters">                                          
                    <div class="col-12">

                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <span class="nav-link"><strong>Date</strong>
                                    <span class="float-right" id="date_modal">>2021-03-18 12:05:16</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Status</strong>
                                    <span class="float-right" id="access_allowed_modal">Access Allowed</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>QR Code</strong>
                                    <span class="float-right" id="qrcode_modal">>112233</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Gate Code</strong>
                                    <span class="float-right" id="gate_code_modal">GS101212</span>
                                </span>
                            </li>
                        </ul>

                    </div>                                            
                </div>
                <div class="row mb-4">
                    <div class="col-5">
                        <div class="border-simple p-1" id="plate_image_modal">

                        </div>
                    </div>
                    <div class="col-7">
                        <div class="border-simple h-100 p-3">
                            <h4 class="mb-3" id="device_name"></h4>                         
                            <p><strong>Plate Number: </strong><span id="plate_number_modal"></span></p>                           
                            <p><strong>Country: </strong><span id="plate_country_modal"></span></p>
                            <p><strong>Category: </strong><span id="plate_type_modal"></span></p>
                            <p><strong>Confidence Rate: </strong><span id="confidence_rate_modal"></span></p>                                                    
                        </div>
                    </div>
                </div>
                <!-- end / info -->

                <!-- info -->
                <div class="row mb-4" id="plate_secondary_modal">
                    <div class="col-5">
                        <div class="border-simple p-1" id="plate_image_modal_secondary">

                        </div>
                    </div>
                    <div class="col-7">
                        <div class="border-simple h-100 p-3">
                            <h4 class="mb-3" id="device_name"></h4>   
                            <p><strong>Plate Number: </strong><span id="plate_number_modal_secondary"></span></p>                           
                            <p><strong>Country: </strong><span id="plate_country_modal_secondary"></span></p>
                            <p><strong>Category: </strong><span id="plate_type_modal_secondary"></span></p>
                            <p><strong>Confidence Rate: </strong><span id="confidence_rate_modal_secondary"></span></p>                                                                             
                        </div>
                    </div>
                </div>        
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<!-- Modal -->
<div class="modal fade text-dark" id="plate-mismatch-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Manual plate correction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">      
            <div id="alert-div-edit-plate" class="alert bg-danger d-none">
                    <h5> Plates are not matching!</h5>                                            
                </div> 
                <label for="PlateNumber">PlateNumber in access request</label>  
                <input class="form-control form-control-lg mb-2" type="text" placeholder="Plate Number" id="plate_number_request" disabled="">                    

                <div class="row mb-4">
                    <div class="col-5">
                        <div class="border-simple p-1" id="plate_image1">

                        </div>
                    </div>
                    <div class="col-7">
                        <div class="border-simple h-100 p-3">                                                
                        <label for="PlateNumber">Current PlateNumber</label>
                        <input class="form-control form-control-lg" type="text" placeholder="Plate Number" id="plate_number1" disabled="">
                        <br/>
                        <label for="PlateNumber">Enter Corrected PlateNumber</label>
                        <input class="form-control form-control-lg" type="text" placeholder="Plate Number" id="corrected_plate_number1">
                        </div>
                    </div>
                </div>
                <!-- end / info -->

                <!-- info -->
                <div class="row mb-4" id="plate_secondary_modal">
                    <div class="col-5">
                        <div class="border-simple p-1" id="plate_image2">

                        </div>
                    </div>
                    <div class="col-7">
                        <div class="border-simple h-100 p-3">
                        <label for="PlateNumber">Current PlateNumber</label>
                        <input class="form-control form-control-lg" type="text" placeholder="Plate Number" id="plate_number2" disabled="">
                        <br/>
                        <label for="PlateNumber">Enter Corrected PlateNumber</label>
                        <input class="form-control form-control-lg" type="text" placeholder="Plate Number" id="corrected_plate_number2">                                                        
                        </div>
                    </div>
                </div>                 
            </div>
            <div class="modal-footer">                
                <button type="button" class="btn btn-primary" id="btn-save">Save changes</button>                
        </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<!-- Manual operation reason -->
<div class="modal fade text-dark" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="manual-reason-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Operation Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4">
                <p>Reason:</p>
                <textarea name='reason_text' id='reason_text' class="form-control mb-4"></textarea>
                <span id="reasonempty"></span>
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info' name='ok_reason' id='ok_reason' value='OK'>Ok</button>
                <button type='button' class='btn btn-info' name='cancel_reason' id='cancel_reason'
                        value='Cancel'>Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- end Manual operation reason -->

<div class="content-wrapper">       
    <section class="content daily-overview">
        <div class="container-wide">    
            <div class="row">
                <!-- <div class="col-md-6"> -->
                <div class="col-md-12">

                    <div class="jspdf-graph">

                        <!--/Block view/-->
                        <div id="live-device_status"></div>
                        <div id="access_denied"></div>                    
                    </div>

                </div>
            </div>  


            <!-----Dashboard-->
            <div>
                <!-- <div class="row">
                <div class="card-header">
               
                </div>
                </div> -->
                <div class="row">
                    <div class="col-md-12 mt-4 mb-4 block-data">
                        <div class="card p-0">

                            <div class="card-header" style="background-color:#44484a">
                                <div class="nav-item d-flex justify-content-between align-items-center p-2">
                                    <h3 class="card-title ml-2">Last Transaction</h3> 
                                                                    <!-- <a id='plate_mismatch_edit' href='#'><i class='fas fa-pencil-alt'></i>Manual plate correction</a>-->
                                </div>                                
                            </div>
                            <div class="card-body p-0">
                            
                                <div class="row no-gutters">                                          
                                    <div class="col-12">
                                        <div id="alert-div" class="alert bg-success mt-2">
                                            <h5><i class="icon fa fa-check"></i> Alert!</h5>                                            
                                        </div> 
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
                                                <span class="nav-link">QR Code
                                                    <span class="float-right qr_code"></span>
                                                </span>
                                            </li>
                                            <li class="nav-item">
                                                <span class="nav-link">Gate Code
                                                    <span class="float-right gate_code"></span>
                                                </span>
                                            </li>                                                
                                        </ul>


                                    </div>                                            
                                </div>
                                <div class="row no-gutters mt-4" id="plate-details">
                                    <div class="col-3"  id="plate-image-content">
                                        <img src ="" >

                                    </div>
                                    <div class="col-3">
                                        <ul class="nav flex-column">

                                            <li class="nav-item">
                                                <span class="nav-link"><strong>Camera 1</strong>
                                                    <span></span>
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



                                            <!--<li class="nav-item list-reason">
                                                <span class="nav-link">Reason
                                                    <span class="float-right reason"></span>
                                                </span>
                                            </li>-->

                                    </div>
                                    <!-- </div>
                                    <div class="row no-gutters mt-4"> -->

                                    <div class="col-3"  id="plate-image-content_secondary">
                                        <img src ="" >

                                    </div>
                                    <div class="col-3">
                                        <ul class="nav flex-column">
                                            <li class="nav-item">
                                                <span class="nav-link"><strong>Camera 2</strong>
                                                    <span></span>
                                                </span>
                                            </li>
                                            <li class="nav-item">
                                                <span class="nav-link">Plate Number
                                                    <span class="float-right plate_number_secondary"></span>
                                                </span>
                                            </li>

                                            <li class="nav-item">
                                                <span class="nav-link">Country
                                                    <span class="float-right plate_country_secondary"></span>
                                                </span>
                                            </li>
                                            <li class="nav-item">
                                                <span class="nav-link">Category
                                                    <span class="float-right plate_type_secondary"></span>
                                                </span>
                                            </li>
                                            <li class="nav-item">
                                                <span class="nav-link">Confidence Rate
                                                    <span class="float-right confidence_rate_secondary"></span>
                                                </span>
                                            </li>


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


            <!-- entry exit chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card barchart-box" id="HourlyOccupancyGraph">

                        <nav class="navbar navbar-light navbar-expand-md bg-faded justify-content-center p-0">
                            <a href="/" class="d-flex w-50 mr-auto"></a>

                            <div class="navbar-collapse w-100" id="collapsingNavbar3">

                                <ul class="navbar-nav w-100 justify-content-center">
                                    <p class="text-center chart-header text-dark justify-content-middle">
                                        Hourly Entry 

                                    </p>
                                </ul>

                                <div class="w-100 row m-0">
                                    <div class="col-4 ml-auto p-0">
                                        <select id="linechart-select">                                            
                                            <option value="0">This day last week</option>
                                            <option value="1">Last week's average</option>
                                            <option value="2">All-time average</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </nav>

                        <div class="">
                            <div class="position-relative">
                                <canvas id="hourly-occ-chart" height="300"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </div>                   
            <!-- end / entry exit chart -->

        </div>
    </section>

</div>
</div>


<script>
 
    var hourly_occ_chart;
    var mindata = [];
    var avgdata = [];
    var task;
    function device_live_view() {
        //block view
        $.get("ajax/dashboard.php?task=1", function (data) {
            $('#live-device_status').html(data);
        });

    }



    $(document).ready(function () {

        device_live_view();
        gethourlyentry();        
        get_live_access_request_details()

        setInterval(function () {
            if($('.modal').is(':visible')==false)
                {                
                get_live_access_request_details()
                device_live_view();
                updateHourlyOccupancy();
                }
        }, 30000);

    });


    /* Generate Modal Data */

    var device_name;
    var device_ip;
    var device_number;
    var rask, movement_type, description;
    var barrier;
    $('body').on('click', "[data-target='#error-log-modal']", function (e) {
        device_name = $(this).find(".card-title").text();
        device_ip = $(this).find(".device_ip").text();
        device_img = $(this).attr("data-img");
        device_number = $(this).attr("data-number");

        if (!$(e.target).is("input"))
        {
            $("#error-log-modal").modal('show')
            barrier = $(this).attr("barrier");
            $("#device_img").attr("src", ("../../dist/img/icon/" + device_img + "-lg.png"));
            var data = {
                device_number: device_number
            };
            var jsondata = JSON.stringify(data);
            $.post("ajax/dashboard.php?task=4", jsondata, function (data) {

                $('#device_details').html(data);
                if (barrier == "true")
                    $('.btn-open-barrier').show();
                else
                    $('.btn-open-barrier').hide();
            });

            $.post("ajax/dashboard.php?task=2", jsondata, function (data) {
                $('#watchdog-data').html(data);
            });
        }

    });


    $(document).on("click", ".btn-barrier", function ()
    {
        var value = $(this).attr('value');
        switch (value)
        {
            case "Open Barrier1":
                task = "S01";
                movement_type = 3;
                description = "Open Barrier1 From Server"
                break;
	    case "Close Barrier1":
                task = "S02";
                movement_type = 4;
                description = "Close Barrier1 From Server"
                break;
            case "Open Barrier2":
                task = "S14";
                movement_type = 4;
                description = "Open Barrier2 From Server"
                break;
	    case "Close Barrier2":
                task = "S15";
                movement_type = 4;
                description = "Close Barrier2 From Server"
                break;

        }
        $('#detailModal').modal('show');
    });

//modal cancel	
    $(document).on('click', '#cancel_reason', function () {
        $('#reason_text').val("");
        $('#detailModal').modal('hide');
    });

//modal ok
    $(document).on('click', '#ok_reason', function ()
    {
        var reason = $('#reason_text').val();
        if (reason != "")
        {
            $('#detailModal').modal('hide');
            var operator = $("#username2").text();
            var data = {
                device_number: device_number,
                device_ip: device_ip,
                device_name: device_name,
                description: description,
                carpark_number: "1",
                task: task,
                reason: reason,
                device_type: "1",
                movement_type: movement_type
            };
            var jsontemp = JSON.stringify(data);
            
            $.post("ajax/dashboard.php?task=3", jsontemp)
                    .done(function (result) {
                        
                    }, "json");

            $('#reason_text').val("");
        } else
        {
            alert("Please enter a valid reason");
        }
    });



    function gethourlyentry()
    {
        $.get("ajax/dashboard.php?task=5", function (data) {
            var json = JSON.parse(data);
            mindata = json;
            if (typeof hourly_occ_chart === 'undefined')
                getaverageentry()
            else
            {
                hourly_occ_chart.data.datasets[0].data = mindata;
                hourly_occ_chart.update();
            }

        });


    }
    function getaverageentry()
    {
        var type = $('#linechart-select').val();
        $.get("ajax/dashboard.php?task=6&type=" + type, function (data) {
            var json = JSON.parse(data);
            avgdata = json;
            if (typeof hourly_occ_chart === 'undefined')
                hourlyOccupancy();
            else
            {
                hourly_occ_chart.data.datasets[1].data = avgdata;
                hourly_occ_chart.update();
            }

        });

    }
    function updateHourlyOccupancy()
    {
        gethourlyentry();
    }


    function hourlyOccupancy()
    {

        // set up multiselect
        $(document).ready(function ()
        {
            $('#linechart-select').on('change', function (e) {
                getaverageentry();
                hourly_occ_chart.data.datasets[1].label = $('#linechart-select').find("option:selected").text();
            });




            //mindata =gethourlyentry();


            $(function () {
                'use strict'

                var default_data = {
                    labels: hours_label,
                    datasets: [{
                            data: mindata,
                            label: 'Entry',

                            // transparent bar with normal border
                            backgroundColor: "rgba(40,167,69, 0.5)",
                            borderColor: '#28a745',
                            borderWidth: 1.5,
                        },
                        {
                            type: 'line',
                            label: "This day last week",
                            borderColor: '#28a745',
                            borderWidth: 2,
                            fill: false,
                            data: avgdata
                        }
                    ]
                }

                /* end datasets */

                var ticksStyle = {
                    fontColor: '#000',
                    // fontStyle: 'bold'
                }

                var mode = 'index'
                var intersect = true

                var $hourly_occ_chart = $('#hourly-occ-chart')
                hourly_occ_chart = new Chart($hourly_occ_chart, {
                    type: 'bar',

                    data: default_data,
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            mode: mode,
                            intersect: intersect
                        },
                        hover: {
                            mode: mode,
                            intersect: intersect
                        },
                        legend: {
                            display: true,
                            text: 'test1'
                        },
                        scales: {
                            yAxes: [{
                                    gridLines: {
                                        display: true,
                                        lineWidth: '4px',
                                        color: 'rgba(0, 0, 0, .2)',
                                        zeroLineColor: 'transparent'
                                    },
                                    // scaleLabel: {
                                    //   display: true,
                                    //   labelString: 'Number of Cars',
                                    //   fontStyle: 'inherit',
                                    // },
                                    ticks: $.extend({
                                        beginAtZero: true,
                                    }, ticksStyle)
                                }],
                            xAxes: [{
                                    display: true,
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: ticksStyle
                                }]
                        },
                        // animation: false,
                    },
                    plugins: [{
                            beforeInit: function (chart) {
                                chart.data.labels.forEach(function (e, i, a) {
                                    // add linebreak where "\n" occurs
                                    if (/\n/.test(e)) {
                                        a[i] = e.split(/\n/);
                                    }
                                });
                            }
                        }]
                })

            })

        });
    }
    $('body').on('click', '#access_record', function ()
    {
        $('#plate_image_modal').html('<img src ="" width="100%"; height="100%";>');
        $('#plate_number_modal').html("");
        $('#plate_country_modal').html("");
        $('#plate_type_modal').html("");
        $('#confidence_rate_modal').html("");
        $('#plate_image_modal_secondary').html('<img src ="" width="100%"; height="100%";>');
        $('#plate_number_modal_secondary').html("");
        $('#plate_country_modal_secondary').html("");
        $('#plate_type_modal_secondary').html("");
        $('#confidence_rate_modal_secondary').html("");
        $('#access_allowed_modal').html("");
        $('#date_modal').html("");
        $('#qrcode_modal').html("");
        $('#gate_code_modal').html("");
        $('#reason_modal').html("");
        var data = {};
        data["id"] = $(this).attr('access_id');
        data["task"] = 14;
        data["language"] = $("#language").val();
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php?task=14", jsondata, function (data)
        {
            console.log(data);
            var response = JSON.parse(data);

            if (response["plates_match"] == 1)
            {
                $("#alert-div-access-details").removeClass("bg-danger");
                $("#alert-div-access-details").addClass("bg-success");
                $("#alert-div-access-details").html("<h5><i class='icon fa fa-check'></i>Plates match</h5>")
            } else if (response["plates_match"] == 2)
            {
                $("#alert-div-access-details").removeClass("bg-danger");
                $("#alert-div-access-details").addClass("bg-success");
                $("#alert-div-access-details").html("<h5><i class='icon fa fa-times'></i>No Plate Comparison required</h5>")               
            } else
            {
                $("#alert-div-access-details").removeClass("bg-success");
                $("#alert-div-access-details").addClass("bg-danger");
                $("#alert-div-access-details").html("<h5><i class='icon fa fa-times'></i> Plates mismatch</h5>")
            }
            //var image_name = response["plate_image_name"];
            var camera_id = response["camera_device_number"];
            var image_url = "<?php echo ANPRImageURL ?>";
            var date_captured = response["capture_date_time"];
	    var image_name = camera_id+'/'+response["capture_date"]+'/Scene_'+response["plate_image_name"];
            if (date_captured != null)
                date_captured = date_captured.substring(0, 10);
            //$('#plate_image_modal').html('<img src ="'+image_url+'/'+camera_id+'/'+date_captured+'/Scene_'+image_name+'" width="100%"; height="350";>');
            $('#plate_image_modal').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="100%";>');
            
            if(response["initial_plate_number"]!=null)    
                $('#plate_number_modal').html(response["initial_plate_number"]);
            else
                $('#plate_number_modal').html(response["plate"]);

            
            $('#plate_country_modal').html(response["plate_country"]);
            $('#plate_type_modal').html(response["plate_type"]);
            $('#confidence_rate_modal').html(response["confidence_rate"]);
            //$('#access_allowed_modal').html(response["response_status"]);
            $('#date_modal').html(response["api_datetime_request"]);
            $('#qrcode_modal').html(response["reference_number"]);
            $('#gate_code_modal').html(response["gate_code"]);

            camera_id = response["camera_device_number2"];
                //image_name = response["plate_image_name2"]; 
	    image_name = camera_id+'/'+response["capture_date2"]+'/Scene_'+response["plate_image_name2"];           
                    
            $('#plate_image_modal_secondary').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="100%";>');
            
            if(response["initial_plate_number"]!=null)    
                $('#plate_number_modal_secondary').html(response["initial_plate_number2"]);
            else
                $('#plate_number_modal_secondary').html(response["plate_number2"]);

            
            $('#plate_country_modal_secondary').html(response["plate_country2"]);
            $('#plate_type_modal_secondary').html(response["plate_type2"]);
            $('#confidence_rate_modal_secondary').html(response["confidence_rate2"]);

            if (response["response_status"] == "Success")
            {
                $('#reason-p').hide();
                $('#reason_modal').html("");
                $('#access_allowed_modal').html("Access Allowed");
            } else
            {
                // $('#reason-p').show();
                //$('#reason_modal').html(response["fault_code_text"]);
                var status = "Access Denied,"
                status = status + response["fault_code_text"];
                $('#access_allowed_modal').html(status);

            }
            if (response["gate_code"] != "Manual Entry")
            {
                $('#plate_secondary_modal').show();
                
            } else
            {
                $('#plate_secondary_modal').hide();
            }
        })
                .fail(function (jqxhr, status, error)
                {
                    alert("Error: " + error);
                });
    });

    function get_live_access_request()
    {
        $.get("ajax/parking.php?task=13", function (data)
        {
            $('#access-request-report-content').html(data);
            reportSuccess();
            loadDataTable();
        });
    }

var plate_number;
var seconday_plate_number;
var plate_captured_id1;
var plate_captured_id2;
var access_id=0;
var plate1_corrected;
var plate2_corrected;
    function get_live_access_request_details()
    {
        

        var data = {};
        data["id"] = "";
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php?task=14", jsondata, function (data)
        {         
            $('#plate_secondary').show();
            var response = JSON.parse(data);
            if(access_id!=parseInt(response["id"],10))
                {
                $('#plate-image-content').html('<img src ="default.jpg" width="100%"; height="280";>');
                $('#plate-image-content_secondary').html('<img src ="default.jpg" width="100%"; height="280";>');
                $("#corrected_plate_number1").val("");
                $("#corrected_plate_number2").val("");

                $('.plate_number').html("");
                $('.plate_country').html("");
                $('.plate_type').html("");
                $('.confidence_rate').html("");
                $('.access_allowed').html("");
                $('.response_date').html("");
                $('.qr_code').html("");
                $('.gate_code').html("");
                $('.reason').html("");
                
                $('.plate_number_secondary').html("");
                $('.plate_country_secondary').html("");
                $('.plate_type_secondary').html("");
                $('.confidence_rate_secondary').html("");

                access_id=parseInt(response["id"],10);
                plate_captured_id1=response["plate_capture_id"];
                plate_captured_id2=response["plate_capture_id_secondary"];       
                

                if (response["plates_match"] == 1)
                {
                    $("#alert-div").removeClass("bg-danger");
                    $("#alert-div").addClass("bg-success");
                    $("#alert-div").html("<h5><i class='icon fa fa-check'></i> Plates match</h5>")
                    $("#plate_mismatch_edit").removeClass("d-none");
                } else if (response["plates_match"] == 2)
                {
                    $("#alert-div").removeClass("bg-danger");
                    $("#alert-div").addClass("bg-success");
                    $("#alert-div").html("<h5><i class='icon fa fa-times'></i> No plate comprison required</h5>")
                    $("#plate_mismatch_edit").addClass("d-none");
                } else
                {
                    $("#alert-div").removeClass("bg-success");
                    $("#alert-div").addClass("bg-danger");
                    $("#alert-div").html("<h5><i class='icon fa fa-times'></i> Plates mismatch</h5>")
                    $("#plate_mismatch_edit").removeClass("d-none");
                }
                
                var camera_id = response["camera_device_number"];
                var image_url = "<?php echo ANPRImageURL ?>";
		var image_name = camera_id+'/'+response["capture_date"]+'/Scene_'+response["plate_image_name"];
                var date_captured = response["capture_date_time"];
                if (date_captured != null)
                    date_captured = date_captured.substring(0, 10);            
                $('#plate-image-content').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="280";>');
                
                $('#plate_image1').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="100%";>');
                $('#plate_number1').val(response["plate"]);
                $("#plate_number_request").val(response["plate_number"])
                
                if(response["initial_plate_number"]!=null)    
                    {
                    $('.plate_number').html(response["initial_plate_number"]);
                    plate1_corrected=1;
                    }
                else
                    {
                    $('.plate_number').html(response["plate"]);
                    plate1_corrected=0;
                    }

                $('.plate_country').html(response["plate_country"]);
                $('.plate_type').html(response["plate_type"]);
                $('.confidence_rate').html(response["confidence_rate"]);

                $('.response_date').html(response["api_datetime_request"]);
                $('.qr_code').html(response["reference_number"]);
                $('.gate_code').html(response["gate_code"]);

		camera_id = response["camera_device_number2"];
                //image_name = response["plate_image_name2"]; 
		image_name = camera_id+'/'+response["capture_date2"]+'/Scene_'+response["plate_image_name2"];           
                $('#plate-image-content_secondary').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="280";>');                    
                
                if(response["initial_plate_number2"]!=null)                        
                    {
                    $('.plate_number_secondary').html(response["initial_plate_number2"]);
                    plate2_corrected=1;
                    }
                else
                    {
                    $('.plate_number_secondary').html(response["plate_number2"]);
                    plate2_corrected=0;
                    }


                $('#plate_image2').html('<img src ="' + image_url + '/' + image_name + '" width="100%"; height="100%";>');
                $('#plate_number2').val(response["plate_number2"]);

                $('.plate_country_secondary').html(response["plate_country2"]);
                $('.plate_type_secondary').html(response["plate_type2"]);
                $('.confidence_rate_secondary').html(response["confidence_rate2"]);



                if (response["response_status"] == "Success")
                {
                    $('.access_allowed').html("Access Allowed");
                    $('li.list-reason').hide();
                } else
                {
                    //$('li.list-reason').show();
                    var status = "Access Denied,"
                    status = status + response["fault_code_text"];
                    $('.access_allowed').html(status);
                    //$('.reason').html(response["fault_code_text"]);
                }
                if (response["gate_code"] != "Manual Entry")
                {
                    $('#plate_secondary').show();
                            
                } else
                {
                    $("#plate-details").hide();
                    $('#plate_secondary').hide();
                }

                if (response["reference_number"] != '')
                {
                    $("#plate-details").hide();
                }

            get_live_access_request();
            }
        });
    }

    $(document).on("click", "#plate_mismatch_edit", function (){
    $("#plate-mismatch-modal").modal("show");
});   

$(document).on("click", "#btn-save", function (){    
    $("#alert-div-edit-plate").addClass("d-none");    

    var plate1=$("#plate_number1").val();
    var plate2=$("#plate_number2").val();
    
    var corrected_plate1=$("#corrected_plate_number1").val();
    var corrected_plate2=$("#corrected_plate_number2").val();
    var data = {};
    if(corrected_plate1=="")
        corrected_plate1=plate1;
    else
        {
        data["plate_captured_id"]=plate_captured_id1;
        data["initial_plate_number1"]=plate1;
        data["plate1_already_corrected"]=plate1_corrected;
        }
    if(corrected_plate2=="")
        corrected_plate2=plate2;
    else
        {
        data["plate_captured_id_secondary"]=plate_captured_id2;            
        data["initial_plate_number2"]=plate2;
        data["plate2_already_corrected"]=plate2_corrected;
        }
    
    if(corrected_plate1!=corrected_plate2)
        {        
        $("#alert-div-edit-plate").removeClass("d-none");
        }
    else    
        {                            
        data["plate_number"]=corrected_plate1;       
        var jsondata = JSON.stringify(data);
	console.log(jsondata);          
        $.post("ajax/parking.php?task=16", jsondata, function (data){ 
            console.log(data);
            $("#plate-mismatch-modal").modal("hide");      
            access_id=0;                 
            get_live_access_request_details();
        });                     
        }
    }); 
   
</script>

<?php include('../../includes/footer.php'); ?>


