<?php
header('Access-Control-Allow-Origin: *');

include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>

</ul>
<div class="header text-dark" id="pdf-report-header">Send Camera Data To Terminal</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="">Device</label>
                                <select id="device_ip">                             
                                    <?php echo parcxV2Settings(array("task" => "60", "type" => "1,2")); ?>
                                </select>
                            </div>                           
                             <div class="row ">				 
                                <div class="col form-group">                                    
                                    <label for="">Plate Numbers</label>
                                    <input type="text" class="form-control" id="plate_number">
                                </div>
                                <div class="col form-group">
                                    <label for="">Confidence Rate</label>
                                    <input type="number" class="form-control" id="confidence_rate" value="98" min="0" max="100" >
                                </div> 
                                <div class="col form-group">
                                    <label for="">Country</label>
                                    <select id="country">                             
                                        <option value="UAE">UAE</option>
                                        <option value="KUW">Kuwait</option>
                                        <option value="BAH">Bahrain</option>
                                        <option value="KSA">Saudi Arabia</option>
                                        <option value="OMN">Oman</option>
                                    </select>
                                </div> 
                             </div>
                           <div class="row mt-2">
                               
                                <button type="button" class="btn btn-info mb-3 mr-3 btn-option" data-camera="71" id="camera1">Camera1</button> 
                            
                                <button type="button" class="btn btn-info mb-3 btn-option" data-camera="72" id="camera2">Camera2</button>                             
                             
                           </div>
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


    var camera = 0;
    $(".btn-option").click(function () {
        camera = $(this).attr("data-camera");
        var data = {};
        data["task"] = "4";
        data["plate"] = $("#plate_number").val();        
        data["confidence"]=$("#confidence_rate").val();
        data["country"] = $("#country").val();
        data["category"]= "private";
        data["camera"]=camera;
        data["ip"] = $("#device_ip").val();     
        var jsondata = JSON.stringify(data);
        console.log(jsondata)
        $.post("../../modules/ajax/send_to_device.php", jsondata, function (result) {    
           $("#result").html(result.replace(/\n/g, "<br />"));
        });
    });
    
    
    
                                        
</script>

