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
var facility_number =<?php echo $_GET["facility_number"] ;?>;
var carpark_number=0;

get_live_revenue();


function get_live_revenue()
    {
    //$.get("../ajax/dashboard.php?task=28&facility_number="+facility_number, function (data) {   
     	var req = {};
  	req["task"]=22;
  	req["language"]=$("#language").val();
	req["facility_number"]=facility_number;
    	var json = JSON.stringify(req);
	console.log(json);
  	$.post("../ajax/dashboard-ajax.php",json,function(data){   
	console.log(data);     
        $('#revenue-live-block').html(data);  
        carpark_number=$("#carpark_number").val();           
        if (typeof carpark_number !== "undefined")
            window.location="finance_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;
       else
            show_donut_chart();
               
    });
    }
    
$(document).on('click', '.show-carpark-details', function () {
    facility_number = $(this).attr("facility_number");
    carpark_number = $(this).attr("carpark_number");
    window.location="finance_device.php?facility_number="+facility_number+"&carpark_number="+carpark_number;
    
});
    

  
//////////////////////////////
// donut chart - revenue sources
//////////////////////////////

var donutChart=[];
var donutChartValues=[];
var carparkNumber=[];

function show_donut_chart()
    {        
    $(".donutChart").each(function(index){   
        var donutData        = {
            labels: [
                '<?=$json["parking_fee"]?>',//'Parking Fee',           
                '<?=$json["lost_fee"]?>',//'Lost Fee',                   
                '<?=$json["product_sale_amount"]?>',//'Product Sale',
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
        carparkNumber[index]=$(this).attr("carpark-number");
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
    req["task"]=23;
    req["facility_number"]=facility_number;
    req["language"]=$("#language").val();
    var json = JSON.stringify(req);
    $.post("../ajax/dashboard-ajax.php",json,function(data){              
        amount = JSON.parse(data);        
        for(var i=0;i<amount.length;i++)
            {              
            var index = carparkNumber.indexOf(amount[i]["carpark_number"]);              
            donutChartValues[index][0] = amount[i]["parking_fee"];            
            donutChartValues[index][1] = amount[i]["lost_fee"];
            donutChartValues[index][2] = amount[i]["product_sale_amount"];             
            donutChartValues[index][3] = amount[i]["vat_amount"];             
            donutChart[index].update();        
            }
        });
    /*$.get("../ajax/dashboard.php?task=29&facility_number="+facility_number, function (data) 
        {   
        console.log(data);    
        amount = JSON.parse(data);        
        for(var i=0;i<amount.length;i++)
            {              
            var index = carparkNumber.indexOf(amount[i]["carpark_number"]);              
            donutChartValues[index][0] = amount[i]["parking_fee"];            
            donutChartValues[index][1] = amount[i]["lost_fee"];
            donutChartValues[index][2] = amount[i]["product_sale_amount"];   
            donutChartValues[index][3] = amount[i]["vat_amount"];             
            donutChart[index].update();        
            }
        });*/
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
