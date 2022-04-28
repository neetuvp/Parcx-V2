var id,status;   
var report_id="0",tag;
$("#alert-div").hide();
$(document).on("click", ".btnWhitelistAdd", function() 
	{    
    $("#addToWhiteList").modal("toggle");
    $("#form").show(); 
    $("#form").trigger('reset');       
    $("#emirates-div").hide();   
    $('#multiselect option').prop('selected', true);
    $('#multiselect').multiselect("refresh"); 
    report_id = $(this).attr("data-id");	
    tag=$(this).attr("data-tag");	
    $('#tag').val(tag);
    $('#qr_code').val(tag);       
  });
$('#country,#emirates,#prefix,#plate_number').on('input', function () 
	{          
  var plate;    
  if($("#country").val().toUpperCase()==="UAE")
      plate= $("#emirates").val()+" "+$("#prefix").val().toUpperCase()+" "+$("#plate_number").val();           
  else
      plate=$("#country").val()+" "+ $("#prefix").val().toUpperCase()+" "+$("#plate_number").val();               
  $("#display_plate").html(plate);         
  });

$('#country').on('input', function () 
	{
	if($("#country").val().toUpperCase()==="UAE")
		$("#emirates-div").show();
	else
    $("#emirates-div").hide();
    if($("#country").val()=="")
      {
        $("#prefix").val("");
        $("#plate_number").val("");  
        $("#display_plate").html("");
      }
  });

$(document).ready(function () 
  {
       $('#multiselect').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,      
            selectAllText: "All carparks",
            nonSelectedText: "Select carparks",
            selectAllNumber: false,
            allSelectedText: "Select carparks",       
            });
            
    
    $('#whitelist-active-date').daterangepicker({
        timePicker: false,
        timePickerIncrement: 10,
        format: 'YYYY-MM-DD',
        singleDatePicker: true,
        //maxDate:$("#license-expiry-date").html()
    })

    $('#whitelist-expiry-date').daterangepicker({
        timePicker: false,
        timePickerIncrement: 10,
        format: 'YYYY-MM-DD',
        singleDatePicker: true,
        //maxDate:$("#license-expiry-date").html()
    })   
  $('#RecordsTable').DataTable(
    {
    "paging": true,
    "lengthChange":true,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "aaSorting": []
    
    });  
$(document).on("click", "* [data-target]", function() 
    {     
    $("#alert-div").hide();     
    var $target = $(this).data('target');     
    if($target=="form")                        
      {
      $("#form").trigger('reset');
      $("#display_plate").html("");
      $("#add-edit-button").val("Submit");    
      $("#emirates-div").hide();  
      report_id="0";      
      $('#multiselect option').prop('selected', true);
      $('#multiselect').multiselect("refresh");
      } 
    $('.block-data').css('display', 'none');
    $('.block-data[data-status="' + $target + '"]').fadeIn('slow');        
   $('.tab-link').removeClass('active');
    $(this).addClass('active');  
  });

 

  //FormSubmit
  var formElement = $("#form");
    var rules_set = {};

    formElement.find('input[type=text]').each(function()
        {
        var name = $(this).attr('name');
        rules_set[name] = 'required';
        });

    formElement.validate({
        rules: rules_set,
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.addClass("text-danger");
            error.insertAfter(element);
        },
        submitHandler: function () 
            {	                                                             
            var data = {};
            if($("#add-edit-button").val()=="Submit")              
              data["id"]="";                            
            else              
              data["id"]=id;                            
             
            data["report_id"]=report_id;               
            data['carpark_number'] = $('#multiselect').val().toString(); 
            data["facility_number"]=$("#facility").val();           
            //data['parking_zone']=$('#parking_zone').val().toString();   
            data['access_zones']="";
            data['ticket_id'] = $('#qr_code').val();
            //data['tag_serial'] = $('#tag_serial').val();
            //data['tag_tid'] = $('#tag_tid').val();
            data['tag_serial'] = "";
            data['tag_tid'] = "";
            data['tag'] = $('#tag').val();            
            data['customer_name'] = $('#customer_name').val();
            data['message1'] = $('#message1').val();
            data['message2'] = $('#message2').val();
            data['start_date'] = $("#whitelist-active-date").val();    
            data['expiry_date'] = $("#whitelist-expiry-date").val(); 		        
        
            data["country"]= $("#country").val(); 
            if(data["country"]!="")
              data["plate_number"]=$("#display_plate").html();
            else
              data["plate_number"]="";
            if ($("#antipassback").is(":checked"))               
                data['anti_passback'] = "1";                
            else                
                data['anti_passback'] = "0";   
                
            if($("#wallet-payment").is(":checked"))            
              data['wallet_payment'] = "1";                       
            else            
              data['wallet_payment'] = "0";   
            
            if($("#corporate-parker").is(":checked"))            
              data['corporate_parker'] = "1";                       
            else            
              data['corporate_parker'] = "0";      
        
            if($("#season-card").is(":checked"))            
              data['season_card'] = "1";                       
            else            
              data['season_card'] = "0";                                        
           
            data["task"]="11";            
            var jsondata=JSON.stringify(data); 
            var url="ajax/parking.php?task=11";
            if(report_id!="0")
                url="ajax/parking.php?task=11";
            $.post(url,jsondata,function(result){ 
		result = result.trim();                               
              if(result=="Successful")
                {                
                if(report_id!="0")
                    {
                    $("#addToWhiteList").modal("toggle");
                    if ((!daterange)) 
                        location.reload(); 
                    else
                        $("#view-report-button").click();    
                    } 
                else
                    location.reload();                  
                }
              else
                alert("here"+result);
           })
           .fail(function(jqxhr,status,error){
               alert("Error: "+error);

           });                              
        }
    });

});

