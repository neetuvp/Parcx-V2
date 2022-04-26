var id, status;
var report_id = "0", tag;
var ticket_id,customer_name;
$("#alert-div").hide();
$(document).on("click", ".btnWhitelistAdd", function ()
{
    $("#addToWhiteList").modal("toggle");
    $("#form").show();
    $("#form").trigger('reset');
    $("#emirates-div").hide();
    $('#multiselect option').prop('selected', true);
    $('#multiselect').multiselect("refresh");
    report_id = $(this).attr("data-id");
    tag = $(this).attr("data-tag");
    $('#tag').val(tag);
    $('#qr_code').val(tag);
});
$('#country,#emirates,#prefix,#plate_number').on('input', function ()
{
    var plate;
    if ($("#country").val().toUpperCase() === "UAE")
        plate = $("#emirates").val() + " " + $("#prefix").val().toUpperCase() + " " + $("#plate_number").val();
    else
        plate = $("#country").val() + " " + $("#prefix").val().toUpperCase() + " " + $("#plate_number").val();
    $("#display_plate").html(plate);
});

$('#country').on('input', function ()
{
    if ($("#country").val().toUpperCase() === "UAE")
        $("#emirates-div").show();
    else
        $("#emirates-div").hide();
    if ($("#country").val() == "")
    {
        $("#prefix").val("");
        $("#plate_number").val("");
        $("#display_plate").html("");
    }
});

$(document).ready(function ()
{
    $('#multiselect').multiselect(
        {
        buttonWidth: '100%',
        includeSelectAllOption: true,
        selectAllText: "All carparks",
        nonSelectedText: "Select carparks",
        selectAllNumber: false,
        allSelectedText: "All carparks",
        });


    $('#whitelist-active-date').daterangepicker({
        timePicker: false,
        timePickerIncrement: 10,
        format: 'YYYY-MM-DD',
        singleDatePicker: true,
        //maxDate:$("#license-expiry-date").html()
    })

    $('#whitelist-expiry-date').daterangepicker({
        timePicker: false,
        timePickerIncrement: 10,
        format: 'YYYY-MM-DD',
        singleDatePicker: true,
        //maxDate:$("#license-expiry-date").html()
    })

    $(document).on("click", "* [data-target]", function ()
    {
        $("#alert-div").hide();
        var $target = $(this).data('target');
        if ($target == "whitelist-form")
        {
            $("#whitelist-form").trigger('reset');
            $("#display_plate").html("");
            $("#add-edit-button").val("Submit");
            $("#emirates-div").hide();
            report_id = "0";
            $('#multiselect option').prop('selected', true);
            $('#multiselect').multiselect("refresh");
            
            $('#whitelist_customer_name').prop('enabled', true);
            $("#whitelist_customer_name").val(ticket_id);    
            $('#whitelist_customer_name').prop('disabled', true);
        }
        $('.block-data').css('display', 'none');
        $('.block-data[data-status="' + $target + '"]').fadeIn('slow');
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
    });



    //FormSubmit
    var formElement = $("#whitelist-form");
   
    var rules_set = {};

    formElement.validate({
        rules: rules_set,
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.addClass("text-danger");
            error.insertAfter(element);
        },
        submitHandler: function ()
        {
            var data = {};
            if ($("#add-edit-button").val() == "Submit")
                {
                data["id"] = "";
                data["activity_message"]="Add access whitelist ticket id: "+ticket_id;
                }
            else
                {
                data["id"] = id;
                data["activity_message"]="Edit access whitelist ticket id: "+ticket_id;
                }

            data["report_id"] = report_id;            
            data['ticket_id'] = ticket_id;                                    
            data['tag'] = $('#tag').val();
            data['customer_name'] = customer_name;           
            data['start_date'] = $("#whitelist-active-date").val();
            data['expiry_date'] = $("#whitelist-expiry-date").val();

            data["country"] = $("#country").val();
            if (data["country"] != "")
                data["plate_number"] = $("#display_plate").html();
            else
                data["plate_number"] = "";
            if ($("#antipassback").is(":checked"))
                data['anti_passback'] = "1";
            else
                data['anti_passback'] = "0";           

            data["task"] = "3";
            var jsondata = JSON.stringify(data);
            console.log(jsondata);
            var url = "../../modules/ajax/whitelist.php";
            if (report_id != "0")
                url = "../../../modules/ajax/whitelist.php";
            $.post(url, jsondata, function (result) {
                if (result == "Successfull")
                {
                    if (report_id != "0")
                    {
                        $("#addToWhiteList").modal("toggle");
                        if ((!daterange))
                            location.reload();
                        else
                            $("#view-report-button").click();
                    } else
                        location.reload();
                } else
                    alert(result);
            });
        }
    });
    
       //FormSubmit
    var formElement2 = $("#user-form");       
    formElement2.validate({
        rules: rules_set,
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.addClass("text-danger");
            error.insertAfter(element);
        },
        submitHandler: function ()
        {
            var data = {};
            customer_name=$("#customer_name").val();
            if ($("#add-edit-customer-button").val() == "Submit")
                {
                data["ticket_id"] = "";
                data["activity_message"]="Add access whitelist User: "+customer_name;
                }
            else
                {
                data["ticket_id"] = ticket_id;
                data["activity_message"]="Edit access whitelist User ticket id: "+ticket_id;
                }                                                         
            data['customer_name'] = customer_name;                      
            data['company_name'] = $("#company_name").val();;
            data["country"] = $("#customer_country").val();
            data['message1'] = $('#message1').val();
            data['message2'] = $('#message2').val();                   
            data["task"] = "17";
            var jsondata = JSON.stringify(data);
            console.log(jsondata);
            var url = "../../modules/ajax/whitelist.php";
            
            $.post(url, jsondata, function (result) {
                if (result == "Successfull")                                   
                 location.reload();
                else
                    alert(result);
            });
        }
    });

});



