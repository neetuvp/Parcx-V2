<?php
$page_title = "Playlist";

include('../../includes/header.php');

$access = checkPageAccess("playlist_settings");
?>
<script src="<?=URL?>plugins/jQueryUI/jquery-ui.js"></script>
<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Playlist Settings</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview" id="overview-tab">Overview</div>
	<?php
        if ($access["add"] == 1)
        	echo '<div class="tab-link" data-target="form" id="form-tab">Add Playlist</div>';
	?>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<!--<style>
* {
  box-sizing: border-box;
}

body {
  font-family: Arial, Helvetica, sans-serif;
}

/* Float four columns side by side */
.playlistcolumn {
  float: left;
  width: 100%;
  padding: 0 10px;
}

/* Remove extra left and right margins, due to padding */
.playlistrow {margin-bottom: 15px;}

/* Clear floats after the columns */
.playlistrow:after {
  content: "";
  display: table;
  clear: both;
}

/* Responsive columns */
@media screen and (max-width: 600px) {
  .playlistcolumn {
    width: 100%;
    display: block;
    margin-bottom: 20px;
  }
}

/* Style the counter cards */
.playlistcard {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  padding: 16px;
  text-align: center;
  /*background-color: gray;*/
  width: 100%;
    display: block;
    margin-bottom: 20px;
}
/* Clear floats after the columns */
.playlistcard:after {
  content: "";
  display: table;
  clear: both;
}

</style>-->
<!--View Playlist Modal  -->
<div class="modal fade text-dark" id="view_playlist_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display:none; z-index:2000">
    <div class="modal-dialog" role="document" id="edit-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Playlist</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
                
            </div>

            <div class="modal-body pt-4 pl-4 pr-4 pb-4 view-playlist-body">
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->

<!--Add Playlist Modal  -->
<!-- Modal -->
<div class="modal fade" id="add-edit-playlist-modal"  tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true" style="z-index: 1999;">
    <div class="modal-dialog modal-xl" style="width:1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add/Edit Playlist</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-playlist-modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2" id="playlist-modal-body">  
                <form name="form1" class="block-data " data-status="form"  id="form">                
                    <!--<div class="alert alert-light mb-2" role="alert" id="messagebox">
                    </div>-->
                    <div class="row">                    
                        <div class="col form-group">
                            <label for="">Playlist Name</label>
                            <input type="text" class="form-control" id="playlist_name" required name="playlist_name">
                        </div>
                        
                    
                        <div class="col form-group">
                            <label for="">Description</label>
                            <input type="text" class="form-control" id="description"  name="description">
                        </div> 
                        
                    </div>  
                    <div id="playlist-div" class="block-data p-3">

                        <div class="row  mb-3">
                            <div class="col h5">
                                <label for="">Drag the media file in sequence to create the playlist</label>
                            </div>
                        </div>
                        <div class="row  mb-1">
                            <div class="col"><h4>MEDIA LIBRARY</h4></div>
                            <div class="col p-2"></h4></div>
                            <div class="col"><h4>PLAYLIST</h4></div>
                        </div>
                        <div class="row dragdrop ">
                            <div class="col connectedSortable overflow-hidden" id="left">
                                <?php
                                    $data["task"] = 4;
                                    parcxContentManagement($data);
                                ?> 
                            </div>
                            
                            <div class="col connectedSortable overflow-hidden" id="right">

                            </div>                    
                        </div>   
                    </div>

                    <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Submit" id="add-edit-button">

                </form>             
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->
<!-- Info modal -->
<div class="modal fade" id="info-modal" tabindex="-1" role="dialog" aria-labelledby="edit-preview" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4 pb-4 h5" id='info-message'> 
           </div>
        </div>
    </div>
</div>
<!-- Info modal -->


