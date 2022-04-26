
<?php 
$page_title="Daemon Status";
include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

<?php
include('../../includes/navbar-start.php');
?>

</ul>

<div class="header text-dark" id="pdf-report-header">Daemon Status</div>

<?php 
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<div class="content-wrapper block-data">
  <section class="content">
    <div class="container-wide">
      <div id = "daemonlist">
      <?php echo parcxDaemonStatus(array("task"=>"1"));?>
      </div> 
    </div>
  </section>
</div>   
<?php include('../../includes/footer.php');?>
<script>
function callDaemonFucntion(data)
  {
  var jsondata = JSON.stringify(data);    
  $.post("../../modules/ajax/daemon_status.php",jsondata,function(data){                     
        location.reload();                  
  })
  .fail(function(jqxhr,status,error){
      alert("Error: "+error);

  });
  }
function stopDaemon(daemon_name)
  {
  var data={};
  data["task"]=2;
  data["daemon"]=daemon_name;
  callDaemonFucntion(data);           
  }

function startDaemon(daemon_name)
  {
  var data={};
  data["task"]=3;
  data["daemon"]=daemon_name;
  callDaemonFucntion(data);           
  }

function restartDaemon(daemon_name)
  {
  var data={};
  data["task"]=4;
  data["daemon"]=daemon_name;
  callDaemonFucntion(data);           
  }
</script>