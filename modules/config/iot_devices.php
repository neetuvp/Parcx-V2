<?php 
$page_title = "Manage Devices";
include('../../includes/header.php'); 
$access = checkPageAccess("devices");

?>
<div class="navbar-has-tablink">

<?php
include('../../includes/navbar-start.php');
?>
    
</ul>

<div class="header text-dark" id="pdf-report-header">IOT devices</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="add-form">Add device</div>

    </div>
</div>
<?php 
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php'); 
?>

<script>
    document.title = "Parcx Cloud | Manage Devices";
</script>




<!-- confirm enable disable -->
<div class="modal fade" id="enable-disable-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="enable-disable-modal-body">        
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="modal-conform">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper licenses-page desktop-container">


    <section class="content">
        <div class="container-wide">        

            <!-- add/update whitelist form --> 
            <form class="block-data card card-form-custom" data-status="add-form" style="display:none;" id="form"> 			

                <div class="row">
                    <div class="col form-group">
                        <label for="">IOT Mode</label>
                        <select id ="iot_mode" class="form-control">  
                            <?php echo parcxV2Configuration(array("task" => "6")); ?>
                        </select>
                        <small class="form-text text-muted">IOT mode</small>
                    </div>
                    <div class="col form-group">
                        <label for="">Device Category</label>
                        <select name = "device_category" id ="device_category" class="form-control">
                            <option value = "1" selected>Entry</option>
                            <option value = "2">Exit</option>                            
                        </select>
                        <small class="form-text text-muted">Device category</small>
                    </div>

                </div>


                <div class="row">
                    <div class="col form-group">
                        <label for="">Device name</label>
                        <input type="text" class="form-control" id="device_name" placeholder=""  required >
                        <small class="form-text text-muted">Name of the device</small>
                    </div>
                    <div class="col form-group">
                        <label for="">Device number</label>
                        <input type="number" class="form-control" id="device_number" value="0" min="0" required >
                        <small class="form-text text-muted">Device number</small>
                    </div>                    
                </div>

                <div class="row">                                        
                    <div class="col form-group">
                        <label for="">Facility Name</label>
                        <select id="facility">  
                            <option value="0">Select facility</option>
                            <?php echo parcxV2Configuration(array("task" => "4")); ?>
                        </select>
                        <small class="form-text text-muted">Name of Facility</small>
                    </div> 

                    <div class="col form-group">
                        <label for="">Carpark Name</label>
                        <select id="carpark">                                             
                        </select>
                        <small class="form-text text-muted">Name of carpark</small>
                    </div>                     
                </div>
                <div class="row">
                    <div class="col form-group">
                        <label for="">Site Id</label>
                        <input type="text" class="form-control" id="site_id" placeholder="" >
                        <small class="form-text text-muted">Site Id</small>
                    </div>
                    <div class="col form-group">
                        <label for="">Lane id</label>
                        <input type="text" class="form-control" id="lane_id" >
                        <small class="form-text text-muted">Lane id</small>
                    </div>                    
                </div>
                <div class="row">
                    <div class="col form-group ">
                        <label for="">Camera Id</label>
                        <input type="number" class="form-control" id="camera_id" value="0" >

                        <small class="form-text text-muted">Camera connected to device</small>
                    </div>
                    <div class="col form-group ">
                        <label for="">Camera IP</label>
                        <input type="text" class="form-control" id="camera_ip" >

                        <small class="form-text text-muted">Camera IP connected to device</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group ">
                        <label for="">Server handshake interval</label>
                        <input type="number" class="form-control" id="server_handshake_interval" value="90"  required >                    
                        <small class="form-text text-muted">Time interval for server handshake from device in seconds</small>
                    </div>
                    <div class="col form-group">
                        <label for="">Plate capturing wait delay</label>
                        <input type="number" class="form-control" id="plate_capturing_wait_delay" value="7"  required >
                        <small class="form-text text-muted"></small>
                    </div>
                </div>
                <div class="row ">
                    <div class="col form-group">
                        <label for="">Barrier open time limit</label>
                        <input type="number" class="form-control" id="barrier_open_time_limit" value="10"  required >
                        <small class="form-text text-muted">Time limit in seconds for barrier to stay open</small>
                    </div>    
                    <div class="col form-group">
                        <label >Mobile qr code validity limit</label>
                        <input type="number" class="form-control" id="mobile_qrcode_time_limit" value="10"  required >
                        <small class="form-text text-muted">Mobile qr code validity in seconds</small>
                    </div>    
                </div>
                <div class="row">
                    <div class="col form-group">
                        <label for="">Download delay</label>
                        <input type="number" class="form-control" id="download_delay" value="0"  required >
                        <small class="form-text text-muted">Time limit in minutes for download access whitelist</small>
                    </div> 
                    <div class="col form-group">
                        <label for="">UUID</label>
                        <input type="text" class="form-control" id="uuid" value="<?php echo bin2hex(openssl_random_pseudo_bytes(16));?>" required >
                        <small class="form-text text-muted">Unique number to identify device</small>
                    </div> 
                </div>
                <div class="row">	
                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                        <input type="checkbox" class="custom-control-input" id="quick_barrier_close">
                        <label class="custom-control-label" for="quick_barrier_close">Quick barrier close</label>
                        <small class="form-text text-muted">Close barrier quickly after operation</small>
                    </div> 
                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                        <input type="checkbox" class="custom-control-input" id="anpr_enabled">
                        <label class="custom-control-label" for="anpr_enabled">Enable ANPR</label>
                        <small class="form-text text-muted">Enable ANPR Camera</small>
                    </div> 
                    <div class="col form-group custom-control custom-checkbox mt-4 mb-4">
                        <input type="checkbox" class="custom-control-input" id="display_anpr_image">
                        <label class="custom-control-label" for="display_anpr_image">Display ANPR Image</label>
                        <small class="form-text text-muted">Display vehicle image in device</small>
                    </div>    
                </div>


                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-whitelist">
            </form>

            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table table-bordered">                    
                            <?php echo parcxV2Configuration(array("task" => "8")); ?>
                        </table>
                    </div>                                                  
                </div>             
            </div>  

        </div>
    </section>
