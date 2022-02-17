<?php

$page_title="Application Home";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=19;
$json=parcxReport($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["open_transactions"]?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');

//# App Function Classes

?>
<!-- Modal -->
<div class="modal fade" id="image-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?=$json["anpr_image"]?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="image-content-modal">                                           
              
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<div class="content-wrapper">
  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1">

      <div class="flex-grow-1 row additional-menu-left">
        <!-- carparks -->
        <div class="col-md-2 mb-4">
          <select class="form-control" id="multiselect" multiple="multiple">
          <?php echo parcxSettings(array("task"=>"12"));?>
          </select>
        </div>

        <!-- payment devices multiselect-->
        <div class="col-md-2">
          <select class="form-control" id="deviceNumber" multiple="multiple">
          <?php echo parcxSettings(array("task"=>"14","type"=>"1"));?>
          </select>
        </div>
        <!--parking duration-->
        <div class="col-md-2">
          <select class="form-control" id="parking_duration">          
          <option value="" id="sel_duration"><?=$json["sel_duration"]?></option>
          <option value="0" id="less_1_day"><?=$json["less_1_day"]?></option>
          <option value="1" id="greater_1_day"><?=$json["greater_1_day"]?></option>
          <option value="2" id="greater_2_day"><?=$json["greater_2_day"]?></option>
          <option value="3" id="greater_3_day"><?=$json["greater_3_day"]?></option>
          <option value="4" id="greater_4_day"><?=$json["greater_4_day"]?></option>
          <option value="5" id="greater_5_day"><?=$json["greater_5_day"]?></option>
          <option value="6" id="greater_6_day"><?=$json["greater_6_day"]?></option>
          <option value="7" id="greater_7_day"><?=$json["greater_7_day"]?></option>          
          </select>
        </div>


        <!-- plate -->
        <div class="col-md-2">
          <input type="text" id="plate_number" class="form-control" placeholder="<?=$json["plate_ticket"]?>">
        </div>        
        <?php include('../../../includes/additional-menu-report.php');?>           
  <!-- end / additional menu -->

  <section class="content">
    <div class="container-wide" id="report-content">      
   
        <?php
        $current_date=date("Y-m-d");    
        $data["from"]=$current_date." ".DAY_CLOSURE_START;
        $data["to"]=$current_date." ".DAY_CLOSURE_END;           
        $data["carpark"]="";    
        $data["device"]="";	        
        $data["plate_number"]="";
        $data["parking_duration"]=""; 
        $data["image_location"]=ImageURL;
        $data["language"]=$_SESSION["language"];  
        $data["task"]=1;                          
        echo parcxReport($data);
        ?>   
         
</div>
</section>
</div>

<?php include('../../../includes/footer.php');?>

<script>
$(function() 
    {
    $('#deviceNumber').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?=$json["all_devices"]?>",               
        nonSelectedText:"<?=$json["select_devices"]?>",       
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_devices"]?>"  
        });
        
    $('#multiselect').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,      
        selectAllText: "<?=$json["all_carparks"]?>",
        nonSelectedText: "<?=$json["select_carparks"]?>",
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_carparks"]?>",       
        });    
 });
function callReport()
{    	
    var data={};
    data["from"]=from;
    data["to"]=to;           
    data["carpark"]=$("#multiselect").val().toString();    
    data["device"]=$("#deviceNumber").val().toString();	
    data["plate_number"]=$("#plate_number").val(); 
    data["parking_duration"]=$("#parking_duration").val(); 
    data["language"] = $("#language").val();
    data["image_location"]="<?php echo ImageURL; ?>";
    data["task"]=1;
    var temp = JSON.stringify(data);   
    console.log(temp);
    $.post("../../ajax/reports.php", temp)
      .done(function (result) {        
        loadReport(result);

      }, "json");   
  }
 // }); 
 
 $('#view-report-button').click(function (event) 
{ 	   
    callReport();	    
});

function loadReportLabels()    
    {
    var data={};
    data["task"]=29;
    data["language"]=$("#language").val();    
    data["page"]=19;
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {	
        var json=JSON.parse(data);
        date_range_message=json.choose_datetime_range;
        $("#reservationtime").attr('placeholder',json.choose_datetime_range);        
        $("#pdf-report-header").html(json.open_transactions);   
        $("#view-report-button").html(json.view_report);   
        $("#export").html(json.export);   
        $("#export_excel_report").html(json.export_to_excel);           
        $("#export_pdf_report").html(json.export_to_pdf); 
        $("#logout").html(json.logout); 
        search_label=json.search;   
        entries_label= json.entries_label;
        info_label=json.info_label;
        previous_label=json.previous;
        next_label=json.next;        
        $("#sel_duration").html(json.sel_duration);
        $("#less_1_day").html(json.less_1_day);
        $("#greater_1_day").html(json.greater_1_day);
        $("#greater_2_day").html(json.greater_2_day);
        $("#greater_3_day").html(json.greater_3_day);
        $("#greater_4_day").html(json.greater_4_day);
        $("#greater_5_day").html(json.greater_5_day);
        $("#greater_6_day").html(json.greater_6_day);
        $("#greater_7_day").html(json.greater_7_day);
        $("#exampleModalLabel").html(json.anpr_image);
        $("#plate_number").attr('placeholder',json.plate_ticket);
        $('#deviceNumber').multiselect('destroy');
        $('#deviceNumber').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: json.all_devices,                                    
            nonSelectedText:json.select_devices,                   
            selectAllNumber: false,
            allSelectedText: json.all_devices             
            });  
            
        $('#multiselect').multiselect('destroy');
        $('#multiselect').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,      
            selectAllText: json.all_carparks,
            nonSelectedText: json.select_carparks,
            selectAllNumber: false,
            allSelectedText: json.all_carparks
            }); 
    
        
        });    
    }

$("#language").change(function()
    {
    update_session();    
    loadReportLabels();    
    callReport();		
    });       

$('#export_excel_report').click(function (event) 
  {
  export_to_excel("#report-content", "PARCX_Open_Transaction_Report")  
  });


$('body').on('click', "[data-target='#image-modal']", function () 
  {
  var name = $(this).data('value');      
  var url = "<?php echo ImageURL; ?>";
  var image="<img src='"+url+"/"+name+"' width='100%' height='700'>"; 
  $("#image-content-modal").html(image);      
  });

</script>