<?php
$page_title = "Application";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 20;
$json = parcxReport($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["plates_captured"] ?></div>

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

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1">

            <div class="flex-grow-1 row additional-menu-left">

                <div class="col-md-2 mb-4">
                    <select class="form-control" id="deviceNumber" multiple="multiple">
                        <?php echo parcxSettings(array("task" => "14", "type" => "8")); ?>
                    </select>
                </div>
                

                <div class="col-md-2">                    
                    <select class="form-control" id="plate_type" placeholder="<?= $json["plate_type"] ?>" >
                        <option value='' id = 'type'><?= $json["plate_type"] ?></option>  
                        <option value='Police' id='police'><?= $json["police"] ?></option>
                        <option value='Transport' id='transport'><?= $json["transport"] ?></option>  
                        <option value='Commercial' id='commercial'><?= $json["commercial"] ?></option>
                        <option value='Private' id='private'><?= $json["private"] ?></option>
                        <option value='Learning' id='learning'><?= $json["learning"] ?></option>
                        <option value='CD_Diplomat' id='cddiplomat'><?= $json["cddiplomat"] ?></option>					   
                        <option value='CC_Diplomat' id='ccdiplomat'><?= $json["ccdiplomat"] ?></option>  
                        <option value='Protocol' id='protocol'><?= $json["protocol"] ?></option>
                        <option value='Government' id='government'><?= $json["government"] ?></option>  
                        <option value='Export' id='export_type'><?= $json["export"] ?></option>
                        <option value='Taxi' id='taxi'><?= $json["taxi"] ?></option>
                        <option value='Testing' id='testing'><?= $json["testing"] ?></option>
                        <option value='Transfer' id='transfer'><?= $json["transfer"] ?></option>					   
                        <option value='truck' id='truck'><?= $json["truck"] ?></option>
                        <option value='Bus' id='bus'><?= $json["bus"] ?></option>
                        <option value='PublicTransport' id='publictransport'><?= $json["publictransport"] ?></option>
                        <option value='PrivateTransport' id='privatetransport'><?= $json["privatetransport"] ?></option>                                    
                    </select>
                </div>
                
                <div class="col-md-2">                    
                    <select class="form-control" id="plate_country" placeholder="Plate Country" >
                        <option value='' id="select_country"><?= $json["select_country"] ?></option>  
                        <option value='UnitedArabEmirates' id='uae'><?= $json["uae"] ?></option> 
                        <option value='Kuwait' id='kuwait'><?= $json["kuwait"] ?></option> 
                        <option value='Saudi Arabia' id='saudi'><?= $json["saudi"] ?></option> 
                        <option value='Oman' id='oman'><?= $json["oman"] ?></option> 
                        <option value='Qatar' id='qatar'><?= $json["qatar"] ?></option> 
                        <option value='Bahrain' id='bahrain'><?= $json["bahrain"] ?></option> 

                    </select>
                </div>
                
                <div class="col-md-2" id="plate_area_div">                    
                    <select class="form-control" id="plate_area" placeholder="<?= $json["plate_area"] ?>" >
                        <option value='' id="area"><?= $json["plate_area"] ?></option>
                        <option value='AUH'>AUH</option>
                        <option value='DUB'>DUB</option>
                        <option value='SHJ'>SHJ</option>  
                        <option value='AJ'>AJ</option>
                        <option value='RAK'>RAK</option>
                        <option value='UAQ'>UAQ</option>
                        <option value='FUJ'>FUJ</option>                                    
                    </select>
                </div>

                
                
                <div class="col-md-2">                    
                    <input type = "text" class="form-control" id="plate" placeholder="<?= $json["plate_number"] ?>" >
                    <?php echo "<input type = 'hidden' class='form-control' id='imageurl' value=" . ImageURL . " >" ?>
                </div>

                <?php include('../../../includes/additional-menu-report.php'); ?>           
                <!-- end / additional menu -->

                <section class="content">
                    <div class="container-wide" id="report-content">                       
                        <?php
                        $data["task"] = 12;
                        $data["language"] = $_SESSION["language"];
                        echo parcxReport($data);
                        ?>                          
                    </div>
                </div>
            </section>
        </div>

        <?php include('../../../includes/footer.php'); ?>

        <script>
            $('#plate_country').on('input', function ()
                {
                if ($("#plate_country").val() === "UnitedArabEmirates" ||$("#plate_country").val() === "")
                    $("#plate_area_div").show();
                else
                    $("#plate_area_div").hide();
    
                });

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
                var device = $("#deviceNumber").val().toString();                
                var plate = $('#plate').val();
                var type = $('#plate_type').val();
                var area = $('#plate_area').val();
                var country = $('#plate_country').val();
                var language = $("#language").val();
                var imageurl = $("#imageurl").val();

                var data = {
                    device: device,
                    to: to,
                    from: from,
                    plate: plate,
                    type: type,
                    area: area,
                    country: country,
                    language: language,
                    imageurl: imageurl,
                    task: 12
                };
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
                    $("#type").html(json.plate_type);
                    $("#police").html(json.police);
                    $("#transport").html(json.transport);
                    $("#commercial").html(json.commercial);
                    $("#private").html(json.private);
                    $("#learning").html(json.learning);
                    $("#cddiplomat").html(json.cddiplomat);
                    $("#ccdiplomat").html(json.ccdiplomat);
                    $("#protocol").html(json.protocol);
                    $("#government").html(json.government);
                    $("#export_type").html(json.export);
                    $("#taxi").html(json.taxi);
                    $("#testing").html(json.testing);
                    $("#transfer").html(json.transfer);
                    $("#truck").html(json.truck);
                    $("#bus").html(json.bus);
                    $("#taxi").html(json.taxi);
                    $("#publictransport").html(json.publictransport);
                    $("#privatetransport").html(json.privatetransport);
                    $("#uae").html(json.uae);
                    $("#bahrain").html(json.bahrain);
                    $("#qatar").html(json.qatar);
                    $("#oman").html(json.oman);
                    $("#saudi").html(json.saudi);
                    $("#kuwait").html(json.kuwait);
                    $("#select_country").html(json.select_country);
                    $("#area").html(json.plate_area);


                    $("#plate").attr('placeholder', json.plate_number);
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
                $("#image-content-modal").html("<img src='" + id + "' width='100%' height='700'>");
            });
            
            $('#export_excel_report').click(function () 
                {                
                export_to_excel("#report-content", "PARCX_Plate_Captured_Report")
                });
        </script>