</div>

<?php include('../../includes/footer.php'); ?>

<!-- jquery validate -->
<script src="../../dist/js/validate/jquery.validate.js"></script>

<script>
    var table;    
    var page=0;
    
    $("#facility").change(function(){
        var data = {};
        data["facility_number"] = $("#facility").val();        
        data["task"] = "5";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../ajax/iot_terminal.php", jsondata, function (response)
        {
            console.log(response);
            $("#carpark").html(response);
        });
    });
    /* === tab click function === */

    $(document).ready(function () {
        table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});

        $(document).on("click", "* [data-target]", function () {
            var $target = $(this).data('target');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
            $("#add-edit-whitelist").val("Submit");
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');

        });



        /* form validation */

        var formElement = $("#form");
        formElement.validate({
            submitHandler: function () {
                var data={};
                
                if ($("#add-edit-button").val() == "Submit")
                    {
                    data["id"] = "";                    
                    data["activity_message"]="Add device "+$("#device_name").val();
                    } 
                else
                    {
                    data["id"] = id;                  
                    data["activity_message"]="Edit device "+device_name;
                    }
                
                data["iot_mode"]=$("#iot_mode").val();
                data["device_category"]=$("#device_category").val();
                data["device_name"]=$("#device_name").val();
                data["device_number"]=$("#device_number").val();
                data["carpark_number"]=$("#carpark").val();
                data["carpark_name"] = $("#carpark option:selected").text();
                data["facility_number"] = $("#facility").val();
                data["facility_name"] = $("#facility option:selected").text();
                data["camera_id"] = $("#camera_id").val();
                data["camera_ip"] = $("#camera_ip").val();
                data["site_id"] = $("#site_id").val();
                data["lane_id"] = $("#lane_id").val();
                data["server_handshake_interval"] = $("#server_handshake_interval").val();
                data["plate_capturing_wait_delay"] = $("#plate_capturing_wait_delay").val();
                data["barrier_open_time_limit"] = $("#barrier_open_time_limit").val();
                data["mobile_qrcode_time_limit"] = $('#mobile_qrcode_time_limit').val();
                data["download_delay"] = $('#download_delay').val();
                data["uuid"] = $('#uuid').val();
                
                if ($('#quick_barrier_close').is(":checked"))
                    data["quick_barrier_close"] = "1";
                else
                    data["quick_barrier_close"] = "0";
                if ($('#anpr_enabled').is(":checked"))
                    data["anpr_enabled"] = "1";
                else
                    data["anpr_enabled"] = "0";
                
                if ($('#display_anpr_image').is(":checked"))
                    data["display_anpr_image"] = "1";
                else
                    data["display_anpr_image"] = "0";
                data["task"]=7;
                var jsondata = JSON.stringify(data);
                console.log(JSON.stringify(data));
                $.post("../ajax/iot_terminal.php", jsondata, function (response)
                {
                    
                    if (response == "Successfull")
                        location.reload();
                });
                
            }
        });

    });

