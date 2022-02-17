<?php

$page_title="Application";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=21;
$json=parcxReport($data);

?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["validation_report"]?></div>

<?php

include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>



<div class="content-wrapper">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1">

      <div class="flex-grow-1 row additional-menu-left">
        <!-- carparks multiselect -->
        <div class="col-md-3 mb-4">
          <select class="form-control" id="multiselect" multiple="multiple">
          <?php echo parcxSettings(array("task"=>"12"));?>
          </select>
        </div>

        <!-- Validators -->
        <div class="col-md-3">
          <select class="form-control" id="validator" multiple="multiple">          
          <?php echo parcxSettings(array("task"=>"20"));?>
          </select>
        </div>

        <!-- Validators -->
        <div class="col-md-2">
          <select class="form-control" id="validation_product" multiple="multiple">          
          <?php echo parcxSettings(array("task"=>"21"));?>
          </select>
        </div>

        <!-- Ticket number -->
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
        $data["validator"]="";	        
        $data["product"]="";	        
        $data["plate_number"]=""; 
        $data["language"]=$_SESSION["language"];   	
        $data["task"]=3;                          
        echo parcxReport($data);
        ?>          
    </div>
  </section>
</div>

<?php include('../../../includes/footer.php'); ?>

<script>
$(function() 
{
   $('#multiselect').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,      
        selectAllText: "<?=$json["all_carparks"]?>",
        nonSelectedText: "<?=$json["select_carparks"]?>",
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_carparks"]?>"       
        });      
      
  $('#validator').multiselect({
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "<?=$json["all_validators"]?>",
        nonSelectedText: "<?=$json["select_validator"]?>",
        selectAllNumber: false,
        allSelectedText: "<?=$json["all_validators"]?>"
      });
      

    $('#validation_product').multiselect({
      buttonWidth: '100%',
      includeSelectAllOption: true,
      selectAllText: "<?=$json["all_products"]?>",
      nonSelectedText: "<?=$json["select_product"]?>",
      selectAllNumber: false,
      allSelectedText: "<?=$json["all_products"]?>"
    });
  });


//$('#view-report-button').click(function (event) 
function callReport()
{ 
    var data={};
    data["from"]=from;
    data["to"]=to;           
    data["carpark"]=$("#multiselect").val().toString();    
    data["validator"]=$("#validator").val().toString();	
    data["product"]=$("#validation_product").val().toString();	
    data["plate_number"]=$("#plate_number").val(); 
    data["language"] = $("#language").val();
    data["task"]=3;
    var temp = JSON.stringify(data);    
    console.log(temp);  
    $.post("../../ajax/reports.php", temp)
      .done(function (result) {        
        loadReport(result);    

      }, "json");
 
    event.preventDefault();
  }
 
 $('#view-report-button').click(function (event) 
{ 	
    if (!daterange)		
        alert(date_range_message);        		
    else 
        callReport();	    
});

 function loadReportLabels()    
    {
    var data={};
    data["task"]=29;
    data["language"]=$("#language").val();    
    data["page"]=21;
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {	
        var json=JSON.parse(data);
        date_range_message=json.choose_datetime_range;
        $("#reservationtime").attr('placeholder',json.choose_datetime_range);        
        $("#pdf-report-header").html(json.validation_report);   
        $("#view-report-button").html(json.view_report);   
        $("#export").html(json.export);   
        $("#export_excel_report").html(json.export_to_excel);           
        $("#export_pdf_report").html(json.export_to_pdf); 
        $("#logout").html(json.logout); 
        $("#plate_number").attr('placeholder',json.plate_ticket); 
        search_label=json.search;   
        entries_label= json.entries_label;
        info_label=json.info_label;
        previous_label=json.previous;
        next_label=json.next;        
 
        $('#validation_product').multiselect('destroy');
        $('#validation_product').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: json.all_products,                                    
            nonSelectedText:json.select_product,                   
            selectAllNumber: false,
            allSelectedText: json.all_products             
            });  
            
        $('#validator').multiselect('destroy');
        $('#validator').multiselect(
            {
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: json.all_validators,                                    
            nonSelectedText:json.select_validator,                   
            selectAllNumber: false,
            allSelectedText: json.all_validators             
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
 
  $('#export_excel_report').click(function (event) {

    export_to_excel("#report-content", "PARCX_Validation_Report")

  }); // end click event function
</script>