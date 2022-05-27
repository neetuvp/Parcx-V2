<?php
$page_title = "Device Dashboard";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Settings</div>
<div class="row">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="system_settings">Facility</div>        
        <div class="tab-link" data-target="upload_settings">Cloud Upload</div>
        <div class="tab-link" data-target="download_settings">Cloud Download</div>
        <div class="tab-link" data-target="interface_settings">Interface</div>
        <div class="tab-link" data-target="export_settings">Export</div>
        <div class="tab-link" data-target="maintenance_settings">Maintenance</div>
        <div class="tab-link" data-target="manual_reasons">Manual movement reasons</div>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<div class="content-wrapper " >
    <section class="content">
        <div class="container-wide"> 

            <div id="message-box" class="alert alert-primary" role="alert" hidden>
                
            </div>  

            <form class="block-data card card-body col-md-4" data-status="interface_form" style="display:none;" id="form">  
                <div class="row">
                    <div class="col form-group">
                        <label for="">Interface Name</label>
                        <input type="text" class="form-control" id="interface_name"  name="interface_name" required="">
                    </div>
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label for="">Type</label>
                        <select id = "interface_type">
                            <option value="ssh">SSH</option>
                            <option value="ftp">FTP</option>
                        </select>
                    </div>
                </div>

                <div class="row">	
                    <div class="col form-group">
                        <label for="">Host</label>
                        <input type="text" class="form-control" id="host"  name="host" required="">
                    </div>
                </div> 

                <div class="row">	
                    <div class="col form-group">
                        <label for="">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required="">
                    </div>
                </div> 


                <div class="row">	
                    <div class="col form-group">
                        <label for="">Password</label>
                        <div class="input-group pw-view-btn" id="pvb-1">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><span class="fa fa-eye-slash"></span></div>
                            </div>
                            <input type="password" class="form-control" id="password" name="Password" required="">
                        </div>
                    </div>
                </div> 

                <div class="row" id = "show_ftp" style="display:none">	
                    <div class="col form-group">
                        <label for="" id="ftp_label">FTP Url</label>
                        <input type="text" class="form-control" id="url" autocomplete="off" placeholder="" name="url">
                    </div>
                </div> 

                <div class="row" id = "show_ssh">	
                    <div class="col form-group">
                        <label for="" id="ssh_label">SSH Folder Path</label>
                        <input type="text" class="form-control" id="path" autocomplete="off" placeholder="" name="path">
                    </div>
                </div> 


                <input type="submit" class="btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">
            </form>             

            <div class="card" id="table-card">               
                <div class="card-body">  

                    <div class="block-data" data-status="system_settings" id="system_settings">                                      
                        <table class="table  table-bordered "id="table_system_settings"> 
                            <?php echo parcxV2Settings(array("task" => "17")); ?>   
                        </table>    
                    </div> 
                    <div class="block-data" data-status="upload_settings" style="display:none;" id="upload_settings">                                                        
                        <table class="table  table-bordered " id="table_upload_settings"> 
                            <?php echo parcxV2Settings(array("task" => "25")); ?>   
                        </table>
                    </div> 
                    <div class="block-data" data-status="download_settings" style="display:none;" id="download_settings">               
                        <table class="table  table-bordered " id="table_download_settings">                      
                            <?php echo parcxV2Settings(array("task" => "28")); ?>
                        </table>
                    </div> 
                    <div class="block-data" data-status="maintenance_settings" style="display:none;" id="maintenance_settings">               
                        <table class="table  table-bordered " id="table_maintenance_settings">                     
                            <?php echo parcxV2Settings(array("task" => "22")); ?>
                        </table>
                    </div> 
                    <div class="block-data" data-status="interface_settings" style="display:none;" id="interface_settings">               
                        <table class="table  table-bordered " id="table_interface_settings" >                     
                            <?php echo parcxV2Settings(array("task" => "53")); ?>
                        </table>
                    </div> 
                    <div class="block-data" data-status="export_settings" style="display:none;" id="export_settings">               
                        <table class="table  table-bordered " id="table_export_settings">                     
                            <?php echo parcxV2Settings(array("task" => "49")); ?>
                        </table>
                    </div> 
                    
                    <div class="block-data" data-status="manual_reasons" style="display:none;" id="manual_reasons">               
                        <table class="table  table-bordered " id="table_manual_reasons">                     
                            <?php echo parcxV2Settings(array("task" => "41")); ?>
                        </table>
                    </div>                     
                </div>             
            </div> 

        </div>          
    </section>
