
$(document).ready(function(){
    var data={};
           $.post("../../modules/ajax/vmsconfig.php?type=1","",function(data){                         
                $("#vmslist").html(data);                    
           })
           .fail(function(jqxhr,status,error){
               alert("Error: "+error);

           }); 
        
});

$("#addvms").click(function (event) {
    //$('#devicetitle').html('Add Device');	
    var datastring = {};  
    datastring["vms_name"] = $('#vms_name').val();
    datastring["serial_port"] = $('#port_no').val();
    datastring["sign_type"]=$('#sign_type').val();
    datastring["vms_width"]=$('#vms_width').val();
    datastring["vms_height"]=$('#vms_height').val();
    datastring["left_font_size"]=$('#left_font_size').val();
    datastring["left_icon_text"] =$('#left_icon_text').val();
    datastring["left_color"]=$('#left_counter_color').val();
    datastring["left_character_count"]=$('#left_character_count').val();
    datastring["left_counter"]=$('#left_counter_id').val();
    datastring["left_icon"]=$('#left_icon').val();
    datastring["center_font_size"]=$('#center_font_size').val();
    datastring["center_icon_text"] =$('#center_icon_text').val();
    datastring["center_color"]=$('#center_counter_color').val();
    datastring["center_character_count"]=$('#center_character_count').val();
    datastring["center_counter"]=$('#center_counter_id').val();
    datastring["center_icon"]=$('#center_icon').val();
    datastring["right_font_size"]=$('#right_font_size').val();
    datastring["right_icon_text"] =$('#right_icon_text').val();
    datastring["right_color"]=$('#right_counter_color').val();
    datastring["right_character_count"]=$('#right_character_count').val();
    datastring["right_counter"]=$('#right_counter_id').val();
    datastring["right_icon"]=$('#right_icon').val();

    $.ajax({
             type: "post",
             url: "../../modules/ajax/devicesettings.php?type=3",
             data: JSON.stringify(datastring),
             cache: false,
             contentType: "application/json",
             success: function (data) {
                //alert(data);
                 location.reload(true);

             },
             error: function (jqXHR, textStatus, errorThrown) {

                 //alert(textStatus);alert(errorThrown);                                       
             } //end of error
         }); //end of ajax
        
     }); //end of Add Device

//Edit VMS
function EditVMS(id)
{
//var id = $(this).attr('id');
var $target = "add_vms";
$('.block-data').css('display', 'none');
$('.block-data[data-status="' + $target + '"]').fadeIn('slow');
$('.tab-link').removeClass('active');
$(this).addClass('active');

$('#page_title').html('Edit VMS');
//$('#add_device').html('Edit Device');

/*$('.card-body').find('input').val('');
$('.card-body').find('input[type=checkbox]:checked').removeAttr('checked');
$('.card-body').find('select').prop('selectedIndex',0);*/

var data = {id: id}
$.ajax({
    type: "post",
    url: "../../modules/ajax/vmsconfig.php?type=2",
    data: JSON.stringify(data),
    cache: false,
    contentType: "application/json",
    success: function (data) {
        //alert(data);
        var output = JSON.parse(data);
        //alert(output[0].device_category);
       // location.reload(true);
      //alert(output[0].customer_receipt_mandatory);
      $('#vms_id').val(id);
       $('#vms_name').val(output[0].vms_name);
       $('#port_no').val(output[0].serial_port);
       $('#sign_type').val(output[0].sign_type);
       $('#vms_width').val(output[0].vms_width);
       $('#vms_height').val(output[0].vms_height);
       $('#left_font_size').val(output[0].left_font_size);
       $('#left_icon_text').val(output[0].left_icon_text);
       $('#left_counter_color').val(output[0].left_color);
       $('#left_character_count').val(output[0].left_character_count);
       $('#left_counter_id').val(output[0].left_counter);
       //alert("#left-" + output[0].left_icon);
       //$('#left-' + output[0].left_icon).prop('selected', true);
       $('#left_icon').val(output[0].left_icon);
       $('#left_icon-button span').text(output[0].left_icon);
      // $('#left_icon').selectmenu();
       //$('#left_icon').selectmenu('refresh', true);
            //$('#left_icon').selectmenu("value", output[0].left_icon);
      // $('#left_icon').val(output[0].left_icon);
       $('#center_font_size').val(output[0].center_font_size);
       $('#center_icon_text').val(output[0].center_icon_text);
       $('#center_counter_color').val(output[0].center_color);
       $('#center_character_count').val(output[0].center_character_count);
       $('#center_counter_id').val(output[0].center_counter);
       $('#center_icon').val(output[0].center_icon);
       $('#center_icon-button span').text(output[0].center_icon);
       //$('#center_icon').selectmenu('refresh', true);
       $('#right_font_size').val(output[0].right_font_size);
       $('#right_icon_text').val(output[0].right_icon_text);
       $('#right_counter_color').val(output[0].right_color);
       $('#right_character_count').val(output[0].right_character_count);
       $('#right_counter_id').val(output[0].right_counter);
       //alert(output[0].right_icon);
       $('#right_icon').val(output[0].right_icon);
       $('#right_icon-button span').text(output[0].right_icon);
       // $('#right_icon').selectmenu('refresh', true);
       $('#brightness_time1').val(output[0].brightness_time1);
       $('#brightness_value1').val(output[0].brightness_value1);
       $('#brightness_time2').val(output[0].brightness_time2);
       $('#brightness_value2').val(output[0].brightness_value2);
       $('#brightness_time3').val(output[0].brightness_time3);
       $('#brightness_value3').val(output[0].brightness_value3);
       $('#brightness_time4').val(output[0].brightness_time4);
       $('#brightness_value4').val(output[0].brightness_value4);
       $('#brightness_time5').val(output[0].brightness_time5);
       $('#brightness_value5').val(output[0].brightness_value5);

        $('#canceledit').css("visibility","visible");
    },
    error: function (jqXHR, textStatus, errorThrown) {

        //alert(textStatus);alert(errorThrown);                                       
    } //end of error
}); //end of ajax

//event.preventDefault();
};//end of Edit VMS


