<?php
$page_title = "Application Home";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');


$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 7;
$json = parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["payment_transaction_exception"] ?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>


<div class="content-wrapper">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">

                <!-- carparks -->
                <div class="col-md-2">
                    <select class="form-control" id="multiselect" multiple="multiple">
<?php echo parcxV2Settings(array("task" => "12")); ?>
                    </select>
                </div>


                <!-- payment devices multiselect-->
                <div class="col-md-2">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
<?php echo parcxV2Settings(array("task" => "14", "type" => "3,4,5")); ?>
                    </select>
                </div>



                <!-- options -->
                <div class="col-md-2">
                    <select class="form-control" id="exception_option">
                        <option value="0" id="exception0"><?= $json["select_exception"] ?></option>
                        <option value="Blacklisted" id="exception1"><?= $json["blacklisted"] ?></option>
                        <option value="Invalid Carpark" id="exception2"><?= $json["invalid_carpark"] ?></option>
                        <option value="Already Paid Chipcoin" id="exception3"><?= $json["already_paid_chipcoin"] ?></option>
                        <option value="Invalid Chipcoin" id="exception4"><?= $json["invalid_chipcoin"] ?></option>
                        <option value="Cancelled Transaction" id="exception5"><?= $json["cancelled_transaction"] ?></option>
                    </select>
                </div>

<?php include('../../../includes/additional-menu-report.php'); ?>       


                <section class="content">
                    <div class="container-wide" id="report-content">
                      
                    <?php
                    $current_date = date("Y-m-d");
                    $data["from"] = $current_date . " " . DAY_CLOSURE_START;
                    $data["to"] = $current_date . " " . DAY_CLOSURE_END;
                    $data["carpark"] = "";
                    $data["device"] = "";
                    $data["exception_option"] = 0;
                    $data["task"] = 20;
                    $data["language"] = $_SESSION["language"];
                    echo parcxV2Report($data);
                    ?>                           

                    </div>
                </section>
            </div>

<?php include('../../../includes/footer.php'); ?>

            <script>
            //$('#view-report-button').click(function (event) 
                var id;
                var date_range_message = "choose date range";
                from = "<?= $current_date . " " . DAY_CLOSURE_START ?>";
                to = "<?= $current_date . " " . DAY_CLOSURE_END ?>";

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

                    $('#multiselect').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: "<?= $json["all_carparks"] ?>",
                                nonSelectedText: "<?= $json["select_carparks"] ?>",
                                selectAllNumber: false,
                                allSelectedText: "<?= $json["all_carparks"] ?>",
                            });
                });
                function callReport()
                {
                    var data = {};
                    data["from"] = from;
                    data["to"] = to;
                    data["carpark"] = $("#multiselect").val().toString();
                    data["device"] = $("#deviceNumber").val().toString();
                    data["exception_option"] = $("#exception_option").val();
                    data["language"] = $("#language").val();
                    data["task"] = 20;
                    var jsondata = JSON.stringify(data);
                    //console.log(jsondata);
                    $.post("../../ajax/reports.php", jsondata, function (data)
                    {
                        loadReport(data);
                        ;
                    });
                }
            

                $('#view-report-button').click(function (event)
                {
                    if (!daterange)
                        alert(date_range_message);
                    else
                        callReport();
                });


                $('#export_excel_report').click(function (event)
                {
                    export_to_excel("#report-content", "PMS_Payment_transaction")
                });



                function loadReportLabels()
                {
                    var data = {};
                    data["task"] = 29;
                    data["language"] = $("#language").val();
                    data["page"] = 7;
                    var json = JSON.stringify(data);
                    $.post("../../ajax/reports.php", json, function (data)
                    {
                        var json = JSON.parse(data);
                        date_range_message = json.choose_datetime_range;
                        $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                        $("#pdf-report-header").html(json.payment_transaction_exception);
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

                        $("#exception0").html(json.select_exception);
                        $("#exception1").html(json.blacklisted);
                        $("#exception2").html(json.invalid_carpark);
                        $("#exception3").html(json.already_paid_chipcoin);
                        $("#exception4").html(json.invalid_chipcoin);
                        $("#exception5").html(json.cancelled_transaction);


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

                        $('#multiselect').multiselect('destroy');
                        $('#multiselect').multiselect(
                                {
                                    buttonWidth: '100%',
                                    includeSelectAllOption: true,
                                    selectAllText: json.all_carparks,
                                    nonSelectedText: json.select_carparks,
                                    selectAllNumber: false,
                                    allSelectedText: json.all_carparks
                                });


                    });
                }

                $("#language").change(function ()
                {
                    update_session();
                    loadReportLabels();
                    callReport();
                });
            </script>
