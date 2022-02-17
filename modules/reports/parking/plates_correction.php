<?php
$page_title = "Application";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 24;
$json = parcxReport($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["anpr_plate_correction"] ?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade" id="image-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= $json["anpr_image"] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="image-content-modal">
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->


<div class="content-wrapper">
    <?php echo "<input type = 'hidden' class='form-control' id='imageurl' value=" . ImageURL . " >" ?>
    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 ">

            <div class="flex-grow-1 row additional-menu-left">

                <div class="col-md-2">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
                        <?php echo parcxSettings(array("task" => "14", "type" => "8")); ?>
                    </select>
                </div>

                <?php include('../../../includes/additional-menu-report.php'); ?>           
                <!-- end / additional menu -->

                <section class="content">
                    <div class="container-wide" id="report-content">                       
                        <?php
                        $data["task"] = 13;
                        $current_date = date("Y-m-d");
                        $data["from"] = $current_date . " " . DAY_CLOSURE_START;
                        $data["to"] = $current_date . " " . DAY_CLOSURE_END;
                        $data["language"] = $_SESSION["language"];
                        echo parcxReport($data);
                        ?>                                               
                    </div>
                </section>
            </div>

            <?php include('../../../includes/footer.php'); ?>

            <script>
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
                function callReport()
                {
                    var device = $("#device").val();
                    var daterange = $("#reservationtime").val();
                    var from = daterange.substring(0, 19);
                    var to = daterange.substring(22, 41);
                    var language = $("#language").val();
                    var imageurl = $("#imageurl").val();

                    var data = {
                        device: device,
                        to: to,
                        from: from,
                        language: language,
                        imageurl: imageurl,
                        task: 13
                    };
                    var temp = JSON.stringify(data);
                    $.post("../../ajax/reports.php", temp)
                            .done(function (result) {
                                loadReport(result);
                            }, "json");

                    event.preventDefault();
                }


                $('#view-report-button').click(function ()
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
                    data["page"] = 20;
                    var json = JSON.stringify(data);
                    $.post("../../ajax/reports.php", json, function (data)
                    {
                        var json = JSON.parse(data);
                        date_range_message = json.choose_datetime_range;
                        $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                        $("#pdf-report-header").html(json.plates_captured);
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
                    callReport();
                });


                $('body').on('click', "[data-target='#image-modal']", function () {
                    var id = $(this).data('value');
                    var camera_no = $('#camera_no' + id).val();
                    var plate_image = $('#plate_image' + id).val();
                    var date = $('#date' + id).val();
                    var imageurl = $("#imageurl").val();
                    var data = {
                        camera_no: camera_no,
                        plate_image: plate_image,
                        date: date,
                        imageurl: imageurl,
                        task: 14
                    };
                    var json = JSON.stringify(data)
                    $.post("../../ajax/reports.php", json)
                            .done(function (result) {
                                $("#image-content-modal").html(result);
                            }, "json");
                });

            $('#export_excel_report').click(function () 
                {                
                export_to_excel("#report-content", "PARCX_Plate_Correction_Report")
                });
            </script>