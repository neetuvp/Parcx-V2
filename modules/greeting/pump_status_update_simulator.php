<?php

$page_title="Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');

?>

</ul>

<div class="header text-dark" id="pdf-report-header">Pump Status</div>

<?php

include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
include('classes/cms.php');
$obj=new cms();
?>
<div class="modal fade show" id="modal-default"  aria-modal="true" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">STAGE</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>          
        <div class="modal-body p-3">
            <div class="form-group div-message" id="div-message1">
                <label id="label1">Line 1</label>
                <input type="text" class="form-control" id="line_1">
                <select id="customer_type">
                    <option>Known Customer</option>
                    <option>Unknown Customer</option>
                </select>
            </div> 

            <div class="form-group div-message" id="div-message2">
                <label id="label2">Line 2</label>
                <input type="text" class="form-control" id="line_2">
            </div> 
            <div class="form-group div-message" id="div-message3">
                <label id="label3">Line 3</label>
                <input type="text" class="form-control" id="line_3">
            </div> 
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="btn-save">Send</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

<div class="content-wrapper">
  <section class="content">
    <div class="container-wide" id="report-content">          
        <div class="card col-md-2">  
            <div class="card-title">
                <h2>PUMP STATUS</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <select id="pump_details">
                        <?php
                        $obj->pump_selection();
                        ?>
                    </select>
                </div>
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Vehicle Arrived</button> </div> 
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Vehicle Present</button> </div> 
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Fueling Started</button> </div> 
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Rewards</button> </div>                     
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Fueling Completed</button> </div>                     
                <div class="form-group"><button type="button" class="btn btn-block btn-secondary btn-pump-status" >Vehicle Away</button> </div>                    
            </div>                
        </div>           
    </div>
  </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
    var data = {};
    var status;
$(".btn-pump-status").click(function(){   
    status=$(this).html();
    data = {};
    data["status"]=status;
    $(".div-message").hide();
    $("#customer_type").hide();
    $("#line_1").show();
    $("input").val("");
    switch (status){
        case "Vehicle Arrived":break;
        case "Vehicle Present":
            $("#label1").html("Customer Type");
            $("#div-message1").show();
            $("#customer_type").show();
            $("#line_1").hide();            
            break;
        case "Fueling Started":
            $("#label1").html("Message");
            $("#label2").html("Fuel Type");
            $("#div-message1").show();
            $("#div-message2").show();
            break;
        case "Rewards":
            $("#label1").html("Message");
            $("#label2").html("Rewards Points earned");   
             $("#div-message1").show();
            $("#div-message2").show();
            break;
        case "Fueling Completed":
            $("#label1").html("Message");
            $("#label2").html("Quantity Filled");
            $("#label3").html("Amount to Pay");
            $(".div-message").show();
            break;
        case "Vehicle Away":break;
    }
    $(".modal-title").html(status);
    $("#modal-default").modal('show');
});

$("#btn-save").click(function () {   
    data["pump_details"]=$("#pump_details").val();
    switch (status){
        case "Vehicle Arrived":break;
        case "Vehicle Present":           
            data["customer_type"]=$("#customer_type").val();
            break;
        case "Fueling Started":
            data["message"]=$("#line_1").val();
            data["fuel_type"]=$("#line_2").val();
           
            break;
        case "Rewards":
            data["message"]=$("#line_1").val();
            data["rewards_points_earned"]=$("#line_2").val();                       
            break;
        case "Fueling Completed":
            data["message"]=$("#line_1").val();
            data["quantity_filled"]=$("#line_2").val();  
            data["amount_to_pay"]=$("#line_3").val();             
            break;
        case "Vehicle Away":break;
    }                
    
    var jsondata = JSON.stringify(data);
    console.log(jsondata);
    $.post("update_pump_status.php", jsondata, function (result) { 
        console.log(result);
        $("#modal-default").modal('hide');
    });
});
</script>