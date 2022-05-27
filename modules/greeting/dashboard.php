<?php

$page_title="Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');

?>

</ul>
<style>
    table.dataTable tbody td {
    word-break: break-word;
    vertical-align: top;
}
#request_field_modal {
    word-break: break-word;
}
</style>
<div class="header text-dark" id="pdf-report-header">Dashboard</div>

<?php

include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
include('classes/dashboard.php');
$obj=new dashboard();
?>
<!-- Modal -->
<div class="modal fade text-dark" id="request-details-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="width:1000px;">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Interface Request Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- info -->
                <div class="row no-gutters">                                          
                    <div class="col-12">

                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <span class="nav-link"><strong>Request Date Time : </strong>
                                    <span id="date_modal"></span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Request : </strong>
                                    <span id="request_field_modal"></span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Response : </strong>
                                    <span id="response_field_modal"></span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Status Code : </strong>
                                    <span id="response_status_modal"></span>
                                </span>
                            </li>
                        </ul>
                    </div>                                            
                </div>
          
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
                    <input type = "number" class="form-control" id="pump_id" placeholder="Pump ID" >
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
                        <!--<button type="button" class="btn btn-warning" id="export_pdf_report">Export PDF</button>-->
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end / additional menu -->

  <section class="content">
    <div class="container-wide" >          
        
                <div class="card">
                    <div class="card-body" id="report-content">

                    </div>
                </div> <!-- End . Live Access Request --->
                  
    </div>
  </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
    $(document).ready(function () {

        get_live_access_request();
        setInterval(function () {     
            console.log("here");
                get_live_access_request()  ;         
        }, 30000);

    });
    $("#view-report-button").click(function(){
        get_live_access_request();
    })
    function get_live_access_request()
    {
        var from,to;
        var pump_id = $("#pump_id").val();
        var daterange = $("#reservationtime").val();
        if(daterange>"")
        {
            from = daterange.substring(0, 19);
            to = daterange.substring(22, 41);
        }
        if(pump_id==="")
            pump_id = "0";
        var data = {
            pump: pump_id,
            toDate: to,
            fromDate: from
        };
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("ajax/dashboard.php?task=1", jsondata, function (data)
        {
            $('#report-content').html(data);
            reportSuccess();
            $('#RecordsTable1').DataTable();
        })
        .fail(function (jqxhr, status, error)
        {
            alert("Error: " + error);
        });
    }
    
    $('body').on('click', '#access_record', function ()
    {
        $('#date_modal').html("");
        $('#request_field_modal').html("");
        $('#response_field_modal').html("");
        $('#response_status_modal').html("");
        var data = {};
        data["id"] = $(this).attr('access_id');
        data["task"] = 2;
        var jsondata = JSON.stringify(data);
        $.post("ajax/dashboard.php?task=2", jsondata, function (data)
        {
            console.log(data);
            var response = JSON.parse(data);

             
            $('#date_modal').html(response["request_date_time"]);
            $('#request_field_modal').html(response["request"]);
            $('#response_field_modal').html(response["response"]);
            $('#response_status_modal').html(response["response_code"]);
        })
        .fail(function (jqxhr, status, error)
        {
            alert("Error: " + error);
        });
    });
    
</script>