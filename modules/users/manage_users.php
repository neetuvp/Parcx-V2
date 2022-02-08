<?php
$page_title = "Manage Users";

include('../../includes/header.php');
$access = checkPageAccess("manage_user");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Manage Users</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <?php
        if ($access["add"] == 1)
            echo '<div class="tab-link" data-target="form">Add User</div>';
        ?>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade text-dark" id="changePasswordModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
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

                            <div class="password-strength">		
                                <div class="row">
                                    <div class="col form-group">
                                        <label for="">Current Password</label>
                                        <div class="input-group pw-view-btn" id="pvb-3">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                            </div>
                                            <input type="password" class="form-control" id="current_password" placeholder="" name="current_password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col form-group">
                                        <label for="">New Password</label>
                                        <div class="input-group pw-view-btn" id="pvb-1">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                            </div>
                                            <input type="password" class="form-control " id="new_password" placeholder="" name="new_password">
                                        </div>
                                    </div>
                                    <div class="col form-group">
                                        <label for="">Confirm Password</label>
                                        <div class="input-group pw-view-btn" id="pvb-2">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                            </div>
                                            <input type="password" class="form-control"  placeholder="" name="conform_new_password">
                                        </div>                            
                                    </div>
                                </div>
                                <small class="row w-100 form-text text-muted m-0">
                                    <div id="reset-pwd-container">
                                        <div class="pwstrength_viewport_progress"></div>
                                    </div>
                                    <div class="col">Password must contain letters, numbers and special characters, and be at least 8 characters long</div>
                                </small>
                            </div>  
                            <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Reset Password" id="resetPassword">                                                         
                            <div id="reset-password-message" class="alert text-danger"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal > Edit User -->
<div class="modal fade text-dark" id="user_edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="edit-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
                
            </div>
            <!--<div id="alert-div_update" class="alert bg-danger">
           <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fa fa-check"></i> Alert!</h5>
            <div id="alert-message"></div>
          </div> -->
            <div class="modal-body pt-4 pl-4 pr-4">
                <form id="form-edit-user">
                    <div class="alert-light mb-2" role="alert" id="messagebox_modal"></div>
                <div class="row">
                    
                </div>
                <div class="row">                    
                    <div class="col form-group">
                        <label for="">User Name</label>
                        <input type="text" class="form-control" id="user_name_edit" required name='user_name_edit'>
                        <input type="hidden"  id="user_id" value="">
                    </div>
                </div>                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Full Name</label>
                        <input type="text" class="form-control" id="full_name_edit"  required name='full_name_edit'>
                    </div>

                    <div class="col form-group">
                        <label for="">Email</label>
                        <input type="email" class="form-control" id="email_edit" >
                    </div>                    
                </div>   
                <div class="row">
                    <div class="col form-group">
                        <label for="">User role</label>
                        <select id="user_role_edit">
                            <?php
                            $data["task"] = 6;
                            parcxV2UserManagement($data);
                            ?>
                        </select>
                    </div>
                    <div class="col form-group">
                        <label for="">Language</label>
                        <select id="user_language_edit">
                            <option>English</option>
                            <option>Arabic</option>
                            <option>Spanish</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">						     
                    <div class="col form-group">
                        <label for="">Company name</label>
                        <input type="text" class="form-control" id="company_name_edit"  >
                    </div>
                    <div class="col form-group">
                        <label for="">Phone</label>
                        <input type="text" class="form-control" id="phone_edit">
                    </div>  
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label for="">Account Activation Date</label>
                        <input type="text" class="form-control" id="start_date_edit" autocomplete="off" placeholder="" required name='start_date_edit'>
                    </div>
                    <div class="col form-group">
                        <label for="">Account Expiry Date</label>
                        <input type="text" class="form-control" id="expiry_date_edit" autocomplete="off" placeholder="" required name='expiry_date_edit'>
                    </div>
                </div>
                    
                <div class="modal-footer">
                    <input type='submit' class='btn btn-info' name='user_edit' id='user-edit-save' value='Edit'>
                    <input type='button' class='btn btn-info' name='update_cancel' id='update_cancel' value='Cancel'>
                </div>
                
                </form>           
            </div>
            
            
            <!--<div class="modal-footer">
                <button type='button' class='btn btn-info' name='update_payment_btn' id='update_payment_btn' value='Update'>Update</button>  
            </div>-->
        </div>
    </div>
