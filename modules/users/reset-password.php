<?php
include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>
<div class="header text-dark" id="pdf-report-header">Reset Password</div>
<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<script>
    document.title = "Parcx Cloud | Reset Password";
</script>

<div class="content-wrapper desktop-container">
    <section class="content">
        <div class="container-wide">
            <div id="alert-div">
            </div>                 
            <form class="block-data card card-form-custom col-6 p-4" data-status="reset-password"  id="form">
                <h5 class="mt-0">Reset Password</h5>                    
                <div class="password-strength">		
                    <div class="row">
                        <div class="col form-group">
                            <label for="">Current Password</label>
                            <div class="input-group pw-view-btn" id="pvb-3">
                                <div class="input-group-prepend">
                                    <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                </div>
                                <input type="password" class="form-control" id="current_password" placeholder="" name="current-password" required>
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
                                <input type="password" class="form-control" id="password" placeholder="" name="password">
                            </div>
                        </div>
                        <div class="col form-group">
                            <label for="">Confirm Password</label>
                            <div class="input-group pw-view-btn" id="pvb-2">
                                <div class="input-group-prepend">
                                    <button class="btn btn-tertiary" type="button"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
                                </div>
                                <input type="password" class="form-control"  placeholder="" name="ConfirmPassword">
                            </div>                            
                        </div>
                    </div>
                    <small class="row w-100 form-text text-muted m-0">
                        <div id="pwd-container">
                            <div class="pwstrength_viewport_progress"></div>
                        </div>
                        <div class="col">Password must contain letters, numbers and special characters, and be at least 8 characters long</div>
                    </small>
                </div>  
                <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Reset Password" id="resetPassword">                                                         
            </form>          

        </div>
    </section>
</div>


<!-- password strength -->
<script src="../../plugins/password-strength/password.js"></script>
<script src="../../dist/js/password.js"></script>

<?php include('../../includes/footer.php'); ?>
<script>
  
    $(document).ready(function ()
    {
    $("input[type='submit']").prop('disabled', true);        

//Show hide password
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

//form validation       
        var formElement = $("#form")
        var $np = $('#password');

        formElement.validate({
            rules: {
                ConfirmPassword: {
                    equalTo: "#password",
                    required: function () {
                        return $np.val().length > 0
                    }
                }
            },
            messages: {
                ConfirmPassword: {
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
                data["id"] = $("#login_user_id").val();                
                data["current_password"] = $("#current_password").val();
                data["new_password"] = $("#password").val();  
                data["task"] = "10";                
                var jsondata = JSON.stringify(data);     
                console.log(jsondata);
                $.post("../ajax/users.php", jsondata, function (result) {
                    console.log(result);
                    alertMessage(result);
                });
            }
        });
    });

</script>        