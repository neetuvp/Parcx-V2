<?php
$page_title = "Application";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');
$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 18;
$json = parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["manual_movement"] ?></div>

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

                <!-- devices multiselect-->
                <div class="col-md-2">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
                        <?php echo parcxV2Settings(array("task" => "14", "type" => "1,2,3")); ?>
                    </select>
                </div>

                <!-- operation methods -->
                <div class="col-md-2">
                    <select class="form-control" id="operation">
                        <option value="0" id="all_operation_methods" ><?= $json["all_operation_methods"] ?></option>                        
                        <option value="1" id="manual_open_from_cashier"><?= $json["manual_open_from_cashier"] ?></option>
                        <option value="2" id="barrier_open_from_server"><?= $json["barrier_open_from_server"] ?></option>
                        <option value="3" id="remote_pushbutton_open"><?= $json["remote_pushbutton_open"] ?></option>
                        <option value="4" id="barrier_close"><?= $json["barrier_close"] ?></option>
                        <option value="5" id="free_passage_mode"><?= $json["free_passage_mode"] ?></option>
                        <option value="6" id="lane_closed_mode"><?= $json["lane_closed_mode"] ?></option>
                        <option value="7" id="standard_operation_mode"><?= $json["standard_operation_mode"] ?></option>
                    </select>
                </div>

                <?php include('../../../includes/additional-menu-report.php'); ?>       
                <!-- end / additional menu -->


                <section class="content">
                    <div class="container-wide" id="report-content">

                        <?php
                        $current_date = date("Y-m-d");
                        $data["from"] = $current_date . " " . DAY_CLOSURE_START;
                        $data["to"] = $current_date . " " . DAY_CLOSURE_END;
                        $data["carpark"] = "";
                        $data["operation"] = "";
                        $data["language"] = $_SESSION["language"];
                        $data["task"] = 11;
                        echo parcxV2Report($data);
                        ?>

                    </div>
                </section>
            </div>


            <?php include('../../../includes/footer.php'); ?>

            <script>
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
                    var carpark = $("#multiselect").val().toString();
                    var operation = $("#operation").val();
                    var language = $("#language").val();

                    var data = {
                        carpark: carpark,
                        device: $("#deviceNumber").val().toString(),
                        operation: operation,
                        to: to,
                        from: from,
                        language: language,
                        task: 11
                    };
                    var jsondata = JSON.stringify(data);

                    $.post("../../ajax/reports.php", jsondata, function (data)
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

                function loadReportLabels()
                {
                    var data = {};
                    data["task"] = 29;
                    data["language"] = $("#language").val();
                    data["page"] = 18;
                    var json = JSON.stringify(data);
                    $.post("../../ajax/reports.php", json, function (data)
                    {
                        var json = JSON.parse(data);
                        date_range_message = json.choose_datetime_range;
                        $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                        $("#pdf-report-header").html(json.manual_movement);
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
                        $("#all_operation_methods").html(json.all_operation_methods);
                        $("#manual_open_from_cashier").html(json.manual_open_from_cashier);
                        $("#remote_pushbutton_open").html(json.remote_pushbutton_open);
                        $("#barrier_open_from_server").html(json.barrier_open_from_server);
                        $("#barrier_close").html(json.barrier_close);
                        $("#remote_pushbutton_open").html(json.remote_pushbutton_open);
                        $("#free_passage_mode").html(json.free_passage_mode);
                        $("#lane_closed_mode").html(json.lane_closed_mode);
                        $("#standard_operation_mode").html(json.standard_operation_mode);

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
                    callReport();
                });



                $('#export_excel_report').click(function (event) {

                    export_to_excel("#report-content", "PARCX_Manual_Operation_Report")

                }); // end click event function


            </script>
