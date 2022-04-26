<?php
$page_title = "Video Upload";

include('../../includes/header.php');
//$access = checkPageAccess("playlist");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Video Upload</div>


<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper container-wide">
    <section class="content">
        <div class="container-wide">                 
            <!--<form class="block-data card card-form-custom" id="video_upload" action="../ajax/video_upload.php" method="post" enctype="multipart/form-data"">-->
            <form class="block-data card card-form-custom" id="video_upload"  method="post" enctype="multipart/form-data"">
                <div class="row">
                    <div class="col form-group">
                        <label for="">File Name</label>
                        <input type="file" name="file" id="file"  required=""/> 
                    </div>
                </div>                         
                <input class="signUp btn btn-block btn-info mt-2 btn-lg" onclick="upload_3()" type="submit" name="submit" value="Submit" />                
            </form> 
            <div id="progress-div"><div id="progress-bar"></div></div>
            <div id="targetLayer"></div>

            
            <div class="block-data col-md-12" data-status="overview">
        <div class="card" >               
            <div class="card-body" id="div-RecordsTable">     
                <table id="RecordsTable" class="table  table-bordered"> 
                    <?php
                    $data["task"] = 10;
                    parcxContentManagement($data);
                    ?> 
                </table>
            </div>                                                  
        </div>             
    </div>   
        </div>
    </section>
    
              

</div>

<?php include('includes/footer.php'); ?>

<script>

    var files;
    var duration=0;
    window.URL = window.URL || window.webkitURL;
    document.getElementById('file').onchange = setFileInfo;

    function setFileInfo() {
        var file_data = $("#file").prop("files")[0];

        if (typeof file_data !== "undefined")
        {
            var file_name = file_data.name;
            var extension = file_name.split('.').pop();
            if(extension=="mp4"||extension=="avi")
            files = this.files;
            //myVideos=files;
            var video = document.createElement('video');
            video.preload = 'metadata';

            video.onloadedmetadata = function() {
              window.URL.revokeObjectURL(video.src);
              duration = video.duration;
              files.duration = duration;  
              //updateInfos();
            }

            video.src = URL.createObjectURL(files[0]);
        }
    }


function updateInfos() {
    console.log(files.duration);
    duration = files.duration;
  //}
}


    function upload_1()
    {
        if ($('#file').val()) {
            e.preventDefault();

            $(this).ajaxSubmit({
                target: '#targetLayer',
                beforeSubmit: function () {
                    $("#progress-bar").width('0%');
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    console.log(percentComplete);
                    $("#progress-bar").width(percentComplete + '%');
                    $("#progress-bar").html('<div id="progress-status">' + percentComplete + ' %</div>')
                },
                success: function () {
                    $('#loader-icon').hide();
                },
                resetForm: true
            });
            return false;
        }
    }

    function upload_2()
    {

        var file_data = $("#file").prop("files")[0];

        if (typeof file_data !== "undefined")
        {
            var file_name = file_data.name;
            var extension = file_name.split('.').pop();
            if(extension.toString().toLowerCase()=="mp4" || extension.toString().toLowerCase()=="avi")
            {
                setFileInfo();
            }
            var name = Date.parse(new Date()).toString();
            name = name + "." + extension;
            var form_data = new FormData();
            form_data.append("file", file_data);
            form_data.append("name", name);

            $.ajax({
                url: "../../modules/ajax/video_upload.php",
                method: "POST",
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                success: function (result) {

                    $("#message").html(result);
                }
            });
        }

    }

    $(document).ready(function () {
        var formElement = $("#video_upload");
        formElement.validate({
            submitHandler: function () {
                upload_1();
            }
        });
    });
    
function loadTable()
{
        var data = {};
        data["task"] = "10";
        data["edit"] =1;
        data["delete"]=1;
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/cms.php", jsondata, function (result) {
            $("#div-RecordsTable").html("<table id='RecordsTable' class='table  table-bordered'>" + result + "</table>");
            table = $('#RecordsTable').DataTable({"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], "aaSorting": []});
            table.page(page).draw(false);
        });
}
    
function upload_3()
{
    var file_data = $('input[name="file"]').get(0).files[0];
    var formData = new FormData();
    formData.append('file', file_data);
    formData.append('name', file_data.name);

    $.ajax({
      type: "POST",
      url: "../../modules/ajax/video_upload.php?duration="+duration,
      data:formData,
      contentType: false,
      processData: false,
      cache: false,
      success: function(data) {
        if(data=="Success"){
            alert("File Uploaded Successfully");
            
        }
        else
            alert(data);
      }
    });
    loadTable();
}

</script>

