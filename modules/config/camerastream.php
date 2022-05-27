<?php
//https://stackoverflow.com/questions/64023550/sessions-are-not-working-when-the-site-is-called-by-an-iframe
include('../../includes/header.php');
?>
<div class="navbar-has-tablink">

    <?php
    include('../../includes/navbar-start.php');
    ?>

</ul>
<div class="header text-dark" id="pdf-report-header">IFrame</div>
<div class="row hidden-sm-down">
    <div class="col tab-header d-flex justify-content-center">
        <div class="tab-link active" data-target="overview">Overview</div>
        <!--<div class="tab-link" data-target="form">Add Playlist to Device</div>-->
    </div>
</div>

<?php
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>


<script>
$(document).ready(function() {
	$("#btn-iframe").click(function() {
            var btn_name = $("#btn-iframe").html();
            if(btn_name==="View")
            {
                var link = $("#link").val();
                $("#iframe-page").show();
                $("#iframe-page").attr('src',link);
                $("#btn-iframe").html("Close");
            }
            else if(btn_name=="Close")
            {
                $("#iframe-page").hide();
                $("#btn-iframe").html("View");
            }
                
	});
});
</script>

</head>

<body>
<div class="content-wrapper">
    <section class="content">
        <div class="container-wide">        
            <!--<div class='row'>
                <div class="col form-group">
                        <label for="">Enter Link</label>
                        <input type="text" class="form-control" id="link" required name="link">
                </div>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for=""></label>
                    <button type="submit" class="btn btn-primary btn-info" id="btn-iframe">View</button>
                </div>
            </div>
            -->

            <div class='row mt-5'>
                <div class='col'>
                    <!--<video controls>
                        <source src="http://192.168.1.64/doc/page/preview.asp?loginuse=admin&amp;loginpas=Hik12345">
                    </video>-->
                    <video width="320" height="240" autoplay controls>
                        <source src="http://192.168.1.64/doc/page/preview.asp?loginuse=admin&amp;loginpas=Hik12345" type="video/mp4">
                        <object width="320" height="240" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.5.swf">
                            <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.5.swf" /> 
                            <param name="flashvars" value='config={"clip": {"url": "%StreamURL%", "autoPlay":true, "autoBuffering":true}}' /> 
                            <p><a href="http://192.168.1.64/doc/page/preview.asp?loginuse=admin&amp;loginpas=Hik12345">view with external app</a></p> 
                        </object>
                    </video>
                    <?php
                    $vlc = "/usr/bin/vlc"; // or whatever the path to vlc is;
                    $path_file = "rtsp://admin:Hik12345@192.168.1.64:554"; // you can also retrieve that with a relative path like using realpath(relative_path);

                    // launch vlc
                    pclose(popen("vlc rtsp://admin:Hik12345@192.168.1.64:554"));
                    ?>
                    </video>
                </div>
            </div>
        </div>
    </section>
</div>

</body>

</html>