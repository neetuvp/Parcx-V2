<?php
$page_title = "Device Dashboard";
include('../../includes/header.php');
$access = checkPageAccess("device_status");
$_SESSION["dashboard"]=1;
?>

<div class="navbar-has-tablink navbar-has-sublink">

    <?php
    include('../../includes/navbar-start.php');
    $data = array();
    $data["task"] = 9;
    $data["language"] = $_SESSION["language"];
    $data["page"] = 2;
    $json = parcxDashboard($data);
    ?>

</ul>


<div class="header text-dark" id="pdf-report-header"><?= $json["device_status"] ?></div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<style>
    #cash-level{
        word-break: break-all;
        overflow-y:auto;
        height:450px;
    }
</style>

<!-- Modal -->
<div class="modal fade" id="error-log-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4" >

                <!-- info -->
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="border-simple h-100 p-4">
                            <img src="" class="mx-auto d-block img-fluid" id="device_img">
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="border-simple h-100 p-3" id="device_details">

                        </div>
                    </div>
                </div>
                <!-- end / info -->  
                <div id="manual_operation" class="hidden">
                    <div  class="row mb-4 ">
                        <div class="btn-group btn-group-toggle col-12" data-toggle="buttons">
                            <label class="btn btn-secondary col-2">
                                <input class="btn-manual-operation" type="radio" name="options" data-reason="<?= $json["open_barrier_reason"] ?>" data-value="Open Barrier" id="open_barrier" autocomplete="off" checked=""><span id="open_barrier_label"><?= $json["open_barrier"] ?></span>
                            </label>
                            <label class="btn btn-secondary col-2">
                                <input class="btn-manual-operation" type="radio" name="options" data-reason="<?= $json["close_barrier_reason"] ?>" data-value="Close Barrier" id="close_barrier" autocomplete="off"><span id="close_barrier_label"><?= $json["close_barrier"] ?></span>
                            </label>
                            <label class="btn btn-secondary col-2">
                                <input class="btn-manual-operation" type="radio" name="options" data-reason="<?= $json["lane_closed_mode_reason"] ?>" data-value="Lane Closed Mode" id="lane_close" autocomplete="off"><span id="lane_close_label"><?= $json["lane_closed_mode"] ?></span>
                            </label>
                            <label class="btn btn-secondary col-3">
                                <input class="btn-manual-operation" type="radio" name="options" data-reason="<?= $json["free_passage_mode_reason"] ?>" data-value="Free Passage Mode" id="free_passage" autocomplete="off"><span id="free_passage_label"><?= $json["free_passage_mode"] ?></span>
                            </label>
                            <label class="btn btn-secondary col-3">
                                <input class="btn-manual-operation" type="radio" name="options" data-reason="<?= $json["standard_operation_mode_reason"] ?>" data-value="Standard Operation Mode" id="normal_operation" autocomplete="off"><span id="normal_operation_label"><?= $json["standard_operation_mode"] ?></span>
                            </label>                       
                        </div>                    
                    </div>
                    
                    <div id="manual_operation_reason" style="display: none;">
                        <div class="card card-light mb-4" >
                            <div class="card-header">
                                <h3 class="card-title" id="reason_heading"><?= $json["reason"] ?></h3>

                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" id="reason-body-open"><i class="fas fa-minus"></i></button>                            
                                </div>
                            </div>

                            <div id="reason-body" class="card-body" style="display: block;"> 
                                <div  class="col-1" id='loader' ><img src='../../dist/img/loading.gif'></div>
                                <span id="manual_result" ><?= $json["reason"] ?></span>                                
                                <div id="reason_form">                                    
                                    <textarea name='reason_text' id='reason_text' class="form-control mb-4"></textarea>
                                    <p id="reasonempty" class="text-danger"></p>                       
                                    <button type='button' class='btn btn-success' name='ok_reason' id='ok_reason' value='OK' title="<?= $json["ok"] ?>"><i class="fas fa-check-square"></i></button>
                                    <button type='button' class='btn btn-danger' name='cancel_reason' id='cancel_reason' value='Cancel' title="<?= $json["cancel"] ?>"><i class="fas fa-window-close"></i></button>
                                </div>                                

                            </div>
                        </div>
                    </div>
                    
                </div>

                

                <div class="card-secondary card-outline card-tabs border-simple">
                    <div class="p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" >
                            <li class="nav-item " id="nav-item-cash-level">
                                <a class="nav-link active"  id="a-cash-level" data-toggle="pill" href="#cash-level" aria-selected="false"><?= $json["cash_levels"] ?></a>
                            </li> 
                            <li class="nav-item" >
                                <a class="nav-link" id="a-alarm" data-toggle="pill" href="#alarm-data"   aria-selected="true"><?= $json["alarms"] ?></a>
                            </li>



                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            <div class="tab-pane fade active show" id="cash-level">
                                cashlevels
                            </div>
                            <div class="tab-pane fade " id="alarm-data">
                                alarms
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>               
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->


