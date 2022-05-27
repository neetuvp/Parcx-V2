<?php
$page_title = "Device Dashboard";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Parking Rates</div>

<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="sheduled-rates">Sheduled Rates</div>        
        <div class="tab-link" data-target="form">Add Rate Shedule</div>        
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>


<div class="content-wrapper " >
    <!-- additional menu -->
    <div class="additional-menu bg-white row m-0 block-data " data-status="overview">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left nav-button-group">                                                        
                <?php echo parcxParkingSettings(array("task" => "3")); ?>
            </div>

        </div>
    </div>
    <!-- end / additional menu -->

    <section class="content">
        <div class="container-wide">
            <div  class="block-data" data-status="overview">


                <input type='hidden' name = 'rateno' id = 'rateno' value='parking_rates1'> 

                <div class="card card-light mb-4 col-md-4 p-0 collapsed-card" >
                    <div class="card-header">
                        <h3 class="card-title p-1" id="current_parking_rate_label">Edit Parking Rate Name</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-edit"></i>
                            </button>                            
                        </div>
                    </div>

                    <div class="card-body" style="display: none;"> 
                        <form id="edit-rate-name-form">                            
                            <div class="row">
                                <div class="col form-group">
                                    <label for="">New  name</label>
                                    <input type="text" class="form-control" id="new_parking_rate_label" placeholder=""  required name="name">
                                </div>
                            </div>                            
                            <button type="submit" class="btn btn-info float-right" value="Submit" id="edit-rate-name-button" title="Save" /><i class="fa fa-save"></i></button>

                        </form>
                    </div>
                </div>
                <div class="card card-light mb-4 col-md-8 p-0" >
                    <div class="card-header">
                        <h3 class="card-title p-1">Fixed Parking Rates</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                            
                        </div>
                    </div>

                    <div class="card-body" style="display: block;">     
                        <table  class="table table-bordered" id="table-fixed-rate"> 
                            <?php echo parcxParkingSettings(array("task" => "2", "parking_rate_name" => "parking_rates1")); ?> 
                        </table>

                    </div>
                </div>

                <div class="card card-light mb-4 col-md-8 p-0"  >
                    <div class="card-header">
                        <h3 class="card-title p-1">Parking Rates</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>                           
                        </div>
                    </div>

                    <div class="card-body" style="display: block;">     
                        <table  class="table table-bordered" id="table-parking-rate"> 
                            <?php echo parcxParkingSettings(array("task" => "4", "parking_rate_name" => "parking_rates1")); ?>                             
                        </table>


                    </div>
                </div>
            </div>
            <div class="block-data" data-status="sheduled-rates" style="display:none;">
                <div class="card" >               
                    <div class="card-body">     
                        <table id="dynamicRateTable" class="table table-bordered">                    
                            <?php echo parcxV2Settings(array("task" => "45")); ?>
                        </table>
                    </div>                                                  
                </div>             
            </div>  

            <div  class="block-data" data-status="form" style="display:none;">               
                <form class="card col-md-4" data-status="form"  id="add-rate-div"> 
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Parking Rate Name</label>
                                    <input type="text" class="form-control" name="dynamicRateName" id="dynamicRateName" placeholder="Dynamic Rate Name">
                                    <input type="radio" name="rdbDate" value="date" id="rdbDate" checked="checked">
                                    <label class="col-form-label">Date</label>
                                    <input type="date" class="form-control" id="dynamicRateDate" />
                                    <input type="radio" name="rdbDate" value="day" id="rdbDay">
                                    <label class="col-form-label">Day</label>
                                    <select id="dynamicRateDay" class="form-control">
                                        <option value="Sunday">Sunday</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>              

                                    <input type="radio" name="rateType" value="2" id="rdbFixedRate" checked="checked">
                                    <label class="col-form-label">Fixed Rate</label>              
                                    <input type="radio" name="rateType" value="1" id="rdbVariableRate">
                                    <label class="col-form-label">Variable Rate</label>
                                    <br>
                                    <label class="col-form-label">Parking Rate</label>
                                    <select id="dynamicRate">
                                       <?php echo parcxV2Settings(array("task" => "13")); ?>
                                    </select>
                                    <br>
                                    <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
                                </div>
                            </div>                        
                        </div><!--row-->
                    </div>
                </form>

            </div>
    </section>
</div>


<?php include('../../includes/footer.php'); ?>

