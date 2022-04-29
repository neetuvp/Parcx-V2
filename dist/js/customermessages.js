var message_limit;
var upload_limit;
var upload_path;
var language = "English";
var type = "1";
var category="0";
var media_name="";
var form_language="";
var table;
var page=0;
function loadTable()
{
    table=$('#RecordsTable').DataTable({
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "aaSorting": []
    });
    table.page(page).draw(false);    
        
}
$(document).ready(function () {
  
    $('#device').DataTable({
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "aaSorting": []
    });
    
    loadCustomerMessages();
    
    data = {}
    data["task"] = 5;
    jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/customer_messages.php", jsondata, function (data) {
        var json = JSON.parse(data);
        message_limit = json.message_limit;
        upload_limit = json.upload_limit;
        upload_path = json.media_path;
    });
});


function loadCustomerMessages()
    {
    var data = {};
    data["type"] = type;
    data["language"] = language;
    data["category"] = category;
    data["task"] = 1;
    var jsondata = JSON.stringify(data);    
    $.post("../../modules/ajax/customer_messages.php?", jsondata, function (data) {
        $("#messagedata").html(data);                        
        loadTable();

    });    
    }

$(".btn-language").click(function () {
    language = $(this).val();
});

$(".btn-device-type").click(function () {
    type = $(this).attr("data-id");
});

$(".btn-device_category").click(function () {
    category = $(this).attr("data-id");
});




function CancelEdit()
{
    loadCustomerMessages();
}

function EditMessage(id)
{
    page=table.page();
    $('#' + language + '1' + id).html("<input class='w-100' type = 'text' id = '" + language + "1text" + id + "' name = '" + language + "1text" + id + "' value = '" + $('#' + language + '1' + id).html() + "'>");
    $('#' + language + '2' + id).html("<input class='w-100' type = 'text' id = '" + language + "2text" + id + "' name = '" + language + "2text" + id + "' value = '" + $('#' + language + '2' + id).html() + "'>");
    $('#' + language + '3' + id).html("<input class='w-100' type = 'text' id = '" + language + "3text" + id + "' name = '" + language + "3text" + id + "' value = '" + $('#' + language + '3' + id).html() + "'>");
    $('#editdivfixed' + id).html("<button type='button' class='btn btn-info' onclick='EditMessageRecord(" + id + ")'><i class='fas fa-save'></i></button><button type='button' class='btn btn-danger mt-2' onclick='CancelEdit()'><i class='fas fa-window-close'></i></button>");        
    
}

function EditMessageRecord(id)
{
    type = $('#device_type').val();
    if ($('#' + language + '1text' + id).val().length <= message_limit && $('#' + language + '2text' + id).val().length <= message_limit && $('#' + language + '3text' + id).val().length <= message_limit)
    {
        var data = {};
        data["language"] = language;
        data["line1"] = $('#' + language + '1text' + id).val();
        data["line2"] = $('#' + language + '2text' + id).val();
        data["line3"] = $('#' + language + '3text' + id).val();
        data['id'] = id;
        data["task"] = 2;
        data["activity_message"]="Edit customer message";
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/customer_messages.php", jsondata, function (data) {
            loadCustomerMessages();
        });

    } else
    {
        alert("Message must be less than " + message_limit + " characters");
    }
}


function EditMedia(id)
{


    $('#type' + id).html("<input class='w-100' type = 'text' id = 'english1text" + id + "' name = 'typetext" + id + "' value = '" + $('#type' + id).html() + "'>");
    $('#path' + id).html("<input class='w-100' type = 'text' id = 'english2text" + id + "' name = 'pathtext" + id + "' value = '" + $('#path' + id).html() + "'>");
    $('#editdivfixed' + id).html("<input class='btn btn-info btn-block btn-edit-parking' id ='editmessage" + id + "' type='submit'  value='Save' onclick='EditMessageRecord(" + id + ")' />");
    $('#cancelbtn' + id).css("visibility", "visible");
}




function AddMessage(language)
{
    if ($('#message').val() == "")
        {
        alert("Message field is mandatory");
        return;
        }

    for (var j = 0; j < language.length; j++) {
        if ($('#' + language[j] + '_line1').val().length > 25 || $('#' + language[j] + '_line2').val().length > 25 || $('#' + language[j] + '_line3').val().length > 25)
            {
            alert("Message must be less than " + message_limit + " characters");
            return;
            }
        }
        
    media_name="";
    if($("#image_file").val()!="")
        {                
        var file=$("#image_file");    
        id="";  
        var extension=file.val().split('.').pop();  
        var d=new Date();
        media_name=d.getTime()+"."+extension;
        addRecord(language);
        
        updateMedia(file);
        }
    else
        addRecord(language);
}