function loadExpiredAccessWhitelist()
{
    var data = {};
    data["task"] = "14";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        $("#RecordsTable3").html(result);
    });
}

function loadExpiringAccessWhitelist()
{
    var data = {};
    data["task"] = "13";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        $("#RecordsTable4").html(result);
    });
}


function loadAccessWhitelist()
{
    var data = {};
    data["task"] = "1";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        $("#RecordsTable").html(result);
    });
}

function loadTable(table)
    {
    var data = {};
    if(table=="RecordsTable")
        {
        data["task"] = "1";
        page=table1.page();
        }
    else if(table=="RecordsTable3")
        {
        data["task"] = "14";
        page=table3.page();
        }
    else if(table=="RecordsTable4")
        {
        data["task"] = "13";
        page=table4.page();
        }
    else
        {
        data["ticket_id"] = ticket_id;    
        data["task"] = "19";        
        }
    
    
    var jsondata = JSON.stringify(data);
    
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        console.log("table_name: "+table);
        $("#div-"+table).html("<table  id='"+table+"' class='table table-bordered'>"+result+"</table>");
        if(table=="RecordsTable")
            {
            table1=$('#RecordsTable').DataTable({"lengthMenu": [[25, 50, 75, 100, -1], [25, 50, 75, 100, "All"]],});
            if(page>0)
                {
                console.log("page: "+page);                
                table1.page(page).draw(false);    
                }
            }
        else if(table=="RecordsTable3")
            {
            table3=$('#RecordsTable3').DataTable({"lengthMenu": [[5, 25, 50, -1], [5, 25, 50, "All"]]});
            if(page>0)
                {
                table3.page(page).draw(false);    
                }
            loadTable("RecordsTable");
            loadTable("RecordsTable4");
            }
        else if(table=="RecordsTable4")
            {
            table4=$('#RecordsTable4').DataTable({"lengthMenu": [[5, 25, 50, -1], [5, 25, 50, "All"]]});
            if(page>0)
                {
                table4.page(page).draw(false);    
                }
            loadTable("RecordsTable");
            }
        else
            {
             $("#customer_access_details").html(result);       
            }
    });    
    }
    
/* === enable disable === */
var status;
var id;
$(document).on("click", ".whitelist-enable-disable-btn", function ()
{
    var table_name=$(this).closest("table").attr('id');
    id = $(this).parent('td').parent('tr').data('id');
    //ticket_id=$(this).parent('td').siblings(":eq( 2 )").text();
    
    var status_text = $(this).attr("data-text");
    if (status_text == "Disable")
        status = 0;
    else
        status = 1;

    var data = {};
    data["id"] = id;
    data["status"] = status;
    data["task"] = "2";
    data["activity_message"]=status_text+" access whitelist id: "+id;
    var jsondata = JSON.stringify(data);   
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        if (result == "Successfull")
        {
            loadTable(table_name);
        } else
            alert(result);
    });
});


