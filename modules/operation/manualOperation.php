<?php
//Language switch changes : manualOperation.php,ajax/operations.php,phpcpp/parcx_reports,manualOperations.js,phpcpp/manualOperation,includes/sidebar.php
header('Access-Control-Allow-Origin: *');
include('../../includes/header.php');
include('../../includes/navbar-start.php');
$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 26;
$json = parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["manual_operation"]?></div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');

?>

<!-- Modal > Manual open Reason -->
<div class="modal fade text-dark" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="manual-reason-content">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title"><?=$json["manual_operation"]?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4">
                <p id="reason_heading"></p>
                <p id="reason_label"><?=$json["reason"]?></p>
                <textarea name='reason_text' id='reason_text' class="form-control mb-4"></textarea>
                <span id="reasonempty" class="text-danger"></span>
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info' name='ok_reason' id='ok_reason' value='OK'><?=$json["ok"]?></button>
                <button type='button' class='btn btn-info' name='cancel_reason' id='cancel_reason'
                        value='Cancel'><?=$json["cancel"]?></button>
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">

            <div class="col-md-12 padding-reset mb-5">
                <div class="card" id="page-content">   
                    <div class="card card-primary" id="active-rates-table">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="col" id="device_name_label"><?=$json["device_name"]?></div>
                                <div class="col" id="device_ip_label"><?=$json["device_ip"]?></div>
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                            </div>      
                        </div> 
                        <div id='manualoperationlist'>
                        <?php
                        listManualOperation($_SESSION["language"]);
                        ?>
                        </div>


                    </div>      
                </div>
            </div>
            
            <h4><?=$json["latest_manual_operations"]?></h5>
            <div class="card mt-3" id='latestmanualreport'>
           
              <?php
                            $current_date = date("Y-m-d");
                            $data["from"] = $current_date . " " . DAY_CLOSURE_START;
                            $data["to"] = $current_date . " " . DAY_CLOSURE_END;
                            $data["carpark"] = "";
                            $data["operation"] = "";
                            $data["language"] = $_SESSION["language"];
                            $data["task"] = 11;
                            $data["limit"] = 10;
                            echo parcxV2Report($data);
                            ?>
            
      </div> 

        </div>
</div>
</section>
</div>
<script>
    var valid_reason_message = "<?=$json["enter_valid_reason"]?>";
</script>
<script src="../../dist/js/manualOperations.js"></script>
<?php
include('../../includes/footer.php');
?>

