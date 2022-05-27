<?php 
include('../../includes/header.php'); 
$access = checkPageAccess("anpr_review");
$_SESSION["dashboard"]=1;
?>
<div class="navbar-has-tablink">
    <?php
    include('../../includes/navbar-start.php');
    $data = array();
    $data["task"] = 9;
    $data["language"] = $_SESSION["language"];
    $data["page"] = 5;
    $json = parcxV2Dashboard($data);
    ?>
</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["anpr_review"] ?></div>
<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<!-- Modal Update Plate Information-->
<div class="modal fade" id="UpdatePlateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" class="updatetitle"><?= $json["update_plate_number"] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row">    
                <div class="col-7">
                    <img  id="plate-image" class="img-responsive" src="plateimage.jpg" class="img-rounded" alt="PlateNumber" style="width:100%;height: 500px">
                </div>
                <div class="col-4 ml-4">
                    <label for="PlateNumber" id="current_plate_label"><?= $json["current_plate_number"] ?></label>
                <input class="form-control form-control-lg" type="text" class="plate_number_text" placeholder="<?= $json["plate_number"] ?>" id="plate-number" disabled="">
                <br/><br/>
                <label for="PlateNumber" class="enter_correct_plate"><?= $json["enter_corrected_plate_number"] ?></label>
                <input style="text-transform:uppercase" class="form-control form-control-lg" type="text" class="plate_number_text" placeholder="<?= $json["plate_number"] ?>" id="corrected-plate-number">            
                <br/><br/>
                <h3 id="plate_corrected_result"></h3>
                </div>
                
                
                
            </div>
            <div class="modal-footer">                
                <button type="button" class="btn btn-primary" id="btn-save" title="<?= $json["save_changes"] ?>"><i class="fas fa-save"></i></button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="btn-cancel" title="<?= $json["cancel"] ?>"><i class="fas fa-window-close"></i></button>      
            </div>
        </div>
    </div>
</div>    
<!-- End . Modal for Plate Update -->

<!-- Modal POP to get the operator Attention -->
<div class="modal fade" id="PopUpAlertModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">

            </div>
            <div class="modal-body">        
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading1" ><label id="alert-heading1"></label>
                        <label id="alert-heading1_text"><?= $json["plates_require_attention"] ?></label>
                    </h4>

                    <h4 class="alert-heading2" ><label id="alert-heading2"></label>
                        <label id="alert-heading2_text"><?= $json["plate_mismatch"] ?></label>
                    </h4>
                    <p id="review_text"><?= $json["plate_review_text"] ?></p>
                    <hr>
                    <p class="mb-0" id="click_update_label"> <?= $json["click_update_text"] ?> </p>
                </div>                    
            </div> <!-- End . Modal Body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal" id="footer-ok"><i class="fas fa-check-square"></i></button>      
            </div>
        </div>
    </div>
</div>    
<!-- End . Modal for Plate Update -->

<!-- Modal -->
<div class="modal fade text-dark" id="plate-mismatch-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1" class="manualtitle"><?= $json["manual_plate_correction"] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">      
                <div id="alert-div" class="alert bg-danger d-none">
                    <h5> <?= $json["plates_not_matching"] ?></h5>                                            
                </div>                 
                <label id="result"></label> 
                <div class="row mb-4">
                    <div class="col">
                        <div class="border-simple p-1" id="plate_image1">

                        </div>
                    </div>
                    <div class="col">
                        <div class="border-simple p-1" id="plate_image2">

                        </div>
                    </div>

                </div>
                <!-- end / info -->

                <!-- info -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="border-simple h-100 p-3">
                            <label for="PlateNumber" id="entry_plate_label"><?= $json["entry_plate_number"] ?></label>
                            <input class="form-control form-control-lg" type="text" placeholder="<?= $json["plate_number"] ?>" id="plate_number2" disabled="">
                            <br/>
                            <label for="PlateNumber" class="enter_correct_plate"><?= $json["enter_corrected_plate_number"] ?></label>
                            <input class="form-control form-control-lg" type="text" placeholder="<?= $json["plate_number"] ?>" id="corrected_plate_number2">                                                        
                        </div>
                    </div>
                    <div class="col">
                        <div class="border-simple h-100 p-3">                                                
                            <label for="PlateNumber" id="exit_plate_label"><?= $json["exit_plate_number"] ?></label>
                            <input class="form-control form-control-lg" type="text" placeholder="<?= $json["plate_number"] ?>" id="plate_number1" disabled="">
                            <br/>
                            <label for="PlateNumber" class="enter_correct_plate"><?= $json["enter_corrected_plate_number"] ?></label>
                            <input class="form-control form-control-lg" type="text" placeholder="<?= $json["plate_number"] ?>" id="corrected_plate_number1">
                        </div>
                    </div>                    
                </div>                 
            </div>
            <div class="modal-footer">   
                <button type="button" class="btn btn-success" id="btn-allow"><?= $json["allow"] ?></button>                
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<div class="content-wrapper">  
    <section class="content">
        <div class="container-wide"> 
            <input type="hidden" id="user-id" value="<?php echo $_SESSION['userId']; ?>">
            <input type="hidden" id="user-name" value="<?php echo $_SESSION['user']; ?>">  
            <input type="hidden" id="image-location" value="<?php echo ImageURL; ?>">  

            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title p-1" id="plate_correction_label"><?= $json["plate_correction_required"] ?></h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>                        
                    </div>
                </div>

                <!--<div class="card-body" style="display: block;">
                    <table id="plate-correction-required" class="table table-bordered table-hover"></table>
                </div>-->     
                <div class="card-body" id="plate-correction-required" style="display: block;">
                </div>
            </div>

            <br>

            <div class="card card-light">
                <div class="card-header"><h3 class="card-title col" id="plate_mismatch_label"><?= $json["plate_mismatch"] ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>                        
                    </div>
                </div>                       
                <!--<div class="card-body" style="display: block;">
                    <table id="plate-mismatch" class="table table-bordered table-hover"></table>
                </div>-->   
                <div class="card-body" id="plate-mismatch" style="display: block;">
                </div>
            </div>
            <br>

            <div class="card card-light">
                <div class="card-header"><h3 class="card-title col" id="plates_corrected_label"><?= $json["plates_corrected"] ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>                        
                    </div>
                </div>            
                <!--<div class="card-body" style="display: block;">
                    <table id="plate-correction-complete" class="table table-bordered table-hover"></table>
                </div> -->   
                <div class="card-body" id="plate-correction-complete" style="display: block;">
                </div>
            </div>                   
        </div>             
    </section>