</div>
<script>
    var ssh_interface = "<?php echo parcxV2Settings(array("task" => "50")) ?>";
    var ftp_interface = "<?php echo parcxV2Settings(array("task" => "51")) ?>";
    var table_system_settings,table_manual_reasons,table_export_settings,table_interface_settings,table_maintenance_settings;
    var table_download_settings,table_upload_settings,table_system_settings;
    var page=0;
    
    $("#message-box").hide();
    function showMessage(msg)
    {
        $("#message-box").html(msg);
        $("#message-box").show();
    }


    $(".pw-view-btn .input-group-prepend").on('click', function (event) {
        event.preventDefault();
        var currentElement = $(this).parents(".pw-view-btn");
        var id = $("#" + currentElement.attr('id'))
        var inputElement = id.find('input');
        var inputIcon = id.find('span');

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


    $("#interface_type").change(function ()
    {
        if ($('#interface_type').val() == 'ftp')
        {
            $("#show_ftp").show();
            $("#show_ssh").hide();
        } else
        {
            $("#show_ftp").hide();
            $("#show_ssh").show();
        }
    });

    function loadSettings(task, location)
    {
        $("#message-box").hide();
        var data = {};
        data["task"] = task;
        var jsondata = JSON.stringify(data);
        
        if(location==="system_settings")
            page=table_system_settings.page();
        else if(location==="manual_reasons")
            page=table_manual_reasons.page();
        else if(location==="export_settings")
            page=table_export_settings.page();
        else if(location==="interface_settings")
            page=table_interface_settings.page();
        else if(location==="maintenance_settings")
            page=table_maintenance_settings.page();
        else if(location==="download_settings")
            page=table_download_settings.page();
        else if(location==="upload_settings")
            page=table_upload_settings.page();            
        
        $.post("../../modules/ajax/settings.php", jsondata, function (result) {           
            $("#" + location).html('<table  class="table  table-bordered " id="table_' + location + '">' + result + '</table>');
            if(location==="system_settings")
                {
                table_system_settings=$('#table_system_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_system_settings.page(page).draw(false);    
                }
            else if(location==="manual_reasons")
                {
                table_manual_reasons=$('#table_manual_reasons').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_manual_reasons.page(page).draw(false);    
                }
            else if(location==="export_settings")
                {
                table_export_settings=$('#table_export_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_export_settings.page(page).draw(false);    
                }
            else if(location==="interface_settings")
                {
                table_interface_settings=$('#table_interface_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_interface_settings.page(page).draw(false);    
                }
            else if(location==="maintenance_settings")
                {
                table_maintenance_settings=$('#table_maintenance_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_maintenance_settings.page(page).draw(false);    
                }
            else if(location==="download_settings")
                {
                table_download_settings=$('#table_download_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_download_settings.page(page).draw(false);    
                }
            else if(location==="upload_settings")
                {
                table_upload_settings=$('#table_upload_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_upload_settings.page(page).draw(false);    
                }
        });
    }

    $(document).ready(function () {
        table_system_settings=$('#table_system_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                
        table_manual_reasons=$('#table_manual_reasons').DataTable({                
                "lengthMeservernu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        table_export_settings=$('#table_export_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        table_interface_settings=$('#table_interface_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        table_maintenance_settings=$('#table_maintenance_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        table_download_settings=$('#table_download_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        table_upload_settings=$('#table_upload_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
        
                
        $('.tab-link').on('click', function () {
            $("#table-card").show();
            $("#message-box").hide();
            var $target = $(this).data('target');
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
                    data["activity_message"]="Add interface settings "+$("#interface_name").val();
                    }
                else
                    {
                    data["id"] = interface_id;
                    data["activity_message"]="Edit interface settings "+interface_name;
                    }


                data["name"] = $("#interface_name").val();
                data["interface_type"] = $("#interface_type").val();
                data["host"] = $("#host").val();
                data["username"] = $("#username").val();
                data["password"] = $("#password").val();
                if ($("#interface_type").val() == "ssh")
                    data["folder_path"] = $("#path").val();
                else
                    data["url"] = $("#url").val();

                data["task"] = "55";
                var jsondata = JSON.stringify(data);                

            $.post("../../modules/ajax/settings.php", jsondata, function (result) {                
                if (result == "Successfull")
                    {
                    loadSettings(53, "interface_settings");
                    $("#table-card").show();
                    $('.block-data').css('display', 'none');
                    $('.block-data[data-status="interface_settings"]').fadeIn('slow');
                    }
                else
                    alert(result);
                    
            });                                 
            }
        });
    });
    
    
    $(document).on("click", ".manual-movement-add-btn", function ()
        {
        
            var newRow = $("<tr>");
            var cols = "";                                          
            cols += '<td><input type = "text" id = "manual_reason"></td>';            
            cols += '<td><button type="button" class="btn btn-success btn-add-manual-movement mr-1" title="Save"><i class="fas fa-floppy-o"></i></button><button type="button" class="btn btn-danger manual-movement-enable-disable-btn" data-text="Cancel" title="Cancel"><i class="fas fa-window-close"></i></button></td>';               
                
            newRow.append(cols);
            
            $("#manual_reasons table").prepend(newRow);
            
            
        });
        
    $(document).on("click", ".btn-add-manual-movement", function ()
        {        
        if($("#manual_reason").val()=="")
            {
            showMessage("Please enter manual movement reason");
            return;    
            }
        else
            {
            setting_value = $("#manual_reason").val().trim();
            var data = {};
            data["id"] = "0";
            data["reason"] = setting_value;            
            data["task"] = "42";
            data["activity_message"]="Add manual movement reason "+setting_value;
            var jsondata = JSON.stringify(data);            
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(41, "manual_reasons");
                else
                    alert(result);
            });       
            }
        });


    function showInterfaceForm()
    {
        $("#table-card").hide();
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="interface_form"]').fadeIn('slow');
    }
    $(document).on("click", ".interface-add", function ()
    {
        $("#form").trigger('reset');
        $("#show_ftp").hide();
        $("#show_ssh").show();
        $("#add-edit-button").val("Submit");
        showInterfaceForm();        
    });

    /* === enable disable === */
    var status;
    var id;
    var interface_id;
    var interface_name;
    var download_setting_name;
    var export_setting_name;
    var upload_setting_name;
    var manual_reason_name;
    var system_setting_name;
    var maintenance_setting_name;
    

    $(document).on("click", ".manual-movement-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        manual_reason_name=$(this).parent('td').siblings(":eq( 0 )").text();;
        var status_text = $(this).attr("data-text");
        if (status_text != "Cancel")
        {
            if (status_text == "Disable")
                status = 0;
            else
                status = 1;

            var data = {};
            data["id"] = id;
            data["status"] = status;
            data["task"] = "44";
            data["activity_message"]=status_text+" manual reason "+manual_reason_name;
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                if (result == "Successfull")
                    loadSettings(41, "manual_reasons");
                else
                    alert(result);
            });
        } else
        {
            loadSettings(41, "manual_reasons");
        }
    });
    
    
    $(document).on("click", ".setting-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        system_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        
        var status_text = $(this).attr("data-text");
        if (status_text != "Cancel")
        {
            if (status_text == "Disable")
                status = 0;
            else
                status = 1;

            var data = {};
            data["id"] = id;
            data["status"] = status;
            data["activity_message"]=status_text+" system seettings "+system_setting_name;
            data["task"] = "19";
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                if (result == "Successfull")
                    loadSettings(17, "system_settings");
                else
                    alert(result);
            });
        } else
        {
            loadSettings(17, "system_settings");
        }
    });


    $(document).on("click", ".upload-setting-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        upload_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var status_text = $(this).attr("data-text");
        if (status_text != "Cancel")
        {
            if (status_text == "Disable")
                status = 0;
            else
                status = 1;

            var data = {};
            data["id"] = id;
            data["status"] = status;
            data["task"] = "27";
            data["activity_message"]=status_text+" cloud upload settings "+upload_setting_name;
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                if (result == "Successfull")
                    loadSettings(25, "upload_settings");
                else
                    alert(result);
            });
        } else
        {
            loadSettings(25, "upload_settings");
        }
    });

    $(document).on("click", ".maintenance-setting-cancel-btn", function ()
    {
        loadSettings(22, "maintenance_settings");
    });
    
    

    $(document).on("click", ".download-setting-enable-disable-btn", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        download_setting_name = $(this).parent('td').parent('tr').data('name');
        var status_text = $(this).attr("data-text");
        if (status_text != "Cancel")
        {
            if (status_text == "Disable")
                status = 0;
            else
                status = 1;

            var data = {};
            data["id"] = id;
            data["status"] = status;
            data["task"] = "30";
            data["activity_message"]=status_text+" cloud download settings "+download_setting_name;
            
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result) {
                if (result == "Successfull")
                    loadSettings(28, "download_settings");
                else
                    alert(result);
            });
        } else
        {
            loadSettings(28, "download_settings");
        }
    });

    /*=====edit======*/

    $(document).on("click", ".interface-edit", function ()
    {
        interface_id = $(this).parent('td').parent('tr').data('id');
        interface_name=$(this).parent('td').siblings(":eq( 0 )").text();
        var data = {};
        data["id"] = interface_id;
        data["task"] = "54";
        data["activity_message"]="Edit interface settings "+interface_name;
        var jsondata = JSON.stringify(data);

        $.post("../../modules/ajax/settings.php", jsondata, function (result) {            
            var response = JSON.parse(result);
            $("#interface_name").val(response.interface_name);
            $("#interface_type").val(response.interface_type);
            $("#host").val(response.host);
            $("#username").val(response.username);
            $("#password").val(response.password);
            $("#url").val(response.url);
            $("#path").val(response.folder_path);
            if (response.interface_type == "ssh")
            {
                $("#show_ftp").hide();
                $("#show_ssh").show();

            } else if (response.interface_type == "ftp")
            {
                $("#show_ftp").show();
                $("#show_ssh").hide();

            }

            $("#add-edit-button").val("Edit");

            showInterfaceForm();
        });
    });

    $(document).on("click", ".manual-movement-edit", function ()
        {
        id = $(this).parent('td').parent('tr').data('id');
        manual_reason_name=$(this).parent('td').siblings(":eq( 0 )").text();;
        if ($(this).attr("data-text") === "Edit")
        {
            setting_value = $(this).parent('td').siblings(":eq( 0 )").text();            
            $(this).parent('td').siblings(":eq( 0 )").html("<input type = 'text' id = 'manual_reason" + id + "'  value = '" + setting_value + "'>");

            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");
            $(this).parent('td').parent('tr').find(".manual-movement-enable-disable-btn").attr("data-text", "Cancel");
            $(this).parent('td').parent('tr').find(".manual-movement-enable-disable-btn").attr("title", "Cancel");
            $(this).parent('td').parent('tr').find(".manual-movement-enable-disable-btn").html('<i class="fas fa-window-close"></i>')
            $(this).parent('td').parent('tr').find(".manual-movement-enable-disable-btn").removeClass("btn-success");
            $(this).parent('td').parent('tr').find(".manual-movement-enable-disable-btn").addClass("btn-danger");
        } else
        {
            setting_value = $("#manual_reason" + id).val().trim();
            var data = {};
            data["id"] = id;
            data["reason"] = setting_value;            
            data["task"] = "42";
            data["activity_message"]="Edit manual movement reason "+manual_reason_name;
            
            var jsondata = JSON.stringify(data);            
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(41, "manual_reasons");
                else
                    alert(result);
            });                    
        }
    });
    
    $(document).on("click", ".setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        setting_value = $(this).parent('td').siblings(":eq( 1 )").text();
        system_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        if ($(this).attr("data-text") === "Edit")
        {            
            setting_name = $(this).parent('td').parent('tr').data('name');
            var td=$(this).parent('td').siblings(":eq( 1 )");
            
            var data = {};
            data["id"] = id;
            data["setting_value"] = setting_value;
            data["setting_name"] = setting_name;
            data["task"] = "56";
            var jsondata = JSON.stringify(data);              
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
               td.html(result);
            }); 
                        
            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");
            
            if($(this).parent('td').parent('tr').find(".setting-enable-disable-btn").length==0)
                $(this).after("<button type='button' class='btn btn-danger setting-enable-disable-btn ml-2' data-text='Cancel'><i class='fas fa-window-close'></li></button>");
            else
                {
                $(this).parent('td').parent('tr').find(".setting-enable-disable-btn").attr("data-text", "Cancel");
                $(this).parent('td').parent('tr').find(".setting-enable-disable-btn").attr("title", "Cancel");
                $(this).parent('td').parent('tr').find(".setting-enable-disable-btn").html('<i class="fas fa-window-close"></i>')
                $(this).parent('td').parent('tr').find(".setting-enable-disable-btn").removeClass("btn-success");
                $(this).parent('td').parent('tr').find(".setting-enable-disable-btn").addClass("btn-danger");
                }
        } else
        {
            setting_value = $("#setting" + id).val().trim();
            var data = {};
            data["id"] = id;
            data["setting_value"] = setting_value;
            data["setting_name"] = setting_name;
            
            data["activity_message"]="Edit system setting "+system_setting_name;
            data["task"] = "18";
            var jsondata = JSON.stringify(data);            
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(17, "system_settings");
                else
                    alert(result);
            });                    
        }
    });

    $(document).on("click", ".maintenance-setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        maintenance_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        if ($(this).attr("data-text") === "Edit")
        {
            setting_value = $(this).parent('td').siblings(":eq( 1 )").text();
            $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'text' id = 'maintenance" + id + "'  value = '" + setting_value + "'>");
            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");
            $(this).after("<button type='button' class='btn btn-danger maintenance-setting-cancel-btn ml-2' ><i class='fas fa-window-close'></li></button>");
        } else
        {
            setting_value = $("#maintenance" + id).val().trim();
            var data = {};
            data["id"] = id;
            data["setting_value"] = setting_value;
            data["task"] = "23";
            data["activity_message"]="Edit maintenance setting "+maintenance_setting_name;
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(22, "maintenance_settings");
                else
                    alert(result);
            });
                   
        }
    });

    $(document).on("click", ".upload-setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        upload_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        if ($(this).attr("data-text") === "Edit")
        {
            var obj = $(this).parent('td').siblings(":eq( 1 )").find('input:checkbox');
            if (obj.attr("checked") == "checked")
            {
                $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'checkbox' id = 'dc" + id + "' checked>");
            } else
            {
                $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'checkbox' id = 'dc" + id + "'>");
            }

            setting_value = $(this).parent('td').siblings(":eq( 2 )").text();
            $(this).parent('td').siblings(":eq( 2 )").html("<input type = 'text' id = 'limittext" + id + "'  value = '" + setting_value + "'>");

            setting_value = $(this).parent('td').siblings(":eq( 3 )").text();
            $(this).parent('td').siblings(":eq( 3 )").html("<input type = 'text' id = 'intervaltext" + id + "'  value = '" + setting_value + "'>");


            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");

            $(this).parent('td').parent('tr').find(".upload-setting-enable-disable-btn").attr("data-text", "Cancel");
            $(this).parent('td').parent('tr').find(".upload-setting-enable-disable-btn").attr("title", "Cancel");
            $(this).parent('td').parent('tr').find(".upload-setting-enable-disable-btn").html('<i class="fas fa-window-close"></i>')
            $(this).parent('td').parent('tr').find(".upload-setting-enable-disable-btn").removeClass("btn-success");
            $(this).parent('td').parent('tr').find(".upload-setting-enable-disable-btn").addClass("btn-danger");
        } else
        {

            var data = {};
            data["id"] = id;
            if ($('#dc' + id).is(":checked"))
                data["dc"] = "1";
            else
                data["dc"] = "0";
            data["cloudtask"] = $('#tasktext' + id).val();
            data["limit"] = $('#limittext' + id).val();
            data["interval"] = $('#intervaltext' + id).val();
            data["task"] = "26";
            data["activity_message"]="Edit cloud upload setting "+upload_setting_name;
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(25, "upload_settings");
                else
                    alert(result);
            });
        }
    });

    $(document).on("click", ".export-setting-cancel-btn", function ()
    {
        loadSettings(49, "export_settings");
    });


    $(document).on("click", ".export-setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        export_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        if ($(this).attr("data-text") === "Edit")
        {
            $(this).parent('td').parent('tr').find('input:checkbox').removeAttr("disabled");
            setting_value = $(this).parent('td').siblings(":eq( 3 )").text();
            $(this).parent('td').siblings(":eq( 3 )").html("<select id = 'ssh_interface" + id + "'>" + ssh_interface + "</select>");
            $("#ssh_interface" + id + " option:contains(" + setting_value + ")").attr('selected', 'selected');


            setting_value = $(this).parent('td').siblings(":eq( 5 )").text();
            $(this).parent('td').siblings(":eq( 5 )").html("<select id = 'ftp_interface" + id + "'>" + ftp_interface + "</select>");
            $("#ftp_interface" + id + " option:contains(" + setting_value + ")").attr('selected', 'selected');

            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");
            $(this).after("<button type='button' class='btn btn-danger export-setting-cancel-btn ml-2' ><i class='fas fa-window-close'></li></button>");
        } else
        {
            var data = {};
            data["id"] = id;
            if ($("#export_check" + id).prop("checked") == true)
            {
                if ($("#ssh_check" + id).prop("checked") == false && $("#ftp_check" + id).prop("checked") == false)
                {
                    showMessage("Please enable SSH/FTP Interface");
                    return;
                }
                data["export_flag"] = 1;
            } else
                data["export_flag"] = 0;

            if ($("#ftp_check" + id).prop("checked") == true)
            {
                data['ftp'] = 1;
                data['ftp_interface_id'] = $('#ftp_interface' + id).val();
            } else
            {
                data['ftp'] = 0;
                data['ftp_interface_id'] = 0;
            }

            if ($("#ssh_check" + id).prop("checked") == true)
            {
                data['ssh'] = 1;
                data['ssh_interface_id'] = $('#ssh_interface' + id).val();
            } else
            {
                data['ssh'] = 0;
                data['ssh_interface_id'] = 0;
            }

            data["task"] = 52;
            data["activity_message"]="Edit export setting "+export_setting_name;
            var jsondata = JSON.stringify(data);            
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(49, "export_settings");
                else
                    alert(result);
            });
        }

    });

    $(document).on("click", ".download-setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        download_setting_name = $(this).parent('td').parent('tr').data('name');
        if ($(this).attr("data-text") === "Edit")
        {
            var obj = $(this).parent('td').siblings(":eq( 1 )").find('input:checkbox');
            if (obj.attr("checked") == "checked")
            {
                $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'checkbox' id = 'dcdownload" + id + "' checked>");
            } else
            {
                $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'checkbox' id = 'dcdownload" + id + "'>");
            }


            setting_value = $(this).parent('td').siblings(":eq( 2 )").text();
            $(this).parent('td').siblings(":eq( 2 )").html("<input type = 'text' id = 'limittext" + id + "'  value = '" + setting_value + "'>");

            setting_value = $(this).parent('td').siblings(":eq( 3 )").text();
            $(this).parent('td').siblings(":eq( 3 )").html("<input type = 'text' id = 'intervaltext" + id + "'  value = '" + setting_value + "'>");


            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");

            $(this).parent('td').parent('tr').find(".download-setting-enable-disable-btn").attr("data-text", "Cancel");
            $(this).parent('td').parent('tr').find(".download-setting-enable-disable-btn").attr("title", "Cancel");
            $(this).parent('td').parent('tr').find(".download-setting-enable-disable-btn").html('<i class="fas fa-window-close"></i>');
            $(this).parent('td').parent('tr').find(".download-setting-enable-disable-btn").removeClass("btn-success");
            $(this).parent('td').parent('tr').find(".download-setting-enable-disable-btn").addClass("btn-danger");
        } else
        {
            var dcdownload = 0;
            if ($('#dcdownload' + id).is(":checked"))
            {
                dcdownload = 1;
            }
            var data = {};
            data["id"] = id;
            data["dc"] = dcdownload;
            data["cloudtask"] = $('#tasktext' + id).val();
            data["limit"] = $('#limittext' + id).val();
            data["interval"] = $('#intervaltext' + id).val();
            data["task"] = "29";
            data["activity_message"]="Edit cloud download setting "+download_setting_name;
            var jsondata = JSON.stringify(data);
            $.post("../../modules/ajax/settings.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(28, "download_settings");
                else
                    alert(result);
            });                    
        }
    });
</script>

<?php include('../../includes/footer.php'); ?>