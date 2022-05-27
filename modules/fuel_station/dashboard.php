<?php
include('../../includes/header.php');
$_SESSION["dashboard"] = 1;
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Dashboard</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Latest transaction</div>
        <div class="tab-link" data-target="live_status">Live Status</div>
        <div class="tab-link" data-target="live_report">Live Report</div>
        
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<script>
    document.title = "Parcx";
</script>

<div class="modal fade" id="transaction-detail">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-pump-name">Transaction details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">                                         
                <h5 id="modal-comment">Comment</h5> 

                <label id="modal-date-time">Date time: </label>           
                <div class=" row mt-2">                    
                    <div class="col "  >
                        <h5 id="modal-camera-name-1">Camera 1</h5>
                        <div class="row border-simple mb-2" >                                            
                            <img id="modal-plate-image-1" src ="" width="100%"; height="350";>
                        </div>
                        <div class="row">  
                            <div class="col border-simple">                                  
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <span class="nav-link">Date time
                                            <span class="float-right mdl-trsn " id="modal-response-date-1"></span>
                                        </span>
                                    </li>                                                                                              
                                    <li class="nav-item">
                                        <span class="nav-link">Plate Number
                                            <span class="float-right mdl-trsn " id="modal-plate-number-1"></span>                                                    
                                        </span>
                                    </li>

                                    <li class="nav-item">
                                        <span class="nav-link">Confidence Rate
                                            <span class="float-right mdl-trsn " id="modal-confidence-rate-1"></span>
                                        </span>
                                    </li>                                                                              
                            </div>
                        </div>
                    </div>
                    <div class="ml-2 col"  >
                        <h5 id="modal-camera-name-2">Camera 2</h5>
                        <div class="row border-simple mb-2" >                                            
                            <img id="modal-plate-image-2" src ="" width="100%"; height="350";>
                        </div>
                        <div class="row" > 
                            <div class="col border-simple"  >
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <span class="nav-link">Date time
                                            <span class="float-right mdl-trsn " id="modal-response-date-2"></span>
                                        </span>
                                    </li>

                                    <li class="nav-item">
                                        <span class="nav-link">Plate Number
                                            <span class="float-right mdl-trsn " id="modal-plate-number-2"></span>                                                    
                                        </span>
                                    </li>

                                    <li class="nav-item">
                                        <span class="nav-link">Confidence Rate
                                            <span class="float-right mdl-trsn " id="modal-confidence-rate-2"></span>
                                        </span>
                                    </li>                                                                              


                            </div>                                                                              
                        </div>

                    </div>
                </div>
                <div class=" mt-2 border-simple p-2" id="api_details">                    

                </div>
            </div>            
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="content-wrapper">        
    <section class="content">
        <div class="container-wide">            
            <div id="pump_details" data-status="overview" class="block-data">
                
            </div> 

            <div class="card block-data" data-status="live_report" style="display:none;">
                <div class="card-body" >
                    <div class="dataTables_wrapper dt-bootstrap4" id="report-content">

                    </div>
                </div>
            </div> 
            
            <div class="card block-data col-md-4" data-status="live_status" style="display:none;">
                <div class="card-body" >
                    <div class="dataTables_wrapper dt-bootstrap4" id="live-status">

                    </div>
                </div>
            </div> 

        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>
<script>
    var pump1_id = 0, pump2_id = 0, call_live_report = false;
    var pump_transaction_id = {};
    var total_pumps=0

    $("* [data-target]").on('click', function ()
    {                
        var $target = $(this).data('target');           
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
            
    });

    function show_pump_details()
    {
        var data = {};
        data["task"] = "6";
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {
            $('#pump_details').html(result);
            total_pumps=$(".pump-dashboard").length;
            for (let i = 0; i < total_pumps; i++) {
             pump_transaction_id[i]=0   
            }

        });
    }


    function get_pump_details()
    {
        var data = {};
        data["task"] = "7";
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {
            var json = JSON.parse(result);
            call_live_report=false;
            for (let i = 0; i < json.length; i++) {
                pump = json[i];
                pump_id = pump.pump_id;
                $("#response-date1-" + pump_id).html(pump.capture_date_time1);
                $("#response-date2-" + pump_id).html(pump.capture_date_time2);
                $("#confidence-rate1-" + pump_id).html(pump.confidence_rate1);
                $("#confidence-rate2-" + pump_id).html(pump.confidence_rate2);
                $("#plate-number1-" + pump_id).html(pump.plate_number1);
                $("#plate-number2-" + pump_id).html(pump.plate_number2);
                $("#plate-image1-" + pump_id).attr("src", pump.image_location1);
                $("#plate-image2-" + pump_id).attr("src", pump.image_location2);
                if(pump.transaction_id!=pump_transaction_id[i])
                    {
                    call_live_report=true;
                    pump_transaction_id[i]=pump.transaction_id;
                    } 
                }  
                
            if(call_live_report==true)
                live_report();
        });
    }

    function live_report()
    {
        var data = {};
        data["task"] = "2";
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {
            $('#report-content').html(result);
            loadDataTable();
        });
    }
    
    
    function live_status()
    {
        var data = {};
        data["task"] = "8";
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {
            $('#live-status').html(result);
            
        });
    }
    
    $('body').on('click', "[data-target='#transaction-detail']", function ()
    {
        $(".mdl-trsn").html("");
        var id = $(this).data('id');
        get_transaction_details(id);
    });

    function get_transaction_details(id)
    {
        var data = {};
        data["task"] = "1";
        data["id"] = id;
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {
//            console.log(result);    
            var json = JSON.parse(result);
            $("#api_details").html(json.api_transactions);
            $("#modal-pump-name").html("PUMP " + json.pump_id);
            $("#modal-comment").html(json.comment);
            $("#modal-date-time").html("Date time: " + json.transaction_date_time);
            $("#modal-camera-name-1").html(json.camera1);
            $("#modal-camera-name-2").html(json.camera2);
            $("#modal-response-date-1").html(json.capture_date_time1);
            $("#modal-response-date-2").html(json.capture_date_time2);
            $("#modal-confidence-rate-1").html(json.confidence_rate1);
            $("#modal-confidence-rate-2").html(json.confidence_rate2);
            $("#modal-plate-number-1").html(json.plate_number1);
            $("#modal-plate-number-2").html(json.plate_number2);
            $("#modal-plate-image-1").attr("src", json.image_location1);
            $("#modal-plate-image-2").attr("src", json.image_location2);
        });
    }
    show_pump_details();
    get_pump_details();
    live_status();
    setInterval(function ()
    {
         get_pump_details();        
    }, 10 * 1000);
    
    setInterval(function ()
    {
         live_status();    
    }, 5 * 1000);
</script>
