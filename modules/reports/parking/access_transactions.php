<?php

$page_title="Application";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header"> Access Transactions</div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade" id="image-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ANPR Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="image-content-modal">
		<!-- info -->
		<div class="row no-gutters">                                          
                    <div class="col-12">

                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <span class="nav-link"><strong>Capture Date</strong>
                                    <span class="float-right" id="date_modal">2022-05-11 09:00:00</span>
                                </span>
                            </li>
			    <li class="nav-item">
                                <span class="nav-link"><strong>Duration of Stay</strong>
                                    <span class="float-right" id="date_modal">1 min 30 sec</span>
                                </span>
                            </li>
			    <li class="nav-item">
                                <span class="nav-link"><strong>Camera</strong>
                                    <span class="float-right" id="camera_modal">Camera 71</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link"><strong>Gate Code</strong>
                                    <span class="float-right" id="gate_code_modal">GS3112121</span>
                                </span>
                            </li>
                        </ul>

                    </div>                                            
                </div>
                <div class="row mb-4">
                    <div class="col-5">
                        <div class="border-simple p-1" id="plate_image_modal"style="width:100%; height:100%;">
				<img src ="default.png" style="width:100%; height:100%;">
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="border-simple h-100 p-3">
                            <h4 class="mb-3" id="device_name">Entry 1</h4>                         
                            <p><strong>Plate Number </strong><span class="float-right" id="plate_number_modal">Q 56785</span></p>                           
                            <p><strong>Country </strong><span class="float-right" id="plate_country_modal">UAE</span></p>
                            <p><strong>Category </strong><span class="float-right" id="plate_type_modal">Private</span></p>
                            <p><strong>Confidence Rate </strong><span class="float-right" id="confidence_rate_modal">98%</span></p>                                                    
                        </div>
                    </div>
                </div>
                <!-- end / info -->
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->


    
    <div class="content-wrapper">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">

                <div class="col-md-2">                    
                    <select class="form-control" id="device">
                        <option value=0>Select Device</option>
                        <option value = '1'>Entry 1</option>
                        <option value = '2'>Entry 2</option>
                    </select>
                </div>

		<div class="col-md-2">                    
                    <select class="form-control" id="device">
                        <option value=0>Select Camera</option>
                        <option value = '1'>Camera 1</option>
                        <option value = '2'>Camera 2</option>
                    </select>
                </div>

		<div class="col-md-2">                    
                    <select class="form-control" id="device">
                        <option value="">Select Delay</option>
                        <option value = "">Delayed Vehicle</option>
                    </select>
                </div>		

                <div class="col-md-1">                    
                     <input type="text" class="form-control" id="plate" placeholder="Plate No.">
                </div>

                <!-- date and time -->
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                        <input type="text" class="form-control float-right" id="reservationtime" autocomplete="off"
                            placeholder="Choose Date-Time Range">
                    </div>
                </div>

                <!-- search -->
                <div class="col-md-1">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button">Search</button>
                </div>

                <!-- loader -->
                <div class='col-1' id='loader'>
                    <img src='../../../dist/img/loading.gif'>
                </div>

            </div>

            <div class="additional-menu-right">
                <div id="action-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" id="export_pdf_report">Export PDF</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end / additional menu -->

    <section class="content">
        <div class="container-wide">

            <!-- begin report -->
            <div class="card">
		<div class="card-body" id="report-content">
			<table id="RecordsTable1" class="table table-blue table-bordered table-striped">
           		<thead><tr>
				<th>Gate</th>
				<th>Entry Device</th>
				<th>Camera</th> 
		   		<th>Plate Number</th>
		   		<th>Time Arrived</th>
				<th>Time Departed</th>		   		
		   		<th>Duration of Stay</th> 
		   		<th>ANPR Details</th> 
				        
           		</tr></thead>
	   		<tbody>
				
				<tr><td>GS3112121</td><td>Entry 1</td><td>Camera 71</td><td>Q 56785</td><td>2022-05-11 09:00:00</td><td>2022-05-11 09:05:30</td><td>5M 30S</td><td><input type="button" data-value = 1 data-toggle="modal" data-target="#image-modal"value="View" class="btn btn-link"></td></tr>
				<tr><td>GS3112122</td><td>Entry 2</td><td>Camera 72</td><td>S 36432</td><td>2022-05-10 16:30:00</td><td>2022-05-10 16:37:20</td><td style="color:red">7M 20S</td><td><input type="button" data-value = 2 data-toggle="modal" data-target="#image-modal"value="View" class="btn btn-link"></td></tr>
				<tr><td>GS3112121</td><td>Entry 1</td><td>Camera 71</td><td>M 12764</td><td>2022-05-10 09:00:00</td><td>2022-05-10 09:03:10</td><td>3M 10S</td><td><input type="button" data-value = 3 data-toggle="modal" data-target="#image-modal"value="View" class="btn btn-link"></td></tr>
				
			</tbody>
			</table>
                </div>
                
            </div>
            <!-- / end report -->

        </div>

</div>
</section>
</div>

<script>
    	$(document).ready(function () {
	 	callReport();
	});

	$('#view-report-button').click(function (event) {
		callReport();
	});  

	 
	function callReport()
	{
		reportSuccess();
	    	$('#RecordsTable1').DataTable();
	}

</script>
<?php include('../../../includes/footer.php'); ?>
