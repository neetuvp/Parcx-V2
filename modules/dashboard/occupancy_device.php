<?php
include('../../includes/header.php');
$access = checkPageAccess("occupancy");
$_SESSION["dashboard"]=1;
?>

<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    $data = array();
    $data["task"] = 9;
    $data["language"] = $_SESSION["language"];
    $data["page"] = 4;
    $json = parcxV2Dashboard($data);
    ?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["occupancy"] ?></div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<div class="content-wrapper">        

    <section class="content">
        <div class="container-wide container-fix">

            <div class="row text-dark" id="occupancy_content">

            </div> <!-- End. Row text Dark -->

            <!-- entry exit chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card barchart-box" id="HourlyOccupancyGraph">

                        <nav class="navbar navbar-light navbar-expand-md bg-faded justify-content-center p-0">
                            <a href="/" class="d-flex w-50 mr-auto"></a>

                            <div class="navbar-collapse w-100" id="collapsingNavbar3">

                                <ul class="navbar-nav w-100 justify-content-center">
                                    <p class="text-center chart-header text-dark justify-content-middle">
                                        <?= $json["hourly_occupancy"] ?>

                                    </p>
                                </ul>

                                <div class="w-100 row m-0">
                                    <div class="col-4 ml-auto p-0">
                                        <select id="linechart-select">                                            
                                            <option value="0" id="option_1"><?= $json["this_day_last_week"] ?></option>
                                            <option value="1" id="option_2"><?= $json["last_week_average"] ?></option>
                                            <option value="2" id="option_3"><?= $json["all_time_average"] ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </nav>

                        <div class="">
                            <div class="position-relative">
                                <canvas id="hourly-occ-chart" height="300"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </div>                   
            <!-- end / entry exit chart -->
        </div>

    </section>

</div>

<script src="../../plugins/knob/jquery.knob.js"></script>

<!-- save day_closure_start as js variable for use in graphs -->
<div id="dom-target" style="display: none;">
    <?php
    $date = DAY_CLOSURE_START;
    echo htmlspecialchars($date); /* You have to escape because the result will not be valid HTML otherwise */
    ?>
