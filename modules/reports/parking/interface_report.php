<?php
$page_title = "Application";

//# Import application layout.
include('../../includes/header.php');
$access = checkPageAccess("cms_interface_report");
include('../../includes/navbar-start.php');

?>

</ul>
<style>
    table.dataTable tbody td {
    word-break: break-word;
    vertical-align: top;
    width:10%;
}
#request_field_modal {
    word-break: break-word;
}
</style>
<div class="header text-dark" id="pdf-report-header">Interface Report</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
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

                <!-- interfaces -->
                <div class="col-md-2">
                    <select class="form-control" id="interface" multiple="multiple">
                    <option value="'PX-Adnoc-CMS-Pump-Status'">PX-Adnoc-CMS-Pump-Status</option>
		    <option value="'PX-Greeting-Screen-Controller'">PX-Greeting-Screen-Controller</option>
		    <option value="'PX-Adnoc-CMS-Pump-BillingInfo'">PX-Adnoc-CMS-Pump-BillingInfo</option>
                    </select>
                </div>

                <!-- interface type-->
                <div class="col-md-2">
                    <select class="form-control" id="interface_type">
                    <option value = "">--Select Request Type--</option>
		    <option value = "0">Incoming</option>
		    <option value = "1">Outgoing</option>
                    </select>
                </div>
	
		<!-- date and time -->
            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="text" class="form-control float-right" id="reservationtime" autocomplete="off"
                        placeholder="Choose Date Time">
                </div>
            </div>

            <!-- search -->
            <div class="col-md-2">
                <button type="button" class="btn btn-block btn-secondary" id="view-report-button">Search</button>
            </div>

            <!-- loader -->
            <div class='col-1' id='loader'>
                <img src='../../../dist/img/loading.gif'>
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


    <section class="content">
        <div class="container-wide" id="report-content">
            <?php
            $current_date = date("Y-m-d");
            $data["from"] = $current_date . " " . DAY_CLOSURE_START;
            $data["to"] = $current_date . " " . DAY_CLOSURE_END;
            $data["type"] = "";
            $data["interface"] = "";
            $data["task"] = 15;
            echo parcxGreetingScreen($data);
            ?>                           
        </div>
    </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>

    var id;
    var date_range_message = "choose date range";
    from = "<?= $current_date . " " . DAY_CLOSURE_START ?>";
    to = "<?= $current_date . " " . DAY_CLOSURE_END ?>";

    $(function ()
    {
        $('#interface').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "All Interfaces",
            nonSelectedText: "Select Interface",
            selectAllNumber: false,
            allSelectedText: "All Interfaces",
            });
    });
    function callReport()
    {
        var data = {};
        data["from"] = from;
        data["to"] = to;
        data["type"] = $("#interface_type").val();
        data["interface"] = $("#interface").val().toString();
        data["task"] = 15;
        var jsondata = JSON.stringify(data);

        $.post("ajax/greeting_screen.php", jsondata, function (data)
        {
            loadReport(data);

        });
    }

    $('#view-report-button').click(function (event)
    {
        if (!daterange)
            alert(date_range_message);
        else
            callReport();
    });
   

    $('#export_excel_report').click(function (event) {
        export_to_excel("#report-content", "ADNOC_Interface Report")
    }); // end click event function
</script>
