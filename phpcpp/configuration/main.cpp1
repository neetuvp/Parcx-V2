
#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include <cppconn/resultset_metadata.h>
#include<phpcpp.h>
#include <sstream>
#include "validation.h"
//#include "MQTTClient.h"

#define ServerDB "v2_parcx_server"
#define ReportingDB "v2_parcx_reporting"
#define DashboardDB "v2_parcx_dashboard"
#define dateFormat "%Y-%m-%d"

using namespace std; 
GeneralOperations General;
Validation validation;
string validation_failed = "Validation Failed\n";
sql::Connection *con, *dcon;
sql::Statement *stmt;
sql::ResultSet *res, *res2;
sql::PreparedStatement *prep_stmt;
string query;

 void writeLog(string function, string message) {
    General.writeLog("Services/ApplicationLogs/PX-iot-terminal-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeException(string function, string message) {
    General.writeLog("Services/ExceptionLogs/PX-iot-terminal-error-" + General.currentDateTime("%Y-%m-%d"), function, message);
    //Php::out << message << endl;
    writeLog(function, "Exception: " + message);
}

string ToString(Php::Value param) {
    string value = param;
    return value;
}


void logUserActivity(string category, string description, int user_id, string user_name, string status, string ip, string request, string response) {
    try {
        sql::Connection *con;
        sql::PreparedStatement *pstmt;
        con = General.mysqlConnect(ReportingDB);
        pstmt = con->prepareStatement("insert into system_journal(category,description,user_id,user_name,status,ip_address,request,response)values(?,?,?,?,?,?,?,?)");
        pstmt->setString(1, category);
        pstmt->setString(2, description);
        pstmt->setInt(3, user_id);
        pstmt->setString(4, user_name);
        pstmt->setString(5, status);
        pstmt->setString(6, ip);
        pstmt->setString(7, request);
        pstmt->setString(8, response);
        pstmt->executeUpdate();
        delete pstmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("logUserActivity", e.what());
    }
}


string arrayToString(Php::Value json) {

    stringstream ss;
    ss << "{";
    for (auto &iter : json)
        ss << "\"" << iter.first << "\":\"" << iter.second << "\",";
    string response = ss.str();
    response = response.substr(0, response.size() - 1) + "}";
    return response;
}

void carparkDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_carparks");
        while (res->next()) {
            Php::out << "<option value='" << res->getString("carpark_number") << "'>" << res->getString("carpark_name") << "</option>" << std::endl;
        }

        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("carparkDropdown", e.what());
    }
}

void facilityDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select setting_value from system_settings where setting_name in('facility_name','facility_number') order by setting_name desc");
        if (res->next())
            Php::out << "<option value='" << res->getString("setting_value") << "'>";
        if (res->next())
            Php::out << res->getString("setting_value") << "</option>" << std::endl;
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("facilityDropdown", e.what());
    }
}

void deviceDropdown(Php::Value data) {
    string msg = "Failed";
    string type=data["type"];
    Php::Value validation_response;
    try {    
        validation_response = validation.DataValidation(type,0,11,1,1);
        msg = validation_failed;
        if(validation_response["result"]==false)
        {
            msg = msg+"Invalid Device Type:"+ ToString(validation_response["reason"]);    
            logUserActivity("deviceDropdown", "Device Dropdown list, Result: " + msg, data["login_user_id"], ToString(data["login_user_name"]), "Failed", ToString(data["request_ip"]), arrayToString(data), msg); 
        }
        else
        {
            con = General.mysqlConnect(ServerDB);
            stmt = con->createStatement();
            if(type!="")
                res = stmt->executeQuery("select * from system_devices where device_category in (" + type + ") order by device_number");
            else
                res = stmt->executeQuery("select * from system_devices order by device_number");
            while (res->next()) {
                Php::out << "<option value='" << res->getString("device_number") << "'>" << res->getString("device_name") << "</option>" << std::endl;
            }

            delete res;
            delete stmt;
            delete con;
        }
    } catch (const std::exception& e) {
        writeException("deviceDropdown", e.what());
    }
}


