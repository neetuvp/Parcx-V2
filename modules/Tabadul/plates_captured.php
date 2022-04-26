<?php

$page_title="Application";

//# Import application layout.
include('../../includes/header.php');
include('../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header">Plates Captured</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
include('classes/parking.php');
$obj = new parking();
?>
<!-- Modal -->
<div class="modal fade" id="image-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ANPR Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="image-content-modal">
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

                <!--<div class="col-md-2">
                    <select class="form-control" id="device">
                        
                    </select>
                </div>-->
		<div class="col-md-2">                    
                    <select class="form-control" id="device">
                        <option value=0>Select Device</option>
                        <option value = '1'>Camera 1</option>
                        <option value = '2'>Camera 2</option>
                    </select>
                </div>
                <div class="col-md-2">                    
                     <input type="text" class="form-control" id="plate" placeholder="Plate Number">
                </div>

                <!-- date and time -->
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                        <input type="text" class="form-control float-right" id="reservationtime" autocomplete="off"
                            placeholder="Choose Date and Time Range">
                    </div>
                </div>

                <!-- search -->
                <div class="col-md-1">
                    <button type="button" class="btn btn-block btn-secondary" id="view-report-button">Search</button>
                </div>

                <!-- loader -->
                <div class='col-1' id='loader'>
                    <img src='../../dist/img/loading.gif'>
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
                <div class="card-body" id="plates-captured-report-content">
		No Records Found
                </div>
            </div>
            <!-- / end report -->

        </div>

</div>
</section>
</div>

<script>
    $('#view-report-button').click(function (event) {
        callReport();

    }); //

    function callReport()
    {
        var device = $("#device").val();
        var daterange = $("#reservationtime").val();
        var from = daterange.substring(0, 19);
        var to = daterange.substring(22, 41);
        var plate = $('#plate').val();
        var type = $('#plate_type').val();
        var area = $('#plate_area').val();
        //var country = $('#plate_country').val();

        if ((!daterange)) {
            alert("choose date range");
        } else {
            var data = {
                device: device,
                toDate: to,
                fromDate: from,
                plate: plate,
                type: type,
                area: area,
                //country: country
            };
            var temp = JSON.stringify(data);
            //alert(temp);
            $.post("ajax/parking.php?task=2", temp)
                .done(function (result) {
                    $("#plates-captured-report-content").html(result);
                    reportSuccess();
		    loadDataTable();
                }, "json");
        } // end if

        event.preventDefault();
        
    }
    
    
    $('body').on('click', "[data-target='#image-modal']", function () {
    var id = $(this).data('value');
    var camera_no = $('#camera_no'+id).val();
    var plate_image = $('#plate_image'+id).val();
	//console.log("Image:"+plate_image);
    var data = {
        camera_no:camera_no,
        plate_image:plate_image
    };
    var json = JSON.stringify(data)
    //console.log(json);
    $.post("ajax/parking.php?task=10", json)
    .done(function (result) {
        //console.log(result)
    $("#image-content-modal").html(result);
    }, "json");
});

$("#language").change(function ()
{
    update_session();
    callReport();
    /*loadReportLabels();
    */

});
</script>
<?php include('../../includes/footer.php');?>

