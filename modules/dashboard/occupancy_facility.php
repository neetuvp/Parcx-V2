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
    $json = parcxDashboard($data);
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

            </div>
        </div>

    </section>
</div>
<script src="../../plugins/knob/jquery.knob.js"></script>
</div>
<?php include '../../includes/footer.php'; ?>
<script>
    var facility_number = 0;
    var carpark_number = 0;

    function get_live_occupancy()
    {
        var req = {};
        req["task"] = 27;
        req["language"] = $("#language").val();
        var json = JSON.stringify(req);
        $.post("../ajax/dashboard-ajax.php", json, function (data) {
            $('#occupancy_content').html(data);
           
            carpark_number = $("#carpark_number").val();
            facility_number = $("#facility_number").val();
            if (typeof carpark_number !== "undefined")
                window.location = "occupancy_device.php?facility_number=" + facility_number + "&carpark_number=" + carpark_number;
            else if (typeof facility_number !== "undefined")
                window.location = "occupancy_carpark.php?facility_number=" + facility_number;
            else
                LoadKnob();
        });
    }


    $(document).on('click', '.show-facility-details', function () {
        facility_number = $(this).attr("facility_number");
        window.location = "occupancy_carpark.php?facility_number=" + facility_number;
    });

    get_live_occupancy();

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
            console.log(data);
            var json = JSON.parse(data);
            $("#pdf-report-header").html(json.occupancy);
            $("#logout").html(json.logout);



        });
    }



    setInterval(function ()
    {
        get_live_occupancy();
    }, 30000);










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





</script>


