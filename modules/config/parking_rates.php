<?php 
$page_title="Device Dashboard";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

<?php
include('../../includes/navbar-start.php');
?>

</ul>
<div class="header text-dark" id="pdf-report-header">Parking Rates</div>


<?php 
ini_set("display_errors",1);
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>

<!-- Active Parking-->
<div class="content-wrapper block-data" data-status="active_parking">

  <!-- additional menu -->
  <div class="additional-menu row m-0 bg-white border-bottom">
    <div class="col d-flex pl-1 align-items-center">

      <div class="flex-grow-1 row additional-menu-left nav-button-group">

        <?php
          $num = GetParkingRateCount();
          //$test = GetParkingRateSettings('parking_rate2');
          $result = GetParkingRateLabels("");
          foreach($result as $i=>$row)
          {
            for($j=1;$j<=$num-1;$j++)
            {
              $prate = "parking_rates".$j;
              echo "<div class='col-auto'><input class='btn btn-info btn-parking-rate' id ='parking_rates".$j."' type='submit'  value='".$row['parking_rate'.$j]."' onclick = ShowParkingRate('".$prate."')></div>";
            }
          }
        ?>

      </div>

    </div>
  </div>
  <!-- end / additional menu -->

  <section class="content">
    <div class="container-wide">

      <div class="card card-primary mb-4 col-md-8 p-0" id="active-parking" data-status="parking_details">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="col-2">No</div>
            <div class="col">Fixed Fees</div>
            <div class="col-2">Rate</div>
            <div class="col-2"></div>
            <div class="col-2"></div>
          </div>
        </div>
        <?php
  $no = 1;
  $rate = 'parking_rates1';
   $result = GetFixedParkingRateSettings('parking_rates1');
   echo "<input type='hidden' name = 'rateno' id = 'rateno' value='parking_rates1'>";				
   foreach($result as $i=>$row)
   {
  
      echo "<div class='table-view'>";
      echo "<div class='card-text'>";
      echo "<div class='d-flex justify-content-between align-items-center'>";
      echo "<div class='col-2'>".$no."</div>";
      echo "<div class='col' id = 'ratelabel".$row['id']."'>".$row['rate_labels']."</div>";
      echo "<div class='col-2' id = 'fixedrate".$row['id']."'>".$row['parking_rates1']."</div>";
      echo "<div class='col-2'>";
      echo "<input class='btn btn-danger btn-block btn-cancel-parking' id ='cancelbtn".$row['id']."' type='submit'  value='Cancel' onclick='CancelEdit(".$row['id'].")' style='visibility:hidden'/>";
      echo"</div>";
      echo "<div class='col-2' id = 'editdivfixed".$row['id']."'>";
      echo "<input class='btn btn-info btn-block btn-edit-parking' id ='editbtnfixed".$row['id']."' type='submit'  value='Edit' onclick = 'EditFixedRate(".$row['id'].")' id='".$row['id']."' >";
      echo "</div>";
     
      echo "</div>";
      echo "</div>";
      echo "</div>";
      $no++;
     
   }

   ?>


      </div>

      <div class="card card-primary mb-4 col-md-8 p-0" id="active-parking" data-status="parking_details">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="col">No</div>
            <div class="col">Time Unit</div>
            <div class="col">Time Duration</div>
            <div class="col">Rate</div>
            <div class="col"></div>
            <div class="col"></div>
          </div>
        </div>
        <?php
  $no = 1;
   $result = GetParkingRateSettings('parking_rates1');
   echo "<input type='hidden' name = 'rateno' id = 'rateno' value='parking_rates1'>";				
   foreach($result as $i=>$row)
   {
  
      echo "<div class='table-view'>";
      echo "<div class='card-text'>";
      echo "<div class='d-flex justify-content-between align-items-center'>";
      echo "<div class='col'>".$no."</div>";
      if($row['time_unit_parking_rates1']=="hours")
      {
        $unit = "Hours";
      }
      else if($row['time_unit_parking_rates1']=="mins")
      {
        $unit = "Minutes";
      }
      echo "<div class='col' id = 'time_unit".$row['id']."'>".$unit."</div>";
      echo "<div class='col' id = 'time_duration".$row['id']."'>".$row['time_duration_parking_rates1']."</div>";
      echo "<div class='col' id = 'prate".$row['id']."'>".$row['parking_rates1']."</div>";

      echo "<div class='col'>";
      echo "<input class='btn btn-danger btn-block btn-cancel-parking' id ='cancelbtnparking".$row['id']."' type='submit'  value='Cancel' onclick='CancelEdit(".$row['id'].")' style='visibility:hidden'/>";
      echo"</div>";
      echo "<div class='col' id = 'editdiv".$row['id']."'>";
      echo "<input class='btn btn-info btn-block btn-edit-parking' id ='editbtnparking".$row['id']."' type='submit'  value='Edit' onclick = 'EditRate(".$row['id'].")' id='".$row['id']."' >";
      echo "</div>";
     
      echo "</div>";
      echo "</div>";
      echo "</div>";
      $no++;
     
   }

   ?>


      </div>
    </div>
  </section>
</div><!-- /.row -->

<!-- Show Parking Rate-->
<div class="content-wrapper block-data" data-status="parking_rate" style="display:none;">
  <section class="content">
    <div class="container-wide">
      <div class="row mb-3">
        <div class="col-sm-6">
          <h4 class="text-dark" id="parking_rate_text"></h4>
        </div><!-- /.col -->
      </div><!-- /.row -->

      <div class="card card-primary mb-4" id="active-discount-table">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="col">No</div>
            <div class="col">Time Unit</div>
            <div class="col">Time Duration</div>
            <div class="col">Rate</div>
            <div class="col"></div>
          </div>
        </div>      
      </div>
    </div>
  </section>
</div><!-- /.row -->
</div>
<script>
  /* Table Show - Hide */

  $(document).ready(function () {
    $('.tab-link').on('click', function () {
      var $target = $(this).data('target');
      if ($target != 'all') {
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
      } else {
        $('.block-data').css('display', 'none').fadeIn('slow');
      }
    });
  });

  /* Change Active Tab Styling */

  $('.tab-link').on('click', function () {
    $('.tab-link').removeClass('active');
    $(this).addClass('active');
  });

  
</script>
<script src="../../dist/js/parkingsettings.js"></script>
<?php include('../../includes/footer.php');?>
