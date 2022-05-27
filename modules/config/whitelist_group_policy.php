<?php
$page_title = "Carpark Settings";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Whitelist group policy</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="form">Add Policy</div>

    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update carpark form --> 
            <div class="block-data"data-status="form" style="display:none;">
                 <form class="row card card-body col-md-5 mb-3"   id="form">                 
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Policy name</label>
                            <input type="text" class="form-control" id="policy_name" placeholder=""  required name="policy_name">
                        </div>
                    </div>	
                    <div class="row">	
                        <div class="col form-group">
                            <label for="">Description</label>
                            <input type="text" class="form-control" id="description" >
                        </div>
                    </div>
                    <div class="row">	
                        <div class="col form-group">
                            <label for="">Carpark Name</label>
                            <select id="carpark" multiple="multiple">                  
                                <?php echo parcxV2Settings(array("task" => "12")); ?>
                            </select>
                        </div> 
                        <div class="col form-group">
                            <label for="">Parking Zone</label>
                            <select id="parking_zone" multiple="multiple">                           
                               
                            </select>
                        </div>  
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Facility Name</label>
                            <select id="facility">                  
                                <?php echo parcxV2Settings(array("task" => "5")); ?>
                            </select>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Activation Date</label>
                            <input type="text" class="form-control" id="start_date">
                        </div>
                        <div class="col form-group">
                            <label for="">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Validity Days</label>
                            <select id="days" multiple="multiple" class="weekdays">
                                <option value="Sunday">Sunday</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                        <div class="col form-group">
                            <label for="">Time Slots</label>
                            <select id="time-slots" multiple="multiple" class="">
                                <?php
                                for ($i = 0; $i < 24; $i++) {
                                    $value = sprintf("%02d", $i) . ":00 - " . sprintf("%02d", $i + 1) . ":00";
                                    echo "<option value='" . $value . "'>" . $value . "</option>\n";
                                }
                                ?>                           
                            </select>
                        </div>
                    </div>
                    
                </form>
                
                <div id="customer-div" class="row card card-body">
                    <div class="row mb-3">
                        <div class="col"><h4>USERS</h4></div>
                        <div class="col"></h4></div>
                        <div class="col"><h4>GROUP</h4></div>
                    </div>
                    <div class="row" id="add-group-policy">
                        <div class="col" id="left">
                            <?php echo parcxWhitelistSettings(array("task" => "12")); ?>
                            
                            
                        </div>
                        <div class="p-5" >
                            <input type="button" class="btn  btn-block btn-outline-primary btn-lg" value="Add Users" id="add_user_button"/>
                            <input type="button" class="btn  btn-block btn-outline-primary btn-lg" value="Remove Users" id="remove_user_button"/>
                        </div>
                        <div class="col" id="right"></div>                        
                    </div>                                                                                                  
                </div>
            <input type="submit" class="signUp btn btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </div>

            
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table  table-bordered ">                                                
                            <?php echo parcxWhitelistSettings(array("task" => "9")); ?>
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
    var users=$("#left").html();
    
$("#add_user_button").click(function(){  
  $('div#left input:checked').each(function() {            
    $("#right").append('<div class="form-group custom-control custom-checkbox">'+$(this).parents('div').html()+'</div>')    
     $(this).closest('div').remove();
    });
}); 

