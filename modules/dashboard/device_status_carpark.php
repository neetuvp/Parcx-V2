<?php
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
$json = parcxV2Dashboard($data);
?>

</ul>


<div class="header text-dark" id="pdf-report-header"><?=$json["device_status"]?></div>


<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- Modal -->
<div class="modal fade" id="alarm-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?=$json["alarms"]?></h5>
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
var facility_number =<?php echo $_GET["facility_number"] ;?>;
var carpark_number = 0;


function get_device_status_by_carpark()
{
   var req={};
  req["task"]=11;
  req["language"]=$("#language").val();
  req["facility_number"]=facility_number;
  var json = JSON.stringify(req);
  console.log(json);
  $.post("../ajax/dashboard-ajax.php",json,function(data){
        $('#device-status-block').html(data);  
        carpark_number=$("#carpark_number").val();           
        if (typeof carpark_number !== "undefined")
            window.location="device_status_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;        
    });
}

    
$(document).on('click', '.show-carpark-details', function () {
    facility_number = $(this).attr("facility_number");
    carpark_number = $(this).attr("carpark_number");
    window.location="device_status_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;
    
});

function get_alarmlist()
    {
    var data = {};
    data["device_number"] = 0;
    data["facility_number"] = facility_number;
    data["carpark_number"] = carpark_number;
    data["language"]=$("#language").val();
    data["task"] = 15;
    var jsondata = JSON.stringify(data);
    $.post("../ajax/dashboard-ajax.php", jsondata, function (result) {         
            $('#latest-alarm-data').html(result);   
            if($("#latest-alarm-data").find('#RecordsTable').length!=0)
                $('#RecordsTable').DataTable(
                    {
                    "paging": true,
                    "lengthChange":true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "lengthMenu": [[5,10, 25, 50, -1], [5,10, 25, 50, "All"]],
                    "aaSorting": []

                    });  
    });   
    }

$('body').on('click', "[data-target='#alarm-modal']", function () {                 
        get_alarmlist();                
});

$(document).on('click', '.btn-dismis-alarm', function () {
    var id = $(this).attr('id'); 
    var data = {};        
    data["id"]=id;        
    data["task"] = 16;
    var jsondata = JSON.stringify(data);
    console.log(jsondata);
    $.post("../ajax/dashboard-ajax.php",jsondata,function(data){                    
	get_alarmlist(); 
        get_device_status_by_carpark();
	});     
    });


get_device_status_by_carpark();

setInterval(function ()
    {
    get_device_status_by_carpark();    
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
	$("#exampleModalLabel").html(json.logout); 
    
        
        });    
    }

$("#language").change(function()
{
    update_session();  
    loadReportLabels();
    get_device_status_by_carpark() ;
    //callReport();		
});
</script>