function loadTable()
    {
        page=table.page();
        var data = {};
        data["task"] = "8";
        var jsondata = JSON.stringify(data);
        $.post("../ajax/iot_terminal.php", jsondata, function (response){
            $("#div-table").html("<table id='RecordsTable' class='table  table-bordered'>" + response + "</table>");
            table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});
            table.page(page).draw(false); 
        });
    }
    
/* === enable disable product === */
    var status;
    var id,status_text;
    $(document).on("click", ".device-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        device_name=$(this).parent('td').siblings(":eq( 0 )").text();
        status_text = $(this).attr("data-text");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;
        
        
        $("#enable-disable-modal-body").text("Are you sure you want to " + status_text + " this device?");
        $('#enable-disable-modal').modal('show');

        
    });
    
    
    $(document).on("click", "#modal-conform", function () {
        $('#enable-disable-modal').modal('hide');
        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "9";
        data["activity_message"]=status_text+" device "+device_name;
        var jsondata = JSON.stringify(data);
        $.post("../ajax/iot_terminal.php", jsondata, function (response){
            if (response == "Successfull")
                loadTable();
            else
                alert(response);
        });          
    });

$(document).on("click", ".device-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        device_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = id;
        data["task"] = "10";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../ajax/iot_terminal.php", jsondata, function (result){
            $("#form").trigger('reset');
            $(":checkbox").attr("checked", false);
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="add-form"]').show();
            $('.tab-link').removeClass('active');
            var response = JSON.parse(result);
            $("#iot_mode").val(response.iot_mode);
            $("#device_category").val(response.device_type);
            $("#device_category").change();
            $("#device_name").val(response.device_name);
            $("#device_number").val(response.device_number);            
            $("#facility").val(response.facility_number);
            $("#facility").change();
            $("#carpark").val(response.carpark_number);
            $("#carpark").change();
            $("#camera_id").val(response.camera_id);
            $("#camera_ip").val(response.camera_ip);
            $("#server_handshake_interval").val(response.server_handshake_interval);
            $("#plate_capturing_wait_delay").val(response.plate_capturing_wait_delay);           
            $("#barrier_open_time_limit").val(response.barrier_open_time_limit);                                   
            $("#site_id").val(response.site_id);
            $("#lane_id").val(response.lane_id);
            $("#mobile_qrcode_time_limit").val(response.mobile_qrcode_time_limit);
            $("#download_delay").val(response.download_delay);
            $("#uuid").val(response.uuid);
                                                               
            if (response.quick_barrier_close == 1)
                $('#quick_barrier_close').attr("checked", "checked");
            
            if (response.anpr_enabled == 1)
                $('#anpr_enabled').attr("checked", "checked");
            
            if (response.display_anpr_image == 1)
                $('#display_anpr_image').attr("checked", "checked");
           

            $("#add-edit-button").val("Edit");            
        });
    });

</script>