$(document).on("click", "#customer-add", function ()
{
  $('*[data-target="user-form"]').click();
});

$(document).on("click", ".whitelist-add", function ()
{
    ticket_id = $(this).parent('td').parent('tr').data('id');
    customer_name=$(this).parent('td').siblings(":eq( 0 )").text();
    $('*[data-target="whitelist-form"]').click();
});


$(document).on("click", ".whitelist-customer-edit", function ()
{
    ticket_id = $(this).parent('td').parent('tr').data('id');  
    var data = {};
    data["id"] = ticket_id;
    data["task"] = "18";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        console.log(result)
        var response = JSON.parse(result);      
        $("#customer_name").val(response.customer_name);
        $("#company_name").val(response.company_name);
        $("#customer_country").val(response.country);
        $("#message1").val(response.personalized_message_line1);
        $("#message2").val(response.personalized_message_line2); 
        $("#add-edit-customer-button").val("Edit");
        $('*[data-target="user-form"]').click();        
    });
    
});


$(document).on("click", ".whitelist-customer-view", function ()
{
    ticket_id = $(this).parent('td').parent('tr').data('id');  
    var data = {};
    data["ticket_id"] = ticket_id;
    data["task"] = "19";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
       $("#customer_access_details").html(result);    
       $('#customer-modal').modal('show');
              
    });
    
});

var device_name;
$(document).on("click", ".synch-enable-disable-btn", function ()
{
    id = $(this).parent('td').parent('tr').data('id');
    device_name=$(this).parent('td').siblings(":eq( 1 )").text();
    var status_text = $(this).attr("data-text");
    if (status_text == "Disable")
        status = 0;
    else
        status = 1;

    var data = {};
    data["id"] = id;
    data["status"] = status;
    data["task"] = "7";
    data["activity_message"]=status_text+" synch whitelist device name: "+device_name;
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        if (result == "Successfull")
        {
            loadDevicelist();
        } else
            alert(result);
    });
});

function loadDevicelist()
{
    var data = {};
    data["task"] = "6";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        $("#RecordsTable2").html(result);
    });
}

/*=====edit======*/
$(document).on("click", ".whitelist-edit", function ()
{
    id = $(this).parent('td').parent('tr').data('id');
    ticket_id=$(this).parent('td').siblings(":eq( 2 )").text();
    var data = {};
    data["id"] = id;
    data["task"] = "4";
    var jsondata = JSON.stringify(data);
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        console.log(result);
        var response = JSON.parse(result);
        $('#customer-modal').modal('hide');
        $('*[data-target="whitelist-form"]').click();
        var plate = response.plate_number;
        $("#display_plate").html(plate);
        $("#emirates-div").hide();
        if (plate != "")
        {
            var array_plate = plate.split(" ");
            var country = array_plate[0];
            var prefix = array_plate[1];
            var number = array_plate[2];
            $("#prefix").val(prefix);
            $("#plate_number").val(number);
            if ($("#country option[value='" + country + "']").length != 0)
                $("#country").val(country);
            else
            {
                $("#country").val("UAE");
                $("#emirates").val(country);
                $("#emirates-div").show();
            }
        } else
        {
            $("#prefix").val("");
            $("#plate_number").val("");
            $("#country").val("");
        }
        
        
        $('#whitelist_customer_name').prop('enabled', true);
        $("#whitelist_customer_name").val(response.ticket_id);    
        $('#whitelist_customer_name').prop('disabled', true);
        $("#tag").val(response.tag);                
        $("#whitelist-active-date").val(response.validity_start_date);
        $("#whitelist-expiry-date").val(response.validity_expiry_date);                                        
        if (response.antipassback_enabled == 1)
            $("#antipassback").attr("checked", true);
        else
            $("#antipassback").attr("checked", false);
                
        $("#add-edit-button").val("Edit");
        
        
    });
});

$(document).on("click", "#btnSynchWhitelist", function () {
    $('#whitelist-modal').modal('show');
    var data = {};
    data["task"] = "5";
    data["activity_message"]="Synch whitelist to devices";
    var jsondata = JSON.stringify(data);    
    $.post("../../modules/ajax/whitelist.php", jsondata, function (result) {
        $("#whitelist-modal").modal('hide');
        alert(result);
    });
});

