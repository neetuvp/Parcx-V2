<?php 
include('../../includes/header.php');
$access = checkPageAccess("finance");
$_SESSION["dashboard"]=1;
?>

<div class="navbar-has-tablink navbar-has-sublink">

<?php
include('../../includes/navbar-start.php');
$data = array();
$data["task"] = 9;
$data["language"] = $_SESSION["language"];
$data["page"] = 3;
$json = parcxDashboard($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?=$json["finance"]?></div>

<?php 
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-wide">
                <div class="row" id="revenue-live-block" >

                </div> 
            </div>
        </section>
    </div>
</div>

<?php include('../../includes/footer.php');?>

<script>
    
//////////////////////////////
// donut chart - revenue sources
//////////////////////////////

var donutChart=[];
var donutChartValues=[];
var facilityNumber=[];
var facility_number=0;
var carpark_number=0;
var amount;
//var parking_fee_label="<?php echo $json['parking_fee'];?>";
//var lost_fee_label="<?php echo $json['lost_fee'];?>";
//var product_sale_label="<?php echo $json['product_sale_amount'];?>";


get_live_revenue();

function get_live_revenue()
{
  var req = {};
  req["task"]=20;
  req["language"]=$("#language").val();
  var json = JSON.stringify(req);
  $.post("../ajax/dashboard-ajax.php",json,function(data){     
        $('#revenue-live-block').html(data);  
        carpark_number=$("#carpark_number").val();
        facility_number=$("#facility_number").val();
        if (typeof carpark_number !== "undefined")
            window.location="finance_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;
        else if (typeof facility_number !== "undefined")  
            window.location="finance_carpark.php?facility_number="+facility_number;
        else
            show_donut_chart();
    });
}
    
$(document).on('click', '.show-facility-details', function () {
    facility_number = $(this).attr("facility_number");  
    window.location="finance_carpark.php?facility_number="+facility_number;
});

function show_donut_chart()
    {        
    $(".donutChart").each(function(index){   
        var donutData        = {
            labels: [
                "<?=$json["parking_fee"]?>",//'Parking Fee',           
                "<?=$json["lost_fee"]?>",//'Lost Fee',                   
                "<?=$json["product_sale_amount"]?>",//'Product Sale',
                'VAT',

            ],
            datasets: [
              {
                data: [],
                backgroundColor : ['#00a65a','#00c0ef','#f56954', '#f39c12'],
              }
            ]
          }
        var donutOptions     = {
          maintainAspectRatio : false,
          responsive : true,
        }
        var donutChartCanvas = $(this).get(0).getContext('2d');
        facilityNumber[index]=$(this).attr("facility-number");
        donutChart[index] = new Chart(donutChartCanvas, {
        type: 'doughnut',
        data: donutData,
        options: donutOptions      
      });
    
    donutChartValues[index] = donutChart[index].data.datasets[0].data;     
    });
    updateRevenueSources() ;  
    }

function updateRevenueSources() 
{
    var req = {};
    req["task"]=21;
    req["language"]=$("#language").val();
    var json = JSON.stringify(req);
    $.post("../ajax/dashboard-ajax.php",json,function(data){              
        amount = JSON.parse(data);        
        for(var i=0;i<amount.length;i++)
            {              
            var index = facilityNumber.indexOf(amount[i]["facility_number"]);              
            donutChartValues[index][0] = amount[i]["parking_fee"];            
            donutChartValues[index][1] = amount[i]["lost_fee"];
            donutChartValues[index][2] = amount[i]["product_sale_amount"];             
            donutChartValues[index][3] = amount[i]["vat_amount"];             
            donutChart[index].update();        
            }
       });
}

$("#language").change(function()
{	
    update_session();     
    get_live_revenue();
    loadReportLabels(); 
});
function loadReportLabels()    
{
	var data={};
	data["task"]=9;
	data["language"]=$("#language").val();    
	data["page"]=3;
	var json = JSON.stringify(data);
	console.log(json);
	$.post("../ajax/dashboard-ajax.php",json,function(data)
	{	
	console.log(data);	            
	var json=JSON.parse(data);
	$("#pdf-report-header").html(json.finance);   
	$("#logout").html(json.logout); 
	
	for(var i=0;i<amount.length;i++)
        { 
		donutChart[i].data.labels[0]=json.parking_fee;
	    	donutChart[i].data.labels[1]=json.lost_fee;
	    	donutChart[i].data.labels[2]=json.product_sale_amount;
	    	donutChart[i].update();
	} 
	 });   
}



</script>