<!-- Modal -->
<div class="modal fade" id="alarm-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= $json["alarms"] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-alarm-modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">  
                <div  id="latest-alarm-data">

                </div>                
            </div>
        </div>
    </div>
</div>
<!-- / end modal -->

<div class="content-wrapper">

    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex justify-content-between pl-1 align-items-center ">
            <div class="flex-grow-1 row additional-menu-left">
                <div class="col tab-header d-flex justify-content-left pl-1" id="device-category-tab">

                </div>
            </div>   
            <div class="additional-menu-right row align-items-center">                   
                <button class="btn btn-danger" data-toggle="modal" data-target="#alarm-modal"><i class="fas fa-bell mr-1"></i><span id="alarm_notification">Alarms:100</span></button>                
            </div>

        </div>
    </div>    
    <section class="content">
        <div class="container-wide">
            <div class="row" id="device-status-block" >

            </div>                        
        </div>
    </section>

</div>
</div>


<script src="../../dist/js/manualOperations.js"></script>
<?php include('../../includes/footer.php'); ?>
<script>

    var facility_number = <?php echo $_GET["facility_number"]; ?>;
    var carpark_number =<?php echo $_GET["carpark_number"]; ?>;
    var device_number = 0;
    var valid_reason_message = "<?= $json["enter_valid_reason"] ?>";

    var $target = "all";

    $("#full-screen").removeClass("d-none");

    function display_target_devices()
    {
        if ($target != 'all')
        {
            $('.block-data').addClass("hidden");
            $('.block-data[data-status="' + $target + '"]').removeClass("hidden").fadeIn('slow');
        } else
        {
            $('.block-data').removeClass("hidden").fadeIn('slow');
        }
    }



    $(document).on("click", ".tab-link", function ()
    {
        $target = $(this).data('target');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
        display_target_devices();
    });


    var alarm_count = 0;

    function get_device_category_tab()
    {
        var req = {};
        req["task"] = 17;
        req["language"] = $("#language").val();
        var json = JSON.stringify(req);

        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            $("#device-category-tab").html(data);
        });
    }

    function get_device_status_by_device()
    {
        var req = {};
        req["task"] = 12;
        req["language"] = $("#language").val();
        req["facility_number"] = facility_number;
        req["carpark_number"] = carpark_number;
        var json = JSON.stringify(req);

        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            $('#device-status-block').html(data);
            alarm_count = $("#alarm_count").val();

            $('#alarm_notification').html(alarm_count);
            display_target_devices();
        });
    }
    var device_name;
    var device_ip;
    var device_img;
    var device_type;
    var device_category;
    var jsondata;
    var status = 0;

    $('body').on('click', "[data-target='#error-log-modal']", function ()
    {
        device_name = $(this).find(".card-title").text();
        device_number = $(this).attr("data-number")
        device_ip = $(this).find(".device_ip").text();
        device_category = $(this).find(".device_category").text();
        device_img = $(this).attr("data-img")
        device_type = $(this).attr("data-type");
        status = $(this).attr("data-device-status");
        
        
        $("#manual_result").html(valid_reason_message);
        $("#manual_operation_reason").hide();

        $("#device_img").attr("src", ("/parcx/dist/img/icon/device_icons/" + device_img ));

        if (device_type == 1 || device_type == 2)
        {
        $("#manual_operation").removeClass("hidden");    
        $('.btn-group label').removeClass('active');
            if (status == 1)
            {
             $('.btn-group input').removeAttr('disabled');
             $('.btn-group label').removeClass('disabled');
                
            } else
                
            {
             $('.btn-group input').attr('disabled','disabled');
             $('.btn-group label').addClass('disabled');
            }

        } else
        {
            $("#manual_operation").addClass("hidden");
        }

        set_jsondata_input();
        get_device_details();
        get_alarmlist();
        //get_hourly_count();

        $('#cash-level').html("");
        if (device_type == 4)
        {
            $("#nav-item-cash-level").removeClass("d-none");
            $("#a-cash-level").click();
            get_cashbox_level();
        } else
        {
            $("#nav-item-cash-level").addClass("d-none");
            $("#a-alarm").click();
        }


    });

    function set_jsondata_input(task)
    {
        var data = {};
        data["device_number"] = device_number;
        data["facility_number"] = facility_number;
        data["carpark_number"] = carpark_number;
        data["device_category"] = device_category;
        data["device_type"] = device_type;
        data["language"] = $("#language").val();
        data["task"] = task;
        jsondata = JSON.stringify(data);
    }

