<?php
$page_title = "Discount Settings";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Discount Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="form">Add Discount</div>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update  form --> 
            <form class="block-data card card-body col-md-4" data-status="form" style="display:none;" id="form"> 
                <input type="hidden" id="user_id" value="<?php echo $_SESSION['userId']; ?>" />
                <div class="row">
                    <div class="col form-group">
                        <label for="">Discount name</label>
                        <input type="text" class="form-control" id="discount_name" placeholder=""  required name="name">
                    </div>
                </div>				                                                  
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Discount Type</label>
                        <select id="discount_type"> 
                            <option value="amount">Amount</option>
                            <option value="percentage">Percentage</option>     
                            <option value="hour">Hour</option>
                        </select>
                    </div> 
                </div>
                
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Discount Option</label>
                        <select id="discount_option"> 
                            <option value="coupon">Coupon</option>
                            <option value="pos">POS</option>     
                        </select>
                    </div> 
                </div>
                
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Discount Value</label>
                        <input type="number" class="form-control spaces" id="discount_value" value="0">
                    </div>                    
                </div>
                
                <div class="row">	
                    <div class="col form-group">
                        <label for="">Carpark Name</label>
                        <select id="carpark_number">                  
                            <?php echo parcxV2Settings(array("task" => "12")); ?>
                        </select>
                    </div> 
                </div>                 
                
                
                                                                         
                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>

            <!-- carpark table -->         
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-table">     
                        <table id="RecordsTable" class="table table-bordered">                    
                            <?php echo parcxV2Settings(array("task" => "37")); ?>
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
    
    
    function loadTable()
        {
            var data = {};
            data["task"] = "37";
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
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
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
                    data["activity_message"]="Add discount "+$("#discount_name").val();
                    }
                 else                
                    {
                    data["id"] = id;
                    data["activity_message"]="Edit discount "+discount_name;
                    }
                   
                
                data["carpark_number"] = $("#carpark_number").val();                                              
                data["discount_name"] = $("#discount_name").val();
                data["discount_type"] = $("#discount_type").val();
                data["discount_option"] = $("#discount_option").val();
                data["discount_value"] = $("#discount_value").val();
               
                data["task"] = "40";
                
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
    var discount_name;
    $(document).on("click", ".discount-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        discount_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text=$(this).attr("data-text");  
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "38";
        data["activity_message"]=status_text+" discount "+discount_name;
        page=table.page();
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable();
            else
                alert(result);
        });
    });

    /*=====edit======*/
    
    $(document).on("click", ".discount-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        discount_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = id;
        data["task"] = "39";        
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {            
            var response = JSON.parse(result);
            $("#discount_name").val(response.discount_name);
            $("#carpark_number").val(response.carpark_number);            
            $("#discount_type").val(response.discount_type);
            $("#discount_option").val(response.discount_option );
            $("#discount_value").val(response.discount_value);            
            $("#add-edit-button").val("Edit");   
            
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');
        });
                
    });

</script>
