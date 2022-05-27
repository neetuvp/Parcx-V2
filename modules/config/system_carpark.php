<?php
$page_title = "Carpark Settings";

include('../../includes/header.php');
$access = checkPageAccess("carpark");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Carpark Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
	<?php
        if ($access["add"] == 1)
            echo '<div class="tab-link" data-target="form">Add carpark</div>';
	?>
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
            <form class="block-data card card-body col-md-4" data-status="form" style="display:none;" id="form"> 
                <input type="hidden" id="user_id" value="<?php echo $_SESSION['userId']; ?>" />
                <div class="row">
                    <div class="col form-group">
                        <label for="">Carpark name</label>
                        <input type="text" class="form-control" id="name" placeholder=""  required name="name">
                    </div>
                </div>				                                                  
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Carpark number</label>
                        <input type="number" class="form-control" id="carpark_number" value="0" min="0">
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
                        <label for="">Rate Type</label>
                        <select id="rate_type"> 
                            <option value="1">Variable Rate</option>
                            <option value="2">Fixed Rate</option>     
                        </select>
                    </div> 
                    <div class="col form-group">
                        <label for="">Rate Plan</label>
                        <select id="rate_plan">                  
                            <?php echo parcxV2Settings(array("task" => "13")); ?>
                        </select>
                    </div>                 
                </div> 
                <div class="row">						     
                    <div class="col form-group">
                        <label for="">Reservation Rate Plan</label>
                        <select id="reservation_rate_plan"> 
                            <option value="">Select Reservation Rate Plan</option>                 
                            <?php echo parcxV2Settings(array("task" => "13")); ?>
                        </select>
                    </div> 
                </div>
                <div class="row">	
                    <div class="col form-group">
                        <label>Total Spaces</label>
                        <h3 id="total_spaces">0</h3>                       

                    </div>                  
                </div>
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Short Term Spaces</label>
                        <input type="number" class="form-control spaces" id="shortterm_spaces" value="0">
                    </div> 
                    <div class="col form-group">
                        <label for="">Access Spaces</label>
                        <input type="number" class="form-control spaces" id="access_spaces" value="0">
                    </div> 

                </div>
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Reservation Spaces</label>
                        <input type="number" class="form-control spaces" id="reservation_spaces" value="0">
                    </div> 
                    <div class="col form-group">
                        <label for="">Occupancy Threshold</label>
                        <input type="number" class="form-control" id="occupancy_threshold" value="0">
                    </div> 
                </div>                                                                                    
                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>

            <!-- carpark table -->         
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table table-bordered">                    
                            <?php echo parcxV2Settings(array("task" => "4","edit"=>$access["edit"],"delete"=>$access["delete"])); ?>
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
    var previous_carpark;
    var previous_facility;
    var carpark_name;
    var table;    
    var page=0;
    
    function loadTable()
    {
        page=table.page();
        var data = {};
        data["task"] = "4";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (data) {
            $("#div-table").html("<table id='RecordsTable' class='table  table-bordered'>" + data + "</table>");
            table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});
            table.page(page).draw(false); 
        });
    } 
        
    $(document).ready(function ()
    {
    table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],"aaSorting": []});  
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $("#add-edit-button").val("Submit");
                $("#total_spaces").html("0");
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });

        $(".spaces").on("change keyup", function () {
            $("#total_spaces").html(parseInt($("#reservation_spaces").val()) + parseInt($("#access_spaces").val()) + parseInt($("#shortterm_spaces").val()))
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
                    data["id"] = "";
                    data["previous_facility"] = $("#facility").val();
                    data["previous_carpark"] =$("#carpark_number").val();
                    data["activity_message"]="Add carpark "+$("#name").val();
                } else
                {
                    data["id"] = id;
                    data["previous_carpark"] = previous_carpark;
                    data["previous_facility"] = previous_facility;
                    data["activity_message"]="Edit carpark "+carpark_name;
                }
                data["carpark_number"] = $("#carpark_number").val();
                data["carpark_name"] = $("#name").val();
                data["facility_number"] = $("#facility").val();
                data["total_spaces"] = $("#total_spaces").html();
                data["occupancy_threshold"] = $("#occupancy_threshold").val();
                data["reservation_spaces"] = $("#reservation_spaces").val();
                data["access_spaces"] = $("#access_spaces").val();
                data["shortterm_spaces"] = $("#shortterm_spaces").val();
                data["facility_name"] = $("#facility option:selected").text();
                data["user_id"] = $("#user_id").val();
                data["rate_type"]=$("#rate_type").val(); 
                data["rate_plan"]=$("#rate_plan").val();
                data["reservation_rate_plan"]=$("#reservation_rate_plan").val(); 
                data["task"] = "6";
                
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
    $(document).on("click", ".carpark-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        carpark_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text=$(this).attr("data-text");  
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "7";
        data["activity_message"]=status_text+" carpark "+carpark_name;
        var jsondata = JSON.stringify(data);        
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable()
            else
                alert(result);
        });
    });

    /*=====edit======*/
    $(document).on("click", ".carpark-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        carpark_name=$(this).parent('td').siblings(":eq( 0 )").text();
        
        var data = {};
        data["id"] = id;
        data["task"] = "9";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
            var response = JSON.parse(result);
            $("#name").val(response.carpark_name);
            $("#carpark_number").val(response.carpark_number);
            $("#facility").val(response.facility_number);
            $("#total_spaces").html(response.total_spaces);
            $("#occupancy_threshold").val(response.occupancy_threshold);
            $("#reservation_spaces").val(response.reservation_spaces);
            $("#access_spaces").val(response.access_spaces);
            $("#shortterm_spaces").val(response.shortterm_spaces);
            $("#rate_type").val(response.rate_type);
            $("#rate_plan").val(response.rate_plan);
            $("#reservation_rate_plan").val(response.reservation_rate_plan);  
            $("#add-edit-button").val("Edit");
            previous_carpark = response.carpark_number;
            previous_facility = response.facility_number;
        });
                
    });

</script>
