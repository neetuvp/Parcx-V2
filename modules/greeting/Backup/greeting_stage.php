<?php
//Important Note : upload_max_filesize = 200M  and post_max_size = 200M in php.ini for upload size upto 200MB
$page_title = "Video Upload";

include('../../includes/header.php');
$access = checkPageAccess("greeting_screen");
?>
<script src="js/lottie-player.js"></script>
<link href="css/parcx.css" rel="stylesheet">
<style>
    .thumbnail embed{
        width: 1080px;
        height: 1920px;
    }

    .thumbnail {
        position: absolute; left: 3%; top: 2%;
        -ms-zoom: 0.40;
        -moz-transform: scale(0.40);
        -moz-transform-origin: 0 0;
        -o-transform: scale(0.40);
        -o-transform-origin: 0 0;
        -webkit-transform: scale(0.40);
        -webkit-transform-origin: 0 0;
    } 

</style>
<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Greeting Stages</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active known-div" data-target="known">Known Customer</div>
        <div class="tab-link unknown-div" data-target="un-known">Unknown Customer</div>                      
    </div>
</div>


<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
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
            <div class="modal-body pt-4 pl-4 pr-4 pb-4" id='info-message'> 
            </div>
        </div>
    </div>
</div>
<!-- Info modal -->


<div class="modal fade" id="preview-modal">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Preview</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="thumbnail-container">
                    <div class="thumbnail">               
                        <embed type="text/html" id="preview-webpage" src="http://localhost/PGEGreeting/" >            
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<!-- Edit modal -->
<div class="modal fade" id="edit-modal"  tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true" style="z-index:1999;">
    <div class="modal-dialog modal-xl" style="width:100%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-playlist-modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="margin:15px;"> 
                <div class="container">
                    <div class="row justify-content-start">
                        <div class="col-3">               
                            <button class='btn btn-block btn-success btn-schedule' data-schedule='1'>Morning</button>
                        </div>
                        <div class="col-3"> 
                            <button class='btn btn-block btn-info btn-schedule' data-schedule='2'>Afternoon</button>
                        </div>
                        <div class="col-3"> 
                            <button class='btn btn-block btn-info btn-schedule' data-schedule='3'>Evening</button>
                        </div>
                        <div class="col-3">
                            <button class='btn btn-block btn-info btn-schedule' data-schedule='4'>Night</button>
                        </div>
                    </div>
                </div>
                <br>
                <div class="alert-danger" id="error-upload" style="display:none;"></div>
                <form name="form1" class=" p-3" data-status="form"  id="form">
                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Title</label>
                            <input type="text" class="form-control" id="title" name="title" readonly>
                        </div>
                    </div>
                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Description</label>
                            <input type="text" class="form-control" id="description" required name="description">
                        </div>
                    </div>

                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Message Line 1</label>
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="message_line1"></textarea>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-2 mt-2">
                                    <select id="m1_font_family">
                                        <option hidden value="">Select font-family</option>
                                        <?php parcxGreetingScreen(array("task"=>"7")) ?>
                                    </select>
                                </div>
                                <div class="col-2 mt-2">
                                    <input type="number" class="form-control" id="m1_font_size" required name="m1_font_size" min="0">
                                    <!--<select id="m1_font_size">
                                        <option hidden value="0">Select font-size</option>
                                        <?php parcxGreetingScreen(array("task"=>"8")) ?>
                                    </select-->
                                </div>
                                <div class="col-2 mt-2">
                                    <select id="m1_font_color">
                                        <option hidden value="">Select font-color</option>
                                        <?php parcxGreetingScreen(array("task"=>"9")) ?>
                                    </select>
                                </div>
                            </div> 
                        </div>
                    </div>

                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Message Line 2</label>
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="message_line2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-2 mt-2">
                                    <select id="m2_font_family">
                                        <option hidden value="">Select font-family</option>
                                        <?php parcxGreetingScreen(array("task"=>"7")) ?>
                                    </select>
                                </div>
                                <div class="col-2 mt-2">
                                    <input type="number" class="form-control" id="m2_font_size" required name="m2_font_size" min="0">
                                    <!--<select id="m2_font_size">
                                        <option hidden value="0">Select font-size</option>
                                        <?php parcxGreetingScreen(array("task"=>"8")) ?>
                                    </select>-->
                                </div>
                                <div class="col-2 mt-2">
                                    <select id="m2_font_color">
                                        <option hidden value="">Select font-color</option>
                                        <?php parcxGreetingScreen(array("task"=>"9")) ?>
                                    </select>
                                </div>
                            </div> 
                        </div> 
                    </div>

                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Message Line 3</label>
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="message_line3"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-2 mt-2">
                                    <select id="m3_font_family">
                                        <option hidden value="">Select font-family</option>
                                        <?php parcxGreetingScreen(array("task"=>"7")) ?>
                                    </select>
                                </div>
                                <div class="col-2 mt-2">
                                    <input type="number" class="form-control" id="m3_font_size" required name="m3_font_size" min="0">
                                    <!--<select id="m3_font_size">
                                        <option hidden value="0">Select font-size</option>
                                        <?php parcxGreetingScreen(array("task"=>"8")) ?>
                                    </select>-->
                                </div>
                                <div class="col-2 mt-2">
                                    <select id="m3_font_color">
                                        <option hidden value="">Select font-color</option>
                                        <?php parcxGreetingScreen(array("task"=>"9")) ?>
                                    </select>
                                </div>
                            </div> 
                        </div> 
                    </div>
                   
                    <div id="auto_stage_option">
                        <div class="row mt-2 mb-1">  
                            <div class="col form-group">
                                <input type="checkbox" class="form-control" id="auto_stage_change">
                                <label>Go to next stage automatically</label> 
                            </div>
                        </div>
                        <div class="row">                         
                             <div class="col-2 form-group">
                                <label for="">Timeout</label>
                                <input type="number" class="form-control" id="timeout" required name="timeout" min="0">
                            </div>
                            <div class="col-4 form-group">
                                <label for="">Next Stage</label>
                                <select id="next_stage">
                                    <option value="0">--Select Stage--</option>
                                    <?php parcxGreetingScreen(array("task"=>"10")) ?>
                                </select>
                            </div> 
                        </div>
                    </div>
                    
                    <div class="row">                         
                        <div class="col-2 form-group">
                            <label for="">Background Opacity(%)</label>
                           <input type="number" class="form-control" id="bg_opacity" required name="opacity" min="0" max="100">
                       </div>
                       <div class="col-4 form-group">
                           <label for="">Background Color</label>
                           <input type="text" class="form-control" id="bg_color" required name="bgcolor" placeholder = "255,255,255">
                       </div> 
                   </div>

                    <div class="row" id="video-div"> 
                        <div class="col form-group">
                            <label for="">Backgroud Image/Video</label>
                            <div class="form-check col-2">
                                <input class="form-check-input radiobtn-bg" type="radio" name="bg-radio" data-name="video" id="file_video" value="video/mp4" checked>
                                <label class="form-check-label" for="videoradio">
                                    Video
                                </label>
                            </div>
                            <div class="form-check col-2">
                                <input class="form-check-input radiobtn-bg" type="radio" name="bg-radio" data-name="image" id="file_image" value="image/jpeg,image/gif,image/png" >
                                <label class="form-check-label" for="imageradio">
                                    Image
                                </label>
                            </div>
                            <input type="hidden" id="bgfile_hidden" value="">
                            <input type="hidden" id="bgtype_hidden" value="">
                            <br>
                            <input type='file'  id='bg_file' accept ="video/mp4" class="mb-3"   />
                            <video width="25%" height="200" controls id="bgvideo_preview">
                                Your browser does not support the video tag.
                            </video>
                            <img src ="" width='25%' height="200" id="bgimage_preview"/>
                        </div> 
                    </div>
                    
                    <div id="video-div-ad"> </div>

                    <div class="row"> 
                        <div class="col form-group">
                            <label for="">Status Animation</label></br>
                            <div class="form-check col-2">
                                <input class="form-check-input radiobtn" type="radio" name="animation-radio" data-name="gif" id="file_gif" value=".gif" checked>
                                <label class="form-check-label" for="gifradio">
                                    Gif
                                </label>
                            </div>
                            <div class="form-check col-2">
                                <input class="form-check-input radiobtn" type="radio" name="animation-radio" data-name="lottie" id="file_lottie" value=".lottie,.json" >
                                <label class="form-check-label" for="lottieradio">
                                    Lottie
                                </label>
                            </div>

                            <input type="file"  id="animation_file" name="animationfile" accept=".gif"/>
                            <input type="hidden" id="animationfile_hidden" value="">
                            <input type="hidden" id="animationtype_hidden" value="">

                            <br>
                            <img src ="" width='25%' height="200" id="image_preview"/>
                            <div id="lottie_preview" style="width:25%;height:200px;"></div>
                        </div>                       
                    </div>  


                    <input type="submit" class="save-stage-details btn btn-block btn-info mt-2 btn-lg" value="Save" id="save-details">

                </form>             
            </div>
        </div>
    </div>
