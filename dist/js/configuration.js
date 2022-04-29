$(function()
{
var editId=0;
//disable dynamic rate    
    $(".btn-disable-dynamic-rate").click(function (event) {
        var id = $(this).attr('id');
        var data = {id: id}
        $.ajax({
            type: "post",
            url: "../../modules/ajax/config.php?type=1",
            data: JSON.stringify(data),
            cache: false,
            contentType: "application/json",
            success: function (data) {
                    //alert(data);
                    location.reload(true);

            },
            error: function (jqXHR, textStatus, errorThrown) {

                alert(textStatus);alert(errorThrown);                                       
            } //end of error
        }); //end of aajax
       
    }); //end of disable dynamic rate


//Enable dynamic rate
    $(".btn-enable-dynamic-rate").click(function (event) {
        var id = $(this).attr('id');
        var data = {id: id}
        $.ajax({
            type: "post",
            url: "../../modules/ajax/config.php?type=2",
            data: JSON.stringify(data),
            cache: false,
            contentType: "application/json",
            success: function (data) {
                //alert(data);
                location.reload(true);

            },
            error: function (jqXHR, textStatus, errorThrown) {

                alert(textStatus);alert(errorThrown);                                       
            } //end of error
        }); //end of aajax
        
    }); //end of Enable dynamic rate

    //disable  vms shedule
    $(".btn-disable-vms-shedule").click(function (event) {       
        var id = $(this).attr('id');
        var data = { id: id }
        $.ajax({
            type: "post",
            url: "../../modules/ajax/config.php?type=7",
            data: JSON.stringify(data),
            cache: false,
            contentType: "application/json",
            success: function (data) {
                alert(data);
                location.reload(true);

            },
            error: function (jqXHR, textStatus, errorThrown) {

                alert(textStatus); alert(errorThrown);
            } //end of error
        }); //end of aajax

    }); //end of disable  vms shedule


    //Enable vms shedule
    $(".btn-enable-vms-shedule").click(function (event) {
        var id = $(this).attr('id');
        var data = { id: id }
        $.ajax({
            type: "post",
            url: "../../modules/ajax/config.php?type=6",
            data: JSON.stringify(data),
            cache: false,
            contentType: "application/json",
            success: function (data) {
                alert(data);
                location.reload(true);

            },
            error: function (jqXHR, textStatus, errorThrown) {

                alert(textStatus); alert(errorThrown);
            } //end of error
        }); //end of aajax

    }); //end of Enable vms shedule

//Add dynamic Rate
    $("#add_dynamic_rate").click(function (event) {        
        var name=$("#dynamicRateName").val();  
        
        var date=null;
        if ($("#rdbDate").is(":checked"))
            date=$("#dynamicRateDate").val();
        
        var day =null;
        if ($("#rdbDay").is(":checked"))
            day=$("#dynamicRateDay").val();   
        
        var type=$("input:radio[name='rateType']:checked").val();          
        var rate=$("#dynamicRate").val();           
        var data = {name:name,date:date,day:day,type:type,rate:rate};                    

        $.ajax({
             type: "post",
             url: "../../modules/ajax/config.php?type=3",
             data: JSON.stringify(data),
             cache: false,
             contentType: "application/json",
             success: function (data) {
                 //alert(data);
                 location.reload(true);

             },
             error: function (jqXHR, textStatus, errorThrown) {

                 alert(textStatus);alert(errorThrown);                                       
             } //end of error
         }); //end of aajax
        
     }); //end of Add dynamic Rate

    //add_vms_device
    $("#add_vms_device").click(function (event) {
        var name = $("#vms-name").val();
        var vmsLocation = $("#location").val();
        var portName = $("#port-name").val();
        if (name == "" || vmsLocation == "" || portName == "")
            alert("Please enter details fully");
        else
            {
            var holdStatus=0;
            if ($("#hold-status").is(":checked"))
                holdStatus=1;                
            var data = {
                name: name,
                vmsLocation: vmsLocation,
                portName: portName,
                holdStatus: holdStatus            
            };

            $.ajax({
                type: "post",
                url: "../../modules/ajax/config.php?type=9",
                data: JSON.stringify(data),
                cache: false,
                contentType: "application/json",
                success: function (data) {                    
                    alert(data);
                   location.reload(true);
                },
                error: function (jqXHR, textStatus, errorThrown) {

                    alert(textStatus); alert(errorThrown);
                } //end of error
            }); //end of aajax
        }

    }); //end of Add dynamic Rate

    //Add vms shedule
$("#add_vms_shedule").click(function (event) {        
        var id = $("#vms_name").val();   
        var name = $("#vms_name option:selected").text();
        var message = $("#message").val();
        var colour = $("#colour").val();
        var action = $("#action").val();
        var time = $("#sheduleTime").val();        
        var interval = $("#shedule-interval").val();
        var holdTime = $("#holdTime").val();
        var startDateTime = $("#startDateTime").val();
        
        if(message=="")
            alert("Please enter the message");
        else if(time=="")
            alert("Please enter the Shedule time");
        else if(holdTime=="")
            alert("Please enter the Hold time");
        else if (startDateTime == "")
            alert("Please enter the start date time");
        else
            {            
            time=time+":00";
            startDateTime = startDateTime+":00";                
            var temp="";
            if(interval.length==7)
                temp = "daily";
            else
                {
                for (var i=0;i<interval.length;i++)
                    {
                    temp = temp + interval[i] + ",";
                    }
                }
            startDateTime=startDateTime.replace("T"," ");
            message=message.replace("\n","[CR]");
            var data = 
                {
                id: id,
                name: name,
                message: message,
                colour: colour,
                action: action,
                time: time,
                interval: temp,
                holdTime: holdTime,
                startDateTime: startDateTime,
                editId:editId
                };            
            alert(JSON.stringify(data));
            if ($(this).text() == "Save")
                {
                    $.ajax({
                    type: "post",
                    url: "../../modules/ajax/config.php?type=5",
                    data: JSON.stringify(data),
                    cache: false,
                    contentType: "application/json",
                    success: function (data) {
                        alert(data);
                        location.reload(true);

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        alert(textStatus); alert(errorThrown);
                    } //end of error
                }); //end of aajax
                }
            else
                {
                 $.ajax({
                     type: "post",
                     url: "../../modules/ajax/config.php?type=8",
                     data: JSON.stringify(data),
                     cache: false,
                     contentType: "application/json",
                     success: function (data) {
                         alert(data);
                         location.reload(true);

                     },
                     error: function (jqXHR, textStatus, errorThrown) {

                         alert(textStatus);
                         alert(errorThrown);
                     } //end of error
                 }); //end of aajax              ;
                }

            }        
    }); //end of Add dynamic Rate   

$("#cancel").click(function(event)
    {
     location.reload(true);
    });
    
    //edit  vms shedule
$(".btn-edit-vms-shedule").click(function (event) {        
        var id = $(this).attr('id');
        editId=id;
        
        
        var name = $("#name-" + id).text();
        var colour = $("#colour-" + id).text();
        var action = $("#action-" + id).text();
        var message = $("#message-" + id).text();
        var time = $("#time-" + id).text();
        var interval = $("#interval-" + id).text();
        var holdTime = $("#hold-time-" + id).text();           
        var startTime = $("#start-datetime-" + id).text();
        
        var $target = "add_vms_shedule";
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');

        $("#add-edit-heading").html("Edit Vms Shedule");
        $("#add_vms_shedule").html("Edit");

        $("#vms_name select").text(name);
        message=message.replace("[CR]","\n");
        $("#message").val(message);
        $("#colour select").val(colour);
        $("#action select").val(action);
        time = time.substring(0, time.length - 3);
        $("#sheduleTime").val(time);
        $("#holdTime").val(holdTime);
        startTime=startTime.replace(" ","T");  
        startTime=startTime.substring(0,startTime.length-3);      
        $("#startDateTime").val(startTime); 
        if (interval!="daily")
            {
            
            interval=interval.substring(0,interval.length-1);
            var array=interval.split(",");
            console.log(array);            
            $("#shedule-interval").val(array);
            $('#shedule-interval').multiselect('updateButtonText');
            }

    }); //end of edit  vms shedule

//disable  vms device
$(".btn-disable-vms-device").click(function (event) {
    var id = $(this).attr('id');
    var data = {
        id: id
    }
    $.ajax({
        type: "post",
        url: "../../modules/ajax/config.php?type=10",
        data: JSON.stringify(data),
        cache: false,
        contentType: "application/json",
        success: function (data) {
            alert(data);
            location.reload(true);

        },
        error: function (jqXHR, textStatus, errorThrown) {

            alert(textStatus);
            alert(errorThrown);
        } //end of error
    }); //end of aajax

}); //end of disable  vms shedule


//Enable vms device
$(".btn-enable-vms-device").click(function (event) {
    var id = $(this).attr('id');
    var data = {
        id: id
    }
    $.ajax({
        type: "post",
        url: "../../modules/ajax/config.php?type=11",
        data: JSON.stringify(data),
        cache: false,
        contentType: "application/json",
        success: function (data) {
            alert(data);
            location.reload(true);

        },
        error: function (jqXHR, textStatus, errorThrown) {

            alert(textStatus);
            alert(errorThrown);
        } //end of error
    }); //end of aajax

}); //end of Enable vms shedule

$(".btn-edit-vms-device").click(function (event) {
    var id = $(this).attr('id');    
    $(".btn-disable-vms-device#"+id).val("Cancel");
    if ($(this).val() == "Edit") {        
        $(this).val("Save");
        var name = $("#name-" + id).text();
        var vmsLocation = $("#location-" + id).text();
        var portName = $("#port-name-" + id).text();
        var holdstatus = $("#hold-status-" + id).text();
       
        $("#name-" + id).html("<input type='text'  class='col' id='txtname' value='" + name + "'>");
        $("#location-" + id).html("<input type='text'  class='col' id='txtlocation' value='" + vmsLocation + "'>");
        $("#port-name-" + id).html("<input type='text'   class='col' id='txtport' value='" + portName + "'>");
        $("#hold-status-" + id).html("<input type='number'   min='0' max='1' class='col' id='txtholdstatus' value='" + holdstatus + "'>");
    }
    else
    {
         var data = {
             id:id,
             name: $("#txtname").val(),
             vmsLocation: $("#txtlocation").val(),
             portName: $("#txtport").val(),
             holdStatus: $("#txtholdstatus").val()
         };
        $.ajax({
            type: "post",
            url: "../../modules/ajax/config.php?type=12",
            data: JSON.stringify(data),
            cache: false,
            contentType: "application/json",
            success: function (data) {
                alert(data);
                location.reload(true);

            },
            error: function (jqXHR, textStatus, errorThrown) {

                alert(textStatus);
                alert(errorThrown);
            } //end of error
        }); //end of aajax
    }

    });
        
});  

