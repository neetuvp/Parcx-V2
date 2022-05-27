<?php
//Important Note : upload_max_filesize = 200M  and post_max_size = 200M in php.ini for upload size upto 200MB
$page_title = "Video Upload";

include('../../includes/header.php');
$access = checkPageAccess("media_library");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Media Library</div>


<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- Upload Media Modal  -->
<div class="modal fade text-dark" id="upload_media_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="edit-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Media</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pt-4 pl-4 pr-4 pb-4"> 
                <div class="alert-danger" id="error-upload" style="display:none;"></div>
                <form class="block-data card card-form-custom" id="video_upload"  enctype="multipart/form-data"">
                    <div class="row">
                        <div class="card-body col form-group">
                            <label for="">Upload File</label>
                            <input type="file" name="file" id="file" required="" accept ="image/*,video/*"/> <!--accept ="image/png, image/gif, image/jpeg,video/mp4,video/avi"-->
                            <div class="image-timeout-div mt-2" style="display:none;">
                                <label for="">Image Timeout(in minutes)</label>
                                <input type="number" name="image_timeout" id="image_timeout"/>
                            </div>
                        </div>
                    </div>                         
                    <input class="btn btn-block btn-info mt-2 btn-lg" onclick="upload_3()" type="submit" name="submit" value="Submit" />                
                </form> 
                <div id="progress-div" ></div>
                <div id="targetLayer"></div>
                <div class="progress" style="height:20px; display:none;">
                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar"  style="min-width: 0px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- end / Modal -->

<!-- View Video Modal  -->
<div class="modal fade text-dark" id="view_video_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="edit-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Media</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pt-4 pl-4 pr-4 pb-4 modal-body-view-video">    
                
            </div>
          

        </div>
    </div>
</div>
<!-- end / Modal -->

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
            <div class="modal-footer hidden" id="info-footer">
                <button type="button" class="btn btn-default" id="modal-btn-yes">Yes</button>
                <button type="button" class="btn btn-primary" id="modal-btn-no">No</button>
            </div>
        </div>
    </div>
</div>
<!-- Info modal -->


<div class="content-wrapper container-wide">
    <section class="content">
                     
            <!--<form class="block-data card card-form-custom" id="video_upload" action="../ajax/video_upload.php" method="post" enctype="multipart/form-data"">-->
            

        <div class="block-data col-md-12" data-status="overview">
                <div class="card" >               
                    <div class="card-body" id="div-RecordsTable">
                        
                        <table id="RecordsTable" class="table  table-bordered" > 
                            <?php
                            $data["task"] = 10;
                            $data["add"] = $access["add"];
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
<script src="../../dist/js/jquery.validate.js"></script>

<script>

    var files;
    var duration=0;
    var file_data_type;
    var isVideo = false;
    window.URL = window.URL || window.webkitURL;
    document.getElementById('file').onchange = setFileInfo;
    table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
    
    function setFileInfo() {
        isVideo = false;
        var file_data = $("#file").prop("files")[0];

        if (typeof file_data !== "undefined")
        {
            var file_name = file_data.name;
            //alert(file_data.type);
            
            var extension = file_name.split('.').pop();
            if(extension=="mp4"||extension=="avi" || extension=="MP4"||extension=="AVI" ||extension=="webm"||extension=="WEBM" )
            {
                isVideo = true;
                files = this.files;
                var video = document.createElement('video');
                video.preload = 'metadata';

                video.onloadedmetadata = function() {
                  window.URL.revokeObjectURL(video.src);
                  duration = format_duration(video.duration);
                  files.duration = duration;  
                  //alert(duration);
                }

                video.src = URL.createObjectURL(files[0]);
            }
            file_data_type=file_data.type.split('/')[0];
            if(file_data_type==="image")
            {
                $(".image-timeout-div").show();
                $("#image_timeout").prop('required', true);
                //$('#image_timeout').show().find(':input').attr('required', true);
            }
        }
    }


function format_duration(sec)
{
    var sec_num = parseInt(sec, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours + ':' + minutes + ':' + seconds;
}

function updateInfos() {
    console.log(files.duration);
    duration = files.duration;
  //}
}

function loadTable()
{
    var data = {};
    data["task"] = 10;
    data["add"] ="<?php echo $access["add"]; ?>";
    data["delete"]="<?php echo $access["delete"]; ?>";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/cms.php", jsondata, function (result) {
        $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
        table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            //table.page(page).draw(false);
    });
}

$(document).on("click", ".upload-media-btn", function ()
{
    $(".image-timeout-div").hide();
    $("#image_timeout").prop('required', false);
    $("#file").val('');
    $("#upload_media_modal").modal('show');
    $("#error-upload").hide();
});

function makeProgress(i){      
            $(".progress-bar").css("width", i + "%").text(i + "%");
    }
