<?php
$page_title = "Application Home";

//# Import application layout.
include('../../../includes/header.php');
$access = checkPageAccess("cash_levels");
include('../../../includes/navbar-start.php');


$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 8;
$json = parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["cash_levels"] ?></div>
<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');

?>


<div class="content-wrapper">
    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">


                <!-- payment devices multiselect-->
                <div class="col-md-2">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
<?php echo parcxV2Settings(array("task" => "14", "type" => "4")); ?>
                    </select>
                </div>



                <!-- search -->
                <div class="col-md-2">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button" onclick="cash_levels()"><?= $json["view_report"] ?></button>
                </div>

                <!-- loader -->
                <div class='col-1' id='loader'>
                    <img src='../../../dist/img/loading.gif'>
                </div>

            </div>
            <div class="additional-menu-right">
            <div id="action-buttons">
                <div class="btn-group">
                    <button type="button" class="btn btn-warning" id="export"><?=$json["export"]?></button>
                    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">                       
                    </button>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="#" id="export_excel_report"><?=$json["export_to_excel"]?></a>
                        <a class="dropdown-item" href="#" id="export_pdf_report"><?=$json["export_to_pdf"]?></a>
                    </div>
                </div>
            </div>
        </div>
        </div></div>


    <section class="content">
        <div class="container-wide"> 
            <div class="card">
                <div class="card-body" id="report-content">
<?php
$data["device"] = "";
$data["language"] = $_SESSION["language"];
$data["task"] = 18;
echo parcxV2Report($data);
?>

                </div>
            </div>
        </div>
    </section>
</div>

<?php include('../../../includes/footer.php'); ?>

<script>
    // $('#view-report-button').click(function (event) { 
    var load_report = 0;
    $("#action-buttons").css("visibility", "visible");  

    $(function ()
    {
        $('#deviceNumber').multiselect(
                {
                    buttonWidth: '100%',
                    includeSelectAllOption: true,
                    selectAllText: "<?= $json["all_devices"] ?>",
                    nonSelectedText: "<?= $json["select_devices"] ?>",
                    selectAllNumber: false,
                    allSelectedText: "<?= $json["all_devices"] ?>"
                });
    });


    function cash_levels() {
        
        var device = $("#deviceNumber").val().toString();
        language = $("#language").val();
        var data = {
            device: device,
            task: 18,
            language: language
        };
        var temp = JSON.stringify(data);
        //alert(temp);
        $.post("../../ajax/reports.php", temp)
                .done(function (result) {
                loadReport(result); 
                }, "json");

       
    }
    //}); 


    $('#export_excel_report').click(function (event)
    {
        export_to_excel("#report-content", "PARCX_APM_Cashlevels")
    });



    function loadReportLabels()
    {
        var data = {};
        data["task"] = 29;
        data["language"] = $("#language").val();
        data["page"] = 8;
        var json = JSON.stringify(data);
        $.post("../../ajax/reports.php", json, function (data)
        {
            var json = JSON.parse(data);
            date_range_message = json.choose_datetime_range;
            $("#reservationtime").attr('placeholder', json.choose_datetime_range);
            $("#pdf-report-header").html(json.cash_levels);
            $("#view-report-button").html(json.view_report);
            $("#export").html(json.export);
            $("#export_excel_report").html(json.export_to_excel);
            $("#export_pdf_report").html(json.export_to_pdf);
            $("#logout").html(json.logout);
            search_label = json.search;
            entries_label = json.entries_label;
            info_label = json.info_label;
            previous_label = json.previous;
            next_label = json.next;




            $('#deviceNumber').multiselect('destroy');
            $('#deviceNumber').multiselect(
                    {
                        buttonWidth: '100%',
                        includeSelectAllOption: true,
                        selectAllText: json.all_devices,
                        nonSelectedText: json.select_devices,
                        selectAllNumber: false,
                        allSelectedText: json.all_devices
                    });


        });
    }

    $("#language").change(function ()
    {
        update_session();
        loadReportLabels();
        cash_levels();
    });

</script>


