/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function get_gauge_names()
   {
       $.get( "../ajax/dashboard.php?task=3", function( data ) {  
          var carparks=JSON.parse(data);    
       
        $('#gauge1_name').text(carparks[0]['carpark_name']);
        $('#gauge2_name').text(carparks[1]['carpark_name']);
        if(typeof(carparks[2])!="undefined")       
            $('#gauge3_name').text(carparks[2]['carpark_name']);
        if(typeof(carparks[3])!="undefined")        
            $('#gauge4_name').text(carparks[3]['carpark_name']);  
       
        });

    //    e.preventDefault();       
       
   } /// End Function 

function UpdateFacilityCounters()
   {  
      //UPDATE GAUGE COUNTERS          
    $.get( "../ajax/dashboard.php?task=21", function( data ) 
      { 
      var counts=JSON.parse(data);        
       var i=0;
       var j=0; 
       var gauge_value;
       var percentage=0;
      
       while(i <counts.length)
          {             
          j=i+1;
          if(i==0)
              {
              $('#last-updated').html(counts[i]['last_updated_datetime']);       
              }
          $("#carpark"+j).trigger( 'configure', { "fgColor": counts[i]['color']} ); //change color according to value
          $("#carpark"+j).val(counts[i]['gauge_value']).trigger('change');   
                 
          if(counts[i]['gauge_value']==100)
          {
              $("#gauge-value"+j).text("FULL");
          }
          else
          {
          $("#gauge-value"+j).text(counts[i]['current_level']+'/'+counts[i]['total_spaces']);
          } 
        i++;
     } 
     });
  } // End. Dasboard Update Facility Counters
  

   
   function UpdateCarparkCounters(facility_number)
   { 
    $.get( "../ajax/dashboard.php?task=1&facility_number="+facility_number, function( data ) {
       var counts=JSON.parse(data);         
       var i=0;
       var j=0; 
       var gauge_value;
       var percentage=0;
       while(i <counts.length)
       {
        if(i==0)
          {
          $('#last-updated').html(counts[i]['last_updated_datetime']);       
          }

          j=i+1;
          gauge_value=(counts[i]['gauge']/counts[i]['gauge_max'])*100;
          $("#carpark"+j).trigger( 'configure', { "fgColor": counts[i]['color']} ); //change color according to value
          $("#carpark"+j).val(gauge_value).trigger('change');   
         
          if(gauge_value==100)
          {
              $("#gauge-value"+j).text("FULL");
          }
          else
          {
        $("#gauge-value"+j).text(counts[i]['gauge']+'/'+counts[i]['gauge_max']);
        }        
       
    //Short Term - Occupancy 
   
    percentage=Math.ceil((counts[i]['shortterm_current_level']/counts[i]['total_shortterm_spaces'])*100);  
  // alert(percentage);
    $("#shortterm-progress-"+j).css('width', percentage+'%');  
    $("#shortterm-progress-"+j).attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#shortterm-count-"+j).text(counts[i]['shortterm_current_level']+'/'+counts[i]['total_shortterm_spaces']);  
   
    //Access- Occupancy 
   
    percentage=Math.ceil((counts[i]['access_current_level']/counts[i]['total_access_spaces'])*100); 
    $("#access-progress-"+j).css('width', percentage+'%'); 
    $("#access-progress-"+j).attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#access-count-"+j).text(counts[i]['access_current_level']+'/'+counts[i]['total_access_spaces']); 
    
    //Reservation - Occupancy    
    percentage=Math.ceil((counts[i]['reservation_current_level']/counts[i]['total_reservation_spaces'])*100);    
    $("#reservation-progress-"+j).css('width', percentage+'%');       
    $("#reservation-progress-"+j).attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#reservation-count-"+j).text(counts[i]['reservation_current_level']+'/'+counts[i]['total_reservation_spaces']); 
    
    i++;    
    }
    });
     
   }// End . Function Update Gauge Counters 
   
   
     function UpdateCarparkDetail(facility_number,carpark_number)
     {
    var percentage=0;       
     $.get( "../ajax/dashboard.php?task=19&carpark_number="+carpark_number+"&facility_number="+facility_number, function( data ) {  
         
     var carpark_data=JSON.parse(data);  
   
    $('#last-updated').html(carpark_data.last_updated_datetime);       
     percentage=Math.ceil((carpark_data.current_level/carpark_data.total_spaces)*100);
     $("#carpark").val(percentage).trigger('change');   
     $("#carpark").trigger( 'configure', { "fgColor":carpark_data.gauge_color} ); //change color according to value
     $("#gauge-value").text(carpark_data.current_level+'/'+carpark_data.total_spaces); 
  
         //Short Term - Occupancy 
    percentage=Math.ceil((carpark_data.shortterm_current_level/carpark_data.shortterm_total_spaces)*100);    
    $("#progress-shortterm").css('width', percentage+'%');         
    $("#progress-shortterm").attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#badge-shortterm").attr('class','badge bg-'+occupancy_choose_bg(percentage));
    $("#badge-shortterm").text(carpark_data.shortterm_current_level+'/'+carpark_data.shortterm_total_spaces);
    
    //ShortTermin Entry / Exit Counts
    $("#shortterm-entry").text(carpark_data.shortterm_entries);
    $("#shortterm-exit").text(carpark_data.shortterm_exits);
     
      //Access - Occupancy 
    percentage=Math.ceil((carpark_data.access_current_level/carpark_data.access_total_spaces)*100);
    $("#progress-access").css('width', percentage+'%');         
    $("#progress-access").attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#badge-access").attr('class','badge bg-'+occupancy_choose_bg(percentage));
    $("#badge-access").text(carpark_data.access_current_level+"/"+carpark_data.access_total_spaces);
    //Access / Exit Counts
    $("#access-entry").text(carpark_data.access_entries);
    $("#access-exit").text(carpark_data.access_exits);
     
    //Reservation - Occupancy 
    percentage=Math.ceil((carpark_data.reservation_current_level/carpark_data.reservation_total_spaces)*100);
    $("#progress-reservation").css('width', percentage+'%');         
    $("#progress-reservation").attr('class','progress-bar bg-'+occupancy_choose_bg(percentage));
    $("#badge-reservation").attr('class','badge bg-'+occupancy_choose_bg(percentage));
    $("#badge-reservation").text(carpark_data.reservation_current_level+"/"+carpark_data.reservation_total_spaces);
    //Reservation / Entry Exit Counts
    $("#reservation-entry").text(carpark_data.reservation_entries);
    $("#reservation-exit").text(carpark_data.reservation_exits);
     
     
     //Manual / Entry Exit Counts
    $("#manual-entry").text(carpark_data.manual_entries);
    $("#manual-exit").text(carpark_data.manual_exits);
     
     });  
         
         
     } // End Function . Get Dashboard Update Car Park Details 
   
   function occupancy_choose_bg(current_level)
   {
       if(current_level <=50)
    {
        bg="success";
    }
    else if((current_level >50) && (current_level <=75))
    {
         bg="warning";
    }
    else if(current_level > 75)
    {
         bg="danger";       
    }
    
    return bg;
       
       
   } // End Choose bg color base don occupancy 
   
    function current_ime()
    {
        $('#current_date').html(new Date($.now()));
    }

 function UpdateBreadcrumbNavigation(type,value)
 {
     //Type 1 = facility , 2= carpark , 3= facility
     var data='';     
     data= '<li class="breadcrumb-item"><a href="#">Dashboards</a></li>';
     data+= ' <li class="breadcrumb-item"><a href="occupancy.php">Occupancy</a></li>';
     if(type==2)
     {
       data+= ' <li class="breadcrumb-item"><a href="#">Carparks</a></li>'; 
     }
     if(type==3)
     {
         
     }
     $("#BreadcrumbNavigation").html(data);
      
     
 } // End .Occupancy Update BreadcrumpNavigation 
	