/* === enable disable === */
var status;   
var id;
$(document).on("click", ".whitelist-enable-disable-btn", function() 
    {
    id = $(this).parent('td').parent('tr').data('id');
    var status_text=$(this).text();           
    if(status_text=="Disable")
        status=0;
    else
        status=1;

    var data = {};
    data["id"]=id;
    data["status"]=status;   
    data["task"]="2";            
    var jsondata=JSON.stringify(data);            
    $.post("../../modules/ajax/whitelist.php",jsondata,function(result){ 
      if(result=="Successfull")
        location.reload();                  
      else
        alert(result);
    })
    .fail(function(jqxhr,status,error){
        alert("Error: "+error);

    }); 
});

$(document).on("click", ".synch-enable-disable-btn", function() 
    {
    id = $(this).parent('td').parent('tr').data('id');
    var status_text=$(this).text();           
    if(status_text=="Disable")
        status=0;
    else
        status=1;

    var data = {};
    data["id"]=id;
    data["status"]=status;   
    data["task"]="7";            
    var jsondata=JSON.stringify(data);            
    $.post("../../modules/ajax/whitelist.php",jsondata,function(result){ 
      if(result=="Successfull")
        {
          loadDevicelist();
        }                 
      else
        alert(result);
    })
    .fail(function(jqxhr,status,error){
        alert("Error: "+error);

    }); 
});

function loadDevicelist()
  {
    var data = {};    
    data["task"]="6";            
    var jsondata=JSON.stringify(data);            
    $.post("../../modules/ajax/whitelist.php",jsondata,function(result){ 
      $("#RecordsTable2").html(result);
    })
    .fail(function(jqxhr,status,error){
        alert("Error: "+error);

    });   
  }

/*=====edit======*/
$(document).on("click", ".whitelist-edit", function() 
    {          
    id = $(this).parent('td').parent('tr').data('id');       
    var data = {};
    data["id"]=id;      
    data["task"]="12";            
    var jsondata=JSON.stringify(data);            
    $.post("ajax/parking.php?task=12",jsondata,function(result){
console.log(result);        
      var response=JSON.parse(result); 
      $('#multiselect option').prop('selected', false);
      $('#multiselect').multiselect("refresh");

      var values=response.carpark_number;      
      $.each(values.split(","), function(i,e){       
        $("#multiselect option[value='" + e + "']").prop("selected", true);
      });
      $("#multiselect").multiselect("refresh");
      var plate=response.plate_number;
      $("#display_plate").html(plate);
      $("#emirates-div").hide();
      if(plate!="")
        {
        var array_plate=plate.split(" ");
        var country=array_plate[0];
        var prefix=array_plate[1];
        var number=array_plate[2];
        $("#prefix").val(prefix);
        $("#plate_number").val(number);       
        if($("#country option[value='"+country+"']").length != 0)
            $("#country").val(country);
        else
            {
            $("#country").val("UAE");    
            $("#emirates").val(country);
            $("#emirates-div").show();
            }
          }
        else
          {
            $("#prefix").val("");
            $("#plate_number").val("");  
            $("#country").val("");  
          }
          $("#customer_name").val(response.customer_name);
          $("#tag").val(response.tag);  
          $("#description").val(response.description);  
          $("#qr_code").val(response.ticket_id);                        
          $("#whitelist-active-date").val(response.validity_start_date);
          $("#whitelist-expiry-date").val(response.validity_expiry_date);
          $("#type").val(response.whitelist_type);
          $("#value").val(response.whitelist_value);
          $("#message1").val(response.personalized_message_line1);
          $("#message2").val(response.personalized_message_line2);
          
          if(response.corporate_parker==1)                
              $("#corporate-parker").attr("checked",true);  
          else                
              $("#corporate-parker").attr("checked",false);

          if(response.antipassback_enabled==1)                
              $("#antipassback").attr("checked",true);  
          else                
              $("#antipassback").attr("checked",false);

          if(response.season_card==1)                
              $("#season-card").attr("checked",true);  
          else                
              $("#season-card").attr("checked",false);

          if(response.wallet_payment==1)                
              $("#wallet-payment").attr("checked",true);  
          else                
              $("#wallet-payment").attr("checked",false);

        
          $('.block-data').css('display', 'none');
          $('.block-data[data-status="form"]').fadeIn('slow');  
          $('.tab-link').removeClass('active');
          $("#add-edit-button").val("Edit");
          $("#alert-div").hide();
    })
    .fail(function(jqXHR,status,error){                
        $("#alert-div").show();
        console.log(jqXHR);
        console.log(jqXHR.responseText);       
    }); 
});

$(document).on("click", "#btnSynchWhitelist", function(){
  $('#whitelist-modal').modal('show');
  var data = {};    
  data["task"]="5";            
  var jsondata=JSON.stringify(data);            
  $.post("../../modules/ajax/whitelist.php",jsondata,function(result){ 
    $("#whitelist-modal").modal('hide');
    alert(result);
  })
  .fail(function(jqxhr,status,error){
    alert(jqxhr.responseText);

  }); 
});




