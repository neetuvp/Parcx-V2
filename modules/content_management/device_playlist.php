<?php
$page_title = "Playlist";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Device Playlist</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <!--<div class="tab-link" data-target="form">Add Playlist to Device</div>-->
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>


</style>
<!-- Modal  -->
<!-- Modal -->
<div class="modal fade" id="view_playlist_modal"  role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Manage Playlist</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-alarm-modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="playlist-modal-body">  
                               
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<div class="content-wrapper">
    <section class="content">
        
        <div class="container-wide" >
            <div class="row" id="screen-list-block" >
                
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
    table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
    
    
    //Load data
function loadPage()
    {
        var data={};
        data["task"]=1;
        var jsondata = JSON.stringify(data);           
           $.post("../../modules/ajax/playlist.php",jsondata,function(data){                       
                    $("#screen-list-block").html(data);                    
           })
           .fail(function(jqxhr,status,error){
               alert("Error: "+error);

           }); 
    }
    loadPage(); 
    $(document).ready(function ()
    {
        //$("#form-edit-user").validate();
        
        //table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                //alert("here");
                $("#left").html("");
                $("#right").html("");
                $("form[name=form1]").trigger('reset');
                //$('#form')[0].reset();
                var data = {};
 
                data["task"] = "4";
                var jsondata = JSON.stringify(data);
                $.post("../../modules/ajax/cms.php", jsondata, function (result) {
                    $("#left").html(result)
                });
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
            playlist_name: {
                required: true,
                minlength: 3,
                maxlength: 100
            }
        };



        formElement.validate({
            rules: rules_set,
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
                var div_id="";
                data["playlist_name"] = $("#playlist_name").val();
                data["description"] = $("#description").val();
                var video_list="";
                $('#right > div').map(function() {
                    div_id = this.id;
                    div_id = div_id.substring(1)
                    video_list = video_list+div_id+",";
                });
                var l = video_list.length;
                video_list = video_list.substring(0,l-1);
                data["video_list"] = video_list;
                data["task"] = "5";
                var jsondata = JSON.stringify(data);
                console.log(jsondata);
                $.post("../../modules/ajax/cms.php", jsondata, function (result) {
                    console.log(result);
                    if (result === "Successfull")
                        location.reload();
                    else
                        alert(result);
                });
            }
        });
     });





    function loadTable()
    {
        var data = {};
        data["task"] = "1";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
            table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            table.page(page).draw(false);
        });
    }
    
   


    /* === enable disable product === */
    var status;
    var id;
    $(document).on("click", ".playlist-enable-disable-btn", function ()
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
        data["task"] = "2";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            if (result === "Successfull")
                loadTable();
            else
                alert(result);
        });
    });

    
    $(document).on("click", ".manage-playlist-button", function ()
    {
        var id = $(this).attr("data-id");
        var data = {};
        data["device_id"] = id;
        data["task"] = "2";
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("../../modules/ajax/playlist.php", jsondata, function (result) {
            $("#view_playlist_modal").modal('show');
            $(".modal-body").html(result);
            $(".pl-options").hide();
            
        });
        event.preventDefault();
    });
    
   
    
   /* function drag(ev) {
        console.log(ev.target.id);
        ev.dataTransfer.setData("div", ev.target.id);
    }
    
    function drop(ev) {
        var _target = $("#" + ev.target.id);
        if ($(_target).hasClass("nodrop")) {
            ev.preventDefault();
            var src = document.getElementById(ev.dataTransfer.getData("div"));
            var srcParent = src.parentNode;
            var tgt = ev.currentTarget.firstElementChild;

            ev.currentTarget.replaceChild(src, tgt);
            srcParent.appendChild(tgt);
        } else {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("div");
            ev.target.appendChild(document.getElementById(data));
        }
        
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }*/
    
    $(document).on("click", ".view-details-button", function ()
    {
        var id = $(this).attr("data-id");
        var data = {};
        data["id"] = id;
        data["task"] = "8";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#view_playlist_modal").modal('show');
           // $(".modal-body").html(result);
            $(".modal-body").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
            table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            table.page(page).draw(false);
            
        });

    });

    $('#view_playlist_modal').on('shown.bs.modal', function (e) {
     $('.scheduledate').daterangepicker(
        {
            timePicker: true,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });
    
    $(document).on("click", ".playlist-checkbox", function ()
    {
        var id = $(this).attr("data-id");
        //alert(id)
        if($(this).is(":checked")) {
            $('.modal-body').find("#pl-options-"+id).show();
            //$("#pl-options-"+id).show();
        } else {
            //$("#pl-options-"+id).hide();
            $('.modal-body').find("#pl-options-"+id).hide();
        }
            
            //$("#expand-div-p1").removeClass("d-none");
        //}
                
    });
 
    
    $(document).on("click", ".add-playlist", function (e)
    {
        var id = $(this).attr("data-id");
        e.preventDefault();
        
        
       $('#playlist-modal-body').find("#right").append("<div class='btn btn-block btn-info btn-lg bg-gray playlist-right' data-playlist="+id+" id='right-v"+id+"'>"+$('#v'+id).html()+"</div>");
       $('#playlist-modal-body').find("#pl-options-"+id).addClass("hidden");
       $('#playlist-modal-body').find("#v"+id).addClass("hidden");//;.remove();
       //$('#playlist-modal-body').find("#c"+id).addClass("hidden");//.remove();
       $('#playlist-modal-body').find("#span-cbox-"+id).addClass("hidden");
       $('#playlist-modal-body').find("#span-del-"+id).removeClass("hidden");
    });
    
    $(document).on("click", ".del-playlist", function (e)
    {
        var id =$(this).attr("data-id");
        e.preventDefault();
        
        $('#playlist-modal-body').find("#right-v"+id).remove();
        $('#playlist-modal-body').find("#pl-options-"+id).removeClass("hidden");
        $('#playlist-modal-body').find("#v"+id).removeClass("hidden");//;.remove();
        //$('#playlist-modal-body').find("#c"+id).removeClass("hidden");//.remove();
        $('#playlist-modal-body').find("#span-cbox-"+id).removeClass("hidden");
        $('#playlist-modal-body').find("#span-del-"+id).addClass("hidden");
        $('#playlist-modal-body').find("#c"+id).prop('checked', false); // Unchecks it
        $('#playlist-modal-body').find("#pl-options-"+id).hide();
    });
    
    $(document).on("click", ".playlist-details-div", function (e)
    {
        id = $(this).attr("data-id");//1;//$(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "3";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
             $('#playlist-modal-body').find("#playlist-details-"+id).html(result);
            
        });
    });
    
    
    $(document).on("click", "#save-screen-playlist", function (e)
    {
        var playlist=[];
        var i=0;
        var id=0;
        var currentElement;
        var repeat_playlist=0;
        var daterange_playlist;
        var from_date;
        var to_date;
        var name;
        screen_id = $(this).attr("data-screen");
        $('.playlist-right').each(function(){
            repeat_playlist=0;
            currentElement = $(this);
            id = currentElement.attr("data-playlist") ;
            name = $('#playlist-modal-body').find("#l"+id).html();
            if ($('#playlist-modal-body').find("#repeat-p"+id).is(":checked"))
            {
                repeat_playlist = 1;  
            }
            
            daterange_playlist = $('#playlist-modal-body').find('#date-p'+id).val();
            from_date = daterange_playlist.substring(0, 19);
            to_date = daterange_playlist.substring(22, 41);
            
            playlist.push({
                id: id, 
                repeat:repeat_playlist,
                start:from_date,
                end:to_date,
                name:name
            });
         });
        var data = {};
        data["device_id"] = screen_id;
        data["task"] = "3";
        data["playlists"]=playlist;
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../../modules/ajax/playlist.php", jsondata, function (result) {
             //location.reload();
            
        });
    });
    
    /*$(document).on("click", "#add-edit-button", function ()
    {
        var data = {};
        var div_id="";
        data["playlist_name"] = $("#playlist_name").val();
        data["description"] = $("#description").val();
        var video_list="";
        $('#right > div').map(function() {
            div_id = this.id;
            div_id = div_id.substring(1)
            video_list = video_list+div_id+",";
        });
        var l = video_list.length;
        video_list = video_list.substring(0,l-1);
        data["video_list"] = video_list;
        data["task"] = "5";
        var jsondata = JSON.stringify(data);
        console.log(jsondata);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            console.log(result);
            if (result === "Successfull")
                loadTable();
            else
                alert(result);
        });
         
    });*/
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
