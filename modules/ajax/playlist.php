<?php 
define("DB_HOST", "localhost");
define("DB_USER", "parcxservice");
define("DB_PASSWORD", "1fromParcx!19514");
define("DB_NAME", "parcx_server");

   
$json=file_get_contents("php://input");
$data=json_decode($json,TRUE);
$task = $data["task"];

switch($task)
{
    case 1:screen_list();
        break;
    case 2:manage_playlist($data);
        break;
    case 3:save_screen_details($data);
        break;
}
function screen_list()
{
    ?>
    <div class="col-lg-9 col-md-6 block-data" data-status='overview' data-device-status="1" data-number="11" data-type="1" data-img="/parcx/Media/default.jpg" ><div class="card p-2"><div class="card-header mb-3"><div class="nav-item d-flex justify-content-between align-items-center"><h3 class="card-title">Screen 1</h3></div></div><div class="card-body p-0"><div class="row no-gutters"><div class="col-4 block-view-img my-auto text-center"><video width="150" controls="controls" preload="metadata"><source src="/parcx/Media/Videos/Video1.mp4#t=0.5" type="video/mp4"></video></div><!--<img class="img-fluid" src="/parcx/Media/default.jpg">--></div><div class="col-12"><ul class="nav flex-column"><li class="nav-item"><span class="nav-link"><span class="float-right device_ip">192.168.1.70</span></span></li><li class="nav-item"><span class="nav-link"><span class="float-right">Other Details</span></span></li><li class="nav-item"><span class="nav-link"><!--<span class="float-left"><button class='btn btn-info view-details-button' data-id='20'>View Playlists</button></span>--><span class="float-right"><button class='btn btn-info manage-playlist-button' data-id='20'>Manage Playlist</button></span></span></li></ul></div></div></div></div>
    <?php
}

