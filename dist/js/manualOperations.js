var task, device_ip, device_number, carpark_number, device_name, description, description_text, movement_type, device_type, controller_task;
var operation_mode = -1;
var reason, reason_text;
var value;
var result_message;


    
$(document).on("click", ".btn-open-barrier, .btn-close-barrier, .btn-restart-machine, .btn-barrier-status, .btn-close-lane, .btn-open-lane, .btn-free-passage,.btn-standard-operation", function () {
    value = $(this).attr('data-value');
    var operation = $(this).attr('value');
    device_number = $(this).attr('id');    
    description_text = $(this).attr('data-description');
    reason_text = $(this).attr('data-reason');    
    $("#message-modal-heading").html(operation);

    getManualOperationDetails();
                
    $('#detailModal').modal('show');
});

function getManualOperationDetails()
    {
    $('#reasonempty').html("");    
    operation_mode = -1;    
    device_name = $("#device_details_" + device_number).attr('device_name');
    carpark_number = $("#device_details_" + device_number).attr('carpark_number');
    device_ip = $("#device_details_" + device_number).attr('device_ip');
    device_type = $("#device_details_" + device_number).attr('device_type');
    switch (value)
    {
        case "Open Barrier":
            task = "S01";
            controller_task = 1;
            movement_type = 3;
            description = "Barrier Open From the Server"
            reason = "Open barrier for " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" barrier opened from the server successfully!";
            break;
        case "Close Barrier":
            task = "S02";
            controller_task = 2;
            movement_type = 4;
            description = "Barrier Close From the Server"
            reason = "Close barrier for " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" barrier closed from the server successfully!";
            break;
        case "Open Barrier1":
            controller_task = 1;
            movement_type = 3;
            description = "Barrier Open1 From the Server";
            reason_text = reason_text + " " + device_name;
            break;
        case "Close Barrier1":
            controller_task = 2;
            movement_type = 4;
            description = "Barrier Close1 From the Server";
            reason_text = reason_text + " " + device_name;            
            break;
        case "Open Barrier2":
            controller_task = 3;
            movement_type = 3;
            description = "Barrier Open2 From the Server";
            reason_text = reason_text + " " + device_name;
            break;
        case "Close Barrier2":
            controller_task = 4;
            movement_type = 4;
            description = "Barrier Close2 From the Server";
            reason_text = reason_text + " " + device_name;
            break;

        case "Restart":
            controller_task = 3;
            task = "S03";
            movement_type = 0;
            description = "Restart Machine From the Server";
            reason = "Restart " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" restart terminal from the server successfully!";
            break;


        case "Lane Closed Mode":
            controller_task = 5;
            task = "S05";
            movement_type = 0;
            description = "Lane Closed Mode From the Server";
            operation_mode = 2;
            reason = "Change operation mode to Lane closed for " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" Changed operation mode to Lane closed mode successfully!";
            break;


        case "Free Passage Mode":
            controller_task = 5;
            task = "S04";
            movement_type = 0;
            description = "Free Passage Mode From the Server";
            operation_mode = 1;
            reason = "Change operation mode to Free Passage for " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" Changed operation mode to free passage mode successfully!";
            break;

        case "Standard Operation Mode":
            controller_task = 5;
            task = "S06";
            movement_type = 0;
            description = "Standard Operation Mode From the Server";
            operation_mode = 0;
            reason = "Change operation mode to Standard operation mode for " + device_name;
            reason_text = reason_text + " " + device_name;
            result_message=device_name+" Changed operation mode to standard operation mode successfully!";
            break;
    } 
    
    $("#reason_heading").html(reason_text);
    }


