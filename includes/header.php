<?php

include 'common.php';
if(isset($_SESSION["last_login_timestamp"]) )
    {       
    $_SESSION["last_login_timestamp"]=time(); 
    $_SESSION["dashboard"]=0;
    }
else
    {
    header("Location:".URL."index.php");
    }

function checkPageAccess($page)
{   
    $data["page"]= $page;
    $data["user_role_id"]=$_SESSION["userRollId"];
    $data["task"]=14;
    //echo json_encode($data);
    $access=parcxV2UserManagement($data);
    if($access["view"]==0)
        {
        header("Location:".URL."home.php");
        exit();
        }
    return $access;
}

?>

<!DOCTYPE html>

<head>

  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="robots" content="index, no-follow">
  <meta name="author" content="AL FALAK - PARCX" />
  <title>Parcx | Parking Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?php echo URL; ?>dist/img/icon/favicon.gif" type="image/x-icon">

  <!-- plugin css -->
  <link rel="stylesheet" href="<?=URL?>plugins/daterangepicker/daterangepicker-bs3.css">

  <!-- theme css -->  
  <link rel="stylesheet" href="<?=URL?>dist/css/parcx.min.css">

  <!-- custom css -->
  <link rel="stylesheet" href="<?=URL?>dist/fonts/noto-sans.css">
  <link rel="stylesheet" href="<?=URL?>dist/css/styles.css">
  <link rel="stylesheet" href="<?=URL?>dist/css/jquery-ui.css">

  <!-- ui dependent scripts -->
  <script src="<?=URL?>plugins/jquery/jquery-3.3.1.min.js"></script>
  <script src="<?=URL?>plugins/bootstrap-multiselect.min.js"></script>

  <!-- unchecked -->

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?=URL?>plugins/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?=URL?>plugins/font-awesome-alt/css/all.css">

</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <!-- pdf loader modal -->
    <div class="modal" id="pdf-loader-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body p-4 text-center">
            <img src="/parcx/dist/img/loading.gif" class="mb-3">
            <h5 class="mb-2">Generating PDF</h5>
          </div>
        </div>
      </div>
    </div>
    
  <script>
    var eventTime=0;
    var currentTime=0;
    var withoutEvent=0;
    $(document).on('click mouseover keydown', function(evt){  
       eventTime=Date.now();    
    });

     $(document).ready(function(){
        setInterval(function(){
            currentTime= Date.now();
            withoutEvent=currentTime-eventTime;
            withoutEvent=withoutEvent/1000; //seconds               
            if(withoutEvent>=60*5)
                {
                $.get("<?=URL?>includes/logout.php", function(data){                
                if(data==1) 
                    window.location.href="<?=URL?>index.php";
                });
                }
        },1*60*1000);
    }); 
      
  </script>
  
