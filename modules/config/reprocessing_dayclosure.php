<?php
$page_title="Application Home";


//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');



?>

</ul>
<div class="header text-dark" id="pdf-report-header">Reprocessing DayClosure Report</div>

<?php

include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- Modal > Reprocess confirm -->
<div class="modal fade text-dark" id="reprocess_confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="reprocess-confirm-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Reprocessing</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body pt-4 pl-4 pr-4">
                <input type="hidden" id="valet_id" value="">
                <h5><i class="icon fa fa-check"></i> Are you sure you want to reprocess the dayclosure report for the selected date?</h5>
                <br>
                
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info' name='ok_reprocess' id='ok_reprocess' value='OK'>Yes</button>
                <button type='button' class='btn btn-info' name='cancel_reprocess' id='cancel_reprocess'
                        value='Cancel'>No</button>
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->

<!-- Modal > Message Modal-->
<div class="modal fade text-dark" id="message_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body pt-4 pl-4 pr-4">
                <h5 id="msg_txt"></h5>
                <br>               
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info' name='ok_message' id='ok_message' value='OK'>OK</button>
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->


<div class="content-wrapper">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1 align-items-center">

      <div class="flex-grow-1 row additional-menu-left">

                                  
          <!-- date and time -->
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                        <input type="text" class="form-control float-right" id="dcdate" autocomplete="off"
                            placeholder="Choose Date">
                    </div>
                </div>

                <!-- search -->
                <div class="col-md-1">
                    <button type="button" class="btn btn-block btn-secondary" id="reprocess-button">Reprocess</button>
                </div>

                <!-- loader -->
                <div class='col-1' id='loader'>
                    <img src='dist/img/loading.gif'>
                </div>

</div>

<?php include('../../includes/footer.php');?>

<script>
//////////////////////////////
// load report data
//////////////////////////////
var id;
var date_message="Choose Date for Reprocessing";
$(function() 
{

    
 $('#dcdate').daterangepicker({
   timePicker: false,
   format: 'YYYY-MM-DD',
   singleDatePicker: true
})
});

$('#reprocess-button').click(function(){
     var dcdate = $("#dcdate").val();
      if (!dcdate)		
        alert(date_message);        		
      else 
        $('#reprocess_confirm').modal('show');
});

$('#ok_reprocess').click(function(){
    var datastring={};
    datastring["reprocess_date"]=$("#dcdate").val();
    datastring["task"]=6;
    datastring["activity_message"]="Reproccess day closure report for  "+datastring["reprocess_date"];
	
    var jsondata = JSON.stringify(datastring);  
        
     $.post("../ajax/daemon_status.php", jsondata, function (data) 
      {	
        if(data!=256)
        {
            $('#reprocess_confirm').modal('hide');
            $('#message_modal').modal('show');
            $('#msg_txt').html("<i class='icon fa fa-check'></i> Please check the day closure reports after 5 minutes");
        }
        else
        {
            $('#reprocess_confirm').modal('hide');
            $('#message_modal').modal('show');
            $('#msg_txt').html("Reprocessing Failed");
            
        }
     
      });    
});

$(document).on('click', '#ok_message', function (){
        $('#message_modal').modal('hide');
    });
 $(document).on('click', '#cancel_reprocess', function (){
        $('#reprocess_confirm').modal('hide');
    });

</script>
