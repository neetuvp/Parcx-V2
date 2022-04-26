<?php

$page_title="Application Home";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=23;
$json=parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["watchdog_network_logs"]?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>

<div class="content-wrapper">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1 align-items-center">

      <div class="flex-grow-1 row additional-menu-left">
          <!-- carparks -->
            <div class="col-md-2">
                <select class="form-control" id="multiselect" multiple="multiple">

                    <?php echo parcxV2Settings(array("task" => "12")); ?>
                </select>
            </div>

            <!-- devices multiselect-->
            <div class="col-md-2">
                <select class="form-control" id="deviceNumber" multiple="multiple">
                    <?php echo parcxV2Settings(array("task" => "14", "type" => "")); ?>
                </select>
            </div>

        <?php include('../../../includes/additional-menu-report.php');?>   
  <!-- end / additional menu -->

  <section class="content">
    <div class="container-wide" id="report-content">     
        <?php 
           $current_date=date("Y-m-d");  
           $data=array();  
           $data["from"]=$current_date;
           $data["to"]=$current_date;                          
           $data["task"]=28;     
           $data["language"]=$_SESSION["language"]; 
           echo parcxV2Report($data);           
       ?>     
    </div>

</div>
</section>
</div>

<?php include('../../../includes/footer.php');?>

<script>
from="<?=$current_date." ".DAY_CLOSURE_START?>";
to="<?=$current_date." ".DAY_CLOSURE_END?>";
$(function ()
    {
    $('#deviceNumber').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?= $json["all_devices"] ?>",
        nonSelectedText: "<?= $json["select_devices"] ?>",
        selectAllNumber: false,
        allSelectedText: "<?= $json["all_devices"] ?>"
        });

    $('#multiselect').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?= $json["all_carparks"] ?>",
        nonSelectedText: "<?= $json["select_carparks"] ?>",
        selectAllNumber: false,
        allSelectedText: "<?= $json["all_carparks"] ?>",
        });
    });
                
$('#view-report-button').click(function () 
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
    data["page"]=23;
    
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {		              
        var json=JSON.parse(data);
        $("#reservationtime").attr('placeholder',json.choose_datetime_range);
        $("#pdf-report-header").html(json.watchdog_network_logs);   
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
    
        
        $("#alarm-severity-select").html(json.select_severity); 
        $("#alarm-severity-low").html(json.low); 
        $("#alarm-severity-medium").html(json.medium); 
        $("#alarm-severity-high").html(json.high);  
                                        
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
    
function callReport()
{
    var data = {
        to: to,
        from: from,
        carpark: $("#multiselect").val().toString(),
        device: $("#deviceNumber").val().toString(),
        language:$("#language").val(),
        task:28
      };      
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
    
  $('#export_excel_report').click(function (event) {
    export_to_excel("#report-content", "PARCX_Watchdog_network_Report")

  }); // end click event function
</script>
