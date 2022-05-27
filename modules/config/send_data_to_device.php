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
                             <div class="row ">				 
                                <div class="col form-group">                                    
                                    <label for="">Plate Numbers</label>
                                    <select id="select_plate">	
                                        <option value=''>Select plate</option>
                                                             
                                    </select>
                                </div>
                                 <div class="col form-group">
                                    <label for="">confidence rate</label>
                                    <input type="number" class="form-control" id="confidence_rate" value="98" min="0" max="100" >
                                </div> 
                             </div>
                           
                            <button type="button" class="btn btn-info mb-3 btn-option" data-option="1" id="plate_capture">Plate Capture</button> 
                                                        
                            <div class="row mt-2">				 
                                <div class="col form-group">                                    
                                    <input type="number" class="form-control" value="1" placeholder="Iteration" id="iteration">
                                </div>
                                 <div class="col form-group">
                                    <button type="button" class="btn btn-success btn-iteration">START</button>                                                                         
                                </div> 
                             </div>
                            
                        </div>
                    </div>
                </div>
                <div class="col-8 card p-3">

                    <div class="row  justify-content-between align-items-center">
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="S01">Barrier Open</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="S02">Barrier Close</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option " data-option="S03">Restart</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="DUS">Update settings</button>
                        </div> 


                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="S04">Change Mode to Free Passage</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="S05">Change Mode to Lane Closed</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="S06">Normal Ticket operation mode</button>
                        </div> 

                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="W02">Wiegand Disabled</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="W03">Wiegand Disabled - Lane Occupied</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="TRT">Retract Ticket</button>
                        </div>
                        <div class="form-group col">
                            <button type="button" class="btn btn-outline-primary btn-option" data-option="TPR">Test Print</button>
                        </div> 


                        <div class="form-group col">
                            <button type="button" class="btn btn-success btn-option" data-option="S10" id="activate_presence">Activate Presence Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-danger btn-option"data-option="S11" id="deactivate_presence">Deactivate Presence Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-success btn-option" data-option="S12" id="activate_safety">Activate Safety Loop</button>
                        </div> 
                        <div class="form-group col">
                            <button type="button" class="btn btn-danger btn-option" data-option="S13" id="deactivate_safety">Deactivate Safety Loop</button>
                        </div> 


                    </div>
                </div>
            </div>               
            <div class="card card-body mt-3">
                <h5>Response</h5>
                <div id="result"></div>
            </div>
        </div>
    </section>
</div>
<?php
include('../../includes/footer.php');
?>

<script>


    function choosePlate()
        {
        var $options = $("#select_plate").find('option');
        var index = Math.floor(Math.random() * $options.length);
        $options.eq(index).prop('selected', true);            
        }
        
    function get_plates()
        {
        var data = {};
        data["task"] = "2";       
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/send_to_device.php", jsondata, function (result) {            
            $("#select_plate").html(result);            
        });   
        }
    get_plates();

    $(".btn-option").click(function () {
        console.log($(this).html());
        var data = {};
        data["task"] = "1";
        data["device_number"] = $("#device_number").val();        
        data["plate_number"] = $("#select_plate").val();
        data["confidence_rate"]=$("#confidence_rate").val();
        data["option"] = $(this).data("option");
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/send_to_device.php", jsondata, function (result) {                
           $("#result").html(result.replace(/\n/g, "<br />"));
        });
    });
    
    $(".btn-iteration").click(function(){
        device_number=$("#device_number").val(); 
        n=$("#iteration").val();
        console.log(device_number);
        console.log(n);
        window.location="send_data_iteration.php?device_number="+device_number+"&count="+n;
    });
    
                                        
</script>

