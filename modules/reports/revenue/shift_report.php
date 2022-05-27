<?php

$page_title="Application Home";

//# Import application layout.
include('../../../includes/header.php');
$access = checkPageAccess("shift_report");
include('../../../includes/navbar-start.php');



$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=4;
$json=parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["shift_report"]?></div>

<?php

include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>

<!-- Modal -->

<div class="modal fade text-dark" id="detailModal"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body">
          
        <div class="card">
          <div class="card-header bg-light">
            <h3 class="card-title d-inline" id='modal_title'><?=$json["detailed_shift"]?></h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <div id="shift-detail-content" class="p-0">              
            </div>
          </div>
          <!-- /.card-body -->
        </div>
      </div>
    </div>
  </div>
</div>



<div class="content-wrapper">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1">

      <div class="flex-grow-1 row additional-menu-left">

      <!-- carparks multiselect -->
        <div class="col-md-2">
          <select class="form-control" id="multiselect" multiple="multiple">
          <?php echo parcxV2Settings(array("task"=>"12"));?>
          </select>
        </div>

        <!-- payment devices multiselect-->
        <div class="col-md-2">
          <select class="form-control" id="deviceNumber" multiple="multiple">
            <?php echo parcxV2Settings(array("task"=>"14","type"=>"3,4,5"));?>
          </select>
        </div>

        <!-- operator -->
        <div class="col-md-2">
          <select class="form-control" id="operatormultiple"  multiple="multiple">
            <?php echo parcxV2Settings(array("task"=>"36"));?>
          </select>
        </div>
        
<?php include('../../../includes/additional-menu-report.php');?>       
  <section class="content">
    <div class="container-wide" id="report-content">      
          <?php                      
          $current_date=date("Y-m-d");    
          $data["from"]=$current_date." ".DAY_CLOSURE_START;
          $data["to"]=$current_date." ".DAY_CLOSURE_END;           
          $data["carpark"]="";    
          $data["device"]="";	
          $data["operator"]="";	  
          $data["language"] = $_SESSION["language"];
        $data["task"]=15; 
          echo parcxV2Report($data); 
          ?>                 
    </div>

</div>
</section>
</div>

<?php include('../../../includes/footer.php');?>

<script>
var id;
var date_range_message="choose date range";
from="<?=$current_date." ".DAY_CLOSURE_START?>";
to="<?=$current_date." ".DAY_CLOSURE_END?>";

$('body').on('click', '#shift_detail', function () 
  {
  var data={};
  data["shift_id"]=$(this).attr('shift_id');
  data["task"]=16;   
  data["language"] = $("#language").val();
  var jsondata = JSON.stringify(data);      
  $.post("../../ajax/reports.php",jsondata,function(data)
    {	
    $("#shift-detail-content").html(data); 
		load_report=1;
    })
  .fail(function(jqxhr,status,error)
    {
    alert("Error: "+error);
    }); 


  }); // end click event function 

$(function() 
{
    $('#multiselect').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,      
        selectAllText: "<?=$json["all_carparks"]?>",
        nonSelectedText: "<?=$json["select_carparks"]?>",
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_carparks"]?>",       
        });    
    $('#operatormultiple').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?=$json["all_operators"]?>",               
        nonSelectedText:"<?=$json["select_operators"]?>",       
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_operators"]?>"      
        }); 
    $('#deviceNumber').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?=$json["all_devices"]?>",               
        nonSelectedText:"<?=$json["select_devices"]?>",       
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_devices"]?>"  
        });
});
//$('#view-report-button').click(function (event) 
 function callReport()
  { 
    var data={};
    data["from"]=from;
    data["to"]=to;         
    data["carpark"]=$("#multiselect").val().toString(); 
    data["device"]=$("#deviceNumber").val().toString();  
    data["operator"]=$("#operatormultiple").val().toString();
    data["language"] = $("#language").val();
    data["task"]=15;   
    var jsondata = JSON.stringify(data);    
    $.post("../../ajax/reports.php",jsondata,function(data)
      {		
       loadReport(data);  
      });  
  
  }
//}); // end traffic report by day 

$('#view-report-button').click(function (event) 
    { 	
    if (!daterange)		
        alert(date_range_message);        		
    else 
	callReport();	    
    });


  /* Excel Export */

  $('#export_excel_report').click(function (event) {
    export_to_excel("#report-content", "PARCX_Shift_Report")
  });
  
  
  function loadReportLabels()    
    {
    var data={};
    data["task"]=29;
    data["language"]=$("#language").val();    
    data["page"]=4;
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {		              
        var json=JSON.parse(data);
        date_range_message=json.choose_datetime_range;
        $("#reservationtime").attr('placeholder',json.choose_datetime_range);        
        $("#pdf-report-header").html(json.shift_report);   
        $("#view-report-button").html(json.view_report);   
        $("#export").html(json.export);   
        $("#export_excel_report").html(json.export_to_excel);           
        $("#export_pdf_report").html(json.export_to_pdf); 
        $("#logout").html(json.logout); 
        $("#modal_title").html(json.detailed_shift); 
                
        search_label=json.search;   
        entries_label= json.entries_label;
        info_label=json.info_label;
        previous_label=json.previous;
        next_label=json.next;        
      
        
                                        
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
    
        $('#operatormultiple').multiselect('destroy');
        $('#operatormultiple').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: json.all_operators,
            nonSelectedText:json.select_operators,
            selectAllNumber: false,
            allSelectedText: json.all_operators
            });   
        
        });    
    }

$("#language").change(function()
    {
     update_session();   
    loadReportLabels();    
    callReport();		
    }); 

</script>
