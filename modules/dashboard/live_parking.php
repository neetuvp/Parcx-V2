<?php
include('../../includes/header.php');
include('../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 25;
$json = parcxV2Report($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["live_parking"] ?></div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">      
            <div class="card">
                <div class="card-body" id="report-content"> 
                    <?php                    
                    $data["task"] = 31;
                    $data["language"] = $_SESSION["language"];
                    echo parcxV2Report($data);
                    ?>  
                </div>
            </div>   
        </div>
    </section>
</div>

<?php include('../../includes/footer.php'); ?>

<script>     
    function callReport()
    {

    var data = {};        
    data["language"] = $("#language").val();
    data["task"] = 31;
    var temp = JSON.stringify(data);  
    console.log(temp);
    $.post("../ajax/reports.php", temp)
            .done(function (result) {                
                loadReport(result);               
            }, "json");

    event.preventDefault();
    }

  

    function loadReportLabels()
    {
        var data = {};
        data["task"] = 29;
        data["language"] = $("#language").val();
        data["page"] = 25;
        var json = JSON.stringify(data);        
        $.post("../ajax/reports.php", json, function (data)
            {            
            var json = JSON.parse(data);            
            $("#pdf-report-header").html(json.live_parking);        
            $("#logout").html(json.logout); 
            search_label=json.search;   
            entries_label= json.entries_label;
            info_label=json.info_label;
            previous_label=json.previous;
            next_label=json.next; 
            });
    }

    $("#language").change(function ()
    {
        update_session();  
        loadReportLabels();
        callReport();
    });

</script>