function manage_playlist($data)
{
    ?>
    <div id='playlist-div' class='row card card-body'>
                 
    <div class='row  mb-3'>
        <div class='col h5'>
            <label for=''>Drag the videos in sequence to create the playlist</label>
        </div>
    </div>
    <div class='row  mb-1'>
        <div class='col'><h4>PLAYLIST LIBRARY</h4></div>
        <div class='col p-2'></div>
        <div class='col'><h4>SCREEN PLAYLISTS</h4></div>
    </div>
    <div class='row'>
        <div class='col form-group form-control scroll-smooth' id='left' ondrop='drop(event)' ondragover='allowDrop(event)'>
            <div class='nodrop btn btn-block btn-info btn-lg bg-gray play-video' id='v1' data-playlist="1" draggable='true' ondragstart='drag(event)'>
                <div class='row'>
                    <fieldset>
                        <span class='float-left mr-3' id="span-cbox-1"><input type='checkbox' class = 'playlist-checkbox' id = 'c1' data-id='1' value='p1'></checkbox></span>
                        <span class='float-left mr-3 hidden' id="span-del-1"><button class='btn btn-danger btn-sm del-playlist' value='delete1' data-id='1'><i class='fas fa-minus'></i></button></span>
                        <label id = 'l1'>Playlist 1</label>
                    </fieldset> 
                </div>
                <div class="row center">
                    <fieldset class='pl-options mt-2' id='pl-options-1'>
                        <p><input type='text' id='date-p1' class='form-control scheduledate' autocomplete='off'
                            placeholder='Choose Date Time'></p>
                        <p><span class='float-left'><input type='checkbox' id='repeat-p1'><label for=''>Repeat</label>
                        </span></p>
                        </br>
                        <p class='mt-2'><span class='text-center'><button class='btn btn-success add-playlist' value='add1' data-id='1'><i class='fas fa-plus'></i></button></span></p>
                    </fieldset>
                </div>
                <!--<a href="#collapseExample" class="small-box-footer small" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">More info <i class="fas fa-arrow-circle-right"></i></a>-->
                <details class="small">
                    <summary class="playlist-details-div" data-id="1">View List</summary>
                    <div id ="playlist-details-1" class="playlist-details d-inline">
                        
                    </div>
                </details>
                
            </div>
            
            <div class='nodrop btn btn-block btn-lg bg-gray play-video' id='v2' data-playlist="2" draggable='true' ondragstart='drag(event)'>
                <div class='row'>
                    <fieldset>
                        <span class='float-left mr-3' id="span-cbox-2"><input type='checkbox' class = 'playlist-checkbox' id = 'c2' data-id='2' value='p2'></checkbox></span>
                        <span class='float-left mr-3 hidden' id="span-del-2"><button class='btn btn-danger btn-sm del-playlist' value='delete2' data-id='2'><i class='fas fa-minus'></i></button></span>
                        <label id = 'l2'>Playlist 2</label>
                    </fieldset> 
                </div>
                <div class="row center">
                    <fieldset class='pl-options mt-2' id='pl-options-2'>
                        <p><input type='text' id='date-p2' class='form-control scheduledate' autocomplete='off'
                            placeholder='Choose Date Time'></p>
                        <p><span class='float-left'><input type='checkbox' id='repeat-p2'><label for=''>Repeat</label>
                        </span></p>
                        </br>
                        <p class='mt-2'><span class='text-center'><button class='btn btn-success add-playlist' value='add2' data-id='2'><i class='fas fa-plus'></i></button></span></p>
                    </fieldset>
                </div>
                <!--<a href="#collapseExample" class="small-box-footer small" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">More info <i class="fas fa-arrow-circle-right"></i></a>-->
                <details class="small">
                    <summary class="playlist-details-div" data-id="2">View List</summary>
                    <div id ="playlist-details-2" class="playlist-details d-inline">
                        
                    </div>
                </details>
            </div>

        </div>
        <div class='p-2'>

        </div>

        <div class='col form-group form-control scroll-smooth' id='right' ondrop='drop(event)' ondragover='allowDrop(event)'>
        <?php 
        
        //get_screen_details($data);
               
        ?>
        </div>                    
    </div> 
        <input type="submit" class="signUp btn btn-block btn-info mt-2 btn-lg" value="Save" id="save-screen-playlist" data-screen="20">
           
</div>
    <?php
}
function get_screen_details($data)
{
    $device_id = $data["device_id"];
    $con = db_connect();    
    $query_string = "select * from device_playlist where device_number = ".$device_id;
    $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
    while($data = mysqli_fetch_assoc($result))
    {
        $id = $data["playlist_id"];
        echo "<div class='nodrop btn btn-block btn-info btn-lg bg-gray playlist-right' id='right-v".$id."' data-playlist='".$id."' draggable='true' ondragstart='drag(event)'>
                <div class='row'>
                    <fieldset>
                        <span class='float-left mr-3 hidden' id='span-cbox-".$id."'><input type='checkbox' class = 'playlist-checkbox' id = 'c".$id."' data-id='".$id."' value='p".$id."'></checkbox></span>
                        <span class='float-left mr-3' id='span-del-".$id."'><button class='btn btn-danger btn-sm del-playlist' value='delete".$id."' data-id='".$id."'><i class='fas fa-minus'></i></button></span>
                        <label id = 'l".$id."'>".$data["playlist_name"]."</label>
                    </fieldset> 
                </div>
                <div class='row center'>
                    <fieldset class='pl-options mt-2 hidden' id='pl-options-".$id."'>
                        <p><input type='text' id='date-p".$id."' class='form-control scheduledate' autocomplete='off'
                            placeholder='Choose Date Time'></p>
                        <p><span class='float-left'><input type='checkbox' id='repeat-p".$id."'><label for=''>Repeat</label>
                        </span></p>
                        </br>
                        <p class='mt-2'><span class='text-center'><button class='btn btn-success add-playlist' value='add".$id."' data-id='".$id."'><i class='fas fa-plus'></i></button></span></p>
                    </fieldset>
                </div>

                <details class='small'>
                    <summary class='playlist-details-div' data-id='".$id."'>View List</summary>
                    <div id ='playlist-details-".$id."' class='playlist-details d-inline'>
                        
                    </div>
                </details>
                
            </div>";
    }
    mysqli_close($con);
    return $result;
}

function save_screen_details($data)
{
    $device_id = $data["device_id"];
    $playlists = $data["playlists"];
    $con = db_connect();
    $i=0;
    foreach ($playlists as $key => $value) {
        if($i==0){
            $query_string = "Delete from device_playlist where device_number = ".$device_id;
            $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
            $i++;
        }
        
        $query_string = "Insert into device_playlist(device_number,playlist_id,playlist_name,start_date_time,end_date_time,repeat_videos) values (".$device_id.",".$value["id"].",'".$value["name"]."','".$value["start"]."','".$value["end"]."',".$value["repeat"].")";
        $result = mysqli_query($con, $query_string) or die(mysqli_error($con));
    }
   mysqli_close($con);
    
    
}

 function db_connect() {
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$con)
        echo("Connection attempt failed");
    else
        return $con;
}
?>