</div>
<script>

    var eventTime = 0;
    var currentTime = 0;
    var withoutEvent = 0;
    $(".alert-heading1").hide();
    $(".alert-heading2").hide();
    $(document).on('click mouseover keydown', function (evt) {
        eventTime = Date.now();
    });

    function correction_required(popup)
    {
        var data = {};
        data["task"] = 1;
        data["language"] = $("#language").val();
        var json = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            $("#plate-correction-required").html(data);

            var count = $('#plate-correction-required tr').length;
           
            if (count > 0)
            {                
                loadMultipleDataTable("TABLE_1");
                if(popup==1)
                    {
                    $(".alert-heading1").show();
                    $("#alert-heading1").html((count - 1));
                    $('#PopUpAlertModal').modal('show');    
                    }
                
            }
        });
    }

    function loadMultipleDataTable(table_id)
    {
        $("#" + table_id).DataTable(
                {
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "aaSorting": [],
                    "language": {
                        search: search_label,
                    },
                    "oLanguage": {
                        "sLengthMenu": entries_label,
                        "info": info_label,
                        "oPaginate": {
                            "sPrevious": previous_label,
                            "sNext": next_label
                        }
                    },
                });
    }

    function plate_mismatch()
    {
        var data = {};
        data["task"] = 6;
        data["language"] = $("#language").val();
        var json = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            $("#plate-mismatch").html(data);
            var count = $('#plate-mismatch tr').length;            
            if (count > 0)
            {
                
                loadMultipleDataTable("TABLE_2");                
                $("#alert-heading2").html((count - 1));
                $('#PopUpAlertModal').modal('show');
            }
        });
    }

    function number_plate_corrected()
    {
        var data = {};
        data["task"] = 3;
        data["language"] = $("#language").val();
        var json = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            $("#plate-correction-complete").html(data);
            loadMultipleDataTable("TABLE_3");
            //$("#plate-correction-complete").DataTable();
        });
    }

    $(function ()
    {
        correction_required(1);
        number_plate_corrected();
        plate_mismatch();

        setInterval(function ()
        {
            currentTime = Date.now();
            withoutEvent = currentTime - eventTime;
            withoutEvent = withoutEvent / 1000; //seconds       
            if ($('#PopUpAlertModal').is(':visible') == false && $('#UpdatePlateModal').is(':visible') == false)
            {
                if (withoutEvent >= 60 * 3)
                {
                    correction_required(1);
                    plate_mismatch();
                }
            }
        }, 1000 * 30);

    });

    var id;
    var plate;
    var update;
    var updated_plate
    $('body').on('click', "[data-target='#UpdatePlateModal']", function ()
    {
        var name = $(this).data('value');
        id = $(this).data('id');
        plate = $(this).data('plate');
        update = $(this).data('update');
        $("#btn-allow").hide();
        $("#plate_corrected_result").html("");
        var location = $("#image-location").val() + "/";
        $("#plate-image").attr("src", location + name);
        $("#plate-number").val(plate);
    });

    $(document).on('click', '#btn-save', function ()
    {
        var data = {};
        data["task"] = 2;
        data["language"] = $("#language").val();
        data["id"] = id;
        data["plate_number"] = plate;
        data["update"] = update;
        updated_plate=$("#corrected-plate-number").val();
        updated_plate=updated_plate.toUpperCase();
        data["corrected_plate_number"] = updated_plate;
        data["user_id"] = $("#user-id").val();
        data["user_name"] = $("#user-name").val();
        var json = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {            
            $("#plate_corrected_result").html(data);   
            update=1;
            $("#plate-number").val(updated_plate);
            $("#corrected-plate-number").val("");
            correction_required(0);
            number_plate_corrected();
        });


    });


    $(document).on('click', '.btn-plate-mismatch', function ()
    {
        id = $(this).data('id');
        var data = {};
        data["task"] = 7;
        data["language"] = $("#language").val();
        data["id"] = id;
        var json = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            console.log(response);
            var response = JSON.parse(data);
            $("#plate-mismatch-modal").modal("show");
            var image_url = "<?php echo ImageURL ?>";
            $('#plate_image1').html('<img src ="' + image_url + '/' + response["entry_image"] + '" width="100%"; height="100%";>');
            $('#plate_number1').val(response["entry_plate_number"]);
            $('#corrected_plate_number1').val(response["entry_plate_number"]);
            $('#plate_image2').html('<img src ="' + image_url + '/' + response["exit_image"] + '" width="100%"; height="100%";>');
            $('#plate_number2').val(response["exit_plate_number"]);
            $('#corrected_plate_number2').val(response["exit_plate_number"]);
            $('#result').html("<h5>" + response["result_description"] + "</h5>");
        });


    });

    $(document).on('click', '#btn-allow', function ()
    {
        $("#alert-div-edit-plate").addClass("d-none");
        var data = {};
        data["task"] = 8;
        data["language"] = $("#language").val();
        data["id"] = id;
        data["plate_number1"] = $('#plate_number1').val();
        data["plate_number2"] = $('#plate_number2').val();
        data["corrected_plate_number1"] = $('#corrected_plate_number1').val();
        data["corrected_plate_number2"] = $('#corrected_plate_number2').val();

        data["user_id"] = $("#user-id").val();
        data["user_name"] = $("#user-name").val();

        if (data["corrected_plate_number1"] != data["corrected_plate_number2"])
            {
            $("#alert-div").removeClass("d-none");
            } 
        else
            {
            var json = JSON.stringify(data);
            console.log(json);
            $.post("../ajax/dashboard-ajax.php", json, function (data)
                {                
                location.reload();
                });
            }

    });

    $("#language").change(function ()
    {
        update_session();
        loadReportLabels();
        correction_required(1);
        number_plate_corrected();
        plate_mismatch();
    });

    function loadReportLabels()
    {
        var data = {};
        data["task"] = 9;
        data["language"] = $("#language").val();
        data["page"] = 5;
        var json = JSON.stringify(data);
        //console.log(json);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            //console.log(data);	            
            var json = JSON.parse(data);
            $("#pdf-report-header").html(json.anpr_review);
            $("#logout").html(json.logout);
            $("#plate_correction_label").html(json.plate_correction_required);
            $("#plate_mismatch_label").html(json.plate_mismatch);
            $("#plates_corrected_label").html(json.plates_corrected);
            $("#entry_plate_label").html(json.entry_plate_number);
            $(".enter_correct_plate").html(json.enter_corrected_plate_number);
            $("#btn-save").attr("title",json.save_changes);
            $("#btn-cancel").attr("title",json.cancel);
            $("#exit_plate_label").html(json.exit_plate_number);
            $("#btn-allow").html(json.allow);
            $("#current_plate_label").html(json.current_plate_number);
            $("#exampleModalLabel").html(json.update_plate_number);
            $("#exampleModalLabel1").html(json.manual_plate_correction);
            $("#review_text").html(json.plate_review_text);
            $("#click_update_label").html(json.click_update_text);
            $("#footer-ok").attr("title",json.ok);
            $(".plate_number_text").html(json.plate_number);
            $("#alert-heading1_text").html(json.plates_require_attention);
            $("#alert-heading2_text").html(json.plate_mismatch);
            $("#alert-div").html(json.plates_not_matching);
            $("#corrected-plate-number").attr("placeholder", json.plate_number);
            $("#corrected_plate_number1").attr("placeholder", json.plate_number);
            $("#corrected_plate_number2").attr("placeholder", json.plate_number);
            search_label = json.search;
            entries_label = json.entries_label;
            info_label = json.info_label;
            previous_label = json.previous;
            next_label = json.next;

        });
    }
    
    

</script>
<?php include '../../includes/footer.php'; ?>