<div class="content-wrapper container-wide">
    <section class="content">
        <!-- playlist table -->         
        <div class="block-data col-md-12" data-status="overview">
            <div class="card" >               
                <div class="card-body" id="div-RecordsTable">     
                    <table id="RecordsTable" class="table  table-bordered"> 
                        <?php
                        $data["task"] = 1;
                        $data["edit"] = $access["edit"];
                        $data["delete"]=$access["delete"];
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
    table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
    


    $(document).ready(function ()
    {
        //$("#form-edit-user").validate();
        
        //table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
        $("* [data-target]").on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target == "form")
            {
                $("#add-edit-playlist-modal").modal('show');
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
            //$('.block-data').css('display', 'none');
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
                event.preventDefault();
                var data = {};
                if ($("#add-edit-button").val() == "Submit")
                {
                    data["id"] = "";
                } else
                {
                    data["id"] = id;
                }
                var div_id="";
                data["playlist_name"] = $("#playlist_name").val();
                data["description"] = $("#description").val();
                var video_list="";
                $('#right > div').map(function() {
                    div_id = this.id;
                    div_id = div_id.substring(1);
                    video_list = video_list+div_id+",";
                });
                var l = video_list.length;
                video_list = video_list.substring(0,l-1);
                data["video_list"] = video_list;
                data["task"] = "5";
                var jsondata = JSON.stringify(data);
                //console.log(jsondata);
                $.post("../../modules/ajax/cms.php", jsondata, function (result) {
                    //console.log(result);
                    if (result === "Successfull"){
                        //location.reload();
                        $("#add-edit-playlist-modal").modal('hide');
                        $("#info-message").html("<i class='fas fa-check'></i>Update Successful");
                        $("#info-modal").modal('show');
                    }
                    else
                        alert(result);
                });
            }
        });
        
         /*$( "#right" ).sortable({
            revert: true
          });
          
          
          
          
          $( "#play-video" ).draggable({
            connectToSortable: "#right",
            helper: "clone",
            revert: "invalid"
          });*/
        
        $( "#left, #right" ).sortable({
            //items: ".play-video",
            //revert: true,
            helper: 'clone',
            appendTo: 'body',
            zIndex: 10000,
            cursor: "move",
            connectWith: ".connectedSortable"
          }).disableSelection();
     });

$('#info-modal').on('hidden.bs.modal', function () {
    location.reload();
   })
   
$('#add-edit-playlist-modal').on('hidden.bs.modal', function () {
    location.reload();
   })
   
    function loadTable()
    {
        var data = {};
        data["task"] = "1";
        data["edit"] = "<?php echo $access["edit"]; ?>";
	data["delete"] = "<?php echo $access["delete"]; ?>";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
            table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            table.page(page).draw(false);
        });
    }
    
   


    /* === enable disable playlist === */
    var status;
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



    /*=====edit======*/
    $(document).on("click", ".playlist-view", function ()
    {
        id = $(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "3";
        data["type"] = 1;
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#view_playlist_modal").modal('show');
            $(".view-playlist-body").html(result);
            
        });

    });
    
    $(document).on("click", ".play-video", function ()
    {
        /*var path = $(this).attr("data-path");
        $("#view_playlist_modal").modal('show');
        $(".view-playlist-body").html("<video controls='controls'  preload='metadata' width='100%'><source src='"+path+"#t=0.5' type='video/mp4'></video>");*/

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
    
    
    
    $(document).on("click", ".playlist-edit", function (){          
        id = $(this).parent('td').parent('tr').data('id');
        var data = {};
        data["id"] = id;
        data["task"] = "12";
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            //console.log(result);
            var json = JSON.parse(result);
            $("#add-edit-playlist-modal").modal('show');
            $("#playlist_name").val(json.playlist_name);
            $("#description").val(json.playlist_description);
            $("#add-edit-button").val("Edit");
        });
        
        data = {};
        data["task"] = "4";
        data["playlist_id"]=id;
        jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#left").html(result)
            
            data = {};
            data["task"] = "3";
            data["id"]=id;
            data["type"] = 0; 
            jsondata = JSON.stringify(data);
            //console.log(jsondata);
            $.post("../../modules/ajax/cms.php", jsondata, function (result1) {
                //console.log(result1);
                $("#right").html(result1)
            });
        });
        
        
        
    });
    $("#close-playlist-modal").click(function (event) {   
        location.reload();
    });
    
    

</script>
