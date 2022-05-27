<?php

$page_title="Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');

?>

</ul>

<div class="header text-dark" id="pdf-report-header">Report</div>

<?php

include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1">
      <div class="flex-grow-1 row additional-menu-left">  
<!--           <div class="col-md-2">
                    <select class="form-control" id="pump_id" multiple="multiple">
                        <option value="1">Pump1</option>
                        <option value="3">Pump2</option>
                    </select>
                </div>-->
            <!-- date and time -->
            <div class="col-md-2">
                <div class="input-group">
                    <input type="date" id="report_date">
                </div>
            </div>

            <!-- search -->
            <div class="col-md-2">
                <button type="button" class="btn btn-block btn-secondary" id="view-report-button">View Report</button>
            </div>          
        </div>

    </div>
</div>
<!-- end / additional menu -->

  <section class="content">
    <div class="container-wide" id="report-content">          
               
    </div>
  </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
var date_range_message = "choose date range";
$(function ()
    {
        
    $('#pump_id').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "Select all pump",
        nonSelectedText: "Select pump",
        selectAllNumber: false,
        allSelectedText: "Select all pump"
        });
    });
function callReport()
    {
    var data = {};
    data["task"] = "3";
    data["report_date"] = $("#report_date").val();    
    //data["pump_id"] = $("#pump_id").val().toString();
    var jsondata = JSON.stringify(data);
    //console.log(jsondata);
    $.post("ajax/parking.php", jsondata, function (result)
        {
         $('#report-content').html(result);
         loadDataTable();
        });
    }

$('#view-report-button').click(function (event) 
    { 	
    if ($("#report_date").val()=="")		
        alert("Choose date");        		
    else 
        callReport();	    
    });

 
</script>