
<?php
include('../../includes/header.php');
?>

<script>
    $(function () {
        $.widget("custom.iconselectmenu", $.ui.selectmenu, {
            _renderItem: function (ul, item) {
                var li = $("<li>"),
                        wrapper = $("<div>", {text: item.label});

                if (item.disabled) {
                    li.addClass("ui-state-disabled");
                }

                $("<span>", {
                    style: item.element.attr("data-style"),
                    "class": "ui-icon " + item.element.attr("data-class")
                })
                        .appendTo(wrapper);

                return li.append(wrapper).appendTo(ul);
            }
        });


        $(".color_icon")
                .iconselectmenu()
                .iconselectmenu("menuWidget")
                .addClass("ui-menu-icons customicons");

    });
</script>
<style>
    h2 {
        margin: 30px 0 0 0;
    }
    fieldset {
        border: 0;
    }
    label {
        display: block;
    }

    /* select with custom icons */
    .ui-selectmenu-menu .ui-menu.customicons .ui-menu-item-wrapper {
        padding: 0.5em 0 0.5em 3em;
    }
    .ui-selectmenu-menu .ui-menu.customicons .ui-menu-item .ui-icon {
        height: 24px;
        width: 24px;
        top: 0.1em;
    }
    .ui-icon.B {
        background: url("../../Media/vmsicons/B.bmp") 0 0 no-repeat;
    }
    .ui-icon.G {
        background: url("../../Media/vmsicons/G.bmp") 0 0 no-repeat;
    }
    .ui-icon.L1 {
        background: url("../../Media/vmsicons/L1.bmp") 0 0 no-repeat;
    }
    .ui-icon.L2 {
        background: url("../../Media/vmsicons/L2.bmp") 0 0 no-repeat;
    }

</style>
<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>

<div class="header text-dark" id="pdf-report-header">VMS Configuration</div>
<div class="row">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="active_daemon">VMS List</div>
        <div class="tab-link" id = "addvmsdiv" data-target="add_vms">Add VMS</div>
    </div>
</div>


<!-- confirm enable disable -->
<div class="modal fade" id="enable-disable-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content card-outline card-warning">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="enable-disable-modal-body">        
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="modal-conform">Confirm</button>
            </div>
        </div>
    </div>
</div>


