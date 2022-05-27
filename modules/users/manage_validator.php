<?php
$page_title = "Manage users";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Manage Validators</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <div class="tab-link" data-target="form">Add Validator</div>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade text-dark" id="changePasswordModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body ">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title d-inline">Change Password</h3>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form id="form-change-password">    

                            <div class="modal-body">

                                <input type="hidden" id="id"/>
                                <div class="control-group">
                                    <label for="current_password" class="control-label">Current Password</label>
                                    <div class="controls">
                                        <input type="password" id="current_password" required name="current_password">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="new_password" class="control-label">New Password</label>
                                    <div class="controls">
                                        <input type="password" id="new_password" required name="new_password">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="confirm_password" class="control-label">Confirm Password</label>
                                    <div class="controls">
                                        <input type="password" id="confirm_password" required name="confirm_password">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <input type="submit" class="col btn btn-info mt-4" id="btn-change-password" value ="change password"/>
                                </div>
                            </div>
                        </form>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update form --> 

            <form class="block-data card card-body col-md-6" data-status="form" style="display:none;" id="form">                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Username(Email Id)</label>
                        <input type="email" class="form-control" id="username" placeholder="" name="Username" required name="name">
                    </div>
                </div>
                <div class="row" id="password-block">
                    <div class="col form-group">
                        <label for="">Password</label>						
                        <input type="password" class="form-control" id="password" placeholder="" required>
                    </div>
                    <div class="col form-group">
                        <label for="">Confirm Password</label>															
                        <input type="password" class="form-control"  placeholder="" name="confirm-password">
                    </div>
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label for="">Account Name</label>
                        <input type="text" class="form-control" id="account-name" aria-describedby="" placeholder="" name="account-name">
                    </div>				
                    <div class="col form-group">
                        <label for="">Mobile</label>
                        <input type="text" class="form-control" id="mobile" placeholder="">
                    </div>                
                </div>

                <div class="row">                      										
                    <div class="col form-group">					
                        <label for="">Carpark</label>
                        <select name="multiselect" id="multiselect" class="form-control" multiple="multiple">
                            <?php
                            //carparkDropdown();                     
                            parcxValidation(array("option-type" => "5"));
                            ?>

                        </select>												
                    </div>
                </div>


                <div class="row">					
                    <div class="col form-group">
                        <label for="">Products Assigned (max 10)</label>
                        <select id="validation-product" multiple="multiple">
                            <?php parcxValidation(array("option-type" => "8")); ?>
                        </select>
                    </div>
                    <div class="col form-group">
                        <label for="">Ticket Age (hours)</label>
                        <input type="number" class="form-control" id="validation-ticket-age" placeholder="" value="0" min="1">
                        <small class="form-text text-muted">Maximum parking duration for which validation can be done</small>
                    </div>
                    <div class="col form-group">
                        <label for="">Ticket Max Validation Hours</label>
                        <input type="number" class="form-control" id="validation-max-hours" placeholder="" value="0">
                        <small class="form-text text-muted">Number of hours of validation which can be given out</small>
                    </div> 
                </div>  
                <h5>Max Validation Hours</h5>
                <div class="row">
                    <div class="col form-group">
                        <label for="">Daily</label>
                        <input type="number" class="form-control"  id="validation-daily-hours" placeholder="" value="0">
                    </div>
                    <div class="col form-group">
                        <label for="">Weekly</label>
                        <input type="number" class="form-control"  id="validation-weekly-hours" placeholder="" value="0">
                    </div>
                    <div class="col form-group">
                        <label for="">Monthly</label>
                        <input type="number" class="form-control"  id="validation-monthly-hours" placeholder="" value="0">
                    </div>
                </div> 	
                <div class="row logo-input">               
                    <div class="col form-group">
                        <label for="">Logo</label>  						
                        <input type="file" class="form-control" id="logo" accept="image/JPEG" >
                        <small class="form-text text-muted">Maximum size 50KB ,550*150,Format: JPG/JPEG</small>                      
                    </div>
                    <div class="col form-group">
                        <img id="logo-preview" src="../../validation/dist/img/customer-logo/user.jpg" alt="your image"/>
                    </div>
                </div>
                <div id="date-div">
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="date-validity">
                        <label class="custom-control-label" for="date-validity">Enable Date Validity</label>
                    </div>
                    <div class="row date-validity d-none">
                        <div class="col form-group">
                            <label for="">Account Activation Date</label>
                            <input type="text" class="form-control" id="account-active-date" autocomplete="off" placeholder="" name="ActivationDate">
                        </div>
                        <div class="col form-group">
                            <label for="">Account Expiry Date</label>
                            <input type="text" class="form-control" id="account-expiry-date" autocomplete="off" placeholder="" name="ExpiryDate">
                        </div>
                    </div>
                </div>  
                <div class="col form-group custom-control custom-checkbox mt-3 mb-3" id="validation-report">
                    <input type="checkbox" class="custom-control-input" id="validation-report-view">
                    <label class="custom-control-label" for="validation-report-view">Validation Report</label>
                </div>	 
                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add_validator">
            </form>


            <!-- carpark table -->         
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-RecordsTable">     
                        <table id="RecordsTable" class="table  table-bordered"> 
                            <?php echo parcxValidation(array("option-type" => "2")); ?>
                        </table>
                    </div>                                                  
                </div>             
            </div>             

        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>
