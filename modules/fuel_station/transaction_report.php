<?php
$page_title = "Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header">Transaction Report</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<style type="text/css">
table 
    {
    table-layout:fixed;
    width:100%;
    }
</style>
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
    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1">
            <div class="flex-grow-1 row additional-menu-left"> 
                                
                <div class="col-md-2">
                    <select class="form-control" id="pump_id" multiple="multiple">
                        <option value="1">Pump1</option>
                        <option value="3">Pump2</option>
                    </select>
                </div>
                <div class="col-md-2">                    
                    <input type = "text" class="form-control" id="plate_number" placeholder="Plate number" >
                    
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
                <div class="col-md-2">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button">View Report</button>
                </div>          
            </div>      
            
            <div class="additional-menu-right">
                <div id="action-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" id="export">Export</button>
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">                       
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="#" id="export_excel_report">Export to Excel</a>
                            <a class="dropdown-item" href="#" id="export_pdf_report">Export to PDF</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- end / additional menu -->


    <!-- end / additional menu -->

    <section class="content">
        <div class="container-wide" id="report-content">          

        </div>
    </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>

    var date_range_message = "choose date range";
    $(function ()
        {
        $('#pump_id').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "Select all pump",
            nonSelectedText: "Select pump",
            selectAllNumber: false,
            allSelectedText: "Select all pump"
            });                    
        });
    function callReport()
    {
        var data = {};
        data["task"] = "5";
        data["from"] = from;
        data["to"] = to;
        data["plate_number"] = $("#plate_number").val();
        data["pump_id"] = $("#pump_id").val().toString();        
        var jsondata = JSON.stringify(data);
//        console.log(jsondata);
        $.post("ajax/parking.php", jsondata, function (result)
        {
            loadReport(result);            
        });
    }

    $('#view-report-button').click(function (event)
    {
        if (!daterange)
            alert(date_range_message);
        else
            callReport();
    });
    
    $('body').on('click', "[data-target='#transaction-detail']", function ()
        {        
        $(".mdl-trsn").html(""); 
        var id=$(this).data('id');
        get_transaction_details(id);       
        });
        
    function get_transaction_details(id)
    {
        var data = {};
        data["task"] = "1";
        data["id"]=id;
        var jsondata = JSON.stringify(data);
        $.post("ajax/parking.php", jsondata, function (result)
        {       
           // console.log(result);    
            var json = JSON.parse(result);                                                
            $("#api_details").html(json.api_transactions);
            $("#modal-pump-name").html("PUMP "+json.pump_id);    
            $("#modal-comment").html(json.comment);   
            $("#modal-date-time").html("Date time: "+json.transaction_date_time);
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
    
    $('#export_excel_report').click(function (event) {
        export_to_excel("#report-content", "ADNOC_Transaction_Report")

    }); // end click event function



</script>