<?php
//https://stackoverflow.com/questions/64023550/sessions-are-not-working-when-the-site-is-called-by-an-iframe
include('../../includes/header.php');
?>
<style>
    .thumbnail embed{
        width: 1080px;
        height: 1920px;
    }

    .thumbnail {
        position: absolute; left: 3%; top: 2%;
        -ms-zoom: 0.40;
        -moz-transform: scale(0.40);
        -moz-transform-origin: 0 0;
        -o-transform: scale(0.40);
        -o-transform-origin: 0 0;
        -webkit-transform: scale(0.40);
        -webkit-transform-origin: 0 0;
    } 

</style>
<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    session_set_cookie_params(['samesite' => 'None', 'secure' => true]);
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">Controller Preview</div>
<!--<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <!--<div class="tab-link" data-target="form">Add Playlist to Device</div>-->
   <!-- </div>
</div>-->

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
include('../../classes/adnoc_dashboard.php');
$obj=new dashboard();
?>

<div class="modal fade" id="preview-modal">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Preview</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="thumbnail-container">
                    <div class="thumbnail">               
                        <embed type="text/html" id="preview-webpage" src="http://localhost/PGEGreeting/" >            
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <div class='row'>
                <div class="col form-group">
                        <label for="">Select Controller</label>
                        <!--<input type="text" class="form-control" id="link" required name="link">-->
                        <select id="controller">
                            <option value="0">--Select Controller--</option>
                            <?php $obj->get_controller_devices();?>
                        </select>
                </div>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for=""></label>
                    <button type="submit" class="btn btn-primary btn-info" id="btn-iframe">View</button>
                </div>
            </div>
            
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
	$("#btn-iframe").click(function() {
            var btn_name = $("#btn-iframe").html();
            if(btn_name==="View")
            {
                
                $(".preview-card").removeClass("hidden");
        	$(".thumbnail-container").css("height", 850)
                var src = "http://"+$("#controller").val();
                src+="/PGEGreeting/";
        	$("#preview-webpage_card").attr("src",src); 
               // $("#preview-modal").modal("show");
               // $("#preview-webpage").attr("src",src); 
            }
            else if(btn_name=="Close")
            {
                $("#iframe-page").hide();
                $("#btn-iframe").html("View");
            }
                
	});
});
</script>