</div>
<!-- / end edit modal -->


<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="container bootstrap snippets bootdeys p-4" >
                <div class="col-md-12">
                    <div class=" block-data timeline-centered timeline-sm" data-status="known">  
                        <?php parcxGreetingScreen(array("task"=>"11","type"=>1,"edit"=>$access["edit"],"delete"=>$access["delete"])) ?>
                    </div>
                    
                    <div class=" block-data timeline-centered timeline-sm" data-status="un-known" style="display:none;">
                        <?php parcxGreetingScreen(array("task"=>"11","type"=>2,"edit"=>$access["edit"],"delete"=>$access["delete"])) ?>         
                    </div>
                </div>
            </div>
        </div> 

    </section>                
</div>

<?php include('../../includes/footer.php'); ?>

<script type="text/javascript">
    var stage_id;
    var schedule = '1';
    var id;
    var add_video=0;
    var known_customer=1;
    
    $("* [data-target]").on('click', function ()
    {
        var $target = $(this).data('target');   
        if($target=="known")
            known_customer=1;
        else
            known_customer=0;
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
    });
        
    $(document).on("click", ".preview-btn", function ()
    {
        stage_id = $(this).attr("data-id");        
        $("#preview-modal").modal("show");
        $(".thumbnail-container").css("height", 850)
        $("#preview-webpage").attr("src","http://<?=$_SERVER['SERVER_NAME']?>/PGEGreeting/?stage="+stage_id+"&known_customer="+known_customer);        
    });

    function initialize()
    {
        $("#form").trigger("reset");
        $("#edit-modal").modal("show");
        $("#error-upload").hide();

        $("#image_preview").attr("src", "");
        $("#image_preview").hide();
        $("#lottie_preview").html("");
        $("#lottie_preview").hide();
        $('#animation_file').attr("accept", ".gif");
        $('#bg_file').attr("accept", "video/mp4");
        document.querySelector("video").src = "";
        $("#bgvideo_preview").hide();
        $("#bgimage_preview").hide();
        $("#next_stage option[value="+stage_id+"]").hide();
        $('#auto_stage_change').prop('checked', false);
    }

    function showform()
    {
        initialize();
        var data = {};
        data["stage_id"] = stage_id;
        data["schedule"] = schedule;
        data["known_customer"] = known_customer;
        data["task"] = 3;
        var jsondata = JSON.stringify(data);
//console.log(jsondata);            
        $.post("ajax/greeting_screen.php", jsondata, function (result) { 
//console.log(result);       
            var json = JSON.parse(result);
            $("#title").val(json.stage_title);
            $("#description").val(json.description);
            
            $("#message_line1").val(json.message_line1);
            $("#m1_font_family").val(json.m1_font_family);
            $("#m1_font_size").val(json.m1_font_size);
            $("#m1_font_color").val(json.m1_font_color);
            
            $("#message_line2").val(json.message_line2);
            $("#m2_font_family").val(json.m2_font_family);
            $("#m2_font_size").val(json.m2_font_size);
            $("#m2_font_color").val(json.m2_font_color);
            
            $("#message_line3").val(json.message_line3);
            $("#m3_font_family").val(json.m3_font_family);
            $("#m3_font_size").val(json.m3_font_size);
            $("#m3_font_color").val(json.m3_font_color);
            
            $("#timeout").val(json.timeout_period);
            $("#bgfile_hidden").val(json.bg_file);
            $("#animationfile_hidden").val(json.animation_file);
            $("#animationtype_hidden").val(json.animation_type);
            if (json.auto_stage_change == 1)
                $('#auto_stage_change').prop('checked', true);
            else
                $('#auto_stage_change').prop('checked', false);
            $("#next_stage").val(json.next_stage_id);
            $("#bg_opacity").val(json.bg_opacity);
            $("#bg_color").val(json.bg_color);
            $("#bgtype_hidden").val(json.bg_type);
            if(json.bg_file>"")
            {
                if((json.bg_type).includes("video"))
                {
                    document.querySelector("video").src = "Media/" + json.bg_file + "#t=0.5";
                    $("#bgvideo_preview").show();
                    $("#file_video").prop("checked", true);
                    $("#file_image").prop("checked", false);
                    $('#bg_file').attr("accept", "video/mp4");
                }
                else if((json.bg_type).includes("image"))
                {
                    $("#bgimage_preview").attr("src", "Media/"+json.bg_file);
                    $("#bgimage_preview").show();
                    $("#file_image").prop("checked", true);
                    $("#file_video").prop("checked", false);
                    $('#bg_file').attr("accept", "image/jpeg,image/gif,image/png");
                }
                
            }
            
            if (json.animation_type == "image/gif")
            {
                $("#file_gif").prop("checked", true);
                $("#file_lottie").prop("checked", false);
                $("#image_preview").show();
                $("#lottie_preview").hide();
                $("#image_preview").attr("src", "Media/" + json.animation_file);
            } else if (json.animation_type == "lottie" || json.animation_type == "application/json")
            {
                $("#file_lottie").prop("checked", true);
                $("#file_gif").prop("checked", false);
                $('#animation_file').attr("accept", ".json");
                $("#image_preview").hide();
                $("#lottie_preview").show();
                $("#lottie_preview").html("<lottie-player autoplay='true' loop='' src='Media/" + json.animation_file + "' speed='1'  width='200' height='150' background='transparent'></lottie-player>");
            }

        });
        
        if(stage_id==5 || stage_id==12)
            {
            $("#video-div").hide();
            $("#video-div-ad").show();
            loadAdVideos();         
            }
        else
            {
            $("#video-div").show();
            $("#video-div-ad").hide();
            }
    }


    $(document).on("click", ".edit-btn", function ()
    {
        stage_id = $(this).attr("data-id");
        schedule = '1';
        showform();        
        //showform(stage_id, 1);        
    });
    
    function loadAdVideos()
        {
        var data = {};           
        //data["schedule"] = schedule;
        data["stage"] = stage_id;
        data["task"] = 4;
        var jsondata = JSON.stringify(data);       
        $.post("ajax/greeting_screen.php", jsondata, function (result) {       
            $("#video-div-ad").html(result);
//            $('#table_videos').DataTable({                
//                "lengthMenu": [[5,10, 25, 50, -1], [5,10, 25, 50, "All"]],
//                "aaSorting": []
//                });
            });      
        }  
        
  
    
    $(document).on("click", ".ad-video-enable-disable-btn", function ()
        {
           var status_text = $(this).attr("data-text");
            if (status_text != "Cancel")
                {
                id = $(this).parent('td').parent('tr').data('id');    
                
                if (status_text == "Disable")
                    status = 0;
                else
                    status = 1;

                var data = {};
                data["id"] = id;
                data["status"] = status;
                data["task"] = 5;
                var jsondata = JSON.stringify(data);
                $.post("ajax/greeting_screen.php", jsondata, function (result) {
                    if (result === "Successfull"){
                        loadAdVideos();
                        add_video=0;
                    }
                    else
                        alert(result);
                    });
                }
            else{
                loadAdVideos();
                add_video=0;
            }
                
        });
        
        
    $(document).on("click", ".advt-video-add-btn", function ()
        {
        if(add_video==0)
            {
            id="";    
            var newRow = $("<tr>");
            var cols = "";                                          
            cols += '<td><input type="file"  id="advt_video_file" accept ="video/mp4,image/jpeg,image/gif,image/png"  /></td>';            
            cols += '<td><input type="date" id="start_date"></td>';            
            cols += '<td><input type="date" id="expiry_date"></td>';            
            cols += '<td>';
            cols += '<video width="100" controls="controls" preload="metadata" id="advt_video"></video>'; 
            cols += '<img src="" width="100" id="advt_image">';
            cols += '</td>'; 
            cols += '<td><button type="button" class="btn btn-danger ad-video-enable-disable-btn mr-1" data-text="Cancel" title="Cancel"><i class="fas fa-window-close"></i></button><button type="button" class="btn btn-success btn-add-advt-video" title="Save"><i class="fas fa-floppy-o"></i></button></td>';               
                
            newRow.append(cols);
            
            $("#table_videos").prepend(newRow);
            add_video=1;
            }
            
            
        });
        
    $(document).on("change", "#advt_video_file", function(evt) {  
        if(this.files[0].type=="video/mp4")
        {
            $('#advt_video').show();
            $('#advt_image').hide();
            var $source = $('#advt_video');
            $source[0].src = URL.createObjectURL(this.files[0]);     
        }
        else
        {
            let blobURL = URL.createObjectURL(this.files[0]);
            $('#advt_video').hide();
            $('#advt_image').show();
            $("#advt_image").attr("src", blobURL);
        }
           
        });                        
    
    $(document).on("click", ".btn-add-advt-video", function (){                        
        if ($('#advt_video_file').val()) {
            if($('#start_date').val()==""){
                alert("Start Date is empty")
                return;
            }
            if($('#expiry_date').val()==""){
                alert("Expiry Date is empty")
                return;
            }
            
            uploadfile(3);
        }                                    
    });
    
   
    
    $(document).on("click", ".ad-video-edit", function ()
        {
        id = $(this).parent('td').parent('tr').data('id');        
        if ($(this).attr("data-text") === "Edit")
            {
            var start_date = $(this).parent('td').siblings(":eq( 1 )").text();            
            var expiry_date = $(this).parent('td').siblings(":eq( 2 )").text();   
            
            $(this).parent('td').siblings(":eq( 1 )").html("<input type = 'date' id = 'start_date" + id + "'  value = '" + start_date + "'>");
            $(this).parent('td').siblings(":eq( 2 )").html("<input type = 'date' id = 'expiry_date" + id + "'  value = '" + expiry_date + "'>");

            $(this).html('<i class="fas fa-save"></i>');
            $(this).attr("data-text", "Save");
            $(this).parent('td').parent('tr').find(".ad-video-enable-disable-btn").attr("data-text", "Cancel");
            $(this).parent('td').parent('tr').find(".ad-video-enable-disable-btn").attr("title", "Cancel");
            $(this).parent('td').parent('tr').find(".ad-video-enable-disable-btn").html('<i class="fas fa-window-close"></i>')
            $(this).parent('td').parent('tr').find(".ad-video-enable-disable-btn").removeClass("btn-success");
            $(this).parent('td').parent('tr').find(".ad-video-enable-disable-btn").addClass("btn-danger");
            } 
        else
            {            
            var data = {}; 
            data["id"]=id;
            data["stage"]=stage_id;           
            data["start_date"]=$("#start_date"+id).val();
            data["expiry_date"]=$("#expiry_date"+id).val();
            data["task"] = 6;
            var jsondata = JSON.stringify(data);
            //console.log(jsondata); 
            $.post("ajax/greeting_screen.php", jsondata, function (result) {
                //console.log(result);
                if (result === "Success")
                    {
                    loadAdVideos();
                    add_video=0;
                    } 
                else
                    alert(result);            
                });
            }
    });
    
    
    function insertUpdateAdvtVideo()
        {
        var data = {}; 
        data["id"]=id;
        //data["schedule"] = schedule;
        data["stage"]=stage_id; 
        data["file_name"] = $("#bgfile_hidden").val();
        data["file_type"] = $("#bgtype_hidden").val();
        data["start_date"]=$("#start_date").val();
        data["expiry_date"]=$("#expiry_date").val();
        data["task"] = 6;
        var jsondata = JSON.stringify(data);
        //console.log(jsondata); 
        $.post("ajax/greeting_screen.php", jsondata, function (result) {
            //console.log(result);
            if (result === "Success")
                {
                loadAdVideos();
                add_video=0;
                } 
            else
                alert(result);
        });
        }

    $(document).on("click", ".btn-schedule", function ()
    {
        schedule = $(this).attr("data-schedule");
        $('.btn-schedule').removeClass('btn-success');
        $('.btn-schedule').addClass('btn-info');
        $(this).removeClass('btn-info');
        $(this).addClass('btn-success');
        showform();
        //showform(stage_id, schedule);
    });


    $('.radiobtn').change(function () {
        $('#animation_file').val(""); // before setting
        $('#animation_file').attr("accept", $(this).val())
    });
    
    $('.radiobtn-bg').change(function () {
        $('#bg_file').val(""); // before setting
        $('#bg_file').attr("accept", $(this).val())
    });

    function uploadfile(type)
    {
        event.preventDefault();
        var formData = new FormData();
        if (type == 1)
            var file_data = $("#bg_file").prop("files")[0];
        else if (type == 2)
            var file_data = $("#animation_file").prop("files")[0];
        else if(type==3)
            var file_data = $("#advt_video_file").prop("files")[0];
        
        file_data.name = new Date().valueOf() + "_" + file_data.name;
        formData.append('file', file_data);

        $.ajax({

            url: "ajax/upload_media.php",
            method: 'POST',
            data: formData,
            async: false,
            contentType: false,
            processData: false,
            success: function (response) {
                try {
                    //console.log("upload response: "+response);
                    var json = JSON.parse(response);
                    if (json.status == 200) 
                        {
                        var new_filname = json.file_name;
                        if (type == 1)
                            {
                            $("#bgfile_hidden").val(new_filname);
                            $("#bgtype_hidden").val(file_data.type);
                            UpdateStageDetails();
                            }
                        else if (type == 2) 
                            {
                            $("#animationfile_hidden").val(new_filname);
                            $("#animationtype_hidden").val(file_data.type);
                            UpdateStageDetails();
                            }
                        else if(type==3)
                            {
                            $("#bgfile_hidden").val(new_filname); 
                            $("#bgtype_hidden").val(file_data.type);
                            insertUpdateAdvtVideo();
                            }
                        result = "Success"
                        
                        } 
                    else
                        {
                        result = "Failed"
                        $("#error-upload").show();
                        $("#error-upload").html(json.message);
                        }
                } catch (e) {

                    $("#error-upload").show();
                    $("#error-upload").html(response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#error-upload").show();
                $("#error-upload").html(textStatus + errorThrown);
            } //end of error
        });

    }
    $(document).on("click", ".save-stage-details", function ()
    {
        var result = "Success";
        if ($('#bg_file').val() || $('#animation_file').val())
        {
            if ($('#bg_file').val()) {
                uploadfile(1);
            }
            if ($('#animation_file').val()) {
                uploadfile(2);
            }
        } else
        {
            UpdateStageDetails();
        }

    });

    function UpdateStageDetails()
    {
        event.preventDefault();
        var data = {};
        data["stage_id"] = stage_id;
        data["schedule"] = schedule;
        data["known_customer"] = known_customer;
        data["title"] = $("#title").val();
        data["description"] = $("#description").val();
        data["message_line1"] = $("#message_line1").val();
        data["m1_font_family"] = $("#m1_font_family").val();
        data["m1_font_size"] = $("#m1_font_size").val();
        data["m1_font_color"] = $("#m1_font_color").val();
        data["message_line2"] = $("#message_line2").val();
        data["m2_font_family"] = $("#m2_font_family").val();
        data["m2_font_size"] = $("#m2_font_size").val();
        data["m2_font_color"] = $("#m2_font_color").val();
        data["message_line3"] = $("#message_line3").val();
        data["m3_font_family"] = $("#m3_font_family").val();
        data["m3_font_size"] = $("#m3_font_size").val();
        data["m3_font_color"] = $("#m3_font_color").val();
        data["timeout"] = $("#timeout").val();
        data["bg_file"] = $("#bgfile_hidden").val();
        data["bg_type"] = $("#bgtype_hidden").val();
        data["animation_file"] = $("#animationfile_hidden").val();
        data["animation_type"] = $("#animationtype_hidden").val();
        if ($('#auto_stage_change').is(":checked"))
            data["auto_stage_change"] = 1;
        else
            data["auto_stage_change"] = 0;
        data["next_stage_id"] = $("#next_stage").val();
        data["bg_color"] = $("#bg_color").val();
        data["bg_opacity"] = $("#bg_opacity").val();
        data["task"] = 2;
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("ajax/greeting_screen.php", jsondata, function (result) {
           // console.log(result);
            if (result === "Success")
            {
                $("#edit-modal").modal('hide');
                $("#info-message").html("Update Successful");
                $("#info-modal").modal('show');

            } else
                alert(result);
        });
    }

    $('#info-modal').on('hidden.bs.modal', function () {
        location.reload();
    })
    
    $('#edit-modal').on('hidden.bs.modal', function () {
        location.reload();
    })
    
    

    document.getElementById("bg_file")
            .onchange = function (event) {
                event.preventDefault();
                let file = event.target.files[0];
                let blobURL = URL.createObjectURL(file);
                if (file.type == "image/jpeg" || file.type == "image/png" || file.type == "image/gif")
                {
                    $("#bgimage_preview").attr("src", blobURL);
                    $("#bgimage_preview").show();
                    $("#bgvideo_preview").hide();
                } else if (file.type == "video/mp4")
                {
                    document.querySelector("video").src = blobURL;
                    $("#bgimage_preview").hide();
                    $("#bgvideo_preview").show();
                }
            }

    document.getElementById("animation_file")
            .onchange = function (event) {
                event.preventDefault();
                let file = event.target.files[0];
                let blobURL = URL.createObjectURL(file);
                if (file.type == "image/gif")
                {
                    $("#image_preview").attr("src", blobURL);
                    $("#image_preview").show();
                    $("#lottie_preview").hide();
                } else if (file.type == "application/json")
                {
                    $("#lottie_preview").show();
                    $("#lottie_preview").html("<lottie-player autoplay='true' loop='' src='" + blobURL + "' speed='1'   background='transparent'></lottie-player>");
                    $("#image_preview").hide();

                }
            }
            
  $(document).on("click", ".enable-disable-btn", function ()
    {
        var status_text= $(this).html();
        var stage_id = $(this).attr("data-id");
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["stage_id"] = stage_id;
        data["status"] = status;
        data["task"] = "12";
        data["customer_type"]=known_customer;
        var jsondata = JSON.stringify(data);
        //console.log(jsondata);
        $.post("ajax/greeting_screen.php", jsondata, function (result) {
            if (result === "Successfull")
            { 
                $("#info-message").html("Update Successful");
                $("#info-modal").modal('show');
            }
            else
                alert(result);
        });
    });

</script>

