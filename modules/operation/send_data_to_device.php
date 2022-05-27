<?php
include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>

</ul>
<div class="header text-dark" id="pdf-report-header">Send Data To Device</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">
            <div class="row">
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="">Device</label>
                                <select id="device_number">                             
                                    <?php echo parcxV2Settings(array("task" => "14", "type" => "1,2,3")); ?>
                                </select>
                            </div> 



                            <label>Plate Number</label><h1 id="display_plate"></h1>				
                            <div class="row">				 
                                <div class="col form-group">
                                    <label for="">Country</label>

                                    <select id="country">	
                                        <option value=''>Select country</option>
                                        <option value='UAE'>UAE</option>
                                        <option value='BAH'>Bahrain</option>
                                        <option value='KUW'>Kuwait</option>
                                        <option value='OMN'>Oman</option>
                                        <option value='QAT'>Qatar</option>
                                        <option value='KSA'>Saudi Arabia</option>                        
                                    </select>
                                </div>
                                <div class="col form-group" id="emirates-div">
                                    <label for="">Emirates</label>
                                    <select id="emirates">									
                                        <option value='AUH'>Abu Dhabi</option>
                                        <option value='DXB'>Dubai</option>
                                        <option value='AJ'>Ajman</option>
                                        <option value='SHJ'>Sharjah</option>
                                        <option value='RAK'>Ras al Khaimah</option>
                                        <option value='FUJ'>Fujairah</option>
                                        <option value='UAQ'>Umm al-Quwain</option>
                                    </select>
                                </div> 
                            </div>
                            <div class="row">
                                <div class="col form-group">
                                    <label for="">Prefix</label>
                                    <input type="text" class="form-control" id="prefix" placeholder=""   style="text-transform:uppercase">
                                </div> 
                                <div class="col form-group">
                                    <label for="">Plate number</label>
                                    <input type="text" class="form-control" id="plate_number" placeholder=""  >
                                </div> 
                            </div>

                            <button type="button" class="btn btn-info btn-block" data-option="1">Plate Capture</button> 
                        </div>
                    </div>
                </div>
                <div class="col-8 card p-3">

                    <div class="row  justify-content-between align-items-center">
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S01">Barrier Open</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S02">Barrier Close</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S03">Restart</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="DUS">Update settings</button>
                        </div> 


                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S04">Change Mode to Free Passage</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S05">Change Mode to Lane Closed</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="S06">Normal Ticket operation mode</button>
                        </div> 

                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="W02">Wiegand Disabled</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="W03">Wiegand Disabled - Lane Occupied</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="TRT">Retract Ticket</button>
                        </div>
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary" data-option="TPR">Test Print</button>
                        </div> 


                        <div class="form-group col">
                            <button type="button" class="btn btn-success" data-option="S10">Activate Presence Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-danger"data-option="S11">Deactivate Presence Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-success" data-option="S12">Activate Safety Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-danger" data-option="S13">Deactivate Safety Loop</button>
                        </div> 


                    </div>
                </div>
            </div>               
        </div>
    </section>
</div>
<?php
include('../../includes/footer.php');
?>

<script>

    $('#country,#emirates,#prefix,#plate_number').on('input', function ()
    {
        var plate;
        if ($("#country").val().toUpperCase() === "UAE")
            plate = $("#emirates").val() + " " + $("#prefix").val().toUpperCase() + " " + $("#plate_number").val();
        else
            plate = $("#country").val() + " " + $("#prefix").val().toUpperCase() + " " + $("#plate_number").val();
        $("#display_plate").html(plate);
    });

    $('#country').on('input', function ()
    {
        if ($("#country").val().toUpperCase() === "UAE")
            $("#emirates-div").show();
        else
            $("#emirates-div").hide();
        if ($("#country").val() == "")
        {
            $("#prefix").val("");
            $("#plate_number").val("");
            $("#display_plate").html("");
        }
    });

    $(".btn").click(function () {
        var data = {};
        data["task"] = "1";
        data["device_number"] = $("#device_number").val();
        data["plate_number"] = $("#display_plate").html();
        data["option"] = $(this).data("option");
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/send_to_device.php", jsondata, function (result) {
            alert(result);
            $("#plate_number").val("");
        });
    });
</script>

