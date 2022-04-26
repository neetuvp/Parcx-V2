<?php 
$page_title="Device Dashboard";

include('../../includes/header.php');
?>

<div class="navbar-has-tablink">

<?php
include('../../includes/navbar-start.php');
?>


</ul>
<div class="header text-dark" id="pdf-report-header">Discount Settings</div>
<div class="row">
  <div class="col tab-header d-flex justify-content-center">
    <div class="tab-link active" data-target="active_discount">Active Discounts</div>
    <div class="tab-link" data-target="disabled_discount">Disabled Discounts</div>
    <div class="tab-link" data-target="add-discount">Add Discounts</div>
    
  </div>
</div>

<?php 
include('../../includes/navbar-end.php');
include('../../includes/sidebar.php');
?>
<!-- add Device -->
<div class="content-wrapper block-data" data-status="add-discount" style="display:none;">
  <section class="content">
    <div class="container-wide">
 
      <div class="card card-primary mb-4 col-md-4" id="add-discount-div">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="form-group">
              <?php
                echo '<form name = "form" method = "post" action = "">';
                echo '<table border=0>';
                echo '<tr><td style = "width:50%">Discount Id</td><td>';
                echo GetNextDiscountId();
                echo '</td></tr>';
        
                echo '<tr><td>Discount Name</td><td>';
                echo '<input type= "text" class="form-control" name="discount_name" id ="discount_name"  value = ""/></td></tr>';
                echo '</td></tr>';
                
                echo '<tr><td>Discount Type</td><td>';
                echo '<select name = "discount_type" id = "discount_type" class="form-control"><option value = "amount">amount</option><option value = "percentage">percentage</option></select>';
                echo '</td></tr>';

                echo '<tr><td>Discount Option</td><td>';
                echo '<select name = "discount_option" id = "discount_option" class="form-control"><option value = "coupon">coupon</option><option value = "pos">pos</option></select>';
                echo '</td></tr>';

                echo '<tr><td>Discount Category</td><td>';
                echo '<input type= "text" class="form-control" name="discount_category" id="discount_category"  value = ""/></td></tr>';
                echo '</td></tr>';

                echo '<tr><td>Discount Value</td><td>';
                echo '<input type= "text" class="form-control" name="discount_value" id="discount_value"  value = ""/></td></tr>';
                echo '</td></tr>';

                echo '<tr><td>Carpark Name</td><td>';
                echo '<select name = "cp_name" id = "cp_name"  class="form-control">';
                $cp = GetCarparks();
                $lastcp = count($cp)-1;
                foreach($cp as $i=>$cprow)
                {
                    echo '<option value = "'.$cprow['carpark_id'].':'.$cprow['carpark_name'].'" >'.$cprow['carpark_name'].'</option>';
                }
                echo '</select></td></tr>';
        
                echo '<tr><td>Status</td><td>';
                echo '<input type= "checkbox" name="status" id="status" value = "" /></td></tr>';

                echo '<tr><td><button type="submit" class="btn btn-primary" id="add_discount">Add Discount</button></td></tr>';
                echo '</table>'
               // echo '<tr><td>Device Category Name</td><td>';
              ?>
                </div>
            </div>                        
          </div><!--row-->
       
        </div><!--card body-->
      </div>
    </div>
  </section>
</div>
<!-- End Add Discount -->

<!-- Active discount-->
<div class="content-wrapper block-data" data-status="active_discount">
  <section class="content">
    <div class="container-wide">

 
<div class="card card-primary mb-4" id="active-discount-table">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <div class="col" >Discount Id</div>
      <div class="col">Discount Name</div>
      <div class="col">Discount Type</div>
      <div class="col">Discount Option</div>
      <div class="col">Discount Category</div>
      <div class="col">Discount Value</div>
      <div class="col">Carpark</div>
      <div class="col"></div>
      <div class="col"></div>
    </div>      
  </div>  
  <?php
   $cp = GetCarparks();
   $result = GetDiscountSettings("");
   foreach($result as $i=>$row)
   {
     if($row['status']==1)
     {
      echo "<div class='table-view'>";
      echo "<div class='card-text'>";
      echo "<div class='d-flex justify-content-between align-items-center'>";
      echo "<div class='col' id = 'discount_id".$row['id']."'>".$row['discount_id']."</div>";
      echo "<div class='col' id = 'discount_name".$row['id']."'>".$row['discount_name']."</div>";
      echo "<div class='col' id = 'discount_type".$row['id']."'>".$row['discount_type']."</div>";
      echo "<div class='col' id = 'discount_option".$row['id']."'>".$row['discount_option']."</div>";
      echo "<div class='col' id = 'discount_category".$row['id']."'>".$row['discount_category']."</div>";
      echo "<div class='col' id = 'discount_value".$row['id']."'>".$row['discount_value']."</div>";
      $carpark_name = "";
      foreach($cp as $j=>$cprow)
      {
          if($row['carpark_number']==$cprow['carpark_id'])
          {
              $carpark_name =  $cprow['carpark_name'];
          }
      }
      echo "<div class='col' id = 'carpark_number".$row['id']."'>".$carpark_name."</div>";
      echo "<div class='col'>";				
      echo "<input class='btn btn-danger btn-disable-discount btn-block' type='submit'  value='Disable' id='".$row['id']."'>";
      echo "</div>";
      echo "<div class='col' id = 'editbtn".$row['id']."'>";				
      echo "<input class='btn btn-info btn-block btn-edit-discount' id ='editbtn".$row['id']."' type='submit'  value='Edit' onclick = 'EditDiscount(".$row['id'].",".json_encode($cp).")' id='".$row['id']."' >";
      echo "</div>";
      echo "</div>";
      echo "</div>";
      echo "</div>";
     }
   }

  ?>
  

</div>
</div>
</section>
</div><!-- /.row -->

<!-- Disabled discount-->
<div class="content-wrapper block-data" data-status="disabled_discount" style="display:none;">
  <section class="content">
    <div class="container-wide">
 

      <div class="card card-primary mb-4" id="active-discount-table">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          <div class="col" >Discount Id</div>
          <div class="col">Discount Name</div>
          <div class="col">Discount Type</div>
          <div class="col">Discount Option</div>
          <div class="col">Discount Category</div>
          <div class="col">Discount Value</div>
          <div class="col">Carpark</div>
            <div class="col"></div>
          </div>
        </div>
        <?php
          $result = GetDiscountSettings("");
          foreach($result as $i=>$row)
          {
            if($row['status']==0)
            {
              echo "<div class='table-view'>";
              echo "<div class='card-text'>";
              echo "<div class='d-flex justify-content-between align-items-center'>";
              echo "<div class='col'>".$row['discount_id']."</div>";
              echo "<div class='col'>".$row['discount_name']."</div>";
              echo "<div class='col'>".$row['discount_type']."</div>";
              echo "<div class='col'>".$row['discount_option']."</div>";
              echo "<div class='col'>".$row['discount_category']."</div>";
              echo "<div class='col'>".$row['discount_value']."</div>";
              echo "<div class='col'>".$row['carpark_number']."</div>";
              echo "<div class='col'>";				
              echo "<input class='btn btn-success btn-enable-discount btn-block' type='submit'  value='Enable' id='".$row['id']."'>";
              echo "</div>";
              echo "</div>";
              echo "</div>";
              echo "</div>";
            }
          }       
        ?>

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
<script src="../../dist/js/discountsettings.js"></script>
<?php include('../../includes/footer.php');?>