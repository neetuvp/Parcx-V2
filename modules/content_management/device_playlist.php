<?php
//Add in footer:  plugins/timepicker/jquery.timepicker.min.js"></script>
//plugins/timepicker/jquery.timepicker.min.css" />
$page_title = "Device sPlaylist";

include('../../includes/header.php');
$access = checkPageAccess("device_playlist");
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

<!-- Modal  -->
<!-- Modal -->
<div class="modal fade" id="view_playlist_modal"  role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true" style="z-index: 1999;">
    <div class="modal-dialog modal-xl" role="document" style="width:1200px;">
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

<div class="content-wrapper container-wide">
    <section class="content">
        
        <div class="block-data col-md-12" data-status="overview">
            <div class="card" >               
                <div class="card-body" id="div-RecordsTable">     
                    <table id="RecordsTable" class="table  table-bordered"> 
                        <?php
                        $data["task"] = 6;
                        $data["edit"] = $access["edit"];
                        parcxContentManagement($data);
                        ?> 
                    </table>
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
    var user_name;
    var page = 0;
    var edit=false;
    table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
    
    //Load data
function loadTable()
{
    var data = {};
    data["task"] = "6";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/cms.php", jsondata, function (result) {
        $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
        table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
        table.page(page).draw(false);
    });
}
    
    //2022-03-01 23:00:00 - 2022-03-28 23:59:59
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




    
    $(document).on("click", ".manage-playlist-button", function ()
    {
        var id = $(this).attr("data-id");
        var name = $(this).attr("data-name");
        var data = {};
        data["device_id"] = id;
        data["device_name"]=name;
        data["task"] = "7";
	data["edit"] ="<?php echo $access["edit"]; ?>";
        //data["task"] = "2";
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        //$.post("../../modules/ajax/playlist.php", jsondata, function (result) {
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#view_playlist_modal").modal('show');
            $("#playlist-modal-body.modal-body").html(result);
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
    
   /* $(document).on("click", ".view-details-button", function ()
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

    });*/
    
   /* $('#view_playlist_modal').on('shown.bs.modal', function (e) {
     $('.scheduledate').daterangepicker(
        {
            timePicker: false,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD'
        });
    });*/
    
    $('#view_playlist_modal').on('shown.bs.modal', function (ev) {
        initializeDateRangePicker(".scheduledate");
        initializeTimePicker(".timepicker");
        /*$('.scheduledate').daterangepicker(
        {
            timePicker: false,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD'
        });*//*.end().on('keypress paste', function (e) {
            e.preventDefault();
            return false;
        });*/
        
        
        /*$('.timepicker').timepicker({
            scrollDefault: '00:00',
            timeFormat: 'H:i',
            step: 15,
        });*/
        
        
        
        
        
    });
    
    
    $(document).on("click", ".playlist-checkbox", function ()
    {
        edit=false;
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
    
   /* $(document).on("click", ".edit-playlist", function ()
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
 */
    function addeditplaylist(id,e)
    {
        e.preventDefault();
        $("#pl-options-"+id).addClass("hidden");
        //$("#v"+id).addClass("hidden");//;.remove();
        $("#span-cbox-"+id).addClass("hidden");
        $("#span-del-"+id).removeClass("hidden");
        $("#span-edit-"+id).removeClass("hidden");
        //$("#right").append("<div class='btn btn-block btn-info btn-lg bg-gray playlist-right' data-playlist="+id+" id='right-v"+id+"'>"+$('#v'+id).html()+"</div>");
        if(edit==false){
            var element = $('#v'+id).clone();
            element.addClass("playlist-right")
            $("#v"+id).remove();
            $("#right").append(element);
            var element = $("#v"+id).find(".scheduledate");
            initializeDateRangePicker(element);
            
            element = $("#v"+id).find('.schedulestarttime');
            initializeTimePicker(element);

            element = $("#v"+id).find('.scheduleendtime');
            initializeTimePicker(element);
            /*$("#v"+id).find(".scheduledate").daterangepicker({
            timePicker: false,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD'
        });*/
        }
        edit=false;
    }
    
    $(document).on("click", ".add-playlist", function (e)
    {
        var id = $(this).attr("data-id");
        var daterange_playlist = $('#playlist-modal-body').find('#date-p'+id).val();
            console.log(daterange_playlist);
            if(daterange_playlist!=""){
                from_date = daterange_playlist.substring(0, 10);
                to_date = daterange_playlist.substring(13, 23);
                console.log(from_date);
                console.log(to_date);
                if(!isValidDate(from_date) || !isValidDate(to_date))
                {
                    alert("Invalid Date");
                }
                else
                {
                    addeditplaylist(id,e);
                }
            }
            else
            {
                alert("Invalid Date");
            }
        
    });
    
   
    
    $(document).on("click", ".del-playlist", function (e)
    {
        var id =$(this).attr("data-id");
        e.preventDefault();
        var element = $('#v'+id).clone();
        element.removeClass("playlist-right");
        
        $("#v"+id).remove();
        $("#left").append(element);
        var element = $("#v"+id).find(".scheduledate");
        initializeDateRangePicker(element);
        
        element = $("#v"+id).find('.schedulestarttime');
        initializeTimePicker(element);
        
        element = $("#v"+id).find('.scheduleendtime');
        initializeTimePicker(element);
        /*$("#v"+id).find(".scheduledate").daterangepicker({
            timePicker: false,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD'
        });*/
        
        /*$("#v"+id).find('.schedulestarttime').timepicker({
            //scrollDefault: '00:00',
            timeFormat: 'H:i',
            step: 15,
            
        });
        
        $("#v"+id).find('.scheduleendtime').timepicker({
            //scrollDefault: '00:00',
            timeFormat: 'H:i',
            step: 15,
            
        });*/
        
        
        $("#pl-options-"+id).removeClass("hidden");
        //$('#playlist-modal-body').find("#c"+id).removeClass("hidden");//.remove();
        $("#span-cbox-"+id).removeClass("hidden");
        $("#span-del-"+id).addClass("hidden");
        $("#span-edit-"+id).addClass("hidden");
        $("#c"+id).prop('checked', false); // Unchecks it
        $("#pl-options-"+id).hide();
    });
    
    $(document).on("click", ".playlist-details-div", function ()
    {
        id = $(this).attr("data-id");//1;//$(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "3";
        data["type"] = "1";
        var jsondata = JSON.stringify(data);
       // console.log("#playlist-details-"+id);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
             $('#playlist-modal-body').find("#playlist-details-"+id).html(result);
            
        });
    });
    
    function isValidDate(dateString) {
        var regEx = /^\d{4}-\d{2}-\d{2}$/;
        if(!dateString.match(regEx)) return false;  // Invalid format
        var d = new Date(dateString);
        var dNum = d.getTime();
        if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
        return d.toISOString().slice(0,10) === dateString;
      }
    
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
        var save = true;
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
            console.log(daterange_playlist);
            if(daterange_playlist!=""){
                from_date = daterange_playlist.substring(0, 10);
                to_date = daterange_playlist.substring(13, 23);
            }
            else{
                from_date="";
                to_date = "";
            }
            if(from_date=="")
            {
                from_date="0000-00-00";
            }
            if(to_date=="")
            {
                to_date="0000-00-00";
            }
            start_time = $('#playlist-modal-body').find('#start-time'+id).val();
            if(start_time=="")
            {
                start_time="00:00:00";
            }
            end_time = $('#playlist-modal-body').find('#end-time'+id).val();
            if(end_time=="")
            {
                end_time="00:00:00";
            }
            if(!isValidDate(from_date) || !isValidDate(to_date))
            {
                alert("Invalid Date");
                e.preventDefault();
                save = false;
            }
            else
            {
                playlist.push({
                    id: id, 
                    repeat:repeat_playlist,
                    start_date:from_date,
                    end_date:to_date,
                    start_time:start_time,
                    end_time:end_time,
                    name:name
                });
            }
            
         });
         if(save){
            var data = {};
            data["device_id"] = screen_id;
            data["task"] = "8";
            data["playlists"]=playlist;
            var jsondata = JSON.stringify(data);
            console.log(jsondata);
            $.post("../../modules/ajax/cms.php", jsondata, function (result) {
                 location.reload();

            });
        }
    });
    
    $(document).on("click", ".edit-playlist", function (e)
    {
        var id =$(this).attr("data-id");
	 $(".pl-options").hide();
	edit=false;
        if(edit==true)
        {
            addeditplaylist(id,e);
        }
        else
        {
            edit=true;
            $("#pl-options-"+id).show();
            $("#pl-options-"+id).removeClass("hidden");
        }
        
    });
    
    /*$("#view_playlist_modal" ).scroll(function() {
        $('.scheduledate').daterangepicker('place')
    });*/
    
    function initializeDateRangePicker(element)
    {
        $(element).daterangepicker({
            timePicker: false,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD'
        });
    }
    
    function initializeTimePicker(element)
    {
        $(element).timepicker({
            scrollDefault: '00:00',
            timeFormat: 'H:i',
            step: 15
        });
    }
   
    /*$("#right").scroll(function () {
        
       var txtestimatestartdate = $('.scheduledate').daterangepicker();
    txtestimatestartdate.daterangepicker('hide');
    $(".scheduledate").blur();             
});*/


</script>