function SaveRecord()
{
    var id = $('#vms_id').val();
    var datastring = {};  
    datastring['id']=id;
    datastring['vms_name'] = $('#vms_name').val();
    datastring['port_no']= $('#port_no').val();
    datastring['sign_type'] = $('#sign_type').val();
    datastring['vms_width']= $('#vms_width').val();
    datastring['vms_height']= $('#vms_height').val();
    datastring['left_font_size']= $('#left_font_size').val();
    datastring['left_icon_text']= $('#left_icon_text').val();
    datastring['left_counter_color'] = $('#left_counter_color').val();
    datastring['left_character_count']= $('#left_character_count').val();
    datastring['left_counter_id'] = $('#left_counter_id').val();
    datastring['left_icon']= $('#left_icon').val();
    datastring['center_font_size']= $('#center_font_size').val();
    datastring['center_icon_text']= $('#center_icon_text').val();
    datastring['center_counter_color'] = $('#center_counter_color').val();
    datastring['center_character_count']= $('#center_character_count').val();
    datastring['center_counter_id'] = $('#center_counter_id').val();
    datastring['center_icon']= $('#center_icon').val();
    datastring['right_font_size']= $('#right_font_size').val();
    datastring['right_icon_text']= $('#right_icon_text').val();
    datastring['right_counter_color'] = $('#right_counter_color').val();
    datastring['right_character_count']= $('#right_character_count').val();
    datastring['right_counter_id'] = $('#right_counter_id').val();
    datastring['right_icon']= $('#right_icon').val();
    datastring['brightness_time1'] = $('#brightness_time1').val();
    datastring['brightness_value1'] = $('#brightness_value1').val();
    datastring['brightness_time2'] = $('#brightness_time2').val();
    datastring['brightness_value2'] = $('#brightness_value2').val();
    datastring['brightness_time3'] = $('#brightness_time3').val();
    datastring['brightness_value3'] = $('#brightness_value3').val();
    datastring['brightness_time4'] = $('#brightness_time4').val();
    datastring['brightness_value4'] = $('#brightness_value4').val();
    datastring['brightness_time5'] = $('#brightness_time5').val();
    datastring['brightness_value5'] = $('#brightness_value5').val();


    //alert(JSON.stringify(datastring));
    console.log(datastring);
    $.ajax({
        type: "post",
        url: "../../modules/ajax/vmsconfig.php?type=3",
        data: JSON.stringify(datastring),
        cache: false,
        contentType: "application/json",
        success: function (data) {
           //alert("hre");
            location.reload(true);

        },
        error: function (jqXHR, textStatus, errorThrown) {

           // alert(textStatus);alert(errorThrown);                                       
        } //end of error
        
    }); //end of ajax
    //return;
    event.preventDefault();
}