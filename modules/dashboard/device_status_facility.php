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
$data["page"] = 1;
$json = parcxDashboard($data);
?>

</ul>


<div class="header text-dark" id="pdf-report-header"><?=$json["device_status"]?></div>


<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">         
    <section class="content">
        <div class="container-wide">
            <div class="row" id="device-status-block" >

            </div>
        </div>
    </section>

</div>
</div>
<?php include('../../includes/footer.php'); ?>
<script>
var facility_number = 0;
var carpark_number=0;

function get_live_device_Status()
{
  var req={};
  req["task"]=10;
  req["language"]=$("#language").val();
  var json = JSON.stringify(req);
  $.post("../ajax/dashboard-ajax.php",json,function(data){
        $('#device-status-block').html(data); 
        carpark_number=$("#carpark_number").val();
        facility_number=$("#facility_number").val();
        if (typeof carpark_number !== "undefined")
            window.location="device_status_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;
        else if (typeof facility_number !== "undefined")  
            window.location="device_status_carpark.php?facility_number="+facility_number;      
    });
}

$(document).on('click', '.show-facility-details', function () {
    facility_number = $(this).attr("facility_number");  
    window.location="device_status_carpark.php?facility_number="+facility_number;
});

get_live_device_Status();

setInterval(function ()
    {
    get_live_device_Status();   
    }, 30000);
    

function loadReportLabels()    
    {
    var data={};
    data["task"]=9;
    data["language"]=$("#language").val();    
    data["page"]=1;
    var json = JSON.stringify(data);
	console.log(json);
    $.post("../ajax/dashboard-ajax.php",json,function(data)
        {	
	console.log(data);	            
        var json=JSON.parse(data);
            
        $("#pdf-report-header").html(json.device_status);   
        $("#logout").html(json.logout); 
    
        
        });    
    }

$("#language").change(function()
{	
    update_session();  
    loadReportLabels(); 
    get_live_device_Status();   
    //callReport();		
}); 
</script>


