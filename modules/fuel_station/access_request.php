<?php
$page_title = "Application";

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
$parking=new parking();
?>
<style type="text/css">
table 
    {
    table-layout:fixed;
    width:100%;
    }
</style>
<div class="content-wrapper">
    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1">
            <div class="flex-grow-1 row additional-menu-left"> 
                <div class="col-md-2">
                    <select class="form-control" id="transaction_type" multiple="multiple">
                        <?php
                        echo $parking->get_interfaces();
                        ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select class="form-control" id="response_type" >
                        <option value="">Response type</option>
                        <option value="success">Vehicle Found-Valid</option>
                        <option value="failed">Vehicle Found-InValid</option>                        
                        
                    </select>
                </div>
                
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
                <div class="col-1">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button">View Report</button>
                </div>          
            </div>
            
            <div class="ml-2 additional-menu-right">
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
            
         $('#transaction_type').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "Select all type",
            nonSelectedText: "Select transaction type",
            selectAllNumber: false,
            allSelectedText: "Select transaction type"
            });
        });
    function callReport()
    {
        var data = {};
        data["task"] = "4";
        data["from"] = from;
        data["to"] = to;
        data["response_type"]=$("#response_type").val();
        data["plate_number"] = $("#plate_number").val();
        data["pump_id"] = $("#pump_id").val().toString();
        data["transaction_type"] = $("#transaction_type").val().toString();
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
    
    
    $('#export_excel_report').click(function (event) {
        export_to_excel("#report-content", "ADNOC_Transaction_Report")

    });



</script>