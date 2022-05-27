$(document).ready(function(){
    $('#parking_rates1').removeClass('btn-info');
    $('#parking_rates1').addClass('btn-success');
});

function ShowParkingRate(rate)
{
    $('.btn-parking-rate').removeClass('btn-success');
    $('.btn-parking-rate').addClass('btn-info');    
    $('#' + rate).addClass('btn-success');
    $('#' + rate).removeClass('btn-info');
    $('.btn-cancel-parking').css("visibility","hidden");
    var data = {rate:rate};
    //alert(JSON.stringify(data));
    $.ajax({
        type: "post",
        url: "../../modules/ajax/parkingsettings.php?type=1",
        data: JSON.stringify(data),
        cache: false,
        contentType: "application/json",
        success: function (data) {
           var output = JSON.parse(data);
           for(var j = 0 ;j< output.length;j++)
           {
                var unit = output[j]["time_unit_"+rate];
                if(unit=="hours")
                {
                    unit = "Hours";
                }
                else if (unit=="mins"){
                    unit = "Minutes"
                }
                var duration = output[j]["time_duration_"+rate];
                var parking_rate = output[j][rate];
                var id =  output[j]['id'];
                $('#time_unit'+id).html(unit);
                $('#time_duration'+id).html(duration);
                $('#prate'+id).html(parking_rate);
                $('#rateno').val(rate);
                $('#editbtnparking'+id).val("Edit");
                $('#editbtnparking'+id).attr("onclick","EditRate("+id+")");
           }
        },
        error: function (jqXHR, textStatus, errorThrown) {

            alert(textStatus);alert(errorThrown);                                       
        } //end of error
    }); //end of ajax

    $.ajax({
        type: "post",
        url: "../../modules/ajax/parkingsettings.php?type=3",
        data: JSON.stringify(data),
        cache: false,
        contentType: "application/json",
        success: function (data) {
           var output = JSON.parse(data);
           for(var j = 0 ;j< output.length;j++)
           {
                var label = output[j]["rate_labels"];
                var parking_rate = output[j][rate];
                var id =  output[j]['id'];
                $('#ratelabel'+id).html(label);
                $('#fixedrate'+id).html(parking_rate);
                $('#rateno').val(rate);
                $('#editbtnfixed'+id).val("Edit");
                $('#editbtnfixed'+id).attr("onclick","EditFixedRate("+id+")");
           }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert(textStatus);alert(errorThrown);                                       
        } //end of error
    }); //end of ajax
    event.preventDefault();
}



function EditRate(id)
{
    $('#cancelbtnparking'+id).css("visibility","visible");
    var unit =$('#time_unit'+id).html();
    if(unit=="Minutes")
    {
        unitvalue = "mins";
    }
    else if(unit=="Hours")
    {
        unitvalue = "hours";
    }
    $('#time_unit'+id).html("<select name = 'timeunit"+id+"' id = 'timeunit"+id+"'><option value='hours'>Hours</option><option value = 'mins'>Minutes</option></select>");
    $('#time_duration'+id).html("<input type = 'text' id = 'timeduration"+id+"' name = 'timeduration"+id+"' value = '"+ $('#time_duration'+id).html()+"'>");
    $('#prate'+id).html("<input type = 'text' id = 'parkingrate"+id+"' name = 'parkingrate"+id+"' value = '"+ $('#prate'+id).html()+"'>");
    $('#editdiv'+id).html("<input class='btn btn-info btn-block btn-edit-parking' id ='editbtnparking"+id+"' type='submit'  value='Save' onclick='EditParkingRateRecord("+id+")' />");
    //$('#editbtn'+id).val("Save")

    $('#timeunit'+id).val(unitvalue);
    
}
function CancelEdit(id)
{
    var rateno = $('#rateno').val();
   // document.location="parkingrates.php";
    ShowParkingRate(rateno);
}
function EditParkingRateRecord(id)
{
    var datastring = {};
    datastring['timeunit'] = $('#timeunit'+id).val();
    datastring['timeduration'] = $('#timeduration'+id).val();
    datastring['parkingrate'] = $('#parkingrate'+id).val();
    var rateno = $('#rateno').val();
    var jsondata = JSON.stringify(datastring);
        $.ajax({
        type:'post',
        url:"../../modules/ajax/parkingsettings.php?id="+id+"&rateno="+rateno+"&type=2",
        data:jsondata,
        cache:false,
        contentType:'application/json',
        dataType:'json',
        success:function(data){
            //location.reload();
            ShowParkingRate(rateno);
        },
        error:function(jqXHR,textStatus,errorThrown)
        {
            alert("textStatus"+textStatus);
            alert(errorThrown+" "+textStatus);
        }
        });
}
function EditFixedRate(id)
{

    
    $('#ratelabel'+id).html("<input type = 'text' id = 'ratelabeltext"+id+"' name = 'ratelabeltext"+id+"' value = '"+ $('#ratelabel'+id).html()+"'>");
    $('#fixedrate'+id).html("<input class='w-100' type = 'text' id = 'fixedratetext"+id+"' name = 'fixedratetext"+id+"' value = '"+ $('#fixedrate'+id).html()+"'>");
    $('#editdivfixed'+id).html("<input class='btn btn-info btn-block btn-edit-parking' id ='editbtnfixed"+id+"' type='submit'  value='Save' onclick='EditFixedRateRecord("+id+")' />");
    //$('#editbtn'+id).val("Save")

    $('#cancelbtn'+id).css("visibility","visible");
}
function EditFixedRateRecord(id)
{
    var datastring = {};
    datastring['ratelabel'] = $('#ratelabeltext'+id).val();
    datastring['fixedrate'] = $('#fixedratetext'+id).val();
    var rateno = $('#rateno').val();
    var jsondata = JSON.stringify(datastring);
        $.ajax({
        type:'post',
        url:"../../modules/ajax/parkingsettings.php?id="+id+"&rateno="+rateno+"&type=4",
        data:jsondata,
        cache:false,
        contentType:'application/json',
        dataType:'json',
        success:function(data){
           // location.reload();
           ShowParkingRate(rateno);
        },
        error:function(jqXHR,textStatus,errorThrown)
        {
            alert("textStatus"+textStatus);
            alert(errorThrown+" "+textStatus);
        }
        });
}