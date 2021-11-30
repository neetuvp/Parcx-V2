<script src="<?=URL?>plugins/chartjs/chart.min.js"></script>
<script src="<?=URL?>plugins/chartjs/custom-graphs.js"></script>

<script>
    //////////////////////////////
    // random numbers for test data
    //////////////////////////////

    var random = Math.floor(Math.random() * 400);

    var randomArray = Array.from({
        length: 24
    }, () => Math.floor(Math.random() * random));

    var randomScalingFactor = function () {
        return Math.round(Math.random() * 100);
    };

    //////////////////////////////
    // hour label from 00:00
    //////////////////////////////

    /* first line of label with starting hour */
    var hours_start = ['0:00', '1:00', '2:00', '3:00', '4:00', '5:00', '6:00', '7:00', '8:00', '9:00',
        '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00',
        '20:00', '21:00', '22:00', '23:00'
    ]

    /* second line of label with ending hour */
    var hours_end = []

    /* push values to hours_end based on hours_start */
    var i;
    for (i = 0; i < hours_start.length; ++i) {

        // if last value, create string using first value
        if (i != (hours_start.length - 1)) {
            var j;
            j = i + 1;
            var string_value = hours_start[j]
            hours_end.push(string_value);
            // create regular string
        } else {
            var string_value = hours_start[0]
            hours_end.push(string_value);
            // console.log(i)
            // console.log(hours.length)
        }

    }

    // create label with linebreak
    var hours_label = []

    for (var i = 0; i < hours_start.length; i++) {
        hours_label[i] = hours_start[i] + "\n" + hours_end[i]
    }

    //////////////////////////////
    // reorder hour label from day closure start
    //////////////////////////////

    // get day closure var

    if ($('#dom-target').length) {
        var domTarget = document.getElementById("dom-target");
        var day_closure_string = domTarget.textContent;

        var time = day_closure_string.split(":");
        var h = +time[0];

        var day_closure_start = h + ":" + time[1];

        /* reorder hours_start and hours_end based on day closure start */

        var time_index = hours_start.indexOf(day_closure_start);

        function reorder(data, index) {
            return data.slice(index).concat(data.slice(0, index))
        };

        var hours_start_reorder = [];
        var hours_end_reorder = [];

        hours_start_reorder = reorder(hours_start, time_index);
        hours_end_reorder = reorder(hours_end, time_index);

        /* create hours label with linebreak */

        var hours_label_reorder = []

        for (var i = 0; i < hours_start_reorder.length; i++) {
            hours_label_reorder[i] = hours_start_reorder[i] + "\n" +
                hours_end_reorder[i]
        }
    }
</script>