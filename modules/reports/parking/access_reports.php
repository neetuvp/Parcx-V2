<?php
$page_title = "Application Home";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 17;
$json = parcxReport($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["access_movements"] ?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade text-dark" id="addToWhiteList" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whitelist_title"><?= $json["add_to_whitelist"] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4">
                <form class="block-data " data-status="add-whitelist"  id="form"> 
                    <div class="row">	
                        <div class="col form-group">
                            <label for="" id="customer_name_lbl"><?= $json["customer_name"] ?></label>
                            <input type="text" class="form-control" id="customer_name" placeholder=""  required name="qr">
                        </div>                    
                    </div>
                    <label id="plate_number"><?= $json["plate_number"] ?></label><h1 id="display_plate"></h1>				
                    <div class="row">				 
                        <div class="col form-group">
                            <label for="" id="country_lbl"><?= $json["country"] ?></label>
                            <!--<input type="text" class="form-control" id="country" style="text-transform:uppercase" placeholder="" required name="country">-->
                            <select id="country">	
                                <option value='' id="select_country"><?= $json["select_country"] ?></option>
                                <option value='UAE' id="uae"><?= $json["uae"] ?></option>
                                <option value='BAH' id="bahrain"><?= $json["bahrain"] ?></option>
                                <option value='KUW' id="kuwait"><?= $json["kuwait"] ?></option>
                                <option value='OMN' id="oman"><?= $json["oman"] ?></option>
                                <option value='QAT' id="qatar"><?= $json["qatar"] ?></option>
                                <option value='KSA' id="saudi"><?= $json["saudi"] ?></option>                        
                            </select>
                        </div>
                        <div class="col form-group" id="emirates-div">
                            <label for="" id="emirate_lbl"><?= $json["emirate"] ?></label>
                            <select id="emirates">									
                                <option value='AUH' id="abudhabi"><?= $json["abudhabi"] ?></option>
                                <option value='DXB' id="dubai"><?= $json["dubai"] ?></option>
                                <option value='AJ' id="ajman"><?= $json["ajman"] ?></option>
                                <option value='SHJ' id="sharjah"><?= $json["sharjah"] ?></option>
                                <option value='RAK' id="rasalkhaimah"><?= $json["rasalkhaimah"] ?></option>
                                <option value='FUJ' id="fujairah"><?= $json["fujairah"] ?></option>
                                <option value='UAQ' id="ummalquwwain"><?= $json["ummalquwwain"] ?></option>
                            </select>
                        </div> 
                        <div class="col form-group">
                            <label for="" id="prefix_lbl"><?= $json["prefix"] ?></label>
                            <input type="text" class="form-control" id="prefix" placeholder=""   style="text-transform:uppercase" >
                        </div> 
                        <div class="col form-group">
                            <label for="" id="plate_number_lbl"><?= $json["plate_number"] ?></label>
                            <input type="text" class="form-control" id="plate_number" placeholder=""  >
                        </div> 
                    </div>

                    <div class="row d-none">	
                        <div class="col form-group">
                            <label for="" id="qrcode"><?= $json["qrcode"] ?></label>
                            <input type="text" class="form-control" id="qr_code" placeholder="" value='<?php echo date("Ymdhis") ?>'  >
                        </div> 

                    </div>

                    <div class="row">	
                        <div class="col form-group">
                            <label for="" id="tag_lbl"><?= $json["tag"] ?></label>
                            <input type="text" class="form-control" id="tag" placeholder=""  required name="qr">
                        </div> 

                    </div>

                    <div class="row">
                        <div class="col form-group">
                            <label for="" id="activation_date"><?= $json["activation_date"] ?></label>
                            <input type="text" class="form-control" id="whitelist-active-date" autocomplete="off" placeholder="" required name="activation-date">
                        </div>
                        <div class="col form-group">
                            <label for="" id="expiry_date"><?= $json["expiry_date"] ?></label>
                            <input type="text" class="form-control" id="whitelist-expiry-date" autocomplete="off" placeholder=""required name="expiry-date">
                        </div> 
                    </div>


                    <div class="form-group">
                        <label for="" id="carpark"><?= $json["carpark"] ?></label>
                        <select name="multiselect" id="multiselect" class="form-control" multiple="multiple">
                            <?php echo parcxSettings(array("task" => "12")); ?>
                        </select>
                    </div>
                    <div class="row">	
                        <div class="col form-group">
                            <label for="" id="facility_name"><?= $json["facility_name"] ?></label>
                            <select id="facility">                  
                                <?php echo parcxSettings(array("task" => "5")); ?>
                            </select>
                        </div> 
                    </div>                           
                    <div class="row">	
                        <div class="col form-group">
                            <label for="" id="personal_message_1"><?= $json["personal_message_1"] ?></label>
                            <input type="text" class="form-control" id="message1" placeholder="">
                        </div>                    
                    </div>
                    <div class="row">	
                        <div class="col form-group">
                            <label for="" id="personal_message_2"><?= $json["personal_message_2"] ?></label>
                            <input type="text" class="form-control" id="message2" placeholder="">
                        </div>                    
                    </div>

                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="antipassback">
                        <label class="custom-control-label" for="antipassback" id="anti_passback_lbl"><?= $json["anti_passback"] ?></label>
                    </div>

                    <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" id="submitbtn" value="<?= $json["submit"] ?>" id="add-edit-button">
                </form>
            </div>      
        </div>
    </div>
</div>
<!-- end / Modal -->
<div class="content-wrapper">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1">

            <div class="flex-grow-1 row additional-menu-left">
                <!-- carparks multiselect -->
                <div class="col-md-3 mb-4">
                    <select class="form-control" id="multiselect_carpark" multiple="multiple">
                        <?php echo parcxSettings(array("task" => "12")); ?>
                    </select>
                </div>

                <!-- devices multiselect-->
                <div class="col-md-3">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
                        <?php echo parcxSettings(array("task" => "14", "type" => "1,2,3,6,7")); ?>
                    </select>
                </div>

                <!-- options -->
                <div class="col-md-2">
                    <select class="form-control" id="acces_option">
                        <option value="" id="select_status"><?= $json["select_status"] ?></option>
                        <option value="Access Allowed" id="access_allowed"><?= $json["access_allowed"] ?></option>
                        <option value="Access Denied" id="access_denied"><?= $json["access_denied"] ?></option>
                        <option value="TAG UNKNOWN" id="tag_unknown"><?= $json["tag_unknown"] ?></option>
                    </select>
                </div>

                <!-- plate -->
                <div class="col-md-2">
                    <input type="text" id="report_plate_number" class="form-control" placeholder="<?= $json["plate_ticket_tag"] ?>">
                </div>

                <?php include('../../../includes/additional-menu-report.php'); ?>           
                <!-- end / additional menu -->
                <input type="hidden" id="user_role" value='<?php echo $_SESSION["userRollName"]; ?>'>

                <section class="content">
                    <div class="container-wide" id="report-content">      

                        <?php
                        $current_date = date("Y-m-d");
                        $data["from"] = $current_date . " " . DAY_CLOSURE_START;
                        $data["to"] = $current_date . " " . DAY_CLOSURE_END;
                        $data["carpark"] = "";
                        $data["device"] = "";
                        $data["option"] = "";
                        $data["plate_number"] = "";
                        $data["wiegand"] = "1";
                        $data["user_role"] = $_SESSION["userRollName"];
                        $data["language"] = $_SESSION["language"];
                        $data["task"] = 4;
                        echo parcxReport($data);
                        ?>                               
                    </div>
                </section>
            </div>
            <script src="../../../dist/js/whitelistsettings.js"></script>
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
                     $('#multiselect_carpark').multiselect(
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
                    data["carpark"] = $("#multiselect_carpark").val().toString();
                    data["device"] = $("#deviceNumber").val().toString();
                    data["plate_number"] = $("#report_plate_number").val();
                    data["option"] = $("#acces_option").val();
                    data["user_role"] = $("#user_role").val();
                    data["wiegand"] = "1";
                    data["language"] = $("#language").val();
                    data["task"] = 4;
                    var temp = JSON.stringify(data);
                    
                    $.post("../../ajax/reports.php", temp)
                            .done(function (result) {
                                loadReport(result);

                            }, "json");
                    
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
                    data["page"] = 17;
                    var json = JSON.stringify(data);
                    $.post("../../ajax/reports.php", json, function (data)
                    {
                        //console.log(data);
                        var json = JSON.parse(data);
                        date_range_message = json.choose_datetime_range;
                        $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                        $("#pdf-report-header").html(json.access_movements);
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

                        $("#whitelist_title").html(json.add_to_whitelist);
                        $("#customer_name_lbl").html(json.customer_name);
                        $("#plate_number_lbl").html(json.plate_number);
                        $("#country_lbl").html(json.country);
                        $("#emirate_lbl").html(json.emirate);
                        $("#select_country").html(json.select_country);
                        $("#prefix_lbl").html(json.prefix);
                        $("#emirate_lbl").html(json.emirate);
                        $("#plate_number_lbl").html(json.plate_number);
                        $("#tag_lbl").html(json.tag);
                        $("#qrcode").html(json.qrcode);
                        $("#activation_date").html(json.activation_date);
                        $("#expiry_date").html(json.expiry_date);
                        $("#carpark").html(json.carpark);
                        $("#facility_name").html(json.facility_name);
                        $("#personal_message_1").html(json.personal_message_1);
                        $("#personal_message_2").html(json.personal_message_2);
                        $("#anti_passback_lbl").html(json.anti_passback);
                        $("#submitbtn").val(json.submit);


                        $("#uae").html(json.uae);
                        $("#bahrain").html(json.bahrain);
                        $("#qatar").html(json.qatar);
                        $("#oman").html(json.oman);
                        $("#saudi").html(json.saudi);
                        $("#kuwait").html(json.kuwait);


                        $("#abudhabi").html(json.abudhabi);
                        $("#dubai").html(json.dubai);
                        $("#sharjah").html(json.sharjah);
                        $("#ajman").html(json.ajman);
                        $("#rasalkhaimah").html(json.rasalkhaimah);
                        $("#fujairah").html(json.fujairah);
                        $("#ummalquwwain").html(json.ummalquwwain);

                        $("#access_allowed").html(json.access_allowed);
                        $("#access_denied").html(json.access_denied);
                        $("#tag_unknown").html(json.tag_unknown);
                        $("#select_status").html(json.select_status);
                        $("#report_plate_number").attr('placeholder', json.plate_ticket_tag);

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
                                
                                
                        $('#multiselect_carpark').multiselect('destroy');
                        $('#multiselect_carpark').multiselect(
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

                $('#export_excel_report').click(function (event) {
                    export_to_excel("#report-content", "PARCX_Access_Report")

                }); // end click event function


            </script>