<script>
    var status;
    var id;
    $("#current_parking_rate_label").html($('#parking_rates1').val());
    
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
    
    $(document).ready(function ()
    {
        //FormSubmit
        var formElement = $("#add-rate-div");
        var rules_set = {};

        formElement.find('input[type=text]').each(function ()
        {
            var name = $(this).attr('name');
            rules_set[name] = 'required';
        });

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
                    data["id"] = "0";
                else
                    data["id"] = id;


                data["name"] = $("#dynamicRateName").val();
                if ($("#rdbDate").is(":checked"))
                    data["date"] = $("#dynamicRateDate").val();
                else
                    data["date"] = "";

                if ($("#rdbDay").is(":checked"))
                    data["day"] = $("#dynamicRateDay").val();
                else
                    data["day"] = "";

                data["parkingRate"] = $("#dynamicRate").val();
                data["type"] = $("input:radio[name='rateType']:checked").val();

                data["task"] = "47";
                data["activity_message"]="Add sheduled rate "+data["name"];

                var jsondata = JSON.stringify(data);
                $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                    if (result == "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }
        });

        var form = $("#edit-rate-name-form");
        form.validate({

            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                error.insertAfter(element);
            },
            submitHandler: function ()
            {
                var data = {};
                data["parking_rate_name"] = $('#rateno').val();
                data["parking_rate_name"] = data["parking_rate_name"].replace('parking_rates', '')
                data["parking_rate_label"] = $("#new_parking_rate_label").val();
                data["task"] = "48";

                var jsondata = JSON.stringify(data);
                $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                    if (result == "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }
        });

    });

    var fixed_rate;
    var step_value;
    var value_type;
    var fixed_rate_name;
    
    $(document).on("click", ".fixed-rate-cancel-btn", function ()
        {
        loadFixedParkingRate($('#rateno').val());
        });
        
    $(document).on("click", ".parking-rate-cancel-btn", function ()
        {
        loadParkingRate($('#rateno').val());
        });

    $(document).on("click", ".fixed-rate-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        value_type=$(this).data('type');
        if(value_type==="fee")
            step_value=$("#step_value").val();
        else
            step_value="1";
        
        if ($(this).attr("data-text") === "Edit")
        {
            fixed_rate = $(this).parent('td').siblings(":eq( 1 )").text();
            fixed_rate_name=$(this).parent('td').siblings(":eq( 0 )").text();
            
            $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'number' step='"+step_value+"' id = 'fixed_rate_" + id + "'  value = '" + fixed_rate + "'>");

            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");

            $(this).after("<button type='button' class='btn btn-danger fixed-rate-cancel-btn ml-2' ><i class='fas fa-window-close'></li></button>");
        } else
        {  
            if($("#fixed_rate_" + id).is(":invalid")==false)
                {
                var data = {};
                data["id"] = id;
                data["parking_rate_name"] = $('#rateno').val();
                data["parking_rate_value"] = $("#fixed_rate_" + id).val();
                data["task"] = "1";
                data["activity_message"]="Changing "+data["parking_rate_name"]+"-"+fixed_rate_name+" from "+fixed_rate+" to "+data["parking_rate_value"];
                var jsondata = JSON.stringify(data);

                $.post("../../modules/ajax/parking_settings.php", jsondata, function (result)
                {
                    if (result == "Successfull")
                        loadFixedParkingRate($('#rateno').val());
                    else
                        alert(result);
                });
            }
        }
    });
    
    
    var parking_rate;
    var time_unit;
    var time_duration;
    $(document).on("click", ".parking-rate-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        if ($(this).attr("data-text") === "Edit")
        {
            time_unit = $(this).parent('td').siblings(":eq( 0 )").text();
            time_duration = $(this).parent('td').siblings(":eq( 1 )").text();
            parking_rate = $(this).parent('td').siblings(":eq( 2 )").text();
            if(time_unit=="Minutes")
                time_unit="mins";
            else
                time_unit="hours";
            
            step_value=$("#step_value").val();
            
            $(this).parent('td').siblings(":eq( 0 )").html("<select id = 'time_unit_" + id + "'><option value='hours'>Hours</option><option value = 'mins'>Minutes</option></select>");
            $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'number' step='1' id = 'time_duration_" + id + "'  value = '" + time_duration + "'>");
            $(this).parent('td').siblings(":eq( 2 )").html("<input type = 'number' step='"+step_value+"' id = 'parking_rate_" + id + "'  value = '" + parking_rate + "'>");

            $('#time_unit_'+id).val(time_unit);
            
            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");

            $(this).after("<button type='button' class='btn btn-danger parking-rate-cancel-btn ml-2' ><i class='fas fa-window-close'></li></button>");
        } else
        {            
            var data = {};
            data["id"] = id;
            data["parking_rate_name"] = $('#rateno').val();
            data["time_unit"] = $("#time_unit_" + id).val();
            data["time_duration"] = $("#time_duration_" + id).val();
            data["parking_rate_value"] = $("#parking_rate_" + id).val();
            data["activity_message"]="Changing "+data["parking_rate_name"]+" for id "+id+".Time unit from "+time_unit+" to "+data["time_unit"]+".Time duration from "+time_duration+" to "+data["time_duration"]+".Rate from "+parking_rate+" to "+data["parking_rate_value"];
            data["task"] = "5";
            var jsondata = JSON.stringify(data);            
            $.post("../../modules/ajax/parking_settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadParkingRate($('#rateno').val());
                else
                    alert(result);
            });
        }
    });

    function loadFixedParkingRate(rate)
    {
        var data = {};
        data["parking_rate_name"] = rate;
        data["task"] = "2";
        var jsondata = JSON.stringify(data);        
        $.post("../../modules/ajax/parking_settings.php", jsondata, function (result)
        {            
            $("#table-fixed-rate").html(result);
        });
    }


    function loadParkingRate(rate)
    {
        var data = {};
        data["parking_rate_name"] = rate;
        data["task"] = "4";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/parking_settings.php", jsondata, function (result)
        {
            $("#table-parking-rate").html(result);
        });
    }

    function ShowParkingRate(rate)
    {       
        $('#rateno').val(rate);
        $("#current_parking_rate_label").html($('#' + rate).val());
        $('.btn-parking-rate').removeClass('btn-success');
        $('.btn-parking-rate').addClass('btn-info');
        $('#' + rate).addClass('btn-success');
        $('#' + rate).removeClass('btn-info');
        loadFixedParkingRate(rate);
        loadParkingRate(rate);
    }
    
    var sheduled_rate_name;
    $(document).on("click", ".dynamic-rate-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        sheduled_rate_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text = $(this).attr("data-text");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "46";
        data["activity_message"]=status_text+" sheduled rate "+sheduled_rate_name;
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            if (result == "Successfull")
                loadDynamicParkingRates();
            else
                alert(result);
        });
    });
    
    function loadDynamicParkingRates()
        {
        var data = {};    
        data["task"] = "45";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            $("#dynamicRateTable").html(result)
        });    

        }


</script>
