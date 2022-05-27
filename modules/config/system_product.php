<?php
$page_title = "Carpark Settings";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Product Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="form">Add Product</div>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">    
            <!-- add/update product form --> 
            <input type="hidden" id="user_id" value="<?php echo $_SESSION['userId']; ?>" />
            <form class="block-data card card-body col-md-5" data-status="form" style="display:none;" id="form">  
                <div class="row">
                    <div class="col form-group">
                        <label for="">Product Name</label>
                        <input type="text" class="form-control" id="name" aria-describedby="" placeholder="" name="product-name">
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
                        <label for="">Type</label>
                        <select id = "product_type">
                            <option value="contract_parking">Contract Parking</option>
                            <option value="validation">Validation</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group">
                        <label for="">Carpark assigned</label>
                        <select name="multiselect" id="multiselect" class="form-control" multiple="multiple">
                            <?php echo parcxV2Settings(array("task" => "12")); ?>
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

                <div id="show-contract-parking">
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Validity Days</label>
                            <input type="number" class="form-control" id="product_value" placeholder="" min="0" value="0"  required>
                        </div>
                        <div class="col form-group">
                            <label for="">Cost</label>
                            <input type="number" class="form-control" id="product_price" min="0" placeholder="" value="0" required>
                        </div>						                       
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Activation Date</label>
                            <input type="text" class="form-control" id="start_date" autocomplete="off" placeholder="" name="active-date">
                        </div>
                        <div class="col form-group">
                            <label for="">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry_date" autocomplete="off" placeholder="" name="expiry-date">
                        </div>
                    </div>                    
                </div>


                <div id="show-validity" style="display:none">				
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="date-validity">
                        <label class="custom-control-label" for="date-validity">Enable Date Validity</label>
                    </div>
                    <div class="row date-validity d-none">
                        <div class="col form-group">
                            <label for="">Activation Date</label>
                            <input type="text" class="form-control" id="start_date_validation">
                        </div>
                        <div class="col form-group">
                            <label for="">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry_date_validation">
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
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Validation Type</label>
                            <select id="validation_type">
                                <option>Time Value</option>
                                <option>Percentage Value</option>
                            </select>
                        </div>
                        <div class="col form-group">
                            <label for="">Value</label>
                            <input type="number" class="form-control" min="0" id="value" name="value" autocomplete="off" placeholder="" value="0" required>
                        </div>
                    </div>

                </div>

                <input type="submit" class="btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>             

            <!-- carpark table -->
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table table-bordered">                    
                        <?php echo parcxV2Settings(array("task" => "31")); ?>
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
    var table;
    var page=0;
    var product_name;
    
    function loadTable()
    {
        var data = {};
        data["task"] = "31";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (data) {
            $("#div-table").html("<table id='RecordsTable' class='table  table-bordered'>" + data + "</table>");
            table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});
            table.page(page).draw(false);    
        });
    } 
    /* === multiselect === */

    function multislect()
    {
        $('#multiselect').multiselect({
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
        var multiselectElements = ['#days', '#time-slots', '#multiselect']

        for (var i in multiselectElements) {
            $(multiselectElements[i] + " option").each(function () {
                this.selected = true;
            });
            $(multiselectElements[i]).multiselect("refresh");
        }
    }

    $(document).ready(function ()
    {
        table=$('#RecordsTable').DataTable(
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
        $('#multiselect').multiselect(
                {
                    buttonWidth: '100%',
                    includeSelectAllOption: true,
                    selectAllText: "All carparks",
                    nonSelectedText: "Select carparks",
                    selectAllNumber: false,
                    allSelectedText: "Select carparks",
                });

        $('#start_date,#expiry_date,#start_date_validation,#expiry_date_validation').daterangepicker({
            timePicker: false,
            timePickerIncrement: 10,
            format: 'YYYY-MM-DD',
            singleDatePicker: true,
        })



        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $("#add-edit-button").val("Submit");
                $("#date-validity").attr("checked", false);
                $("#date-validity").trigger("change");
                multislect();
                $("#add-product-btn").val("Submit");
                $("#product_type").prop("disabled", false);

            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });

        /* === show / hide date validity element === */
        $("#date-validity").change(function ()
        {
            if ($(this).is(':checked'))
            {
                $(".date-validity").removeClass("d-none");
            } else
            {
                $(".date-validity").addClass("d-none");
            }
        });

        //FormSubmit
        var formElement = $("#form");
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
                    {
                    data["id"] = "0";
                    data["activity_message"]="Add product "+$("#name").val();
                    }
                else
                    {
                    data["id"] = id;
                    data["activity_message"]="Edit product "+product_name; 
                    }

                data["product_name"] = $("#name").val();
                data["description"] = $("#description").val();
                data["product_type"] = $("#product_type").val();
                data["facility_number"] = $("#facility").val();
                data["carparks"] = $("#multiselect").val().toString();
                data['date_validity'] = "0";
                if (data["product_type"] == "validation")
                {
                    if ($("#date-validity").is(":checked"))
                    {
                        data['date_validity'] = "1";
                        data["start_date"] = $("#start_date_validation").val();
                        data["expiry_date"] = $("#expiry_date_validation").val();
                    } else
                    {
                        data['date_validity'] = "0";
                        data["start_date"] = "";
                        data["expiry_date"] = "";
                    }
                    data["validity_days"] = $("#days").val().toString();
                    data["validity_time_slots"] = $("#time-slots").val().toString();
                    data["validation_type"] = $("#validation_type").val();
                    data["product_value"] = $("#value").val();
                    data["product_price"] = "0";
                } else
                {
                    data["product_value"] = $("#product_value").val();
                    data["product_price"] = $("#product_price").val();
                    data["start_date"] = $("#start_date").val();
                    data["expiry_date"] = $("#expiry_date").val();
                }
                data["user_id"] = $("#user_id").val();
                data["task"] = "33";
                var jsondata = JSON.stringify(data);
                console.log(jsondata);
                $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                    if (result == "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }
        });

    });

    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".product-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        product_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text=$(this).attr("data-text");  
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "32";
        data["activity_message"]=status_text+" product "+product_name;
        var jsondata = JSON.stringify(data);
        page=table.page();
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable()
            else
                alert(result);
        });               
    });

    /*=====edit======*/
    $(document).on("click", ".product-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        product_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = id;
        data["task"] = "34";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            $("#add-edit-button").val("Edit");
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
            var response = JSON.parse(result);
            $("#name").val(response.product_name);
            $("#description").val(response.description);

            if (response.product_type === "validation")
            {
                $("#product_type").val("validation");
                $("#product_type").change();

                if (response.date_validity == 1)
                    $("#date-validity").attr("checked", true);
                else
                    $("#date-validity").attr("checked", false);

                $("#date-validity").trigger("change");
                $("#start_date_validation").val(response.start_date);
                $("#expiry_date_validation").val(response.expiry_date);


                var array = response.validity_days.split(",");
                $("#days").val(array);
                $('#days').multiselect('refresh');

                array = response.validity_time_slots.split(",");
                $("#time-slots").val(array);
                $('#time-slots').multiselect('refresh');
                
                array = response.carparks.split(",");
                $("#multiselect").val(array);
                $('#multiselect').multiselect('refresh');

                $("#validation_type").val(response.validation_type);
               // $("#validation_type").change();
                $("#value").val(response.product_value);
            } else
            {
                $("#start_date").val(response.start_date);
                $("#expiry_date").val(response.expiry_date);
                $("#product_value").val(response.product_value);
                $("#product_price").val(response.product_price);
            }
            $("#product_type").prop("disabled", true);
        });
    });

    $("#product_type").change(function ()
    {
        if ($('#product_type').val() == 'validation')
        {
            $('#show-validity').show();
            $('#show-contract-parking').hide();
        } else
        {
            $('#show-validity').hide();
            $('#show-contract-parking').show();
        }
    });

</script>
