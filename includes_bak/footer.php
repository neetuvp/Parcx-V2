
<script>
var search_label="<?= isset($json["search"])?$json["search"]:"Search";?>";
var entries_label = "<?= isset($json["entries_label"])?$json["entries_label"]:"Show _MENU_ entries";?>";
var info_label="<?= isset($json["info_label"])?$json["info_label"]:"Showing _START_ to _END_ of _TOTAL_ entries";?>";
previous_label = '<?= isset($json["previous"])?$json["previous"]:"Previous";?>';
next_label = '<?= isset($json["next"])?$json["next"]:"Next";?>';

$("#language").val("<?php echo isset($_SESSION["language"])?$_SESSION["language"]:"English"; ?>");


function hide_language_swtch_for_config()
    {
    var array=  window.location.pathname.split('/');
    array.pop();    
    var module = array.pop();    
    if(module=="config" || module=="users")
        $("#language").hide();
    else
        $("#language").show();
    }
    
function open_active_sidebar_menu()
    {    
    var url=window.location.pathname;     
    if(url.includes("dashboard") && $('a[href*="'+url+'"]').length==0)        
        {        
        url=url.replace("_device","_facility");
        url=url.replace("_carpark","_facility");        
        }
           
    $('a[href*="'+url+'"]').addClass("active");
    
    var id=$('a[href*="'+url+'"]').closest("ul").attr("id");
    if(id!=="sidebar")             
        $('a[href*="'+url+'"]').closest("ul").closest("li").addClass("menu-open");        
        
    }

open_active_sidebar_menu()
hide_language_swtch_for_config()      

$(document).ready(function () 
    {	
        
    $("#language").val("<?php echo isset($_SESSION["language"])?$_SESSION["language"]:"English"; ?>");
    // check date is selected
    $(".applyBtn").click(function () 
        {
        $(this).data('clicked', true);
        });

    // view report button click
    $('#view-report-button').click(function (event) 
        {
        if ($('.applyBtn').data('clicked')) 
            {
            // only display loader if button has been clicked
            $("#loader").css("visibility", "visible");
            }
        });        
    
    if($("#report-content").find('#RecordsTable').length!=0) 
       loadDataTable();    
    });
             
                              
  // button ui changes on successful report load
  function loadReport(data)
      {      
      $("#report-content").html(data);
      $("#loader").css("visibility", "hidden");       
      if($("#report-content").find('table').length!=0)
          {
          $("#action-buttons").css("visibility", "visible");  
          loadDataTable() ;       
          }
      else 
          { 
          $("#action-buttons").css("visibility", "hidden");                
          if($("#export-container").is(":visible")) 
              $("#export-container").addClass("hidden");
          }      
      }


function loadDataTable()
    {      
    $('#RecordsTable').DataTable(
        {
        "paging": true,
        "lengthChange":true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,        
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "aaSorting": [],
        "language": {search: search_label,},
        "oLanguage": 
                {
                "sLengthMenu": entries_label,
                "info":info_label,
                "oPaginate": 
                        {
                        "sPrevious": previous_label,
                        "sNext": next_label
                        }
                },					            
        });   
                
    }
    
    function update_session()
        {
	var session_language = $("#language").val();
	var req = {};
	req["language"]=session_language;
    	var json = JSON.stringify(req);
    	$.post("/parcx/modules/ajax/sessionlanguage.php",json,function(data){ 
            $("#language").val(session_language);            
	}); 
        
        update_sidebar();
        
    }
    
    function update_sidebar()
        {
        var data={}
        data["user_role_id"]= <?=$_SESSION["userRollId"]?>; 
        data["url"]="<?=URL?>"; 
        data["task"]=1; 
        data["lang"]=$("#language").val();
        var json = JSON.stringify(data);
        console.log(json);
        $.post("/parcx/modules/ajax/users.php",json,function(data){ 
            $("#sidebar").html(data);
	});
    
        }
		
</script>

</div>

<!------------------------>
<!-- load plugins -------->
<!------------------------>

<!-- jQuery UI - for draggable divs -->
<script src="<?=URL?>plugins/jquery/jquery-ui.min.js"></script>

<!-- resolve conflict between jQuery UI tooltip with Bootstrap tooltip -->
<script>$.widget.bridge('uibutton', $.ui.button)</script>

<!-- bootstrap 4 -->
<script src="<?=URL?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- daterangepicker -->
<script src="<?=URL?>plugins/jquery/moment.min.js"></script>
<script src="<?=URL?>plugins/daterangepicker/daterangepicker.js"></script>

<!-- theme scripts -->
<script src="<?=URL?>dist/js/parcx.js"></script>

<!-- PDF export -->
<script src="<?=URL?>plugins/jspdf/canvas2image.js"></script>
<script src="<?=URL?>plugins/jspdf/html2canvas.js"></script>
<script src="<?=URL?>plugins/jspdf/jspdf.1.5.3.js"></script>
<script src="<?=URL?>plugins/jspdf/jspdf.autotable.js"></script>
<script src="<?=URL?>plugins/jspdf/Amiri-normal.js"></script>

 <!-- DataTables -->
 <script src="<?=URL?>plugins/datatables/jquery.dataTables.js"></script>
<script src="<?=URL?>plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>


<!-- SlimScroll -->
<script src="<?=URL?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?=URL?>plugins/fastclick/fastclick.js"></script>

<!-- Bootstrap Switch -->
<!--<script src="<?=URL?>plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>-->


<!------------------------>
<!-- load custom scripts-->
<!------------------------>

<!-- chart scripts and global vars -->
<?php include('chart-custom.php'); ?>
<!--validation-->
<script src="<?=URL?>dist/js/jquery.validate.js"></script>

<!-- init daterangepicker -->
<script src="<?=URL?>dist/js/init-daterangepicker.js"></script>

<!-- export scripts -->
<script src="<?=URL?>dist/js/excel-export.js"></script>
<script src="<?=URL?>dist/js/pdf-export.js"></script>

<!-- application scripts --> 
<script src="<?=URL?>dist/js/validationUsers.js"></script>
<!--admin lte -->
<script src="<?=URL?>dist/js/adminlte.js"></script>
</body>

</html>