<script src="<?=URL?>dist/js/validationUsers.js"></script>
<script src="../../validation/dist/js/password-strength/password.js"></script>
<script src="../../validation/dist/js/custom/password.js"></script>
<script src="../../validation/dist/js/custom/licenses-form-validation.js"></script>
<script>
    var status;
    var id;
    var table;
    var user_name;
    var page=0;
    $(document).ready(function ()
    {
        $('#validation-product').multiselect({
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "All Products",
            nonSelectedText: 'Select Product',
            selectAllNumber: false,
            allSelectedText: "All Products",
        });

        $('#multiselect').multiselect({
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: "All Carparks",
            nonSelectedText: 'Select Carparks',
            selectAllNumber: false,
            allSelectedText: "All Carparks",
        });

        $("#date-validity").change(function () {
            if ($(this).is(':checked')) {
                $(".date-validity").removeClass("d-none");
            } else {
                $(".date-validity").addClass("d-none");
            }
        });

        $('#account-active-date').daterangepicker({
            timePicker: false,
            format: 'YYYY-MM-DD',
            singleDatePicker: true
        });

        $('#account-expiry-date').daterangepicker({
            timePicker: false,
            format: 'YYYY-MM-DD',
            singleDatePicker: true
        });



        table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]});
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $('#multiselect').multiselect('refresh');
                $('#validation-product').multiselect('refresh');
                $("#date-validity").attr("checked", false);
                $("#date-validity").trigger("change");
                $("#password-block").show();
                $("#username").attr("disabled", false);
                $("#add_validator").val("Submit");
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });
   

    });


    function loadTable()
    {
        var data = {};
        data["option-type"] = "2";
        var jsondata = JSON.stringify(data);
        $.post("../ajax/validation.php", jsondata, function (data) {
            $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + data + "</table>");
            table=$('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]});
            table.page(page).draw(false); 
        });
    }


    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".btn-enable-disable-validator", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        user_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text = $(this).attr("data-text");
        if (status_text === "Disable")
            status = 0;
        else
            status = 1;
        var data = {};
        data["user_id"] = id;
        data["status"] = status;
        data["option-type"] = 4;
        data["activity_message"]=status_text+" validation user "+user_name;
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        page=table.page();
        $.post("../ajax/validation.php", jsondata, function (data) {
            console.log(data);
            loadTable()
        });
    });
    /*change password*/
    $(document).on("click", ".validator-change-password", function () {
        id = $(this).parent('td').parent('tr').data('id');
        $("#id").val(id);
        $('#changePasswordModal').modal('show');
    });


    /*=====edit======*/
    $(document).on("click", ".btn-edit-validator", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        user_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["user_id"] = id;
        data["option-type"] = 6;
        var jsondata = JSON.stringify(data);
        $.post("../ajax/validation.php", jsondata, function (data) {
            $('.block-data[data-status="overview"]').hide();
            $('.block-data[data-status="form"]').show();
            $('.tab-link').removeClass('active');

            var response = JSON.parse(data);
            $("#username").val(response.email);
            $("#username").prop("disabled", true);
            $("#account-name").val(response.display_name);
            $("#mobile").val(response.mobile);
            $('#multiselect').val(response.carparks.split(','));
            $('#multiselect').multiselect('refresh');
            $("#validation-product").val(response.products.split(','));
            $('#validation-product').multiselect('refresh');
            $("#validation-ticket-age").val(response.ticket_age);
            $("#validation-max-hours").val(response.total_maximum_validation_hours);
            $("#validation-daily-hours").val(response.daily_maximum_validation_hours);
            $("#validation-weekly-hours").val(response.weekly_maximum_validation_hours);
            $("#validation-monthly-hours").val(response.monthly_maximum_validation_hours);
            if (response.date_validity == 1)
            {
                $("#date-validity").attr("checked", true);
                $("#account-active-date").val(response.start_date);
                $("#account-expiry-date").val(response.expiry_date);
            } else
                $("#date-validity").attr("checked", false);
            $("#date-validity").trigger("change");

            if (response.validation_report_view == 1)
                $("#validation-report-view").attr("checked", true);
            else
                $("#validation-report-view").attr("checked", false);

            $("#password").val(response.password);
            $("#conform-password").val(response.password);
            $("#password-block").hide();

            $("#add_validator").val("Edit");
        });

    });

</script>
