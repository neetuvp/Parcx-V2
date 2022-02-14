<?php
$page_title = "Finance Dashboard";

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

<div class="header text-dark" id="pdf-report-header"><?= $json["finance"] ?></div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">

    <!-- tab-link header -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex justify-content-between pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">
                <div class="col tab-header d-flex justify-content-left pl-1">
                    <div class="tab-link active" data-target="all" id="view_all_label"><?= $json["view_all"] ?></div>
                    <div class="tab-link" data-target="manual-cashier" id="manual_cashier_label"><?= $json["manual_cashiers"] ?></div>
                    <div class="tab-link" data-target="payonfoot-machine" id="pay_on_foot_label"><?= $json["pay_on_foot_machines"] ?></div>
                    <div class="tab-link" data-target="handheld-pos" id="handheld_label"><?= $json["handheld_pos"] ?></div>
                </div>
            </div>

            <div class="additional-menu-right row align-items-center">                
                <div id="action-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" id="export"><?= $json["export"] ?></button>
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="#" id="export_excel_report"><?= $json["export_to_excel"] ?></a>

                            <a class="dropdown-item" href="#" id="export_pdf_report">
                                <?= $json["export_to_pdf"] ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- end / tab-link header -->

    <section class="content">
        <div class="container-wide">

            <div class="col-md-12 table-view">

                <div class="row">

                    <div class="col-md-12">

                        <!-- revenue -->
                        <div class="row jspdf-graph mb-4">

                            <!-- rev barchart -->
                            <div class="col-lg-8 pl-0 pr-3">
                                <div class="card barchart-box mt-0 h-100">
                                    <p class="text-center chart-header text-dark" id="revenue_7_days_label"><?= $json["revenue_7_days"] ?></p>
                                    <div class="">
                                        <div class="position-relative">
                                            <canvas id="revenue-7days" height="269"></canvas>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- end / rev chart -->

                            <!-- donut chart - revenue sources -->
                            <div class="col-lg-4 pr-0">
                                <div class="card barchart-box mt-0" id="parking-duration">
                                    <p class="text-center chart-header text-dark" id="revenue_sources_label"><?= $json["revenue_sources"] ?></p>
                                    <div class="">
                                        <div class="position-relative">
                                            <canvas id="revenue_sources_chart" height="175"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end / donut chart -->

                        </div>                        
                            <div id="live-revenue-summary-content">
                            </div>                                                        
                    </div>
                </div>
            </div>

        </div>

    </section>
</div>
</div>


<!-- save day_closure_start as js variable for use in graphs -->
<div id="dom-target" style="display: none;">
    <?php
    $date = DAY_CLOSURE_START;
    echo htmlspecialchars($date); /* You have to escape because the result will not be valid HTML otherwise */
    ?>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
    var facility_number = <?php echo $_GET["facility_number"]; ?>;
    var carpark_number =<?php echo $_GET["carpark_number"]; ?>;
    $("#full-screen").removeClass("d-none");
    function get_live_revenue_summary()
    {
        //$.get( "../ajax/dashboard.php?task=5&facility_number="+facility_number+"&carpark_number="+carpark_number, function( data ) 
        var req = {};
        req["task"] = 24;
        req["language"] = $("#language").val();
        req["facility_number"] = facility_number;
        req["carpark_number"] = carpark_number;
        var json = JSON.stringify(req);
        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            //console.log(data); 
            $('#live-revenue-summary-content').html(data);
            $('[data-target="' + clicked_device_type + '"]').click();
        });
    }

    /* Table Show - Hide */
    var clicked_device_type = "all";

    $(document).ready(function ()
    {
        $('.tab-link').on('click', function ()
        {
            var $target = $(this).data('target');
            if ($target != 'all')
            {
                $('tbody>tr').addClass("hidden");
                ;
                $('tbody>tr[data-status="' + $target + '"]').removeClass("hidden");
            } else
            {
                $('tbody>tr').removeClass("hidden").fadeIn('slow');
            }
            clicked_device_type = $target;
        });


        get_live_revenue_summary();
        rev7days();
        revenueSources();
        setInterval(function ()
        {
            get_live_revenue_summary();
            updateRevenueSources();
        }, 1000 * 30);

    });

    /* Change Active Tab Styling */

    $('.tab-link').on('click', function ()
    {
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
    });


    $('#export_excel_report').click(function (event)
    {
        export_to_excel("#live-revenue-summary-content", "Live_Revenue_Report")
    }); // end click event function