$(function ()
{
    //modal cancel	
    $(document).on('click', '#cancel_reason', function () {
        $('#reason_text').val("");
        $('#reasonempty').html("");
        $('#detailModal').modal('hide');
    });
    //modal ok
    $(document).on('click', '#ok_reason', function ()
    {       
        var reason = $('#reason_text').val();
        
        if (reason != "")
        {
            $("#manual_result").html("");
            $("#loader").css("visibility", "visible");
            $("#reason_form").hide();
            $('#detailModal').modal('hide');
            var operator = $("#username2").text();
            var data = {
                device_number: device_number,
                device_ip: device_ip,
                device_name: device_name,
                description: description,
                carpark_number: carpark_number,
                task: task,
                operator: operator,
                reason: reason,
                device_type: device_type,
                movement_type: movement_type,
                operation_mode: operation_mode};
            var jsontemp = JSON.stringify(data);

            

            if (device_type == 6 || device_type == 7)
            {
                var url = "http://" + device_ip + "/services/ParkingLaneManagement.php";
                var data = {task: controller_task};
                var temp = JSON.stringify(data);
                $.post(url, temp)
                        .done(function (result)
                        {
                            
                            if (result == 1)
                            {
                                $.post("../ajax/operations.php?task=1", jsontemp)
                                        .done(function (result) {
                                            if (result == 0)
                                                showMessage("Insert to Db failed");
                                            else
                                                showMessage(device_name + " " + description + " Success");
                                        }, "json");
                            } else
                                showMessage(device_name + " " + description + " Failed");
                        }, "json")
                        .fail(function () {
                            showMessage("Not reachable");
                        });
            } else
            {
                $.post("../ajax/operations.php?task=1", jsontemp)
                        .done(function (result) {  
                           if(result.includes("Success"))
                           
                            if(result.includes("Success"))
                                showMessage(result_message);
                            else
                                showMessage("Unable to execute the requested operation !");

                        }, "json");
            }
            $('#reason_text').val("");
            $('#reasonempty').html("");
        } else
        {           
            $("#reasonempty").html(valid_reason_message);
        }
    });
    
    
    function showMessage(msg)
        {
        if (typeof $("#manual_result").html() == 'undefined')  
            alertMessage(msg);    
        else
            {
            $("#manual_reason").addClass("hidden");    
            $("#manual_result").html(msg);
            $(".btn-group label").removeClass("active");
            }
        $("#loader").css("visibility", "hidden");
        }

    $("#language").change(function ()
    {
        loadReportLabels();
        loadManualOperationReport();
        loadOperationList();

    });
    function loadManualOperationReport()
    {
        var data = {};
        data["carpark"] = "";
        data["operation"] = "";
        data["language"] = $("#language").val();
        data["task"] = 11;
        data["limit"] = 10;
        var json = JSON.stringify(data);       
        $.post("../ajax/reports.php", json, function (data)
        {
            $("#latestmanualreport").html(data);
        });

    }
    function loadOperationList()
    {
        var data = {};
        data["language"] = $("#language").val();
        var json = JSON.stringify(data);        
        $.post("../ajax/operations.php?task=2", json, function (data)
        {
            $("#page-content").html(data);
        });

    }
    function loadReportLabels()
    {

        var data = {};
        data["task"] = 29;
        data["language"] = $("#language").val();
        data["page"] = 26;
        var json = JSON.stringify(data);        
        $.post("../ajax/reports.php", json, function (data)
        {            
            var json = JSON.parse(data);
            $("#pdf-report-header").html(json.manual_operation);
            $("#device_name_label").html(json.device_name);
            $("#device_ip_label").html(json.device_ip);
            $("#pdf-report-header").html(json.manual_operation);
            $("#logout").html(json.logout);
            $("#modal_title").html(json.manual_operation);
            $("#reason_label").html(json.reason);
            $("#ok_reason").html(json.ok);
            $("#ok_sidebar_modal").html(json.ok);
            $("#cancel_reason").html(json.cancel);
            $("#latest_manual_operations").html(json.latest_manual_operations);
            valid_reason_message = json.enter_valid_reason;
        });
    }
});