function getthumbnail(seekTo,filename) {
    console.log("getting video cover for file: ", file);
    return new Promise((resolve, reject) => {
        // load the file to a video player
        const videoPlayer = document.createElement('video');
        videoPlayer.setAttribute('src', URL.createObjectURL($('input[name="file"]').get(0).files[0]));
        videoPlayer.load();
        var filename_src = videoPlayer.src;
        console.log(filename_src);
        videoPlayer.addEventListener('error', (ex) => {
            reject("error when loading video file", ex);
        });
        // load metadata of the video to get video duration and dimensions
        videoPlayer.addEventListener('loadedmetadata', () => {
            // seek to user defined timestamp (in seconds) if possible
            if (videoPlayer.duration < seekTo) {
                reject("video is too short.");
                return;
            }
            // delay seeking or else 'seeked' event won't fire on Safari
            setTimeout(() => {
              videoPlayer.currentTime = seekTo;
            }, 200);
            // extract video thumbnail once seeking is complete
            videoPlayer.addEventListener('seeked', () => {
                console.log('video is now paused at %ss.', seekTo);
                // define a canvas to have the same dimension as the video
                const canvas = document.createElement("canvas");
                canvas.width = 360;//videoPlayer.videoWidth;
                canvas.height =240;// videoPlayer.videoHeight;
                // draw the video frame to canvas
                const ctx = canvas.getContext("2d");
                ctx.drawImage(videoPlayer, 0, 0, canvas.width, canvas.height);
                // return the canvas image as a blob
                var data = canvas.toDataURL("image/png");
    
                //send to php script
                var xmlhttp = new XMLHttpRequest;

                xmlhttp.onreadystatechange = function(){
                    if (xmlhttp.readyState==4 && xmlhttp.status==200){
                        console.log('saved');
                    }
                }
                //console.log("in getTHumb3");
                //console.log('saving');
                xmlhttp.open("POST", "../../modules/ajax/video_upload.php?task=2", true);
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send('name='+encodeURIComponent(filename_src)+'&data='+data+'&filename='+filename);
                //console.log('name='+encodeURIComponent(filename_src)+'&data='+data);
            });
        });
    });
}


function upload_3()
{
    let isFormValid = $('#video_upload')[0].checkValidity();
    if(!isFormValid)
        return;
    if ($('#file').val()) {
        $(".progress").show();
        event.preventDefault();
        var file_data = $('input[name="file"]').get(0).files[0];
        if(file_data_type==="image")
        {
            duration=$("#image_timeout").val();
        }
        var formData = new FormData();
        formData.append('file', file_data);
        
        
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                  if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    //console.log(percentComplete);
                    $(".progress-bar").css("width", percentComplete + "%").text(percentComplete + "%");
                  }
                }, false);
                return xhr;
              },
            url: "../../modules/ajax/video_upload.php?duration="+duration+"&task=1",
            method: 'POST',
            data: formData,
            //async:false,
            contentType: false,
            processData: false,
            beforeSend: function(){
                $(".progress-bar").css("width", "0%").text("0%");
            },
            success: function (response) {
                try{
                    var json = JSON.parse(response);
                    if (json.status === 200) {
                        //alert("Upload Successful");
                        //location.reload();
                        if(isVideo)
                            getthumbnail(0.0,file_data.name);
                        $("#upload_media_modal").modal('hide');
                        $("#info-message").html("<i class='fas fa-check'></i>Update Successful");
                        $("#info-modal").modal('show');
                        $("#info-footer").addClass("hidden");

                    } else
                    {
                        $(".progress-bar").css("width", "0%").text("0%");
                        $("#error-upload").show();
                        $("#error-upload").html(json.message);
                        //alert(json.message);
                        
                    }
                }
                catch (e) {
                    $(".progress-bar").css("width", "0%").text("0%");
                    console.log("here");
                    $("#error-upload").show();
                    $("#error-upload").html(response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#error-upload").show();
                $("#error-upload").html(textStatus+errorThrown);
                //alert(textStatus);
                //alert(errorThrown);
            } //end of error
        });
    }
    
}

$('#info-modal').on('hidden.bs.modal', function () {
    location.reload();
   })
   
$(document).on("click", ".play-video", function ()
    {
        var path = $(this).attr("data-path");
        var type = $(this).attr("data-type");
        $("#view_video_modal").modal('show');
        if(type.includes("video") || type.includes("audio"))
        {
            if(type=="video/x-msvideo")
            {
                $(".modal-body-view-video").html("<object data="+path+"> <param name='src' value="+path+" /> <embed type='video/x-ms-wm' src="+path+"></embed> </object>");
                $("#view_video_modal").modal('hide');
            }
            else
            {
                $(".modal-body-view-video").html("<video controls='controls'  preload='metadata' width='100%'><source src='"+path+"#t=0.5' type='"+type+"'></video>");
                
            }
        }
        else 
        {
            $(".modal-body-view-video").html("<img src="+path+"  width='100%'>");
        }

    });
    
   
    var modalConfirm = function(callback){
        $("#info-message").html("This action will remove the current media from all the playlists.Do you wish to continue?");
        $("#info-modal").modal('show');
        $("#info-footer").removeClass("hidden");
        
        $("#modal-btn-yes").on("click", function(){
          callback(true);
          $("#info-modal").modal('hide');
        });

        $("#modal-btn-no").on("click", function(){
          callback(false);
          $("#info-modal").modal('hide');
        });
    };
    
    
function enabledisable(id,status)
{
    var data = {};
    data["id"] = id;
    data["status"] = status;
    data["task"] = 11;
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/cms.php", jsondata, function (result) {
        if (result === "Successfull")
            loadTable();
        else
            alert(result);
    });
}

$(document).on("click", ".video-enable-disable-btn", function (e)
{
    id = $(this).parent('td').parent('tr').data('id');
    var status_text = $(this).attr("data-text");
    if (status_text == "Disable")
    {
        status = 0;
        modalConfirm(function(confirm){
            if(confirm){
               enabledisable(id,status);
            }
            else{
                
            }
        });
        
    }
    else
    {
        status = 1;
        enabledisable(id,status);
    }

    
});




</script>