</div>
<!-- end / Modal -->

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update carpark form --> 
            <form class="block-data card card-body col-md-8" data-status="form" style="display:none;" id="form">                
                <div class="alert alert-light mb-2" role="alert" id="messagebox">
                                    
                </div>
                <div class="row">                    
                    <div class="col form-group">
                        <label for="">User Name</label>
                        <input type="text" class="form-control" id="user_name" required name="user_name">
                    </div>
                </div>                
                <div class="row">
                    <div class="col form-group">
                        <label for="">Full Name</label>
                        <input type="text" class="form-control" id="full_name"  required name="full_name">
                    </div>

                    <div class="col form-group">
                        <label for="">Email</label>
                        <input type="email" class="form-control" id="email" >
                    </div>                    
                </div>   
                <div class="row">
                    <div class="col form-group">
                        <label for="">User role</label>
                        <select id="user_role">
                            <?php
                            $data["task"] = 6;
                            parcxV2UserManagement($data);
                            ?>
                        </select>
                    </div>
                    <div class="col form-group">
                        <label for="">Language</label>
                        <select id="user_language">
                            <option>English</option>
                            <option>Arabic</option>
                            <option>Spanish</option>
                        </select>
                    </div>
                </div>

                <div class="row">						     
                    <div class="col form-group">
                        <label for="">Company name</label>
                        <input type="text" class="form-control" id="company_name"  >
                    </div>
                    <div class="col form-group">
                        <label for="">Phone</label>
                        <input type="text" class="form-control" id="phone">
                    </div>  
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label for="">Account Activation Date</label>
                        <input type="text" class="form-control" id="start_date" autocomplete="off" placeholder="" name="ActivationDate">
                    </div>
                    <div class="col form-group">
                        <label for="">Account Expiry Date</label>
                        <input type="text" class="form-control" id="expiry_date" autocomplete="off" placeholder="" name="ExpiryDate">
                    </div>
                </div>

                <div class="row pwd-row">
                    <div class="col form-group">
                        <input type="checkbox" class="form-control" id="automatic_password" name="AutomaticPassword">
                        <label for="">Generate Automatic Password</label>
                    </div>
                    <div class="col form-group">
                        <input type="checkbox" class="form-control" id="reset_password" name="ResetPassword">
                        <label for="">Reset Password Required on Login</label>       
                    </div>
                </div>

                <div class="row pwd-row" id="password-block">	
                    <div class="col form-group">
                            <label for="">New Password</label>
                            <div class="input-group pw-view-btn" id="pvb-1">
                                <div class="input-group-prepend">
                                    <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                </div>
                                 <input type="password" class="form-control" id="password" required name="password">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="">Confirm Password</label>
                            <div class="input-group pw-view-btn" id="pvb-2">
                                <div class="input-group-prepend">
                                    <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                </div>
                                <input type="password" class="form-control" id="conform_password" name="ConfirmPassword">
                            </div>                            
                        </div>
                    <small class="row w-100 form-text text-muted m-0">
                        <div id="pwd-container">
                            <div class="pwstrength_viewport_progress"></div>
                        </div>
                        <div class="col">Password must contain letters, numbers and special characters, and be at least 8 characters long</div>
                    </small>
                </div> 
               
                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
           
            </form>

            <!-- carpark table -->         
            <div class="block-data" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-RecordsTable">     
                        <table id="RecordsTable" class="table  table-bordered"> 
                            <?php
                            $data["task"] = 8;
                            $data["edit"] = $access["edit"];
                            $data["delete"]=$access["delete"];
                            $data["user_role_id"]=$_SESSION["userRollId"];
                            parcxV2UserManagement($data);
                            ?> 
                        </table>
                    </div>                                                  
                </div>             
            </div>             

        </div>
    </section>
