<?php
//https://stackoverflow.com/questions/64023550/sessions-are-not-working-when-the-site-is-called-by-an-iframe
include('../../includes/header.php');
$access = checkPageAccess("greeting_stage_color");
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    session_set_cookie_params(['samesite' => 'None', 'secure' => true]);
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Greeting Stage Background Color</div>
<!--<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <!--<div class="tab-link" data-target="form">Add Playlist to Device</div>-->
   <!-- </div>
</div>-->

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
 
<!-- Info modal -->
<div class="modal fade" id="info-modal" tabindex="-1" role="dialog" aria-labelledby="edit-preview" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4 pb-4" id='info-message'> 
            </div>
        </div>
    </div>
</div>
<!-- Info modal -->


<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">
	    <div class='row'>
                <div class="col-4 form-group">
                        <label for="">Select Stage</label>
                        <select id="stage">
                            <?php parcxGreetingScreen(array("task"=>"10")) ?>    
                        </select>
                </div>
            </div>        
            <div class='row'>
                <div class="col-4 form-group">
                        <label for="">Select Background</label>
                        <select id="bg">
                            <option value="0">--Select Background--</option>
                            <option style = 'background-color:#ffc107;' value="bg-yellow">Yellow</option>
			    <option style = 'background-color:#0099d3;' value="bg-blue">Blue</option>
			    <option style = 'background-color:#dc3545;' value="bg-red">Red</option>
			    <option style = 'background-color:#28a745;' value="bg-green">Green</option>
			    <option style = 'background-color:#fd7e14;' value="bg-orange">Orange</option>
			    <option style = 'background-color:#e83e8c;' value="bg-pink">Pink</option>
			    <option style = 'background-color:#343a40;' value="bg-dark">Dark</option>
			    <option style = 'background-color:#6f42c1;' value="bg-violet">Violet</option>
                        </select>
                </div>
            </div>
	    <?php 
            echo '<div class="row">';
            echo '<div class="col form-group">';
            echo '<label for=""></label>';
		
	    if($access["edit"]==1)
            	echo '<button type="submit" class="btn btn-primary btn-info" id="btn-save-color">Save</button>';
            else
		echo 'Access Denied!';
            echo '</div>';
            echo '</div>';
            ?>
            <div class="row justify-content-center hidden preview-card">
                <div class="thumbnail-container card card-body col-4" >
                    <div class="thumbnail ml-4 p-4">               
                        <embed type="text/html" id="preview-webpage_card" src="" >            
                    </div>
                </div>  
            </div>
        </div>
    </section>
</div>
<?php include('../../includes/footer.php'); ?>

<script> 
$(document).ready(function() {
	$("#btn-save-color").click(function() {   
		var data = {};           
		data["stage"] = $("#stage").val();
		data["bg"] = $("#bg").val();
		data["task"] = 14;
		var jsondata = JSON.stringify(data);       
		$.post("ajax/greeting_screen.php", jsondata, function (result) {       
		    if (result === "Success")
		    {
		        $("#info-message").html("Update Successful");
		        $("#info-modal").modal('show');

		    } else
		        $("#info-message").html(result);
		    });               
		});
});
$('#info-modal').on('hidden.bs.modal', function () {
        location.reload();
    })
</script>
