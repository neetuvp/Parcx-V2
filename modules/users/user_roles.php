<?php
$page_title = "User Roles";

include('../../includes/header.php');
$access = checkPageAccess("user_role");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Manage User Roles</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">View User Role</div>
        <div class="tab-link" data-target="form">Add New User Role</div>        
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<link rel="stylesheet" href="<?= URL ?>dist/css/userrole.css">

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!-- add/update carpark form --> 
            <form class="block-data card card-body col-md-8" data-status="form"  id="form" style="display:none;">                 
                <div class="row">
                    <div class="col form-group">
                        <label for="">User Role Name</label>
                        <input type="text" class="form-control" id="user_role_name" placeholder=""  required>
                    </div>
                </div>	                
                <?php
                $data["task"] = 2;
                $data["user_role_id"]= $userRollId;
                parcxV2UserManagement($data);
                ?>

                <input type="submit" class="btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>

            <!-- carpark table -->         
            <div class="block-data" data-status="overview" >
                <div class="card card-body col-md-8" >               
                    <?php
                    $data["task"] = 4;
                    parcxV2UserManagement($data);
                    ?>
                </div>             
            </div>             

        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>
<script>
    var id;
    var menuid;
    

    $(document).ready(function ()
    {

        $('.accordion').click(function (e)
        {
            e.stopPropagation;
            var t = e.target.tagName.toLowerCase();
            if (t === 'div')
            {
                this.classList.toggle("active");
                $(this).find(".panel").toggle('panelshow');
            }
            return true;
        });


        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#form").trigger('reset');
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
                var i = 0;
                var menu = new Array();
                data["name"] = $("#user_role_name").val();
                data["activity_message"]="Add user role "+data["name"];

                /*formElement.find('input[type=checkbox]').each(function () {
                    if ($(this).is(":checked") == true)
                    {
                        menu[i] = $(this).attr('id')
                        i++;
                    }
                });*/
                $('[id^="menuadd-"]').each(function() {
                    menu_data = {};
                    menuid = $(this).attr('id');
                    //console.log(menuid);
                    menu_data["id"] = menuid.split('-')[1];
                    selector_view = menuid.split('-')[1]+"-view";
                    selector_add = menuid.split('-')[1]+"-add";
                    selector_edit = menuid.split('-')[1]+"-edit";
                    selector_delete = menuid.split('-')[1]+"-delete";
                    //view
                    if( $('#'+selector_view).length )
                    {
                        if ($('#'+selector_view).is(":checked") === true)
                        {
                            menu_data["view"]=1;
                        }
                        else
                        {
                            menu_data["view"]=0;
                        }
                    }
                    else
                    {
                        menu_data["view"]=0;
                    }

                    //add
                    if( $('#'+selector_add).length )
                    {
                        if ($('#'+selector_add).is(":checked") === true)
                        {
                            menu_data["add"]=1;
                        }
                        else
                        {
                            menu_data["add"]=0;
                        }
                    }
                    else
                    {
                        menu_data["add"]=0;
                    }

                    //edit
                    if( $('#'+selector_edit).length )
                    {
                        if ($('#'+selector_edit).is(":checked") === true)
                        {
                            menu_data["edit"]=1;
                        }
                        else
                        {
                            menu_data["edit"]=0;
                        }
                    }
                    else
                    {
                        menu_data["edit"]=0;
                    }

                    //delete
                    if( $('#'+selector_delete).length )
                    {
                        if ($('#'+selector_delete).is(":checked") === true)
                        {
                            menu_data["delete"]=1;
                        }
                        else
                        {
                            menu_data["delete"]=0;
                        }
                    }
                    else
                    {
                        menu_data["delete"]=0;
                    }

                    menu[i] = menu_data;
                    i++;
                });
                data["menu"] = menu;
                data["task"] = 3;
                var jsondata = JSON.stringify(data);
                console.log(jsondata);
                $.post("../../modules/ajax/users.php", jsondata, function (result) {
                    console.log(result);
                    if (result == "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }
        });

    });



    $(document).on("click", ".edit-user-role", function ()
    {
        
        id = $(this).data('id');        
        if ($(this).attr('title') == "Edit")
            {
            $(this).attr('title', 'Update');
             $(this).attr('value', 'Update');
             $("#editrole"+id).html("Update");
            $(this).closest("#parent").find('input[type=checkbox]').prop('disabled', false);
            $(this).closest("#parent").find('input[type=text]').prop('disabled', false);
            } 
        else
            {
            $(this).attr('title', 'Edit');
            $("#editrole"+id).html("Edit");
            $(this).attr('value', 'Edit');
            $(this).closest("#parent").find('input[type=checkbox]').prop('disabled', true);
            $(this).closest("#parent").find('input[type=text]').prop('disabled', true);
            var data = {};
            var i = 0;
            var selector = "";
            var menu = new Array();
            var menu_data = {};
            data["user_role_id"] = id;
            data["user_role_name"]=$("#user_role_name_"+id).val();
            data["activity_message"]="Edit user role "+id;
            
            
            $('[id^="menu-"]').each(function() {
                menu_data = {};
                menuid = $(this).attr('id');
                //console.log(menuid);
                menu_data["id"] = menuid.split('-')[1];
                selector_view = id+"-"+menuid.split('-')[1]+"-view";
                selector_add = id+"-"+menuid.split('-')[1]+"-add";
                selector_edit = id+"-"+menuid.split('-')[1]+"-edit";
                selector_delete = id+"-"+menuid.split('-')[1]+"-delete";
                //view
                if( $('#'+selector_view).length )
                {
                    if ($('#'+selector_view).is(":checked") === true)
                    {
                        menu_data["view"]=1;
                    }
                    else
                    {
                        menu_data["view"]=0;
                    }
                }
                else
                {
                    menu_data["view"]=0;
                }
                
                //add
                if( $('#'+selector_add).length )
                {
                    if ($('#'+selector_add).is(":checked") === true)
                    {
                        menu_data["add"]=1;
                    }
                    else
                    {
                        menu_data["add"]=0;
                    }
                }
                else
                {
                    menu_data["add"]=0;
                }
                
                //edit
                if( $('#'+selector_edit).length )
                {
                    if ($('#'+selector_edit).is(":checked") === true)
                    {
                        menu_data["edit"]=1;
                    }
                    else
                    {
                        menu_data["edit"]=0;
                    }
                }
                else
                {
                    menu_data["edit"]=0;
                }
                
                //delete
                if( $('#'+selector_delete).length )
                {
                    if ($('#'+selector_delete).is(":checked") === true)
                    {
                        menu_data["delete"]=1;
                    }
                    else
                    {
                        menu_data["delete"]=0;
                    }
                }
                else
                {
                    menu_data["delete"]=0;
                }
                
                menu[i] = menu_data;
                i++;
            });
            /*$(this).closest("#parent").find('input[type=checkbox]').each(function ()
            {
                if ($(this).is(":checked") === true)
                {
                    menu_data = {};
                    menuid = $(this).attr('id');
                    menu_data["id"] = menuid.split('-')[1];
                    menu_data["type"] = menuid.split('-')[2];
                    menu[i] = menu_data;
                    i++;
                }
            });*/
            data["menu"] = menu;
            data["task"] = 5;
            var jsondata = JSON.stringify(data);
            //console.log(jsondata);
            $.post("../../modules/ajax/users.php", jsondata, function (result) {
                //alert(result);
                if (result == "Successfull")
                    location.reload();
                else
                    alert(result);
            });
        }

    });
    
    $(document).on("click", ".role-enable-disable-btn", function ()
    {
        var data = {};
        var status;
        id = $(this).data('id'); 
        if ($(this).attr('title') == "Disable")
        {
            status = 0;
        }
        else
        {
            status = 1;
        }
        data["id"] = id;
        data["status"] = status;
        data["task"] = "13";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/users.php", jsondata, function (result) {
                if (result == "Successfull")
                    location.reload();
                else
                    alert(result);
            });
    });



</script>