void showDeviceList(Php::Value data) {
    int edit_access = data["edit"];
    int delete_access = data["delete"];
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_devices");
        if (res->rowsCount() > 0) {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;            
            Php::out << "<th>Device Name</th>" << endl;
            Php::out << "<th>Device Number</th>" << endl;
            Php::out << "<th>Device IP</th>" << endl;
            Php::out << "<th>Device Category</th>" << endl;
            Php::out << "<th>Carpark</th>" << endl;
            //Php::out << "<th>Facility</th>" << endl;
            if(delete_access==1 || edit_access==1)
            {
                Php::out << "<th></th>" << endl;
            }
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;            
            Php::out << "<td>" + res->getString("device_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("device_number") + "</td>" << endl;            
            Php::out << "<td>" + res->getString("device_ip") + "</td>" << endl;
            Php::out << "<td>" + res->getString("device_category_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("carpark_name") + "</td>" << endl;
            //Php::out << "<td>" + res->getString("facility_name") + "</td>" << endl;
            if(delete_access==1 || edit_access==1)
            {
                Php::out << "<td>" << std::endl;
                if(delete_access==1)
                {
                    if (res->getInt("device_enabled") == 1)
                        Php::out << "<button type='button' class='btn btn-danger device-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                    else
                        Php::out << "<button type='button' class='btn btn-success device-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
                }
                if(edit_access==1)
                    Php::out << "<button type='button' class='btn btn-info device-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
                Php::out << "</td>" << std::endl;
            }
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showDeviceList", e.what());
    }

}


Php::Value insertUpdateDevice(Php::Value json) {
    string msg = "Failed";
    try {
        string id = json["id"];
        int carpark_number = json["carpark_number"];
        int facility_number = json["facility_number"];
        int device_number = json["device_number"];
        int device_type = json["device_category"];
        string facility_name = json["facility_name"];
        string carpark_name = json["carpark_name"];
        string device_name = json["device_name"];
        string device_ip = json["device_ip"];
        int camera_id = json["camera_id"];
        int camera_index = json["camera_index"];

//DataValidation(string data,int minlen,int maxlen,int datatype,int mandatoryflag)
        //Validation
        Php::Value validation_response = validation.DataValidation(to_string(carpark_number),1,11,1,1);
        if(validation_response["result"]==false)
        {
            msg = "Carpark Number : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(to_string(facility_number),1,11,1,1);
        if(validation_response["result"]==false)
        {
            msg = "Facility Number : "+ ToString(validation_response["reason"]);
            return msg;
        }
        
        if(device_number==0)
        {
             msg = "Please Enter Device Number";
            return msg;
        }

        validation_response = validation.DataValidation(to_string(device_number),1,11,1,1);
        if(validation_response["result"]==false)
        {
            msg = "Device Number : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(facility_name,1,50,3,1);
        if(validation_response["result"]==false)
        {
            msg = "Facility Name : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(carpark_name,1,50,3,1);
        if(validation_response["result"]==false)
        {
            msg = "Carpark Name : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(device_name,1,50,3,1);
        if(validation_response["result"]==false)
        {
            msg = "Device Name : "+ ToString(validation_response["reason"]);
            return msg;
        }

        if(device_ip!="localhost")
        {
            validation_response = validation.DataValidation(device_ip,1,50,9,1);
            if(validation_response["result"]==false)
            {
                msg = "Device Ip : "+ ToString(validation_response["reason"]);
                return msg;
            }
        }

        validation_response = validation.DataValidation(ToString(json["device_category_name"]),1,50,3,0);
        if(validation_response["result"]==false)
        {
            msg = "Device Category : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["device_category_name"]),1,50,3,0);
        if(validation_response["result"]==false)
        {
            msg = "Device Category : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["device_function"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Device Function : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["network_interface"]),1,11,3,1);
        if(validation_response["result"]==false)
        {
            msg = "Network Interface : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["backout_blacklist_delay"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Backout Blacklist : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["server_handshake_interval"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Server Handshake : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["screensaver_timeout"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Screensaver Timeout : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["backout_blacklist_delay"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Backout Blacklist Display : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["output_pulse_length"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Output Pulse Width : "+ ToString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(ToString(json["mobile_qrcode_time_limit"]),1,11,1,0);
        if(validation_response["result"]==false)
        {
            msg = "Mobile QR Code Validity Limit : "+ ToString(validation_response["reason"]);
            return msg;
        }
 

        con = General.mysqlConnect(ServerDB);
        if (id == "")
            prep_stmt = con->prepareStatement("select * from system_devices where carpark_number=? and device_number=?");
        else {
            prep_stmt = con->prepareStatement("select * from system_devices where carpark_number=? and device_number=? and id!=?");
            prep_stmt->setString(3, id);
        }

        prep_stmt->setInt(1, carpark_number);
        prep_stmt->setInt(2, device_number);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            msg = "Device number already exist in carpark.Try with another device number";
            delete prep_stmt;
            delete res;
        } else {

            prep_stmt = con->prepareStatement("select * from system_carparks where carpark_number=?");
            prep_stmt->setInt(1, carpark_number);
            res = prep_stmt->executeQuery();
            res->next();

            if (id == "") {
                prep_stmt = con->prepareStatement("insert into system_devices(facility_number,facility_name,carpark_number,carpark_name,device_number,device_name,device_category,device_category_name,device_ip,camera_id,customer_receipt_mandatory,shift_receipt_mandatory,shift_physical_cash_count_required,synch_whitelist_flag,issue_lost,camera_index,anpr_enabled, wiegand_enabled, access_enabled, reservation_enabled, review_mode, device_function, barrier_open_time_limit,backout_blacklist_delay, display_anpr_image, barrier_open_status_type, bms_status_enabled, barrier_status_monitoring, wiegand2_enabled, server_handshake_interval, plate_capturing_wait_delay, quick_barrier_close, payment_enabled_exit,rate_plan,reservation_rate_plan,rate_type,anpr_image_path,cropped_picture_required,anpr_com_port,anpr_mismatch_check,plate_captured_interval,allow_manual_open_close,activate_product_sale,activate_subscription,print_entry_ticket,lost_including_parkingfee,float_amount_required,full_max_exit_grace_enabled,receipt_printer_type,entry_printer_type,entry_printer_port_name,receipt_printer_port_name,print_operator_logo,receipt_primary_language,receipt_secondary_language,barrier_open_command,barrier_close_command,barrier_communication,transaction_time_out,rfid_port,central_cashier,controller_ip,enable_port_communication,barrier_port_ip,barrier_port_number,coupon_enabled,short_term_ticket_enabled,validation_enabled,mode_of_operation,wiegand_whitelist_check_enabled,wiegand_whitelist_parking_zone_check_enabled,wiegand_whitelist_expiry_check_enabled,wiegand_whitelist_carpark_check_enabled,wiegand_whitelist_facility_check_enabled,send_wiegand_response_to_port,screensaver_timeout,upload_to_server_delay,mobile_qrcode_time_limit,network_interface,print_client_logo,media_path,output_pulse_length,user_id,device_enabled) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
                prep_stmt->setString(83, ToString(json["user_id"]));
            } else {
                prep_stmt = con->prepareStatement("update system_devices set facility_number=?,facility_name=?,carpark_number=?,carpark_name=?,device_number=?,device_name=?,device_category=?,device_category_name=?,device_ip=?,camera_id=?,customer_receipt_mandatory=?,shift_receipt_mandatory=?,shift_physical_cash_count_required=?,synch_whitelist_flag=?,issue_lost=?,camera_index=?,anpr_enabled=?, wiegand_enabled=?, access_enabled=?, reservation_enabled=?, review_mode=?, device_function=?, barrier_open_time_limit=?,backout_blacklist_delay=?, display_anpr_image=?, barrier_open_status_type=?, bms_status_enabled=?, barrier_status_monitoring=?, wiegand2_enabled=?, server_handshake_interval=?, plate_capturing_wait_delay=?,quick_barrier_close=?, payment_enabled_exit=?,rate_plan=?,reservation_rate_plan=?,rate_type=?,anpr_image_path=?,cropped_picture_required=?,anpr_com_port=?,anpr_mismatch_check=?,plate_captured_interval=?,allow_manual_open_close=?,activate_product_sale=?,activate_subscription=?,print_entry_ticket=?,lost_including_parkingfee=?,float_amount_required=?,full_max_exit_grace_enabled=?,receipt_printer_type=?,entry_printer_type=?,entry_printer_port_name=?,receipt_printer_port_name=?,print_operator_logo=?,receipt_primary_language=?,receipt_secondary_language=?,barrier_open_command=?,barrier_close_command=?,barrier_communication=?,transaction_time_out=?,rfid_port=?,central_cashier=?,controller_ip=?,enable_port_communication=?,barrier_port_ip=?,barrier_port_number=?,coupon_enabled=?,short_term_ticket_enabled=?,validation_enabled=?,mode_of_operation=?,wiegand_whitelist_check_enabled=?,wiegand_whitelist_parking_zone_check_enabled=?,wiegand_whitelist_expiry_check_enabled=?,wiegand_whitelist_carpark_check_enabled=?,wiegand_whitelist_facility_check_enabled=?,send_wiegand_response_to_port=?,screensaver_timeout=?,upload_to_server_delay=?,mobile_qrcode_time_limit=?,network_interface=?,print_client_logo=?,media_path=?,output_pulse_length=? where id=?");
                prep_stmt->setString(83, id);
            }

            prep_stmt->setInt(1, facility_number);
            prep_stmt->setString(2, facility_name);
            prep_stmt->setInt(3, carpark_number);
            prep_stmt->setString(4, carpark_name);
            prep_stmt->setInt(5, device_number);
            prep_stmt->setString(6, device_name);
            prep_stmt->setInt(7, device_type);
            prep_stmt->setString(8, ToString(json["device_category_name"]));
            prep_stmt->setString(9, device_ip);
            prep_stmt->setInt(10, camera_id);
            prep_stmt->setInt(11, json["customer_receipt_mandatory"]);
            prep_stmt->setInt(12, json["shift_receipt_mandatory"]);
            prep_stmt->setInt(13, json["physical_cash_count"]);
            prep_stmt->setInt(14, json["synch_whitelist"]);
            prep_stmt->setInt(15, json["issue_lost"]);
            prep_stmt->setInt(16, camera_index);
            prep_stmt->setInt(17, json["anpr_enabled"]);
            prep_stmt->setInt(18, json["wiegand_enabled"]);
            prep_stmt->setInt(19, json["access_enabled"]);
            prep_stmt->setInt(20, json["reservation_enabled"]);
            prep_stmt->setInt(21, json["review_mode"]);
            prep_stmt->setInt(22, json["device_function"]);
            prep_stmt->setInt(23, json["barrier_open_time_limit"]);
            prep_stmt->setInt(24, json["backout_blacklist_delay"]);
            prep_stmt->setInt(25, json["display_anpr_image"]);
            prep_stmt->setInt(26, json["barrier_open_status_type"]);
            prep_stmt->setInt(27, json["bms_status_enabled"]);
            prep_stmt->setInt(28, json["barrier_status_monitoring"]);
            prep_stmt->setInt(29, json["wiegand2_enabled"]);
            prep_stmt->setInt(30, json["server_handshake_interval"]);
            prep_stmt->setInt(31, json["plate_capturing_wait_delay"]);
            prep_stmt->setInt(32, json["quick_barrier_close"]);
            prep_stmt->setInt(33, json["payment_enabled_exit"]);
            prep_stmt->setString(34, res->getString("rate_plan"));
            prep_stmt->setString(35, res->getString("reservation_rate_plan"));
            prep_stmt->setInt(36, res->getInt("rate_type"));

            prep_stmt->setString(37, ToString(json["anpr_image_path"]));
            prep_stmt->setInt(38, json["cropped_picture_required"]);
            prep_stmt->setInt(39, json["anpr_com_port"]);
            prep_stmt->setInt(40, json["anpr_mismatch_check"]);
            prep_stmt->setInt(41, json["plate_captured_interval"]);
            prep_stmt->setInt(42, json["allow_manual_open_close"]);
            prep_stmt->setInt(43, json["activate_product_sale"]);
            prep_stmt->setInt(44, json["activate_subscription"]);
            prep_stmt->setInt(45, json["print_entry_ticket"]);
            prep_stmt->setInt(46, json["lost_including_parkingfee"]);
            prep_stmt->setInt(47, json["float_amount_required"]);
            prep_stmt->setInt(48, json["full_max_exit_grace_enabled"]);
            prep_stmt->setInt(49, json["receipt_printer_type"]);
            prep_stmt->setInt(50, json["entry_printer_type"]);
            prep_stmt->setString(51, ToString(json["entry_printer_port_name"]));
            prep_stmt->setString(52, ToString(json["receipt_printer_port_name"]));
            prep_stmt->setInt(53, json["print_operator_logo"]);
            prep_stmt->setString(54, ToString(json["receipt_primary_language"]));
            prep_stmt->setString(55, ToString(json["receipt_secondary_language"]));
            prep_stmt->setString(56, ToString(json["barrier_open_command"]));
            prep_stmt->setString(57, ToString(json["barrier_close_command"]));
            prep_stmt->setString(58, ToString(json["barrier_communication"]));
            prep_stmt->setInt(59, json["transaction_time_out"]);
            prep_stmt->setString(60, ToString(json["rfid_port"]));
            prep_stmt->setInt(61, json["central_cashier"]);
            prep_stmt->setString(62, ToString(json["controller_ip"]));
            prep_stmt->setInt(63, json["enable_port_communication"]);
            prep_stmt->setString(64, ToString(json["barrier_port_ip"]));
            prep_stmt->setInt(65, json["barrier_port_number"]);
            prep_stmt->setInt(66, json["coupon_enabled"]);
            prep_stmt->setInt(67, json["short_term_ticket_enabled"]);
            prep_stmt->setInt(68, json["validation_enabled"]);
            prep_stmt->setInt(69, json["mode_of_operation"]);
            prep_stmt->setInt(70, json["wiegand_whitelist_check_enabled"]);
            prep_stmt->setInt(71, json["wiegand_whitelist_parking_zone_check_enabled"]);
            prep_stmt->setInt(72, json["wiegand_whitelist_expiry_check_enabled"]);
            prep_stmt->setInt(73, json["wiegand_whitelist_carpark_check_enabled"]);
            prep_stmt->setInt(74, json["wiegand_whitelist_facility_check_enabled"]);
            prep_stmt->setInt(75, json["send_wiegand_response_to_port"]);
            prep_stmt->setInt(76, json["screensaver_timeout"]);
            prep_stmt->setInt(77, json["upload_to_server_delay"]);
            prep_stmt->setInt(78, json["mobile_qrcode_time_limit"]);
            prep_stmt->setString(79, ToString(json["network_interface"]));
            prep_stmt->setInt(80, json["print_client_logo"]);
            prep_stmt->setString(81, ToString(json["media_path"]));
            prep_stmt->setInt(82, json["output_pulse_length"]);

            delete res;
            if (!prep_stmt->execute()) {
                msg = "Successfull";

                string previous_carpark = json["previous_carpark"];
                string previous_device = json["previous_device"];

                dcon = General.mysqlConnect(DashboardDB);
                prep_stmt = dcon->prepareStatement("select id from watchdog_device_status where carpark_number=" + previous_carpark + " and device_number=" + previous_device);
                res = prep_stmt->executeQuery();
                delete prep_stmt;
                if (res->next()) {
                    id = res->getString("id");
                    prep_stmt = dcon->prepareStatement("update watchdog_device_status set facility_number=?,facility_name=?,carpark_number=?,carpark_name=?,device_number=?,device_name=?,device_ip=?,device_type=? where id=?");
                    prep_stmt->setString(9, id);
                } else {
                    prep_stmt = dcon->prepareStatement("insert into watchdog_device_status(facility_number,facility_name,carpark_number,carpark_name,device_number,device_name,device_ip,device_type)values(?,?,?,?,?,?,?,?)");
                }
                prep_stmt->setInt(1, facility_number);
                prep_stmt->setString(2, facility_name);
                prep_stmt->setInt(3, carpark_number);
                prep_stmt->setString(4, carpark_name);
                prep_stmt->setInt(5, device_number);
                prep_stmt->setString(6, device_name);
                prep_stmt->setString(7, device_ip);
                prep_stmt->setInt(8, device_type);
                prep_stmt->executeUpdate();

                delete res;
                delete prep_stmt;

                if (device_type == 3 || device_type == 4 || device_type == 5) {
                    prep_stmt = dcon->prepareStatement("select id from parking_revenue where carpark_number=" + previous_carpark + " and device_number=" + previous_device);
                    res = prep_stmt->executeQuery();
                    delete prep_stmt;
                    if (res->next()) {
                        id = res->getString("id");
                        prep_stmt = dcon->prepareStatement("update parking_revenue set facility_number=?,facility_name=?,carpark_number=?,carpark_name=?,device_number=?,device_name=?,device_type=? where id=?");
                        prep_stmt->setString(8, id);
                    } else {
                        prep_stmt = dcon->prepareStatement("insert into parking_revenue(facility_number,facility_name,carpark_number,carpark_name,device_number,device_name,device_type)values(?,?,?,?,?,?,?)");
                    }
                    prep_stmt->setInt(1, facility_number);
                    prep_stmt->setString(2, facility_name);
                    prep_stmt->setInt(3, carpark_number);
                    prep_stmt->setString(4, carpark_name);
                    prep_stmt->setInt(5, device_number);
                    prep_stmt->setString(6, device_name);
                    prep_stmt->setInt(7, device_type);
                    prep_stmt->executeUpdate();

                    delete res;
                    delete prep_stmt;
                }
            }
        }
        delete con;
    } catch (const std::exception& e) {
        writeException("insertUpdateDevice", e.what());
    }
    return msg;
}

Php::Value enableDisable(string table, string id_field, string status_field, Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("update " + table + " set " + status_field + "=? where " + id_field + "=?");
        prep_stmt->setString(1, ToString(json["status"]));
        prep_stmt->setString(2, ToString(json["id"]));
        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("enableDisable", e.what());
    }
    return msg;
}

Php::Value getDetails(string table, string id, Php::Value json) {
    Php::Value response;
    try {
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from " + table + " where " + id + "=?");
        prep_stmt->setString(1, ToString(json["id"]));
        res = prep_stmt->executeQuery();
        if (res->next()) {
            sql::ResultSetMetaData *res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();
            for (int i = 1; i <= columns; i++)
                response[res_meta -> getColumnLabel(i)] = string(res->getString(i));
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("getDetails", e.what());
    }
    return response;
}



Php::Value parcxV2Configuration(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:                   
            carparkDropdown();
            break;
        case 2:
            facilityDropdown();
            break;
        case 3:                   
            deviceDropdown(data);
            break; 
        case 4:
            facilityDropdown() ;
            break;
        case 5:
            showDeviceList(data) ;
            break;
        case 6:
            response = insertUpdateDevice(data);
            break;
        case 7:
            response = enableDisable("system_devices", "id", "device_enabled", data);
            break;
        case 8:
            response = getDetails("system_devices", "id", data);
            break;
        
        }
    return response;
    }

extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_V2_Configuration", "1.0");
        extension.add<parcxV2Configuration>("parcxV2Configuration");
        return extension;
    }
}