$("#remove_user_button").click(function(){
   $('div#right input:checked').each(function() {            
    $("#left").append('<div class="form-group custom-control custom-checkbox">'+$(this).parents('div').html()+'</div>')    
     $(this).closest('div').remove();
    });
}); 

    function getParkingZone(value)
    {
        var data = {};
        data["task"] = 57;
        data["carpark_number"] = $("#carpark").val().toString();
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            $("#parking_zone").html(result);
            $('#parking_zone').multiselect({
                buttonWidth: '100%',
                includeSelectAllOption: true,
                selectAllText: "All Parking Zone",
                nonSelectedText: 'Select Parking Zone',
                selectAllNumber: false,
                allSelectedText: "All Parking Zone",
            });
            $('#parking_zone').multiselect('rebuild');
            if (value != "")
            {
                var array = value.split(",");
                $("#parking_zone").val(array);
            }
            $('#parking_zone').multiselect("refresh");
        });
    }

    function multislect()
    {
        $('#carpark').multiselect({
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "All Carparks",
            nonSelectedText: 'Select Carparks',
            selectAllNumber: false,
            allSelectedText: "All Carparks",
        });

        $('#days').multiselect({
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "Everyday",
            nonSelectedText: 'Select Day of the Week',
            selectAllNumber: false,
            allSelectedText: "Everyday",
        });

        $('#time-slots').multiselect({
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "All Time Slots",
            nonSelectedText: 'Select Time Slots',
            selectAllNumber: false,
            allSelectedText: "All Time Slots",
        });


        // select all on load
        var multiselectElements = ['#days', '#time-slots', '#carpark']

        for (var i in multiselectElements) {
//            $(multiselectElements[i] + " option").each(function () {
//                this.selected = true;
//            });
            $(multiselectElements[i]).multiselect("refresh");
        }
    }
var formElement = $("#form");

    $(document).ready(function ()
    {
        multislect();
        getParkingZone("");
        $('#start_date,#expiry_date').daterangepicker({
            timePicker: false,
            timePickerIncrement: 10,
            format: 'YYYY-MM-DD',
            singleDatePicker: true,
        })

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
                multislect();
                $("#left").html(users);
                $("#right").html("");
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });

        $("#carpark").on("change", function () {
            getParkingZone("");
        });

        //FormSubmit
        
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
            }           
        });

    });
    $("#add-edit-button").click(function(){        
        if(formElement.valid()==true)
            formSubmit();
    });
    
    function formSubmit()
            {
                var data = {};
                if ($("#add-edit-button").val() == "Submit")
                    data["id"] = "";
                else
                    data["id"] = id;

                data["policy_name"] = $("#policy_name").val();
                data["description"] = $("#description").val();
                data["facility_number"] = $("#facility").val();
                data["carpark_number"] = $("#carpark").val().toString();
                data["parking_zone"] = $("#parking_zone").val().toString();
                data["validity_days"] = $("#days").val().toString();
                data["validity_timeslot"] = $("#time-slots").val().toString();
                data["start_date"] = $("#start_date").val();
                data["expiry_date"] = $("#expiry_date").val();
                data["task"] = "8";
                
                var selected = [];
                $('div#right input').each(function() {    
                    selected.push($(this).attr('id'));
                });                
                data["users"]=selected.toString();
                var jsondata = JSON.stringify(data);  
                console.log(jsondata);
                $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
                    if (result == "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }

    /* === enable disable product === */
    var status;
    var id;
    
     function loadTable()
        {
            var data = {};
            data["task"] = "9";
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/whitelist.php", jsondata, function (data) {
                $("#div-table").html("<table id='RecordsTable' class='table  table-bordered'>" + data + "</table>");
                $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]});
            });
        } 
    
    $(document).on("click", ".policy-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        var status_text=$(this).attr("data-text");  
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "10";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable();
            else
                alert(result);
        });
    });

    /*=====edit======*/
    $(document).on("click", ".policy-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "11";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
            $("#left").html(users);
            $("#right").html("");   
            
            var response = JSON.parse(result);

            $("#policy_name").val(response.policy_name);
            $("#description").val(response.description);
            //$("#facility").val(response.facility_number);


            $("#start_date").val(response.start_date);
            $("#expiry_date").val(response.expiry_date);

            var array = response.validity_days.split(",");
            $("#days").val(array);
            $('#days').multiselect('refresh');

            array = response.validity_timeslot.split(",");
            $("#time-slots").val(array);
            $('#time-slots').multiselect('refresh');

            array = response.carpark_number.split(",");
            $("#carpark").val(array);
            $("#carpark").multiselect('refresh');
            
            
            array=response.users.split(",");
            for(var i=0;i<array.length;i++)
                {                
                $("#"+array[i]).prop('checked', true);
                }            
            $("#add_user_button").click();
            
            getParkingZone(response.parking_zone);

            $("#add-edit-button").val("Edit");

        });

    });

</script>
