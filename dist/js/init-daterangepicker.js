/* intialize date range picker */

$('#reservationtime').daterangepicker(
    {
    timePicker: true,
    timePickerIncrement: 1,
    format: 'YYYY-MM-DD HH:mm:ss'
    })

// date range with button ops - not in use
$('#daterange-btn').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    },

    function (start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
)

// remove timepicker for specified pages
var pageName = document.location.href.match(/[^\/]+$/)[0];
var pageList = ["parking_duration.php", "traffic_report.php", "revenue_summary.php", "discount_usage.php"];

if ($.inArray(pageName, pageList) != -1) {
    $('#reservationtime').daterangepicker({
        timePicker: false,
        format: 'YYYY-MM-DD'
    })
}

var daterange
var from
var to

// save from/to values for report
$('#reservationtime').on('apply.daterangepicker', function (ev, picker) 
    {
    daterange = $("#reservationtime").val();

    if ($.inArray(pageName, pageList) != -1) 
        {
        // no time selector
        from = daterange.substring(0, 10);
        to = daterange.substring(13, 32);
        } 
   else 
        {
        from = daterange.substring(0, 19);
        to = daterange.substring(22, 41);
        } 
    });