//    function get_hourly_count()
//    {
//
//        if (device_type <= 8)
//        {
//            $.post("../ajax/dashboard.php?task=34", jsondata, function (result) {
//                console.log(result);
//            });
//        }
//    }

    function get_device_details()
    {
        set_jsondata_input(13);
        $.post("../ajax/dashboard-ajax.php", jsondata, function (result) {
            $('#device_details').html(result);
        });
    }

    function get_cashbox_level()
    {
        set_jsondata_input(14);
        $.post("../ajax/dashboard-ajax.php", jsondata, function (result) {
            $('#cash-level').html(result);
        });
    }

    function get_alarmlist()
    {
        set_jsondata_input(15);
        $.post("../ajax/dashboard-ajax.php", jsondata, function (result) {

            if (device_number == 0)
            {
                $('#latest-alarm-data').html(result);
                if ($("#latest-alarm-data").find('#RecordsTable').length != 0)
                    $('#latest-alarm-data #RecordsTable').DataTable(
                            {
                                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                            });
            } else
            {
                var details = '<input type="hidden" device_type="' + device_type + '" id="device_details_' + device_number + '" device_ip="' + device_ip + '" device_name="' + device_name + '" carpark_number="' + carpark_number + '">';
                $('#alarm-data').html(details + result);
                if ($("#alarm-data").find('#RecordsTable').length != 0)
                    $('#alarm-data #RecordsTable').DataTable(
                            {
                                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                            });
            }

        });
    }

    $('body').on('click', "[data-target='#alarm-modal']", function () {
        device_number = 0;
        get_alarmlist();
    });

    $(document).on('click', '.btn-dismis-alarm', function () {
        var id = $(this).attr('id');
        var data = {};
        data["id"] = id;
        data["task"] = 16;
        var jsondata = JSON.stringify(data);
        $.post("../ajax/dashboard-ajax.php", jsondata, function (data) {
            get_alarmlist();
            get_device_status_by_device();
        });
    });


    function loadReportLabels()
    {
        var data = {};
        data["task"] = 9;
        data["language"] = $("#language").val();
        data["page"] = 2;
        var json = JSON.stringify(data);
        
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            
            var json = JSON.parse(data);

            $("#pdf-report-header").html(json.device_status);
            $("#logout").html(json.logout);
            $("#a-alarm").html(json.alarms);
            $("#a-cash-level").html(json.cash_levels);
            $("#exampleModalLabel").html(json.notifications);
            $("#manual_title").html(json.manual_operation_reason);
            $("#reason_title").html(json.reason);
            $("#ok_reason").attr("title",json.ok);
            $("#cancel_reason").attr("title",json.cancel);


            $("#open_barrier_label").html(json.open_barrier);
            $("#close_barrier_label").html(json.close_barrier);
            $("#lane_close_label").html(json.lane_closed_mode);
            $("#free_passage_label").html(json.free_passage_mode);
            $("#normal_operation_label").html(json.standard_operation_mode);

            $("#open_barrier").attr("data-reason", json.open_barrier_reason);
            $("#close_barrier").attr("data-reason", json.close_barrier_reason);
            $("#lane_close").attr("data-reason", json.lane_closed_mode_reason);
            $("#free_passage").attr("data-reason", json.free_passage_mode_reason);
            $("#normal_operation").attr("data-reason", json.standard_operation_mode_reason);
            valid_reason_message = json.enter_valid_reason;
        });
    }


    $(document).on("change", ".btn-manual-operation", function () {
        $("#manual_operation_reason").show();
        $("#manual_result").html(valid_reason_message);
        $("#reason_form").show();
        if ($("#reason-body").is(":visible") == false)
            $("#reason-body-open").click();
        value = $(this).attr('data-value');
        reason_text = $(this).attr('data-reason');
        getManualOperationDetails();
        description_text = description;
    });

    $("#cancel_reason").click(function(){
        $("#manual_operation_reason").hide();
    });

    $("#language").change(function ()
    {
        update_session();
        loadReportLabels();
        get_device_status_by_device();
        get_device_category_tab();

    });


    get_device_status_by_device();
    get_device_category_tab();

    setInterval(function ()
    {
        //get_device_status_by_device();
    }, 30000);

</script>

