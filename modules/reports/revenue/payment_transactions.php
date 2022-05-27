<?php
include('../../../includes/header.php');
$access = checkPageAccess("payment_transactions");
include('../../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 2;
$json = parcxV2Report($data);
?>
</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["payment_transactions"] ?></div>
<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>

<!-- Modal -->
<div class="modal fade text-dark" id="detailModal"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_payment_heading">
                    <?= $json["detailed_payment"] ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="payment-detail-content" class="p-3">              
                </div>
            </div>
        </div>
    </div>
</div>

<!-- pdf receipt modal -->
<div class="modal fade" id="pdfReceiptModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <?= $json["receipt_details"] ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-title">
                    <h5  id="tax-invoice"><?= $json["tax_invoice"] ?></h5>   
                    <small id="duplicate"><?= $json["duplicate"] ?></small>
                </div>
                
                <div id="pdf-receipt">         
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="pdf-modal-close"><i class="fas fa-window-close"></i></button>
                <button type="button" class="btn btn-primary" id="print-pdf-receipt"><i class="fas fa-print"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1">

            <div class="flex-grow-1 row additional-menu-left mr-2">

                <!-- carparks -->
                <div class="col-md-2 mb-4">
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

                <!-- payment type multiple select -->
                <div class="col-md-2">
                    <select class="form-control" id="paymentType" multiple="multiple">
                        <!-- <option value=0>All Payment Types</option> -->
                        <option value="Cash" id="paymentTypeCash"><?= $json["cash"] ?></option>
                        <option value="Credit Card" id="paymentTypeCreditCard"><?= $json["credit_card"] ?></option>             
                    </select>
                </div>

                <!-- payment category multiple select -->
                <div class="col-md-2">
                    <select class="form-control" id="paymentCategory" multiple="multiple">
                        <!-- <option value=0>All Payment Types</option> -->
                        <option value=1 id="paymentCategory1"><?= $json["parking_fee"] ?></option>
                        <option value=2 id="paymentCategory2"><?= $json["lost_ticket"] ?></option>
                        <option value=3 id="paymentCategory3"><?= $json["discount"] ?></option>
                        <option value=4 id="paymentCategory4"><?= $json["grace_period"] ?></option>
                        <option value=5 id="paymentCategory5"><?= $json["product_sales"] ?></option>
                    </select>
                </div>

                <!-- discounts multiple -->
                <div class="col-md-2">
                    <select class="form-control" id="discounts" multiple="multiple">
                        <?php echo parcxV2Settings(array("task" => "35")); ?>
                    </select>
                </div>

                <!-- validation multiple -->
                <div class="col-md-2">
                    <select class="form-control" id="validations" multiple="multiple">
                        <?php echo parcxV2Settings(array("task" => "21")); ?>
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
                        $data["payment-category"] = [];
                        $data["payment-type"] = "";
                        $data["discount"] = "";
                        $data["showvoid"] = 0;
                        $data["task"] = 8;
                        $data["language"] = $_SESSION["language"];
                        echo parcxV2Report($data);
                        ?>

                    </div>
                </section>
            </div>

            <?php include('../../../includes/footer.php'); ?>

            <script>
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
                    $('#paymentCategory').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: "<?= $json["all_category"] ?>",
                                nonSelectedText: "<?= $json["select_category"] ?>",
                                selectAllNumber: false,
                                allSelectedText: "<?= $json["all_category"] ?>"
                            });
                    $('#paymentType').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: "<?= $json["all_payment"] ?>",
                                nonSelectedText: "<?= $json["select_payment"] ?>",
                                selectAllNumber: false,
                                allSelectedText: "<?= $json["all_payment"] ?>"
                            });
                    $('#discounts').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: "<?= $json["all_discount"] ?>",
                                nonSelectedText: "<?= $json["select_discount"] ?>",
                                selectAllNumber: false,
                                allSelectedText: "<?= $json["all_discount"] ?>"
                            });

                    $('#validations').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: "<?= $json["all_validation"] ?>",
                                nonSelectedText: "<?= $json["select_validation"] ?>",
                                selectAllNumber: false,
                                allSelectedText: "<?= $json["all_validation"] ?>"
                            });
                });

                $('body').on('click', '#payment_detail', function ()
                {
                    var data = {};
                    data["payment_id"] = $(this).attr('payment_id');
                    data["task"] = 24;
                    data["language"] = $("#language").val();
                    var jsondata = JSON.stringify(data);
                    //console.log(jsondata);
                    $.post("../../ajax/reports.php", jsondata, function (data)
                    {
                        $("#payment-detail-content").html(data);
                        $('#detailModal').modal('show');
                    });
                }); // end click event function 

                function callReport()
                {
                    var data = {};
                    data["from"] = from;
                    data["to"] = to;
                    data["carpark"] = $("#multiselect").val().toString();
                    data["device"] = $("#deviceNumber").val().toString();
                    data["payment-category"] = $("#paymentCategory").val();
                    data["payment-type"] = $("#paymentType").val().toString();
                    data["discount"] = $("#discounts").val().toString();
                    data["validation"] = $("#validations").val().toString();
                    if ($('#showvoid').is(':checked'))
                        data["showvoid"] = 1;
                    else
                        data["showvoid"] = 0;
                    data["task"] = 8;
                    data["language"] = $("#language").val();
                    var jsondata = JSON.stringify(data);
                    console.log(jsondata);
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


                $('#export_excel_report').click(function (event)
                {
                    export_to_excel("#report-content", "PARCX_Payment_transaction")
                });


                $('body').on('click', '.btn-show-pdf-reciept', function ()
                {
                    id = $(this).attr("data-id");
                    var data = {};
                    data["payment_id"] = id;
                    data["task"] = 25;
                    data["language"] = $("#language").val();
                    var jsondata = JSON.stringify(data);
                    $.post("../../ajax/reports.php", jsondata, function (data)
                    {
                        $("#pdf-receipt").html(data);
                        $('#pdfReceiptModal').modal('show');
                    });
                });

                function loadReportLabels()
                {
                    var data = {};
                    data["task"] = 29;
                    data["language"] = $("#language").val();
                    data["page"] = 2;
                    var json = JSON.stringify(data);
                    $.post("../../ajax/reports.php", json, function (data)
                    {
                        var json = JSON.parse(data);
                        date_range_message = json.choose_datetime_range;
                        $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                        $("#pdf-report-header").html(json.payment_transactions);
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

                        $("#modal_payment_heading").html(json.detailed_payment);
                        $("#exampleModalLabel").html(json.receipt_details);                                                
                        $("#tax-invoice").html(json.tax_invoice);
                        $("#duplicate").html(json.duplicate);
                        $("#paymentTypeCash").html(json.cash);
                        $("#paymentTypeCreditCard").html(json.credit_card);
                        $("#paymentCategory1").html(json.parking_fee);
                        $("#paymentCategory2").html(json.lost_ticket);
                        $("#paymentCategory3").html(json.discount);
                        $("#paymentCategory4").html(json.grace_period);
                        $("#paymentCategory5").html(json.product_sales);


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

                        $('#paymentCategory').multiselect('destroy');
                        $('#paymentCategory').multiselect(
                                {
                                    buttonWidth: '100%',
                                    includeSelectAllOption: true,
                                    selectAllText: json.all_category,
                                    nonSelectedText: json.select_category,
                                    selectAllNumber: false,
                                    allSelectedText: json.all_category
                                });

                        $('#paymentType').multiselect('destroy');
                        $('#paymentType').multiselect(
                                {
                                    buttonWidth: '100%',
                                    includeSelectAllOption: true,
                                    selectAllText: json.all_payment,
                                    nonSelectedText: json.select_payment,
                                    selectAllNumber: false,
                                    allSelectedText: json.all_payment
                                });

                        $('#discounts').multiselect('destroy');
                        $('#discounts').multiselect(
                                {
                                    buttonWidth: '100%',
                                    includeSelectAllOption: true,
                                    selectAllText: json.all_discount,
                                    nonSelectedText: json.select_discount,
                                    selectAllNumber: false,
                                    allSelectedText: json.all_discount
                                });


                        $('#validations').multiselect('destroy');
                        $('#validations').multiselect(
                                {
                                    buttonWidth: '100%',
                                    includeSelectAllOption: true,
                                    selectAllText: json.all_validation,
                                    nonSelectedText: json.select_validation,
                                    selectAllNumber: false,
                                    allSelectedText: json.all_validation
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
