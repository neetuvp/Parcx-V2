
<?php
$page_title = "Daemon Status";
include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>

<div class="header text-dark" id="pdf-report-header">Daemon Status</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="0">Overview</div>
        <div class="tab-link" data-target="1">Enable/Disable</div>
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<div class="content-wrapper block-data">
    <section class="content">
        <div class="container-wide">                         
            <div class="card col-md-6" >               
                <div class="card-body ">                                                              
                    <table id="RecordsTable" class="table  table-bordered ">   
                        <?php echo parcxDaemonStatus(array("task" => "1", "option" => "0")); ?>
                    </table>                    
                </div>
            </div>
        </div> 
</div>
</section>
</div>   
<?php include('../../includes/footer.php'); ?>
<script>
    var option = 0;    
    $("* [data-target]").on('click', function ()
    {
        option = $(this).data('target');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
        loadDaemonEnableDisablePage();
    });

    function loadDaemonEnableDisablePage()
    {
        var data = {};
        data = {};
        data["task"] = 1;
        data["option"] = option;
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/daemon_status.php", jsondata, function (data) {
            $("#RecordsTable").html(data);
        });
    }

    function callDaemonFunction(data)
    {
        var jsondata = JSON.stringify(data);
        $.post("../../modules/ajax/daemon_status.php", jsondata, function (result) {            
                loadDaemonEnableDisablePage();            
        });
    }
    function stopDaemon(daemon_name)
    {
        var data = {};
        data["task"] = 2;
        data["daemon"] = daemon_name;
        data["activity_message"]="Stop daemon "+daemon_name;
        callDaemonFunction(data);
    }

    function startDaemon(daemon_name)
    {
        var data = {};
        data["task"] = 3;
        data["daemon"] = daemon_name;
        data["activity_message"]="Start daemon "+daemon_name;
        callDaemonFunction(data);
    }

    function restartDaemon(daemon_name)
    {
        var data = {};
        data["task"] = 4;
        data["daemon"] = daemon_name;
        data["activity_message"]="Restart daemon "+daemon_name;
        callDaemonFunction(data);
    }

    /* === enable disable product === */
    var status;
    var daemon_name;
    $(document).on("click", ".damon-enable-disable-btn", function ()
    {
        daemon_name = $(this).parent('td').parent('tr').data('id');
        var status_text=$(this).attr("data-text");  
        if (status_text == "Disable")
            status = 0;
        else
            status = 1;

        var data = {};
        data["daemon_name"] = daemon_name;
        data["status"] = status;
        data["task"] = 5;
        data["activity_message"]=status_text+" daemon "+daemon_name;
        callDaemonFunction(data);
    });
</script>