<?php
ini_set("display_errors", 1);
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<!-- add VMS -->
<div class="content-wrapper block-data" data-status="add_vms" style="display:none;">
    <section class="content">
        <div class="container-wide">    
            <div class="card card-primary col-md-6" id="add-vms-div">
                <div class="card-body">
                    <div class="row">
                        <div class="col form-group">
                            <input type='hidden' name='vms_id' id = 'vms_id' value = ''>
                            <label for="">Vms Name</label>
                            <input type="text" class="form-control" id="vms_name" aria-describedby="" placeholder="" name="vms_name" value = "">
                        </div>

                        <div class="col form-group">
                            <label for="">Port</label>
                            <select id="port_no">   
                                <option value = ""></option>
                                <?php
                                $dir = "/dev/";
                                $files = scandir($dir);
                                for ($i = 0; $i < sizeof($files); $i++) {
                                    //echo $files[$i];
                                    if (strpos($files[$i], "tty") !== false) {
                                        echo $files[$i];
                                        echo "<option value = '/dev/" . $files[$i] . "'>/dev/" . $files[$i] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <!--<input type="text" class="form-control" id="port_no" aria-describedby="" placeholder="" name="port_no">
                            -->						</div>

                        <div class="col form-group">
                            <label for="">Sign Type</label>
                            <select id="sign_type">    
                                <option value = "1">Tower</option>
                                <option value = "2">Internal</option>
                                <option value = "3">External</option>
                            </select>                        
                        </div>

                    </div><!--row-->

                    <div class="row">

                        <div class="col form-group">
                            <label for="">Width(px)</label>
                            <select id="vms_width">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>

                        <div class="col form-group">
                            <label for="">Height(px)</label>
                            <select id="vms_height">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>

                    </div> <!--row-->
                    </br><h5 class="mt-0" id="left-counter">LEFT COUNTER</h5> </br>
                    <div class="row">

                        <div class="col form-group">
                            <label for="">Text icon font size</label>
                            <select id="left_font_size">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>

                        <!-- <div class="col form-group">
                                                                     <label for="">Text Icon Color</label>
                           <select id="left_icon_color">    
                             <option value = "red">Red</option>
                             <option value = "green">Green</option>
                             <option value = "blue">Blue</option>
                           </select>
                         </div>-->

                        <div class="col form-group">
                            <label for="">Icon Text</label>
                            <input type="text" class="form-control" id="left_icon_text" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Counter Color</label>
                            <select id="left_counter_color"> 
                                <option value = "0">Default</option>   
                                <option value = "1">Red</option>
                                <option value = "2">Green</option>
                                <option value = "3">Yellow</option>
                                <option value = "4">Blue</option>
                                <option value = "5">Pink</option>
                                <option value = "6">Cyan</option>
                                <option value = "7">White</option>
                            </select>
                        </div>

                    </div> <!--row-->


                    <div class="row">

                        <div class="col form-group">
                            <label for="">Character Count</label>
                            <select id="left_character_count">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>       

                        <div class="col form-group">
                            <label for="">Counter ID</label>
                            <input type="text" class="form-control" id="left_counter_id" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Icon</label>
                            <select id="left_icon" class="color_icon">  
                                <option id ="left-B" value="B" data-class="B">B</option>
                                <option id ="left-G" value="G" data-class="G">G</option>
                                <option id ="left-L1" value="L1" data-class="L1">L1</option>  
                                <option id ="left-L2" value="L2" data-class="L2">L2</option>  
                                <!--<option value = "B">B</option>
                                <option value = "L1">L1</option>
                                <option value = "G">G</option>
                                <option value = "L2">L2</option>-->
                            </select>
                        </div>
                    </div> <!--row-->




                    </br><h5 class="mt-0" id="left-counter">CENTER COUNTER</h5> </br>
                    <div class="row">

                        <div class="col form-group">
                            <label for="">Text icon font size</label>
                            <select id="center_font_size">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>

                        <!--<div class="col form-group">
                                                                    <label for="">Text Icon Color</label>
                          <select id="center_icon_color">    
                            <option value = "red">Red</option>
                            <option value = "green">Green</option>
                            <option value = "blue">Blue</option>
                          </select>
                        </div>-->

                        <div class="col form-group">
                            <label for="">Icon Text</label>
                            <input type="text" class="form-control" id="center_icon_text" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Counter Color</label>
                            <select id="center_counter_color">    
                                <option value = "0">Default</option>   
                                <option value = "1">Red</option>
                                <option value = "2">Green</option>
                                <option value = "3">Yellow</option>
                                <option value = "4">Blue</option>
                                <option value = "5">Pink</option>
                                <option value = "6">Cyan</option>
                                <option value = "7">White</option>
                            </select>
                        </div>

                    </div> <!--row-->


                    <div class="row">

                        <div class="col form-group">
                            <label for="">Character Count</label>
                            <select id="center_character_count">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>       

                        <div class="col form-group">
                            <label for="">Counter ID</label>
                            <input type="text" class="form-control" id="center_counter_id" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Icon</label>
                            <select id="center_icon" class="color_icon">  
                                <option value="B" data-class="B">B</option>
                                <option value="G" data-class="G">G</option>
                                <option value="L1" data-class="L1">L1</option>  
                                <option value="L2" data-class="L2">L2</option> 
                            </select>
                        </div>

                    </div> <!--row-->



                    </br><h5 class="mt-0" id="left-counter">RIGHT COUNTER</h5> </br>
                    <div class="row">

                        <div class="col form-group">
                            <label for="">Text icon font size</label>
                            <select id="right_font_size">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>

                        <!--<div class="col form-group">
                                                                    <label for="">Text Icon Color</label>
                          <select id="right_icon_color">    
                            <option value = "red">Red</option>
                            <option value = "green">Green</option>
                            <option value = "blue">Blue</option>
                          </select>
                        </div>-->

                        <div class="col form-group">
                            <label for="">Icon Text</label>
                            <input type="text" class="form-control" id="right_icon_text" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Counter Color</label>
                            <select id="right_counter_color">    
                                <option value = "0">Default</option>   
                                <option value = "1">Red</option>
                                <option value = "2">Green</option>
                                <option value = "3">Yellow</option>
                                <option value = "4">Blue</option>
                                <option value = "5">Pink</option>
                                <option value = "6">Cyan</option>
                                <option value = "7">White</option>
                            </select>
                        </div>

                    </div> <!--row-->


                    <div class="row">

                        <div class="col form-group">
                            <label for="">Character Count</label>
                            <select id="right_character_count">    
                                <option value = "1">50</option>
                                <option value = "2">100</option>
                                <option value = "3">150</option>
                            </select>
                        </div>       

                        <div class="col form-group">
                            <label for="">Counter ID</label>
                            <input type="text" class="form-control" id="right_counter_id" aria-describedby="" placeholder="" name="port_no">
                        </div>

                        <div class="col form-group">
                            <label for="">Icon</label>
                            <select id="right_icon" class="color_icon">  
                                <option value="B" data-class="B">B</option>
                                <option value="G" data-class="G">G</option>
                                <option value="L1" data-class="L1">L1</option>  
                                <option value="L2" data-class="L2">L2</option> 
                            </select>
                        </div>
                    </div> <!--row-->

                    </br><h5 class="mt-0" id="left-counter">Brightness Settings</h5> </br>
                    <div class="row">

                        <div class="col form-group">
                            <label for="">Hour of the Day</label>
                            <select id="brightness_time1">    
<?php
for ($i = 0; $i < 24; $i++) {
    echo '<option value = "' . $i . '">' . $i . '</option>';
}
?>
                            </select>
                        </div>

                        <div class="col form-group">

                            <label for="">Brightness Level</label>
                            <select id="brightness_value1">
<?php
for ($i = 1; $i <= 7; $i++) {
    echo '<option value = "' . $i . '">Level' . $i . '</option>';
}
?>
                            </select>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col form-group">
                            <select id="brightness_time2">    
<?php
for ($i = 0; $i < 24; $i++) {
    echo '<option value = "' . $i . '">' . $i . '</option>';
}
?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <select id="brightness_value2">
                                <?php
                                for ($i = 1; $i <= 7; $i++) {
                                    echo '<option value = "' . $i . '">Level' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col form-group">
                            <select id="brightness_time3">    
<?php
for ($i = 0; $i < 24; $i++) {
    echo '<option value = "' . $i . '">' . $i . '</option>';
}
?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <select id="brightness_value3">
                                <?php
                                for ($i = 1; $i <= 7; $i++) {
                                    echo '<option value = "' . $i . '">Level' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col form-group">
                            <select id="brightness_time4">    
                                <?php
                                for ($i = 0; $i < 24; $i++) {
                                    echo '<option value = "' . $i . '">' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <select id="brightness_value4">
                                <?php
                                for ($i = 1; $i <= 7; $i++) {
                                    echo '<option value = "' . $i . '">Level' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col form-group">
                            <select id="brightness_time5">    
                                <?php
                                for ($i = 0; $i < 24; $i++) {
                                    echo '<option value = "' . $i . '">' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <select id="brightness_value5">
                                <?php
                                for ($i = 1; $i <= 7; $i++) {
                                    echo '<option value = "' . $i . '">Level' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    </br></br>
                    <div class="row">
                        <div class="col form-group">
                            <input class='btn btn-success btn-block btn-add-vms' id ='addvms' type='submit'  value='Submit' onclick='SaveRecord()' />
                        </div>
                        <div class="col form-group">
                        </div>
                        <div class="col form-group">
                        </div>
                    </div>


                </div><!--card body-->
            </div>
        </div>
    </section>
</div>
<!-- End Add Message -->



<!-- Active Text Messages-->
<div class="content-wrapper block-data" data-status="active_daemon">



    <section class="content">
        <div class="container-wide">
            <div id = "vmslist">
            </div> <!--daemonlist-->
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
                    if (($target == "add_vms") && ($('#page_title').html() == "Edit VMS")) {
                        $('.card-body').find('input').val('');
                        $('.card-body').find('input[type=checkbox]:checked').removeAttr('checked');
                        $('.card-body').find('select').prop('selectedIndex', 0);
                        $('#page_title').html("Add VMS");
                    }
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
    <script src="../../dist/js/vms_config.js"></script>
<?php include('../../includes/footer.php'); ?>