</div>
<!-- password strength -->
<script src="../../plugins/password-strength/password.js"></script>
<script src="../../dist/js/password.js"></script>

<?php include('../../includes/footer.php'); ?>
<script>
    var status;
    var id;
    var table;
    var user_name;
    var page = 0;

    $(".pw-view-btn button").on('click', function (event)
    {
        event.preventDefault();
        var currentElement = $(this).parents(".pw-view-btn");
        var id = $("#" + currentElement.attr('id'))
        var inputElement = id.find('input');
        var inputIcon = id.find('i');

        if (inputElement.attr("type") == "text") {
            inputElement.attr('type', 'password');
            inputIcon.addClass("fa-eye-slash");
            inputIcon.removeClass("fa-eye");
        } else if (inputElement.attr("type") == "password") {
            inputElement.attr('type', 'text');
            inputIcon.removeClass("fa-eye-slash");
            inputIcon.addClass("fa-eye");
        }
    });


    $(document).ready(function ()
    {
        //$("#form-edit-user").validate();
        $('#start_date').daterangepicker({timePicker: false, format: 'YYYY-MM-DD', singleDatePicker: true});
        $('#expiry_date').daterangepicker({timePicker: false, format: 'YYYY-MM-DD', singleDatePicker: true});
        $('#start_date_edit').daterangepicker({timePicker: false, format: 'YYYY-MM-DD', singleDatePicker: true});
        $('#expiry_date_edit').daterangepicker({timePicker: false, format: 'YYYY-MM-DD', singleDatePicker: true});
        table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
                $("#form").validate().resetForm();
                $(".pwd-row").show();
                $("#user_name").attr("disabled", false);
                $("#add-edit-button").val("Submit");
            }
            $('.block-data').css('display', 'none');
            $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });


        //FormSubmit
        var formElement = $("#form");
        var rules_set = {
            user_name: {
                required: true,
                minlength: 3,
                maxlength: 100
            },
            password: {
                required: true,
                minlength: 5,
                maxlength: 25
            },
            ConfirmPassword:
                    {
                        equalTo: "#password",
                        required: true
                    },
            ActivationDate: {required: true},
            ExpiryDate: {required: true}
        };



        formElement.validate({
            rules: rules_set,
            messages: {
                ConfirmPassword: {
                    required: "Retype your new password",
                    equalTo: "Passwords don't match, please check"
                }
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                if(element.is('input:password') )
                    error.insertAfter(element.parent());
                else
                    error.insertAfter(element);                
            },
            submitHandler: function ()
            {
                var data = {};

                if ($("#add-edit-button").val() == "Submit")
                {
                    data["id"] = "";
                    data["activity_message"] = "Add user " + $("#user_name").val();
                } else
                {
                    data["id"] = id;
                    data["activity_message"] = "Edit user " + user_name;
                }

                if ($("#automatic_password").prop('checked') == true) {
                    data["generate_pwd"] = 1;
                } else
                    data["generate_pwd"] = 0;

                if ($("#reset_password").prop('checked') == true) {
                    data["reset_pwd"] = 1;
                } else
                    data["reset_pwd"] = 0;

                data["full_name"] = $("#full_name").val();
                data["user_name"] = $("#user_name").val();
                data["email"] = $("#email").val();
                data["password"] = $("#password").val();
                data["company_name"] = $("#company_name").val();
                data["phone"] = $("#phone").val();
                data["start_date"] = $("#start_date").val();
                data["expiry_date"] = $("#expiry_date").val();
                data["user_role"] = $("#user_role").val();
                data["language"] = $("#user_language").val();
                data["task"] = "7";

                var jsondata = JSON.stringify(data);
                jsondata = jsondata.replace("'", "\\\\\'");
                //console.log(jsondata);
                $.post("../../modules/ajax/users.php", jsondata, function (result) {
                    //console.log(result);
                    if (result == "Successfull")
                        location.reload();
                    else if (result.includes("auto"))
                    {
                        alertMessage(result);
                        location.reload();
                    } else
                        showMessage(result,1);
                });
            }
        });
        

        //FormSubmit change password
        var formElement2 = $("#form-change-password");
        var $np = $('#new_password');

        formElement2.validate({
            rules: {
                conform_new_password: {
                    equalTo: "#new_password",
                    required: function () {
                        return $np.val().length > 0
                    }
                }
            },
            messages: {
                conform_new_password: {
                    required: "Retype your new password",
                    equalTo: "Passwords don't match, please check"
                }
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                error.insertAfter(element.parent());
            },
            submitHandler: function () {

                var data = {};
                data["id"] = id;
                data["current_password"] = $("#current_password").val();
                data["new_password"] = $("#new_password").val();
                data["task"] = "10";
                var jsondata = JSON.stringify(data);
                //console.log(jsondata);
                $.post("../ajax/users.php", jsondata, function (result) { 
                    if(result!="Successfull")
                        $("#reset-password-message").html(result);
                    else
                       location.reload();
                });
            }
        });
        
        //Edit FormSubmit
        var editformElement = $("#form-edit-user");
        var edit_rules_set = {
            user_name_edit: {
                required: true,
                minlength: 3,
                maxlength: 100
            },
            
            start_date_edit: {required: true},
            expiry_date_edit: {required: true}
        };



        editformElement.validate({
            rules: edit_rules_set,
            messages: {
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("text-danger");
                if(element.is('input:password') )
                    error.insertAfter(element.parent());
                else
                    error.insertAfter(element);                
            },
            submitHandler: function ()
            {
                var data = {};

                
                data["id"] = $("#user_id").val();
                data["activity_message"] = "Edit user " + $("#user_name_edit").val();
               

                if ($("#automatic_password").prop('checked') == true) {
                    data["generate_pwd"] = 1;
                } else
                    data["generate_pwd"] = 0;

                if ($("#reset_password").prop('checked') == true) {
                    data["reset_pwd"] = 1;
                } else
                    data["reset_pwd"] = 0;

                data["full_name"] = $("#full_name_edit").val();
                data["user_name"] = $("#user_name_edit").val();
                data["email"] = $("#email_edit").val();
                data["password"] = $("#password_edit").val();
                data["company_name"] = $("#company_name_edit").val();
                data["phone"] = $("#phone_edit").val();
                data["start_date"] = $("#start_date_edit").val();
                data["expiry_date"] = $("#expiry_date_edit").val();
                data["user_role"] = $("#user_role_edit").val();
                data["language"] = $("#user_language_edit").val();
                data["task"] = "7";

                var jsondata = JSON.stringify(data);
                jsondata = jsondata.replace("'", "\\\\\'");
                //console.log(jsondata);
                $.post("../../modules/ajax/users.php", jsondata, function (result) {
                    //console.log(result);
                    if (result == "Successfull")
                        location.reload();
                     else
                        showMessage(result,2);
                });
            }
        });
     
    });





    function loadTable()
    {
        var data = {};
        data["task"] = "8";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/users.php", jsondata, function (result) {
            $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
            table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            table.page(page).draw(false);
        });
    }
    
    $('#update_cancel').click(function () {
    $('#user_edit_modal').modal('hide');
  });
    
    
    $(document).on("click", "#automatic_password", function ()
    {
        if ($("#automatic_password").is(':checked')) {
            $("#password-block").hide();
        } else {
            $("#password-block").show();
        }
    });

    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".user-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        user_name = $(this).parent('td').siblings(":eq( 0 )").text();
        var status_text = $(this).attr("data-text");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["id"] = id;
        data["status"] = status;
        data["task"] = "9";
        data["activity_message"] = status_text + " user " + user_name;
        var jsondata = JSON.stringify(data);
        page = table.page();
        $.post("../../modules/ajax/users.php", jsondata, function (result) {
            if (result == "Successfull")
                loadTable();
            else
                alert(result);
        });
    });
    /*change password*/
    $(document).on("click", ".user-change-password", function () {
        id = $(this).parent('td').parent('tr').data('id');        
        $("#id").val(id);
        
        $("#resetPassword").prop('disabled', true); 
        $("#form-change-password").trigger('reset');
        $('#changePasswordModal').modal('show');
        $("#reset-password-message").html("");
    });


    /*=====edit======*/
    $(document).on("click", ".user-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        user_name = $(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = id;
        data["task"] = "11";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/users.php", jsondata, function (result) {
           // $('.block-data[data-status="overview"]').hide();
          //  $('.block-data[data-status="form"]').show();
          //  $('.tab-link').removeClass('active');
          

            
            //$("#form").validate().resetForm();
            $("#messagebox_modal").html("");
            $("#messagebox_modal").removeClass("alert-danger"); 
            $("#messagebox_modal").addClass("alert-light");
            
            
            $("#user_edit_modal").modal('show');
            var response = JSON.parse(result);
            $("#full_name_edit").val(response.display_name);
            $("#user_name_edit").val(response.user_name);
            $("#user_name_edit").attr("disabled", true);
            $("#email_edit").val(response.email);
            $("#company_name_edit").val(response.company_name);
            $("#phone_edit").val(response.phone);
            $("#start_date_edit").val(response.validity_from_date);
            $("#expiry_date_edit").val(response.validity_to_date);
            $("#user_language_edit").val(response.language);
            $("#user_role_edit").val(response.user_role_id);
            $("#user_id").val(response.id);
            
            if(!response.alert_message=="")
            {
                showMessage(response.alert_message,2)
            }
            /*$("#password").val(response.password);
            $("#conform_password").val(response.password);
            $(".pwd-row").hide();
            $("#user_name").attr("disabled", true);*/

            $("#add-edit-button").val("Edit");
        });

    });
    
    function showMessage(msg,type)
    {
        
        if(type==1){
            $("#messagebox").html("<i class='fas fa-exclamation-triangle ml-2'></i>"+msg);
            $("#messagebox").removeClass("alert-light");
            $("#messagebox").addClass("alert-danger");    
        }
        else if(type==2)
        {
            $("#messagebox_modal").html("<i class='fas fa-exclamation-triangle ml-2'></i>"+msg);
            $("#messagebox_modal").removeClass("alert-light");
            $("#messagebox_modal").addClass("alert-danger"); 
        }
    }
    

    
    /*$(document).on("click", "#user-edit-save", function ()
    {
        var data = {};

        
        data["id"] = id;
        data["activity_message"] = "Edit user " + $("#user_name_edit").val();
        

        if ($("#automatic_password").prop('checked') == true) {
            data["generate_pwd"] = 1;
        } else
            data["generate_pwd"] = 0;

        if ($("#reset_password").prop('checked') == true) {
            data["reset_pwd"] = 1;
        } else
            data["reset_pwd"] = 0;

        data["full_name"] = $("#full_name_edit").val();
        data["user_name"] = $("#user_name_edit").val();
        data["email"] = $("#email_edit").val();
        data["password"] = $("#password_edit").val();
        data["company_name"] = $("#company_name_edit").val();
        data["phone"] = $("#phone_edit").val();
        data["start_date"] = $("#start_date_edit").val();
        data["expiry_date"] = $("#expiry_date_edit").val();
        data["user_role"] = $("#user_role_edit").val();
        data["language"] = $("#user_language_edit").val();
        data["task"] = "7";

        var jsondata = JSON.stringify(data);
        jsondata = jsondata.replace("'", "\\\\\'");
        console.log(jsondata);
        $.post("../../modules/ajax/users.php", jsondata, function (result) {
            console.log(result);
            if (result == "Successfull")
                location.reload();
            else if (result.includes("auto"))
            {
                alertMessage(result);
                location.reload();
            } else
                alertMessage(result);
        });
    });*/

</script>