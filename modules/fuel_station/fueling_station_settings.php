<?php
//Add setting_label in fueling_station_settings table
$page_title = "Fueling Station Settings";

include('../../includes/header.php');
$access = checkPageAccess("fuel_station_settings");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>


</ul>
<div class="header text-dark" id="pdf-report-header">Fueling Station</div>
<div class="row">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="system_settings">Settings</div>        
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

                        

            <div class="card" id="table-card">               
                <div class="card-body">  

                    <div class="block-data" data-status="system_settings" id="system_settings">                                      
                        <table class="table  table-bordered "id="table_system_settings"> 
                            <?php 
				$data["task"] = 7;
				$data["edit"] = $access["edit"];
		                $data["delete"]=$access["delete"];
		                parcxStationConfiguration($data);
				
			    ?>   
                        </table>    
                    </div>                  
                </div>             
            </div> 

        </div>          
    </section>
</div>
<script>
    var table_system_settings,table_manual_reasons,table_export_settings,table_interface_settings,table_maintenance_settings;
    var table_download_settings,table_upload_settings,table_system_settings;
    var page=0;
    
    $("#message-box").hide();
    function showMessage(msg)
    {
        $("#message-box").html(msg);
        $("#message-box").show();
    }


    function loadSettings(task, location)
    {
        $("#message-box").hide();
        var data = {};
        data["task"] = task;
	data["edit"] = "<?php echo $access["edit"]; ?>";
	data["delete"] = "<?php echo $access["delete"]; ?>";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        page=table_system_settings.page();
        
        $.post("ajax/station_config.php", jsondata, function (result) {           
            $("#" + location).html('<table  class="table  table-bordered " id="table_' + location + '">' + result + '</table>');
            if(location==="system_settings")
            {
                table_system_settings=$('#table_system_settings').DataTable({                
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "aaSorting": []
                });
                table_system_settings.page(page).draw(false);    
            }
            
        });
    }

    $(document).ready(function () {
        table_system_settings=$('#table_system_settings').DataTable({                
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

        
    });
   

    /* === enable disable === */
    var status;
    var id;    
    var system_setting_name;

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
            data["activity_message"]=status_text+" system settings "+system_setting_name;
            data["task"] = "8";
            var jsondata = JSON.stringify(data);
            $.post("ajax/station_config.php", jsondata, function (result) {
                if (result == "Successfull")
                    loadSettings(7, "system_settings");
                else
                    alert(result);
            });
        } else
        {
            loadSettings(7, "system_settings");
        }
    });


    
    

    /*=====edit======*/

    $(document).on("click", ".setting-edit", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        //setting_value = $(this).parent('td').siblings(":eq( 1 )").text();
        setting_value = $(this).parent('td').siblings(":eq( 1 )").data('val');
        system_setting_name=$(this).parent('td').siblings(":eq( 0 )").text();
        if ($(this).attr("data-text") === "Edit")
        {            
            setting_name = $(this).parent('td').parent('tr').data('name');
            var td=$(this).parent('td').siblings(":eq( 1 )");
            
            var data = {};
            data["id"] = id;
            data["setting_value"] = setting_value;
            data["setting_name"] = setting_name;
            data["task"] = "9";
            var jsondata = JSON.stringify(data);
            //alert(jsondata);
            $.post("ajax/station_config.php", jsondata, function (result)
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
            data["task"] = "10";
            var jsondata = JSON.stringify(data); 
            //console.log(jsondata);
            $.post("ajax/station_config.php", jsondata, function (result)
            {
                if (result == "Successfull")
                    loadSettings(7, "system_settings");
                else
                    alert(result);
            });                    
        }
    });


  

</script>

<?php include('../../includes/footer.php'); ?>