</div>
</div>
<?php include '../../includes/footer.php'; ?>
<script>
    var facility_number = <?php echo $_GET["facility_number"]; ?>;
    var carpark_number =<?php echo $_GET["carpark_number"]; ?>;
    var hourly_occ_chart;    
    $("#full-screen").removeClass("d-none");
    function get_live_occupancy()
    {        
        var req = {};
        req["task"] = 29;
        req["language"] = $("#language").val();
        req["facility_number"] = facility_number;
        req["carpark_number"] = carpark_number;
        var json = JSON.stringify(req);        
        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            $('#occupancy_content').html(data);
            LoadKnob();
        });
    }
    
    get_live_occupancy();
    hourlyOccupancy();

    /* jQueryKnob */
    function LoadKnob()
    {
        $('.knob').knob({
            draw: function () {

                // "tron" case
                if (this.$.data('skin') == 'tron') {

                    var a = this.angle(this.cv)  // Angle
                            ,
                            sa = this.startAngle          // Previous start angle
                            ,
                            sat = this.startAngle         // Start angle
                            ,
                            ea                            // Previous end angle
                            ,
                            eat = sat + a                 // End angle
                            ,
                            r = true

                    this.g.lineWidth = this.lineWidth

                    this.o.cursor
                            && (sat = eat - 0.3)
                            && (eat = eat + 0.3)

                    if (this.o.displayPrevious) {
                        ea = this.startAngle + this.angle(this.value)
                        this.o.cursor
                                && (sa = ea - 0.3)
                                && (ea = ea + 0.3)
                        this.g.beginPath()
                        this.g.strokeStyle = this.previousColor
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                        this.g.stroke()
                    }

                    this.g.beginPath()
                    this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
                    this.g.stroke()

                    this.g.lineWidth = 2
                    this.g.beginPath()
                    this.g.strokeStyle = this.o.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
                    this.g.stroke()

                    return false
                }
            }
        })
    } // end LoadKnob
    /* END JQUERY KNOB */

    function hourlyOccupancy()
    {
        // set up multiselect
        $(document).ready(function ()
        {
            $('#linechart-select').on('change', function (e) {
                hourly_occ_chart.data.datasets[2].data = getaverageoccupancy();
                hourly_occ_chart.data.datasets[2].label = $('#linechart-select').find("option:selected").text();
                hourly_occ_chart.update();
            });




            var maxdata = [];
            var mindata = [];

            /* occupancy data */
            maxdata = getoccupancy("Max");
            mindata = getoccupancy("Min");
            $(function () {
                'use strict'

                var default_data = {
                    labels: hours_label_reorder,
                    datasets: [{
                            data: mindata,
                            label: "<?= $json["minimum"] ?>", //'Minimum',

                            // transparent bar with normal border
                            backgroundColor: "rgba(40,167,69, 0.5)",
                            borderColor: '#28a745',
                            borderWidth: 1.5,
                        },
                        {
                            data: maxdata,
                            label: "<?= $json["maximum"] ?>", //'Maximum',

                            // transparent bar with normal border
                            backgroundColor: "rgba(0,123,255, 0.5)",
                            borderColor: '#007bff',
                            borderWidth: 1.5,
                        },
                        {
                            type: 'line',
                            label: "<?= $json["this_day_last_week"] ?>", //"This day last week",
                            borderColor: '#28a745',
                            borderWidth: 2,
                            fill: false,
                            data: getaverageoccupancy()
                        }
                    ]
                }

                /* end datasets */

                var ticksStyle = {
                    fontColor: '#000',
                    // fontStyle: 'bold'
                }

                var mode = 'index'
                var intersect = true

                var $hourly_occ_chart = $('#hourly-occ-chart')
                hourly_occ_chart = new Chart($hourly_occ_chart, {
                    type: 'bar',

                    data: default_data,
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            mode: mode,
                            intersect: intersect
                        },
                        hover: {
                            mode: mode,
                            intersect: intersect
                        },
                        legend: {
                            display: true,
                            text: 'test1'
                        },
                        scales: {
                            yAxes: [{
                                    gridLines: {
                                        display: true,
                                        lineWidth: '4px',
                                        color: 'rgba(0, 0, 0, .2)',
                                        zeroLineColor: 'transparent'
                                    },
                                    // scaleLabel: {
                                    //   display: true,
                                    //   labelString: 'Number of Cars',
                                    //   fontStyle: 'inherit',
                                    // },
                                    ticks: $.extend({
                                        beginAtZero: true,
                                    }, ticksStyle)
                                }],
                            xAxes: [{
                                    display: true,
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: ticksStyle
                                }]
                        },
                        // animation: false,
                    },
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

    function getoccupancy(type)
    {
        var tmp = [];
        var dt = {};
        dt['facility'] = facility_number;
        dt['carpark'] = carpark_number;
        dt['type'] = type;
        dt['task'] = 4;
        var jsondata = JSON.stringify(dt);
        
        $.ajax({
            type: 'post',
            url: "../ajax/dashboard-ajax.php",
            data: jsondata,
            cache: false,
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                for (var i = 0; i < data.length; i++) {
                    tmp.push(data[i]);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                //alert("textStatus" + textStatus);
                alert("getoccupancy " + errorThrown + " " + textStatus+ " "+data);
            }
        });
        return tmp;
    }

    function getaverageoccupancy()
    {
        var tmp = [];
        var dt = {};
        dt['carpark'] = carpark_number;
        dt['facility'] = facility_number;
        dt['seloption'] = $('#linechart-select').find("option:selected").val();
        dt['task'] = 5;

        var jsondata = JSON.stringify(dt);
        $.ajax({
            type: 'post',
            url: "../ajax/dashboard-ajax.php",
            data: jsondata,
            cache: false,
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            success: function (data) {
                for (var i = 0; i < data.length; i++) {
                    tmp.push(data[i]);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("getaverageoccupancy " + errorThrown + " " + textStatus);
            }
        });
        return tmp;
    }



    function updateHourlyOccupancy()
    {
        hourly_occ_chart.data.datasets[0].data = getoccupancy("Min");
        hourly_occ_chart.data.datasets[1].data = getoccupancy("Max");
        hourly_occ_chart.data.datasets[1].data = getoccupancy("Max");
        hourly_occ_chart.update();
    }

    setInterval(function ()
    {
        get_live_occupancy();
        updateHourlyOccupancy;

    }, 30000);


    $("#language").change(function ()
    {
        update_session();
        loadReportLabels();
        get_live_occupancy();
    });
    function loadReportLabels()
    {
        var data = {};
        data["task"] = 9;
        data["language"] = $("#language").val();
        data["page"] = 4;
        var json = JSON.stringify(data);
        //console.log(json);
        $.post("../ajax/dashboard-ajax.php", json, function (data)
        {
            //console.log(data);	            
            var json = JSON.parse(data);
            $("#pdf-report-header").html(json.occupancy);
            $("#logout").html(json.logout);
            $(".chart-header").html(json.hourly_occupancy);
            $("#option_1").html(json.this_day_last_week);
            $("#option_2").html(json.last_week_average);
            $("#option_3").html(json.all_time_average);


            hourly_occ_chart.data.datasets[0].label = json.minimum;
            hourly_occ_chart.data.datasets[1].label = json.maximum;
            hourly_occ_chart.data.datasets[2].label = json.this_day_last_week;
            hourly_occ_chart.update();
        });
    }

</script>


