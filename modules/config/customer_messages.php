
<?php
include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>

<div class="header text-dark" id="pdf-report-header">Customer Messages</div>
<div class="row">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="active_messages">Customer Messages Text</div>
        <div class="tab-link" data-target="add_messages">Add Message</div>
    </div>
</div>

<?php
ini_set("display_errors", 1);
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
$result = GetMessageLanguages();
?>

<!-- Modal > Void Reason -->
<div class="modal fade text-dark" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document" id="upload-image-content">
        <div class="modal-content modalcontent">
            <div class="modal-header">
                <h5 class="modal-title">Upload New Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4 pl-4 pr-4">
                <img id = 'imgmodal' src='' alt='Img'  height="400" width="400">
                <br>
                <br>
                <p>Change Image:</p>               
                <input type='file' id='upload_file' name='upload_file' accept='image/*'>
                <span id="reasonempty"></span>
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-info' name='confirm_upload' id='confirm_upload' value='Confirm'>Confirm</button>
                <button type='button' class='btn btn-info btn-danger' name='cancel_upload' id='cancel_upload'
                        value='Cancel'>Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end / Modal -->

<!-- add Message -->
<div class="content-wrapper block-data" data-status="add_messages" style="display:none;">
    <section class="content">
        <div class="container-wide">    
            <div class="card card-primary mb-8 col-md-8" id="add-discount-div">
                <div class="card-body">                    
                    <form name = "form" method = "post" action = "">
                        <div class="row">
                            <div class="col form-group">
                                <label for="">Device Category</label>
                                <select name = "device_type" id = "device_type" class="form-control">
                                    <option value = "1">Entry</option>
                                    <option value = "2">Exit</option>
                                    <option value = "3">Cashier</option>
                                    <option value = "4">APM</option>
                                    <option value = "5">Handheld</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label for="">Message</label>
                                <input type= "text" class="form-control" name="message" id ="message"  value = ""/>
                            </div>
                        </div>
                        
                       
                            <?php
                            
                            for ($i = 0; $i < sizeof($result); $i++) {
                                echo '<h4>'. ucfirst($result[$i]) .'</h4>
                                    <div class="row">
                                        <div class="col form-group">
                                            <label for="">Line 1</label>
                                            <input type= "text" class="form-control" name="' . $result[$i] . '_line1" id ="' . $result[$i] . '_line1"  value = ""/>
                                        </div>
                                        <div class="col form-group">
                                            <label for="">Line 2</label>
                                            <input type= "text" class="form-control" name="' . $result[$i] . '_line2" id ="' . $result[$i] . '_line2"  value = ""/>
                                        </div>
                                        <div class="col form-group">
                                            <label for="">Line 3</label>
                                            <input type= "text" class="form-control" name="' . $result[$i] . '_line3" id ="' . $result[$i] . '_line3"  value = ""/>
                                        </div>
                                    </div>';
                            }
                            ?>
                        <div class="row">
                            <div class="col form-group">
                                <label for="">Upload Image</label>
                                <input type='file' id='image_file' name='image_file' accept='image/*'>
                            </div>
                        </div>
                           
                        <button type='submit' class='btn btn-primary btn-lg' id='add_message_btn' onclick = 'AddMessage(<?=json_encode($result);?>)'>Add Message</button>
                           
                    </form>


                </div><!--card body-->
            </div>
        </div>
    </section>
</div>
<!-- End Add Message -->



<!-- Active Text Messages-->
<div class="content-wrapper block-data" data-status="active_messages">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left nav-button-group">                          
                <div class='col-auto'><input class='btn btn-info btn-device_type' id ='device1' type='submit'  value='Entry' onclick = ShowMessagesByDeviceType('1')></div>
                <div class='col-auto'><input class='btn btn-info btn-device_type' id ='device2' type='submit'  value='Exit' onclick = ShowMessagesByDeviceType('2')></div>
                <div class='col-auto'><input class='btn btn-info btn-device_type' id ='device3' type='submit'  value='Cashier' onclick = ShowMessagesByDeviceType('3')></div>
                <div class='col-auto'><input class='btn btn-info btn-device_type' id ='device4' type='submit'  value='APM' onclick = ShowMessagesByDeviceType('4')></div>
                <div class='col-auto'><input class='btn btn-info btn-device_type' id ='device5' type='submit'  value='Handheld' onclick = ShowMessagesByDeviceType('5')></div>
            </div>

            <div class="flex-grow-1 row additional-menu-right nav-button-group">
                <?php
                for ($i = 0; $i < sizeof($result); $i++)
                    echo "<div class='col-auto'><input class='btn btn-info btn-language' id ='language_" . ucfirst($result[$i]) . "' type='submit'  value='" . ucfirst($result[$i]) . "' onclick = ShowMessagesByLanguage('" . ucfirst($result[$i]) . "')></div>";
                ?>
            </div>


        </div>
    </div>
    <!-- end / additional menu -->

    <section class="content">
        <div class="container-wide">            
            <div id = "messagedata">
            </div> 
        </div>
    </section>

    <script>
        /* Table Show - Hide */

        $(document).ready(function () {
            $('.tab-link').on('click', function () {
                var $target = $(this).data('target');
                if ($target != 'all') {
                    $('.block-data').css('display', 'none');
                    $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
                } else {
                    $('.block-data').css('display', 'none').fadeIn('slow');
                }
            });
        });

        /* Change Active Tab Styling */

        $('.tab-link').on('click', function () {
            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });
    </script>
    <script src="../../dist/js/customermessages.js"></script>
    <?php include('../../includes/footer.php'); ?>