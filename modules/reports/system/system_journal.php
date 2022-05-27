<?php
include('../../../includes/header.php');
$access = checkPageAccess("user_activity");
include('../../../includes/navbar-start.php');

$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=28;
$json=parcxV2Report($data);
?>
</ul>
<div class="header text-dark" id="pdf-report-header"><?=$json["user_activity"]?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>

<div class="content-wrapper">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1 align-items-center">

      <div class="flex-grow-1 row additional-menu-left">
      
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
          $data["operator"]="";	  
          $data["language"] = $_SESSION["language"];
            $data["task"]=33; 
          echo parcxV2Report($data); 
          ?>                  
       </div>
     </section>
</div>

<?php include('../../../includes/footer.php');?>

<script>
from="<?=$current_date." ".DAY_CLOSURE_START?>";
to="<?=$current_date." ".DAY_CLOSURE_END?>";

$(function() {
   $('#operatormultiple').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?=$json["all_operators"]?>",               
        nonSelectedText:"<?=$json["select_operators"]?>",       
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_operators"]?>"      
        });
        });

$('#view-report-button').click(function (event) 
    {     
    if ((!daterange))         
        alert("choose date range");        
    else 
        callReport();
        
    }); // end report
    
function loadReportLabels()    
    {
    var data={};
    data["task"]=29;
    data["language"]=$("#language").val();    
    data["page"]=28;
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {		              
        var json=JSON.parse(data);         
        $("#reservationtime").attr('placeholder',json.choose_datetime_range);
        $("#pdf-report-header").html(json.user_activity);   
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
    
function callReport()
    {
    var data={};
    data["from"]=from;
    data["to"]=to;           
    data["operator"]=$("#operatormultiple").val().toString();
    data["language"] = $("#language").val();
    data["task"]=34;          
    var temp = JSON.stringify(data);      
    $.post("../../ajax/reports.php", temp)
        .done(function (result) 
            {
            loadReport(result);                
            }, "json");
    
    }
    
$("#language").change(function()
    {
    update_session();    
    loadReportLabels();    
    callReport();
    });
    
$('#export_excel_report').click(function (event) 
    {
    export_to_excel("#report-content", "PARCX_user_activity_Report")
    }); // end click event function
</script>
