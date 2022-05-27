<?php
//Add last_updated_datetime in fueling_station_pump_settings table
$page_title = "Fuleing Station Pump Settings";

include('../../includes/header.php');
$access = checkPageAccess("pump_settings");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Pump Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
	<?php
        if ($access["add"] == 1)
        	echo '<div class="tab-link" data-target="form">Add Pump Settings</div>';
	?>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>


<!-- Info modal -->
<div class="modal fade" id="info-modal" tabindex="-1" role="dialog" aria-labelledby="edit-preview" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4 pb-4" id='info-message'> 
            </div>
        </div>
    </div>
</div>
<!-- Info modal -->



<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update pump config form --> 
            <form class="block-data card card-body col-md-8" data-status="form" style="display:none;" id="form">                
                <div class="row">

                </div>
                <div class="row">                    
                    <div class="col form-group">
                        <label for="">Pump Number</label>
                        <input type="number" class="form-control" id="pump_number" required name="pump_number">
                    </div>
                    
                    <div class="col form-group">
                        <label for="">Pump Name</label>
                        <input type="text" class="form-control" id="pump_name" required name="pump_name">
                    </div>
                </div>       
                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Waiting Delay</label>
                        <input type="number" class="form-control" id="waiting_delay" min="0">
                    </div>

                    <div class="col form-group">
                        <label for="">Waiting Timeout</label>
                        <input type="number" class="form-control" id="waiting_timeout" min="0">
                    </div>                 
                </div>   
                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Monitor Pin</label>
                        <input type="number" class="form-control" id="monitor_pin" min="1" max="5">
                    </div> 
                    
                    <div class="col form-group">
                        <label for="">Pump Monitor Port</label>
                        <input type="number" class="form-control" id="pump_monitor_port" min="0" >
                    </div> 
                </div>
                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Camera 1</label>
                        <select id="camera_1">
                            <option value="0">--Select--</option>
                            <?php
                            $data["task"] = 2;
                            parcxStationConfiguration($data);
                            ?>
                        </select>
                    </div> 
                    
                    <div class="col form-group">
                        <label for="">Camera 2</label>
                        <select id="camera_2">
                            <option value="0">--Select--</option>
                            <?php
                            $data["task"] = 2;
                            parcxStationConfiguration($data);
                            ?>
                        </select>
                    </div> 
                </div>
                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Select CMS Controller</label>
                        <select id="cms_id">
                            <option value="0">--Select--</option>
                            <?php
                            $data["task"] = 1;
                            parcxStationConfiguration($data);
                            ?>
                        </select>
                    </div> 
                    
                    <div class="col form-group">
                        <label for="">Controller Id</label>
                        <input type="number" class="form-control" id="controller_id" min="0">
                    </div> 
                </div>
                
                
                <div class="row">
                    <div class="col form-group">
                        <input type="checkbox" id ="call_camera" />
                        <label for="">Call vehicle found camera</label> 
                    </div>
                    
                </div>
                
                

                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>

            <!-- carpark table -->         
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body">     
                        <table id="RecordsTable" class="table table-blue table-bordered table-striped">                    
                            <?php
                            $data["task"] = 3;
			    $data["edit"] = $access["edit"];
                            $data["delete"]=$access["delete"];
                            parcxStationConfiguration($data);
                            ?> 
                        </table>
                    </div>                                                  
                </div>             
            </div>             

        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>
<script>
    var status;
    var id;
    $(document).ready(function ()
    {
        $('#RecordsTable').DataTable(
                {
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "aaSorting": []

                });
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $("#add-edit-button").val("Submit");
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });


        //FormSubmit
        var formElement = $("#form");
        var rules_set;

       
        formElement.validate({
            rules: rules_set,
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                error.insertAfter(element);
            },
            submitHandler: function ()
            {
                var data = {};
                if ($("#add-edit-button").val() == "Submit")
                    data["id"] = "";
                else
                    data["id"] = id;      

                data["pump_number"] = $("#pump_number").val();
                data["pump_name"] = $("#pump_name").val();
                
                if($("#waiting_delay").val()=="")
                    data["waiting_delay"] = "0";
                else
                    data["waiting_delay"] = $("#waiting_delay").val();
                
                if($("#waiting_timeout").val()=="")
                    data["waiting_timeout"] = "0";
                else
                    data["waiting_timeout"] = $("#waiting_timeout").val();
                
                if($("#monitor_pin").val()=="")
                    data["monitor_pin"] = "0";
                else
                    data["monitor_pin"] = $("#monitor_pin").val();
                
                if($("#pump_monitor_port").val()=="")
                    data["pump_monitor_port"] = "0";
                else
                    data["pump_monitor_port"] = $("#pump_monitor_port").val();
                
                if($("#camera_1").val() > "")
                    data["camera_1"] = $("#camera_1").val();
                else 
                    data["camera_1"] = "0";
                if($("#camera_2").val() > "")
                    data["camera_2"] = $("#camera_2").val();
                else 
                    data["camera_2"] = "0";
                
                if($("#cms_id").val()=="")
                    data["cms_id"] = "0";
                else
                    data["cms_id"] = $("#cms_id").val();
                
                if($("#controller_id").val()=="")
                    data["controller_id"] = "0";
                else
                    data["controller_id"] = $("#controller_id").val();
                
                if ($('#call_camera').is(":checked"))
                    data["call_vehicle_found_camera"] = "1";
                else
                    data["call_vehicle_found_camera"] = "0";
                data["task"] = "5";

                var jsondata = JSON.stringify(data);    
                $.post("ajax/station_config.php", jsondata, function (result) {
                    if (result == "Successfull"){ 
                        $("#info-message").html("Update Successful");
                        $("#info-modal").modal('show');
                        //location.reload();
                    }     
                    else
                    {
                        $("#info-message").html(result);
                        $("#info-modal").modal('show');
                         //alert(result);
                    }
                });
            }
        });
    

    });
    
   
    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".pump-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        //var status_text = $(this).text();
        var status_text = $(this).attr("data-text");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "6";
        var jsondata = JSON.stringify(data);
        alert(jsondata)
        $.post("ajax/station_config.php", jsondata, function (result) {
            if (result == "Successfull"){
                $("#info-message").html("Update Successful");
                $("#info-modal").modal('show');
                //location.reload();
            }    
            else{
                $("#info-message").html(result);
                $("#info-modal").modal('show');
                //alert(result);
            }
                
        });
    });
   
                

    /*=====edit======*/
    $(document).on("click", ".pump-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "4";
        var jsondata = JSON.stringify(data);
        $.post("ajax/station_config.php", jsondata, function (result) {
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
     
                
            var response = JSON.parse(result);
            $("#pump_number").val(response.pump_number);
            $("#pump_name").val(response.pump_name);
            $("#waiting_delay").val(response.waiting_delay);
            $("#waiting_timeout").val(response.waiting_timeout);
            $("#monitor_pin").val(response.monitor_pin);
            $("#pump_monitor_port").val(response.pump_monitor_port);
            $("#camera_1").val(response.camera_1);            
            $("#camera_2").val(response.camera_2);
            $("#cms_id").val(response.cms_id);            
            $("#controller_id").val(response.controller_id);
            if(response.call_vehicle_found_camera==1)
                $("#call_camera").prop("checked", true);
            else
                $("#call_camera").prop("checked", false);
            
            $("#add-edit-button").val("Edit");                        
        });

    });
    
    $('#info-modal').on('hidden.bs.modal', function () {
        location.reload();
    })

</script>
