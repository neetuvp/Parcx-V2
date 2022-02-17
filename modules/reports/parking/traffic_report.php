<?php
require_once('../../../includes/common.php');

$page_title = "Application Home";

//# Import application layout.
include('../../../includes/header.php');
include('../../../includes/navbar-start.php');

$data = array();
$data["task"] = 29;
$data["language"] = $_SESSION["language"];
$data["page"] = 13;
$json = parcxReport($data);
?>

</ul>

<div class="header text-dark" id="pdf-report-header"><?= $json["traffic_report"] ?></div>

<?php
include('../../../includes/navbar-end.php');
include('../../../includes/sidebar.php');
?>

<div class="content-wrapper">

    <!-- additional menu -->
    <div class="additional-menu row m-0 bg-white border-bottom">
        <div class="col d-flex pl-1 align-items-center">

            <div class="flex-grow-1 row additional-menu-left">

                <!-- carparks multiselect -->
                <div class="col-md-2">
                    <select class="form-control" id="multiselect" multiple="multiple">
                        <?php echo parcxSettings(array("task" => "12")); ?>
                    </select>
                </div>

                <!--weekdays -->
                <div class="col-md-2">
                    <select id="days" multiple="multiple" class="form-control">
                        <option value="'Sunday'" id = "sunday"><?= $json["sunday"] ?></option>
                        <option value="'Monday'" id = "monday"><?= $json["monday"] ?></option>
                        <option value="'Tuesday'" id = "tuesday"><?= $json["tuesday"] ?></option>
                        <option value="'Wednesday'" id = "wednesday"><?= $json["wednesday"] ?></option>
                        <option value="'Thursday'" id="thursday"><?= $json["thursday"] ?></option>
                        <option value="'Friday'" id="friday"><?= $json["friday"] ?></option>
                        <option value="'Saturday'" id="saturday"><?= $json["saturday"] ?></option>
                    </select>
                </div>


                <?php include('../../../includes/additional-menu-report.php'); ?>    
                <!-- end / additional menu -->

                <section class="content">
                    <div class="container-wide">

                        <!-- traffic summary chart -->
                        <div class="d-none" id="chart_container">
                            <div class="row jspdf-graph">
                                <div class="col-lg-12">
                                    <div class="card barchart-box report-chart">
                                        <div class="position-relative">
                                            <div><canvas id="traffic-chart" height="300"></canvas></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="report-content">       
                        </div>

                    </div>

            </div>
            </section>
        </div>

        <?php include('../../../includes/footer.php'); ?>

        <script>
            //////////////////////////////
            // load report data
            //////////////////////////////

            var id;
            var date_range_message = "choose date range";
            from = "<?= $current_date . " " . DAY_CLOSURE_START ?>";
            to = "<?= $current_date . " " . DAY_CLOSURE_END ?>";

            var click_count = 0;
            var load_report = 0;

            search_label = "<?= $json["search"] ?>";
            entries_label = "<?= $json["entries_label"] ?>";
            info_label = "<?= $json["info_label"] ?>";
            next_label = "<?= $json["next"] ?>";
            previous_label = "<?= $json["previous"] ?>";


            $(function ()
            {
                $('#multiselect').multiselect(
                        {
                            buttonWidth: '100%',
                            includeSelectAllOption: true,
                            selectAllText: "<?= $json["all_carparks"] ?>",
                            nonSelectedText: "<?= $json["select_carparks"] ?>",
                            selectAllNumber: false,
                            allSelectedText: "<?= $json["all_carparks"] ?>",
                        });
                $('#days').multiselect(
                        {
                            buttonWidth: '100%',
                            includeSelectAllOption: true,
                            selectAllText: "<?= $json["all_days"] ?>",
                            nonSelectedText: "<?= $json["select_days"] ?>",
                            selectAllNumber: false,
                            allSelectedText: "<?= $json["all_days"] ?>"
                        });
            });

        //$('#view-report-button').click(function (event) 
            function callReport()
            {
                var weekdays = $("#days").val().toString();
                var carpark = $("#multiselect").val().toString();

                var data = {
                    toDate: to,
                    fromDate: from,
                    weekdays: weekdays,
                    carpark: carpark,
                    language: $("#language").val(),
                    task: 23
                };
                var temp = JSON.stringify(data);

                $.post("../../ajax/reports.php", temp)
                        .done(function (result) {
                            loadReport(result);

                            if ($("#report-content").find('table').length != 0) {

                                // show chart when report first loads
                                $("#chart_container").removeClass("d-none");

                                // clear previous array data
                                entries_data = [];
                                exits_data = [];
                                label_h = [];

                                getChartData();

                                // for first click
                                if (click_count === 0) {

                                    // load chart
                                    // note - chart *must* be visible first
                                    trafficSummaryChart();

                                    click_count += 1;

                                } else {

                                    // for all other clicks
                                    updateTrafficSummaryChart()

                                }

                            } else {
                                $("#chart_container").addClass("d-none");
                            }

                        }, "json");


                event.preventDefault();
            }

            function loadMultipleDataTable()
            {
                $("table[id^='TABLE']").DataTable(
                        {
                            "paging": true,
                            "lengthChange": true,
                            "searching": true,
                            "ordering": true,
                            "info": true,
                            "autoWidth": false,
                            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                            "aaSorting": [],
                            "language": {
                                search: search_label,
                            },
                            "oLanguage": {
                                "sLengthMenu": entries_label,
                                "info": info_label,
                                "oPaginate": {
                                    "sPrevious": previous_label,
                                    "sNext": next_label
                                }
                            },
                        });
            }

            $('#view-report-button').click(function (event)
            {
                if (!daterange)
                    alert(date_range_message);
                else
                    callReport();
            });



            function loadReportLabels()
            {
                var data = {};
                data["task"] = 29;
                data["language"] = $("#language").val();
                data["page"] = 13;
                var json = JSON.stringify(data);
                $.post("../../ajax/reports.php", json, function (data)
                {
                    var json = JSON.parse(data);
                    date_range_message = json.choose_datetime_range;
                    $("#reservationtime").attr('placeholder', json.choose_datetime_range);
                    $("#pdf-report-header").html(json.traffic_report);
                    $("#view-report-button").html(json.view_report);
                    $("#export").html(json.export);
                    $("#export_excel_report").html(json.export_to_excel);
                    $("#export_pdf_report").html(json.export_to_pdf);
                    $("#logout").html(json.logout);
                    search_label = json.search;
                    entries_label = json.entries_label;
                    info_label = json.info_label;
                    previous_label = json.previous;
                    next_label = json.next;

                    $("#sunday").html(json.sunday);
                    $("#monday").html(json.monday);
                    $("#tuesday").html(json.tuesday);
                    $("#wednesday").html(json.wednesday);
                    $("#thursday").html(json.thursday);
                    $("#friday").html(json.friday);
                    $("#saturday").html(json.saturday);



                    $('#multiselect').multiselect('destroy');
                    $('#multiselect').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: json.all_carparks,
                                nonSelectedText: json.select_carparks,
                                selectAllNumber: false,
                                allSelectedText: json.all_carparks
                            });

                    $('#days').multiselect('destroy');
                    $('#days').multiselect(
                            {
                                buttonWidth: '100%',
                                includeSelectAllOption: true,
                                selectAllText: json.all_days,
                                nonSelectedText: json.select_days,
                                selectAllNumber: false,
                                allSelectedText: json.all_days
                            });

                    traffic_summary_chart.data.datasets[0].label = json.entries;
                    traffic_summary_chart.data.datasets[1].label = json.exits;
                    traffic_summary_chart.update();
                });


            }


            $("#language").change(function ()
            {
                update_session();
                loadReportLabels();
                callReport();
            });
            //////////////////////////////
            // excel export
            //////////////////////////////

            $('#export_excel_report').click(function () {
                export_to_excel("#report-content", "PARCX_Traffic_Summary_Report")
            }); // end click event function

            //////////////////////////////
            // bar chart - traffic summary
            //////////////////////////////

            var entries_data = [];
            var exits_data = [];
            var label_h = [];

            function getChartData() {

                // save report data for use in chart
                for (dataCount = 0; dataCount <= 23; dataCount++) {
                    var temp_var = "chartData_" + dataCount
                    var temp1 = $("#" + temp_var).attr('data-entry');
                    var temp2 = $("#" + temp_var).attr('data-exit');
                    var temp3 = $("#" + temp_var).attr('data-hour');
                    //var hour=temp3.split(' ').join('\n');
                    entries_data.push(temp1);
                    exits_data.push(temp2);
                    label_h.push(temp3);
                }

            }

            function updateTrafficSummaryChart() {
                // set barchart data
                traffic_summary_chart.data.datasets[0].data = entries_data;
                traffic_summary_chart.data.datasets[1].data = exits_data;
                traffic_summary_chart.update();
            }

            var traffic_summary_chart

            function trafficSummaryChart() {

                $(document).ready(function () {

                    $(function () {
                        var default_data = {
                            labels: label_h,
                            datasets: [{
                                    data: entries_data,
                                    label: '<?= $json["entries"] ?>', //'Entries',

                                    // transparent bar with normal border
                                    backgroundColor: "rgba(40,167,69, 0.5)",
                                    borderColor: '#28a745',
                                    borderWidth: 1.5,
                                },
                                {
                                    data: exits_data,
                                    label: '<?= $json["exits"] ?>', //'Exits',

                                    // transparent bar with normal border
                                    backgroundColor: "rgba(0,123,255, 0.5)",
                                    borderColor: '#007bff',
                                    borderWidth: 1.5,
                                },
                            ]
                        }

                        var ticksStyle = {
                            fontColor: '#000',
                        }

                        var mode = 'index'
                        var intersect = true

                        var $traffic_summary_chart = $('#traffic-chart')
                        traffic_summary_chart = new Chart($traffic_summary_chart, {
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
        </script>
