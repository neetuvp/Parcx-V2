
<?php 
$page_title="Visitor frequency";

include('../../../includes/header.php');
$data=array();
$data["task"]=29;     
$data["language"]=$_SESSION["language"];
$data["page"]=16;
$json=parcxReport($data);
?>

<div class="navbar-has-tablink">

<?php
include('../../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["visitor_frequency"]?></div>

<?php 
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>



<!-- Modal > Void Reason -->
<div class="modal fade text-dark" id="access_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="void-reason-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Plate Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body pt-4 pl-4 pr-4">
                
                
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info close' data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->

<!-- Active Text Messages-->
<div class="content-wrapper block-data" data-status="active_messages">
<!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1 align-items-center">

      <div class="flex-grow-1 row additional-menu-left">

        <div class="col-md-2">
          <!--<input type="text" id="serial" class="form-control" placeholder="TAG SERIAL">-->
		  <select class="form-control" id="visit_type">
			<option value="all" id="all_category"><?=$json["all_category"]?></option>
            <option value="shortterm" id="shortterm"><?=$json["short_term"]?></option>
            <option value="contract" id="contract"><?=$json["contract"]?></option>
          </select>
        </div>
		
		
        
        <!-- search -->
        <div class="col-md-2">
          <button type="button" class="btn btn-block btn-secondary" id="view-report-button"><?=$json["view_report"]?></button>
        </div>

        <!-- loader -->
        <div class='col-1' id='loader'>
          <img src='../../../dist/img/loading.gif'>
        </div>

      </div>

      <div class="additional-menu-right">
        <div id="action-buttons">
          <div class="btn-group">
            <button type="button" class="btn btn-warning">Export</button>
            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu">
              <a class="dropdown-item" href="#" id="export_excel_report">Export to Excel</a>

              <a class="dropdown-item" href="#" id="export_pdf_report">
                Export to PDF
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end / additional menu -->

  <section class="content">
    <div class="container-wide" id="report-content">    
        <?php echo parcxReport(array("task"=>"6","visit_type"=>"all","language"=>$_SESSION["language"]));?>        
    </div>
</section>
</div>

<script>

  function callReport()
    { 
    var visit_type = $("#visit_type").val();       
    var data={};
    data["visit_type"]=visit_type;
    data["task"]=6;
    data["language"]=$("#language").val();
    var temp = JSON.stringify(data);    

    $.post("../../ajax/reports.php", temp)
      .done(function (result) {
        loadReport(result);
      }, "json");
    event.preventDefault();
  }
  
  
$('#view-report-button').click(function (event) 
{ 	
    callReport();	    
});

function loadReportLabels()    
    {
    var data={};
    data["task"]=29;
    data["language"]=$("#language").val();    
    data["page"]=16;
    var json = JSON.stringify(data);
    $.post("../../ajax/reports.php",json,function(data)
        {	
        var json=JSON.parse(data);      
        $("#pdf-report-header").html(json.visitor_frequency);   
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
 
 
            
        $("#shortterm").html(json.short_term);
        $("#contract").html(json.contract);
        $("#all_category").html(json.all_category);
        
        
        });    
    }

$("#language").change(function()
    {	
    update_session();   	    
    loadReportLabels();    
    callReport();		
    });   


function tdclick(ticket_id)
{	
    var visit_type = $("#visit_type").val();       
    var data={};
    data["task"]=32;
    data["ticket_id"] = ticket_id;
    data["language"]=$("#language").val();
    var temp = JSON.stringify(data);    
    $.post("../../ajax/reports.php", temp)
      .done(function (result) {
	$(".modal-body").html(result);
        $('#access_details').modal('show');
      }, "json");
	        
}

  $('#export_excel_report').click(function (event) 
    {
    export_to_excel("#report-content", "PARCX_VisitorFrequency")
    });
    
</script>
<?php include('../../../includes/footer.php');?>