/////////////////////////////
// bar chart - revenue last 7 days
//////////////////////////////

    var rev_7_days;
    var $result;
    var revenue_data = [];

    function rev7days()
    {
        var $rev_7_days
        var revenue_amounts = {};
        var weekdays = [];

        //$.get("../ajax/dashboard.php?task=4&facility_number="+facility_number+"&carpark_number="+carpark_number, function (data) {                      
        var req = {};
        req["task"] = 25;
        req["facility_number"] = facility_number;
        req["carpark_number"] = carpark_number;
        req["language"] = $("#language").val();
        var json = JSON.stringify(req);
        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            /* chart variables */
            //console.log(data);
            var weekdates = [];
            var date_string = [];
            var test = 0

            $result = JSON.parse(data);
            // console.log($result);

            function addDates()
            {
                if (test <= 7)
                {
                    var temp1 = moment().subtract(test, 'days').format("MM-DD");
                    weekdates.unshift(temp1);

                    var temp2 = moment().subtract(test, 'days').format("ddd");
                    weekdays.unshift(temp2);

                    // used for comparing to $result
                    var temp3 = moment().subtract(test, 'days').format("YYYY-MM-DD");
                    date_string.unshift(temp3);

                    test += 1;

                    addDates();
                }
            }

            addDates()


            // create label

            var daylabels = [];
            for (var i in weekdays)
            {
                if (i < 7)
                {
                    daylabels[i] = (weekdays[i] + "\n" + weekdates[i]);
                } else
                {
                    daylabels[i] = ("Today" + "\n" + weekdays[i] + " " + weekdates[i]);
                }
            }

            // console.log(weekdates);

            // put revenue data in array
            for (var i in date_string) {
                for (var j in $result) {
                    if (date_string[i] === $result[j].date) {
                        revenue_data[i] = $result[j].amount;
                    }
                }
            }

            // console.log("revenue_data = " + revenue_data);

            $(function () {
                'use strict'

                var ticksStyle = {
                    fontColor: '#000',
                    // fontStyle: 'bold'
                }

                var mode = 'index'
                var intersect = true

                // console.log("revd = " + revenue_data);

                $rev_7_days = $('#revenue-7days')
                rev_7_days = new Chart($rev_7_days, {
                    type: 'bar',
                    // type: 'horizontalBar',
                    data: {
                        labels: daylabels,
                        datasets: [{
                                data: revenue_data,
                                label: 'Revenue',

                                // transparent bar with normal border
                                // backgroundColor: "rgba(40,167,69, 0.5)",
                                // borderColor: '#28a745',

                                backgroundColor: [
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(40,167,69, 0.5)",
                                    "rgba(69,40,167, 0.5)"
                                ],

                                borderColor: [
                                    '#28a745',
                                    '#28a745',
                                    '#28a745',
                                    '#28a745',
                                    '#28a745',
                                    '#28a745',
                                    '#28a745',
                                    '#4528a7',
                                ],

                                borderWidth: 2,
                            }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        tooltips: {
                            mode: mode,
                            intersect: intersect
                        },
                        hover: {
                            mode: mode,
                            intersect: intersect
                        },
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                    // display: false,
                                    gridLines: {
                                        display: true,
                                        lineWidth: '4px',
                                        color: 'rgba(0, 0, 0, .2)',
                                        zeroLineColor: 'transparent'
                                    },
                                    ticks: $.extend({
                                        beginAtZero: true,
                                    }, ticksStyle)
                                }],
                            xAxes: [{
                                    display: true,
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: $.extend({
                                        beginAtZero: true,
                                    }, ticksStyle)
                                }]
                        },
                        animation: {
                            duration: 1,
                        }
                    },
                    // plugin to add line break for long label
                    plugins: [{
                            beforeInit: function (chart) {
                                chart.data.labels.forEach(function (e, i, a) {
                                    // add linebreak where "\n" occurs
                                    if (/\n/.test(e)) {
                                        a[i] = e.split(/\n/);
                                    }
                                });
                            }
                        }]
                })

            })

        });
    }

//////////////////////////////
// donut chart - revenue sources
//////////////////////////////

    var config
    var ctx
    var pieChart
    var pieValues

    function revenueSources()
    {
        var pieChartCanvas = $("#revenue_sources_chart").get(0).getContext("2d");
        pieChart = new Chart(pieChartCanvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                        data: [],
                        backgroundColor: ['#00a65a', '#00c0ef', '#f56954', '#f39c12', '#3c8dbc'],
                    }],
                labels: [
                    '<?= $json["parking_fee"] ?>', //'Parking Fee',
                    '<?= $json["lost_fee"] ?>', //'Lost Fee',
                    '<?= $json["product_sale_amount"] ?>', //'Product Sale',
                    '<?= $json["reservation_amount"] ?>',
                    'VAT'
                ]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        })

        // get dynamic data

        pieValues = pieChart.data.datasets[0].data

        updateRevenueSources();
    }

    function updateRevenueSources()
    {
        var req = {};
        req["task"] = 26;
        req["facility_number"] = facility_number;
        req["carpark_number"] = carpark_number;
        req["language"] = $("#language").val();
        var json = JSON.stringify(req);
        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            //console.log(data);      
            amount = JSON.parse(data);
            pieValues[0] = amount["parking_fee"];
            pieValues[1] = amount["lost_fee"];
            pieValues[2] = amount["product_sale_amount"];
            pieValues[3] = amount["reservation_amount"];
            pieValues[4] = amount["vat_amount"];
            pieChart.update();
            //update barchart
            revenue_data[7] = amount["gross_amount"];
            rev_7_days.update();
        });


    }
    $("#language").change(function ()
    {
        update_session();
        get_live_revenue_summary();
        loadReportLabels();
    });

    function loadReportLabels()
    {
        var data = {};
        data["task"] = 9;
        data["language"] = $("#language").val();
        data["page"] = 3;
        var json = JSON.stringify(data);
        //console.log(json);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            //console.log(data);	            
            var json = JSON.parse(data);
            $("#pdf-report-header").html(json.finance);
            $("#logout").html(json.logout);
            $("#view_all_label").html(json.view_all);
            $("#manual_cashier_label").html(json.manual_cashiers);
            $("#pay_on_foot_label").html(json.pay_on_foot_machines);
            $("#handheld_label").html(json.handheld_pos);
            $("#revenue_7_days_label").html(json.revenue_7_days);
            $("#revenue_sources_label").html(json.revenue_sources);
            $("#export").html(json.export);
            $("#export_excel_report").html(json.export_to_excel);
            $("#export_pdf_report").html(json.export_to_pdf);
            $("#logout").html(json.logout);

            pieChart.data.labels[0] = json.parking_fee;
            pieChart.data.labels[1] = json.lost_fee;
            pieChart.data.labels[2] = json.product_sale_amount;
            pieChart.data.labels[3] = json.reservation_amount;
            pieChart.update();
        });
    }

</script>