function addRecord(language)
{
    //Preparing data for insertion
    var data = {};
    data['device_type'] = $('#device_type').val();
    data['category'] = $('#device_category').val();
    data['message'] = $('#message').val();
    data['label'] = $('#label').val();
    data['media_path'] = media_name;
    data["activity_message"]="Add customer message";

    for (var i = 0; i < language.length; i++) {
        data[language[i] + '_line1'] = $('#' + language[i] + '_line1').val();
        data[language[i] + '_line2'] = $('#' + language[i] + '_line2').val();
        data[language[i] + '_line3'] = $('#' + language[i] + '_line3').val();
    }
    data["languages"] = language;
    data["task"] = 3;
    data["activity_message"]="Add customer message";
    var jsondata = JSON.stringify(data);
    //alert(jsondata);    
    $.post("../../modules/ajax/customer_messages.php", jsondata, function (data) {        
        console.log(data);
    });


}

$('#cancel_upload').click(function () {
    $('#uploadModal').modal('hide');
});



$('body').on('click', '.openModal', function () {
    id = $(this).attr("data-id");
    var img_src = $('.openImageDialog' + id).attr('src');        
            
    $(".modal-body #imgmodal").attr("src", img_src);
});



$('body').on('click', '#confirm_upload', function () {
    var file = $("#upload_file");     
    var extension=file.val().split('.').pop();  
    var d=new Date();
    media_name=d.getTime()+"."+extension;        
    updateMedia(file);
});


function updateMedia(file)
{
    /////////Image  Upload
    if (file.val() > '')
    {        
        
        var file_data = file.prop("files")[0];
        var form_data = new FormData();
        form_data.append("file", file_data);
        form_data.append("name", media_name);
        form_data.append("upload_path", upload_path);
        var size = file_data.size;
        name = file_data.name;
        if (size <= upload_limit * 1000)//if size < 100 kb
        {
            $.ajax({
                url: '../../modules/ajax/uploadfile.php',
                method: 'POST',
                data: form_data,
                contentType: false,
                processData: false,
                success: function (response) {
                    var json = JSON.parse(response);
                    if (json.status == 200) {
                        alert("Upload Successful");                        
                        updateMediaPath();                        
                    } else
                        alert(json.Message);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(textStatus);
                    alert(errorThrown);
                }
            });
        } else {
            alert("File size should be less than " + upload_limit + "KB");
        }

    } else {
        alert("No file selected")
    }
}


function updateMediaPath()
    {
    if(id!=="")
        {
        //Preparing data for insertion
        var data = {};
        data['id'] = id;
        data['media_path'] = media_name;
        data["task"] = 4;
        data["activity_message"]="Updated customer message mediapath";
        var jsondata = JSON.stringify(data);

        $.post("../../modules/ajax/customer_messages.php", jsondata, function (data) {
            location.reload(true);
        });
        }    
    }

var device_name;
$(document).on("click", ".synch-messages-btn", function ()
{
    id = $(this).parent('td').parent('tr').data('id');
    device_name=$(this).parent('td').siblings(":eq( 1 )").text();
    var data = {};
    data["id"] = id;
    data["task"] = "7";
    data["activity_message"]="Synch customer message for device "+device_name;
    var jsondata = JSON.stringify(data);
    console.log(jsondata);
    $.post("../../modules/ajax/customer_messages.php", jsondata, function (result) {
        alert(result);
    });

});

$(document).on("click", ".synch-images-btn", function ()
{
    id = $(this).parent('td').parent('tr').data('id');
    device_name=$(this).parent('td').siblings(":eq( 1 )").text();
    var data = {};
    data["id"] = id;
    data["task"] = "8";
    data["activity_message"]="Synch customer images for device "+device_name;
    var jsondata = JSON.stringify(data);
    console.log(jsondata);
    $.post("../../modules/ajax/customer_messages.php", jsondata, function (result) {
       alert(result);
    });

});

$(document).on("click", ".btn-device-type", function ()
    {
    type = $(this).data('id');
    $('.btn-device-type').removeClass('btn-success');
    $('.btn-device-type').addClass('btn-info');
    $(this).addClass('btn-success');
    $(this).removeClass('btn-info');
    $('.btn-cancel-parking').css("visibility", "hidden"); 
    loadCustomerMessages();
    });
    
$(document).on("click", ".btn-language", function ()
    {
    language = $(this).val();
    $('.btn-language').removeClass('btn-success');
    $('.btn-language').addClass('btn-info');
    $(this).addClass('btn-success');
    $(this).removeClass('btn-info');
    $('.btn-cancel-parking').css("visibility", "hidden"); 
    loadCustomerMessages();
    });
    
$(document).on("click", ".btn-category", function ()
    {
    category = $(this).data('id');
    $('.btn-category').removeClass('btn-success');
    $('.btn-category').addClass('btn-info');
    $(this).addClass('btn-success');
    $(this).removeClass('btn-info');
    $('.btn-cancel-parking').css("visibility", "hidden"); 
    loadCustomerMessages();
    });
    