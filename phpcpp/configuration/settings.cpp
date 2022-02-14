#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include <cppconn/resultset_metadata.h>
#include<phpcpp.h>
#include<dirent.h>
#include<sstream>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

using namespace std;
GeneralOperations General;
sql::Connection *con, *dcon;
sql::Statement *stmt;
sql::ResultSet *res, *res2;
sql::PreparedStatement *prep_stmt;
string query;

bool facilityFlag, carparkFlag;

void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-Settings-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-Settings-" + General.currentDateTime("%Y-%m-%d"), function, message);
    Php::out << message << endl;
    writeLog(function, "Exception: " + message);
}

string ToString(Php::Value param) {
    string value = param;
    return value;
}

Php::Value insertUpdateFacility(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "")
            prep_stmt = con->prepareStatement("select * from system_facility where facility_number=?");
        else {
            prep_stmt = con->prepareStatement("select * from system_facility where facility_number=? and facility_id!=?");
            prep_stmt->setString(2, ToString(json["id"]));
        }

        prep_stmt->setString(1, ToString(json["facility_number"]));
        res = prep_stmt->executeQuery();
        if (res->next()) {
            msg = "Facility number already exist.Try with another facility number";
            delete prep_stmt;
            delete res;
        } else {
            if (ToString(json["id"]) == "") {
                prep_stmt = con->prepareStatement("insert into system_facility(facility_number,facility_name,facility_location,user_id,status) values(?,?,?,?,1)");
                prep_stmt->setString(4, ToString(json["user_id"]));
            } else {
                prep_stmt = con->prepareStatement("update system_facility set facility_number=?,facility_name=?,facility_location=? where facility_id=?");
                prep_stmt->setString(4, ToString(json["id"]));
            }

            prep_stmt->setString(1, ToString(json["facility_number"]));
            prep_stmt->setString(2, ToString(json["facility_name"]));
            prep_stmt->setString(3, ToString(json["facility_location"]));

            if (!prep_stmt->execute())
                msg = "Successfull";
            delete prep_stmt;
        }
        delete con;
    } catch (const std::exception& e) {
        writeException("insertUpdateFacility", e.what());
    }
    return msg;
}

Php::Value insertUpdateSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "") {
            prep_stmt = con->prepareStatement("select * from system_settings where setting_name=?");
            prep_stmt->setString(1, ToString(json["setting_name"]));
            res = prep_stmt->executeQuery();
            if (res->next()) {
                msg = "Settings already exist";
                delete prep_stmt;
                delete res;
                delete con;
                return msg;
            }
        }
        if (ToString(json["id"]) == "") {
            prep_stmt = con->prepareStatement("insert into system_settings(setting_value,setting_name,setting_label,setting_status) values(?,?,?,1)");
            prep_stmt->setString(2, ToString(json["setting_name"]));
            prep_stmt->setString(3, ToString(json["setting_description"]));
        } else {
            prep_stmt = con->prepareStatement("update system_settings set setting_value=? where settings_id=?");
            prep_stmt->setString(2, ToString(json["id"]));
        }

        prep_stmt->setString(1, ToString(json["setting_value"]));

        if (!prep_stmt->execute()) {
            msg = "Successfull";
            if (ToString(json["id"]) != "" && ToString(json["setting_name"]) == "facility_number") {
                int facility_number = json["setting_value"];

                prep_stmt = con->prepareStatement("update system_carparks set facility_number=?");
                prep_stmt->setInt(1, facility_number);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update system_devices set facility_number=?");
                prep_stmt->setInt(1, facility_number);
                prep_stmt->execute();
                delete prep_stmt;

                delete con;
                con = General.mysqlConnect(DashboardDB);

                prep_stmt = con->prepareStatement("update counters set facility_number=?");
                prep_stmt->setInt(1, facility_number);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update parking_revenue set facility_number=?");
                prep_stmt->setInt(1, facility_number);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update watchdog_device_status set facility_number=?");
                prep_stmt->setInt(1, facility_number);
                prep_stmt->execute();
                delete prep_stmt;

                delete con;
            }

            if (ToString(json["id"]) != "" && ToString(json["setting_name"]) == "facility_name") {
                string facility_name = json["setting_value"];

                prep_stmt = con->prepareStatement("update system_carparks set facility_name=?");
                prep_stmt->setString(1, facility_name);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update system_devices set facility_name=?");
                prep_stmt->setString(1, facility_name);
                prep_stmt->execute();
                delete prep_stmt;

                delete con;
                con = General.mysqlConnect(DashboardDB);

                prep_stmt = con->prepareStatement("update counters set carpark_name=? where counter_type=0");
                prep_stmt->setString(1, facility_name);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update parking_revenue set facility_name=?");
                prep_stmt->setString(1, facility_name);
                prep_stmt->execute();
                delete prep_stmt;

                prep_stmt = con->prepareStatement("update watchdog_device_status set facility_name=?");
                prep_stmt->setString(1, facility_name);
                prep_stmt->execute();
                delete prep_stmt;

                delete con;
            }
        } else {
            delete prep_stmt;
            delete con;
        }
    } catch (const std::exception& e) {
        writeException("insertUpdateSettings", e.what());
    }
    return msg;
}

Php::Value insertUpdateMaintenanceSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "") {
            prep_stmt = con->prepareStatement("insert into device_maintenance(setting_value,setting_name,enabled) values(?,?,1)");
            prep_stmt->setString(2, ToString(json["setting_name"]));
        } else {
            prep_stmt = con->prepareStatement("update device_maintenance set setting_value=? where id=?");
            prep_stmt->setString(2, ToString(json["id"]));
        }

        prep_stmt->setString(1, ToString(json["setting_value"]));

        if (!prep_stmt->execute()) {
            msg = "Successfull";
        } else {
            delete prep_stmt;
            delete con;
        }
    } catch (const std::exception& e) {
        writeException("insertUpdateMaintenanceSettings", e.what());
    }
    return msg;
}

Php::Value updateCloudUploadSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);

        prep_stmt = con->prepareStatement("update pxcloud_upload_settings set day_closure=?,time_interval=?,upload_row_limit=? where id=?");

        prep_stmt->setString(1, ToString(json["dc"]));
        prep_stmt->setString(2, ToString(json["interval"]));
        prep_stmt->setString(3, ToString(json["limit"]));
        prep_stmt->setString(4, ToString(json["id"]));

        if (!prep_stmt->execute()) {
            msg = "Successfull";
        } else {
            delete prep_stmt;
            delete con;
        }
    } catch (const std::exception& e) {
        writeException("insertUpdateMaintenanceSettings", e.what());
    }
    return msg;
}

Php::Value updateCloudDownloadSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);

        prep_stmt = con->prepareStatement("update pxcloud_download_settings set day_closure=?,time_interval=?,download_row_limit=? where id=?");

        prep_stmt->setString(1, ToString(json["dc"]));
        prep_stmt->setString(2, ToString(json["interval"]));
        prep_stmt->setString(3, ToString(json["limit"]));
        prep_stmt->setString(4, ToString(json["id"]));

        if (!prep_stmt->execute()) {
            msg = "Successfull";
        } else {
            delete prep_stmt;
            delete con;
        }
    } catch (const std::exception& e) {
        writeException("insertUpdateMaintenanceSettings", e.what());
    }
    return msg;
}

Php::Value insertUpdateCarpark(Php::Value json) {
    string msg = "Failed";
    try {
        string id = json["id"];
        int facility_number = json["facility_number"];
        int carpark_number = json["carpark_number"];
        string user_id = json["user_id"];
        string facility_name = json["facility_name"];
        string carpark_name = json["carpark_name"];
        string total_spaces = json["total_spaces"];
        string occupancy_threshold = json["occupancy_threshold"];
        string reservation_spaces = json["reservation_spaces"];
        string access_spaces = json["access_spaces"];
        string shortterm_spaces = json["shortterm_spaces"];
        string rate_type = json["rate_type"];
        string rate_plan = json["rate_plan"];
        string reservation_rate_plan = json["reservation_rate_plan"];


        con = General.mysqlConnect(ServerDB);
        if (id == "")
            prep_stmt = con->prepareStatement("select * from system_carparks where carpark_number=?");
        else {
            prep_stmt = con->prepareStatement("select * from system_carparks where carpark_number=? and carpark_id!=?");
            prep_stmt->setString(2, id);
        }
        prep_stmt->setInt(1, carpark_number);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            msg = "Carpark number already exist in " + facility_name + ".Try with another carpark number";
            delete prep_stmt;
            delete res;
        } else {
            if (id == "") {
                prep_stmt = con->prepareStatement("insert into system_carparks(carpark_number,carpark_name,total_spaces ,occupancy_threshold,reservation_spaces,access_spaces,shortterm_spaces,rate_type,rate_plan,reservation_rate_plan,facility_number,facility_name,user_id,status) values(?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
                prep_stmt->setInt(11, facility_number);
                prep_stmt->setString(12, facility_name);
                prep_stmt->setString(13, user_id);
            } else {
                prep_stmt = con->prepareStatement("update system_carparks set carpark_number=?,carpark_name=?,total_spaces=?,occupancy_threshold=?,reservation_spaces=?,access_spaces=?,shortterm_spaces=?,rate_type=?,rate_plan=?,reservation_rate_plan=? where carpark_id=?");
                prep_stmt->setString(11, id);
            }
            prep_stmt->setInt(1, carpark_number);
            prep_stmt->setString(2, carpark_name);
            prep_stmt->setString(3, total_spaces);
            prep_stmt->setString(4, occupancy_threshold);
            prep_stmt->setString(5, reservation_spaces);
            prep_stmt->setString(6, access_spaces);
            prep_stmt->setString(7, shortterm_spaces);
            prep_stmt->setString(8, rate_type);
            prep_stmt->setString(9, rate_plan);
            prep_stmt->setString(10, reservation_rate_plan);

            if (!prep_stmt->execute()) {
                msg = "Successfull";

                int previous_carpark = json["previous_carpark"];

                //system devices
                prep_stmt = con->prepareStatement("update system_devices set rate_type=?,rate_plan=?,reservation_rate_plan=?,carpark_number=?,carpark_name=? where carpark_number=?");
                prep_stmt->setString(1, rate_type);
                prep_stmt->setString(2, rate_plan);
                prep_stmt->setString(3, reservation_rate_plan);
                prep_stmt->setInt(4, carpark_number);
                prep_stmt->setString(5, carpark_name);
                prep_stmt->setInt(6, previous_carpark);
                prep_stmt->executeUpdate();
                delete prep_stmt;

                facilityFlag = false;
                carparkFlag = false;

                dcon = General.mysqlConnect(DashboardDB);
                if (id != "") {
                    //parking_revenue
                    prep_stmt = dcon->prepareStatement("update parking_revenue set carpark_number=?,carpark_name=?,facility_number=? where carpark_number=?");
                    prep_stmt->setInt(1, carpark_number);
                    prep_stmt->setString(2, carpark_name);
                    prep_stmt->setInt(3, facility_number);
                    prep_stmt->setInt(4, previous_carpark);
                    prep_stmt->executeUpdate();

                    //watchdog_device_status
                    prep_stmt = dcon->prepareStatement("update watchdog_device_status set carpark_number=?,carpark_name=?,facility_number=?,facility_name=? where carpark_number=?");
                    prep_stmt->setInt(1, carpark_number);
                    prep_stmt->setString(2, carpark_name);
                    prep_stmt->setInt(3, facility_number);
                    prep_stmt->setString(4, facility_name);
                    prep_stmt->setInt(5, previous_carpark);
                    prep_stmt->executeUpdate();
                }

                int minId = 0;
                int maxId = 0;
                prep_stmt = dcon->prepareStatement("select id,type from hourly_occupancy where carpark_number=? and data='realtime'");
                prep_stmt->setInt(1, previous_carpark);
                res = prep_stmt->executeQuery();
                while (res->next()) {
                    if (res->getString("type") == "Max")
                        maxId = res->getInt("id");
                    if (res->getString("type") == "Min")
                        minId = res->getInt("id");
                }
                delete res;
                //hourly occupancy
                if (minId == 0)
                    prep_stmt = dcon->prepareStatement("insert into hourly_occupancy(facility_number,facility_name,carpark_number,carpark_name,type,data)values(?,?,?,?,'Min','realtime')");
                else {
                    prep_stmt = dcon->prepareStatement("update hourly_occupancy set facility_number=?,facility_name=?,carpark_number=?,carpark_name=? where id=?");
                    prep_stmt->setInt(5, minId);
                }
                prep_stmt->setInt(1, facility_number);
                prep_stmt->setString(2, facility_name);
                prep_stmt->setInt(3, carpark_number);
                prep_stmt->setString(4, carpark_name);
                prep_stmt->executeUpdate();

                if (maxId == 0)
                    prep_stmt = dcon->prepareStatement("insert into hourly_occupancy(facility_number,facility_name,carpark_number,carpark_name,type,data)values(?,?,?,?,'Max','realtime')");
                else {
                    prep_stmt = dcon->prepareStatement("update hourly_occupancy set facility_number=?,facility_name=?,carpark_number=?,carpark_name=? where id=?");
                    prep_stmt->setInt(5, maxId);
                }
                prep_stmt->setInt(1, facility_number);
                prep_stmt->setString(2, facility_name);
                prep_stmt->setInt(3, carpark_number);
                prep_stmt->setString(4, carpark_name);
                prep_stmt->executeUpdate();


                //counters
                prep_stmt = dcon->prepareStatement("select count_category,counter_type,carpark_number from counters");
                res = prep_stmt->executeQuery();
                while (res->next()) {
                    if (res->getInt("count_category") == 0 && res->getInt("counter_type") == 0)
                        facilityFlag = true;
                    if (res->getInt("count_category") == 0 && res->getInt("counter_type") == 1 && res->getInt("carpark_number") == previous_carpark)
                        carparkFlag = true;
                }
                delete res;
                delete prep_stmt;
                if (carparkFlag == false) {
                    prep_stmt = dcon->prepareStatement("insert into counters(carpark_number,carpark_name,total_spaces,occupancy_threshold,total_reservation_spaces,total_access_spaces,total_shortterm_spaces,dashboard_order,facility_number,counter_type)values(?,?,?,?,?,?,?,?,?,1)");
                    prep_stmt->setInt(8, carpark_number);
                    prep_stmt->setInt(9, facility_number);
                } else {
                    prep_stmt = dcon->prepareStatement("update counters set carpark_number=?,carpark_name=?,total_spaces=?,occupancy_threshold=?,total_reservation_spaces=?,total_access_spaces=?,total_shortterm_spaces=? where carpark_number=? and counter_type=1");
                    prep_stmt->setInt(8, previous_carpark);
                }
                prep_stmt->setInt(1, carpark_number);
                prep_stmt->setString(2, carpark_name);
                prep_stmt->setString(3, total_spaces);
                prep_stmt->setString(4, occupancy_threshold);
                prep_stmt->setString(5, reservation_spaces);
                prep_stmt->setString(6, access_spaces);
                prep_stmt->setString(7, shortterm_spaces);
                prep_stmt->executeUpdate();

                delete prep_stmt;
                prep_stmt = con->prepareStatement("SELECT sum(total_spaces) as total_spaces,sum(reservation_spaces) as reservation_spaces,sum(access_spaces) as access_spaces,sum(shortterm_spaces) as shortterm_spaces FROM system_carparks");
                res = prep_stmt->executeQuery();
                if (res->next()) {
                    delete prep_stmt;
                    if (facilityFlag == false) {
                        prep_stmt = dcon->prepareStatement("insert into counters(total_spaces,total_reservation_spaces,total_access_spaces,total_shortterm_spaces,facility_number,carpark_name,dashboard_order,counter_type)values(?,?,?,?,?,?,?,0)");
                        prep_stmt->setInt(7, facility_number);
                    } else
                        prep_stmt = dcon->prepareStatement("update counters set total_spaces=?,total_reservation_spaces=?,total_access_spaces=?,total_shortterm_spaces=?,facility_number=?,carpark_name=? where counter_type=0");
                    prep_stmt->setString(1, res->getString("total_spaces"));
                    prep_stmt->setString(2, res->getString("reservation_spaces"));
                    prep_stmt->setString(3, res->getString("access_spaces"));
                    prep_stmt->setString(4, res->getString("shortterm_spaces"));
                    prep_stmt->setInt(5, facility_number);
                    prep_stmt->setString(6, facility_name);
                    prep_stmt->executeUpdate();

                    delete prep_stmt;
                    delete res;
                }
            }
        }
        delete con;
    } catch (const std::exception& e) {
        writeException("insertUpdateCarpark", e.what());
    }
    return msg;
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
            prep_stmt->setString(11, ToString(json["customer_receipt_mandatory"]));
            prep_stmt->setString(12, ToString(json["shift_receipt_mandatory"]));
            prep_stmt->setString(13, ToString(json["physical_cash_count"]));
            prep_stmt->setString(14, ToString(json["synch_whitelist"]));
            prep_stmt->setString(15, ToString(json["issue_lost"]));
            prep_stmt->setInt(16, camera_index);
            prep_stmt->setString(17, ToString(json["anpr_enabled"]));
            prep_stmt->setString(18, ToString(json["wiegand_enabled"]));
            prep_stmt->setString(19, ToString(json["access_enabled"]));
            prep_stmt->setString(20, ToString(json["reservation_enabled"]));
            prep_stmt->setString(21, ToString(json["review_mode"]));
            prep_stmt->setString(22, ToString(json["device_function"]));
            prep_stmt->setString(23, ToString(json["barrier_open_time_limit"]));
            prep_stmt->setString(24, ToString(json["backout_blacklist_delay"]));
            prep_stmt->setString(25, ToString(json["display_anpr_image"]));
            prep_stmt->setString(26, ToString(json["barrier_open_status_type"]));
            prep_stmt->setString(27, ToString(json["bms_status_enabled"]));
            prep_stmt->setString(28, ToString(json["barrier_status_monitoring"]));
            prep_stmt->setString(29, ToString(json["wiegand2_enabled"]));
            prep_stmt->setString(30, ToString(json["server_handshake_interval"]));
            prep_stmt->setString(31, ToString(json["plate_capturing_wait_delay"]));
            prep_stmt->setString(32, ToString(json["quick_barrier_close"]));
            prep_stmt->setString(33, ToString(json["payment_enabled_exit"]));
            prep_stmt->setString(34, res->getString("rate_plan"));
            prep_stmt->setString(35, res->getString("reservation_rate_plan"));
            prep_stmt->setString(36, res->getString("rate_type"));

            prep_stmt->setString(37, ToString(json["anpr_image_path"]));
            prep_stmt->setString(38, ToString(json["cropped_picture_required"]));
            prep_stmt->setString(39, ToString(json["anpr_com_port"]));
            prep_stmt->setString(40, ToString(json["anpr_mismatch_check"]));
            prep_stmt->setString(41, ToString(json["plate_captured_interval"]));
            prep_stmt->setString(42, ToString(json["allow_manual_open_close"]));
            prep_stmt->setString(43, ToString(json["activate_product_sale"]));
            prep_stmt->setString(44, ToString(json["activate_subscription"]));
            prep_stmt->setString(45, ToString(json["print_entry_ticket"]));
            prep_stmt->setString(46, ToString(json["lost_including_parkingfee"]));
            prep_stmt->setString(47, ToString(json["float_amount_required"]));
            prep_stmt->setString(48, ToString(json["full_max_exit_grace_enabled"]));
            prep_stmt->setString(49, ToString(json["receipt_printer_type"]));
            prep_stmt->setString(50, ToString(json["entry_printer_type"]));
            prep_stmt->setString(51, ToString(json["entry_printer_port_name"]));
            prep_stmt->setString(52, ToString(json["receipt_printer_port_name"]));
            prep_stmt->setString(53, ToString(json["print_operator_logo"]));
            prep_stmt->setString(54, ToString(json["receipt_primary_language"]));
            prep_stmt->setString(55, ToString(json["receipt_secondary_language"]));
            prep_stmt->setString(56, ToString(json["barrier_open_command"]));
            prep_stmt->setString(57, ToString(json["barrier_close_command"]));
            prep_stmt->setString(58, ToString(json["barrier_communication"]));
            prep_stmt->setString(59, ToString(json["transaction_time_out"]));
            prep_stmt->setString(60, ToString(json["rfid_port"]));
            prep_stmt->setString(61, ToString(json["central_cashier"]));
            prep_stmt->setString(62, ToString(json["controller_ip"]));
            prep_stmt->setString(63, ToString(json["enable_port_communication"]));
            prep_stmt->setString(64, ToString(json["barrier_port_ip"]));
            prep_stmt->setString(65, ToString(json["barrier_port_number"]));
            prep_stmt->setString(66, ToString(json["coupon_enabled"]));
            prep_stmt->setString(67, ToString(json["short_term_ticket_enabled"]));
            prep_stmt->setString(68, ToString(json["validation_enabled"]));
            prep_stmt->setString(69, ToString(json["mode_of_operation"]));
            prep_stmt->setString(70, ToString(json["wiegand_whitelist_check_enabled"]));
            prep_stmt->setString(71, ToString(json["wiegand_whitelist_parking_zone_check_enabled"]));
            prep_stmt->setString(72, ToString(json["wiegand_whitelist_expiry_check_enabled"]));
            prep_stmt->setString(73, ToString(json["wiegand_whitelist_carpark_check_enabled"]));
            prep_stmt->setString(74, ToString(json["wiegand_whitelist_facility_check_enabled"]));
            prep_stmt->setString(75, ToString(json["send_wiegand_response_to_port"]));
            prep_stmt->setString(76, ToString(json["screensaver_timeout"]));
            prep_stmt->setString(77, ToString(json["upload_to_server_delay"]));
            prep_stmt->setString(78, ToString(json["mobile_qrcode_time_limit"]));
            prep_stmt->setString(79, ToString(json["network_interface"]));
            prep_stmt->setString(80, ToString(json["print_client_logo"]));
            prep_stmt->setString(81, ToString(json["media_path"]));
            prep_stmt->setString(82, ToString(json["output_pulse_length"]));

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

void showFacilityList() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_facility");
        if (res->rowsCount() > 0) {
            // Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Facility Number</th>" << endl;
            Php::out << "<th>Facility Name</th>" << endl;
            Php::out << "<th>Facility Location</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("facility_id") << "'>" << endl;
            Php::out << "<td>" + res->getString("facility_number") + "</td>" << endl;
            Php::out << "<td>" + res->getString("facility_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("facility_location") + "</td>" << endl;
            Php::out << "<td>" << std::endl;
            if (res->getInt("status") == 1)
                Php::out << "<button type='button' class='col btn btn-danger facility-enable-disable-btn'>Disable</button>" << std::endl;
            else
                Php::out << "<button type='button' class='col btn btn-success facility-enable-disable-btn'>Enable</button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "<td>" << std::endl;
            Php::out << "<button type='button' class='col btn btn-info facility-edit' ><i class='fas fa-edit'></i>Edit</button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        //Php::out<<"</table>"<<endl;	
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showFacilityList", e.what());
    }

}

void showDeviceList() {
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
            Php::out << "<th></th>" << endl;
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
            Php::out << "<td>" << std::endl;
            if (res->getInt("device_enabled") == 1)
                Php::out << "<button type='button' class='btn btn-danger device-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success device-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
            Php::out << "<button type='button' class='btn btn-info device-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showDeviceList", e.what());
    }

}

void showDiscountList() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT a.*,carpark_name FROM revenue_discounts a,system_carparks b where a.carpark_number=b.carpark_number");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            //Php::out << "<th>Discount Id</th>" << endl;
            Php::out << "<th>Discount Name</th>" << endl;
            Php::out << "<th>Discount Type</th>" << endl;
            Php::out << "<th>Discount Option</th>" << endl;
            Php::out << "<th>Discount Value</th>" << endl;
            Php::out << "<th>Carpark</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("discount_id") << "'>" << endl;
            //Php::out << "<td>" + res->getString("discount_id") + "</td>" << endl;
            Php::out << "<td>" + res->getString("discount_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("discount_type") + "</td>" << endl;
            Php::out << "<td>" + res->getString("discount_option") + "</td>" << endl;
            Php::out << "<td>" + res->getString("discount_value") + "</td>" << endl;
            Php::out << "<td>" + res->getString("carpark_name") + "</td>" << endl;

            Php::out << "<td>" << std::endl;
            if (res->getInt("status") == 1)
                Php::out << "<button  class='btn btn-danger discount-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
            else
                Php::out << "<button class='btn btn-success discount-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
            Php::out << "<button type='button' class='btn btn-info discount-edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showDiscountList", e.what());
    }

}

void showManualMovementReasons() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT * from manual_movements_reasons order by id desc");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Reason</th>" << endl;
            Php::out << "<th><button type='button' class='btn btn-info manual-movement-add-btn' title='Add manual movement reasons'><i class='fas fa-plus'></i></button></th>" << endl;            
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
            Php::out << "<td>" + res->getString("reason") + "</td>" << endl;


            Php::out << "<td>" << std::endl;
            if (res->getInt("status") == 1)
                Php::out << "<button type='button' class='btn btn-danger manual-movement-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success manual-movement-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;            
            Php::out << "<button type='button' class='btn btn-info manual-movement-edit' title='Edit' data-text='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showManualMovementReasons", e.what());
    }

}

string getInterfaceName(string id) {
    string name = "";
    try {

        query = "SELECT interface_name from interface_settings where id=" + id;
        res2 = stmt->executeQuery(query);
        if (res2->next())
            name = res2->getString("interface_name");
        delete res2;
    } catch (const std::exception& e) {
        writeException("getInterfaceName", e.what());
    }
    return name;
}

void showExportSettings() {
    try {
        string id;
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT * from DataExportSettings");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Name</th>" << endl;
            Php::out << "<th>Export</th>" << endl;
            Php::out << "<th>SSH</th>" << endl;
            Php::out << "<th>SSH Interface</th>" << endl;
            Php::out << "<th>FTP</th>" << endl;
            Php::out << "<th>FTP Interface</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            id = res->getString("id");
            Php::out << "<tr data-id='" << id << "'>" << endl;
            Php::out << "<td>" + res->getString("label") + "</td>" << endl;

            if (res->getInt("export_flag") == 1)
                Php::out << "<td><input type= 'checkbox' name='export' id='export_check" << id << "' checked disabled/></td>" << endl;
            else
                Php::out << "<td><input type= 'checkbox' name='export' id='export_check" << id << "' disabled/></td>" << endl;

            if (res->getInt("ssh") == 1)
                Php::out << "<td><input type= 'checkbox' name='ssh' id='ssh_check" << id << "'  checked disabled/></td>" << endl;
            else
                Php::out << "<td><input type= 'checkbox' name='ssh' id='ssh_check" << id << "' disabled/></td>" << endl;

            Php::out << "<td>" + getInterfaceName(res->getString("ssh_interface_id")) + "</td>" << endl;

            if (res->getInt("ftp") == 1)
                Php::out << "<td><input type= 'checkbox' name='ftp' id='ftp_check" << id << "'  checked disabled/></td>" << endl;
            else
                Php::out << "<td><input type= 'checkbox' name='ftp' id='ftp_check" << id << "' disabled/></td>" << endl;

            Php::out << "<td>" + getInterfaceName(res->getString("ftp_interface_id")) + "</td>" << endl;


            Php::out << "<td>" << std::endl;
            Php::out << "<button type='button' class='btn btn-info export-setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showExportSettings", e.what());
    }

}

void showDynamicParkingRates() {
    try {
        sql::Connection *con;
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT * from revenue_parking_rate_label");
        res->next();
        Php::Value label;
        label["parking_rates1"] = string(res->getString("parking_rate1"));
        label["parking_rates2"] = string(res->getString("parking_rate2"));
        label["parking_rates3"] = string(res->getString("parking_rate3"));
        label["parking_rates4"] = string(res->getString("parking_rate4"));
        label["parking_rates5"] = string(res->getString("parking_rate5"));
        delete res;

        string rate;

        res = stmt->executeQuery("SELECT * from revenue_dynamic_parking_rates");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Name</th>" << endl;
            Php::out << "<th>Date</th>" << endl;
            Php::out << "<th>Day</th>" << endl;
            Php::out << "<th>Rate Type</th>" << endl;
            Php::out << "<th>Parking Rate</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
            Php::out << "<td>" + res->getString("parking_rate_label") + "</td>" << endl;
            Php::out << "<td>" + res->getString("datetime") + "</td>" << endl;
            Php::out << "<td>" + res->getString("day") + "</td>" << endl;
            if (res->getInt("rate_type") == 1)
                Php::out << "<td>Variable Rate</td>" << endl;
            else
                Php::out << "<td>Fixed Rate</td>" << endl;

            rate = res->getString("parking_rate");
            Php::out << "<td>" << label[rate] << "</td>" << endl;
            Php::out << "<td>" << std::endl;
            if (res->getInt("status") == 1)
                Php::out << "<button class='btn btn-danger dynamic-rate-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
            else
                Php::out << "<button class='btn btn-success dynamic-rate-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showDynamicParkingRates", e.what());
    }

}

void showCarparkList() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_carparks");
        if (res->rowsCount() > 0) {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;            
            Php::out << "<th>Carpark Name</th>" << endl;
            Php::out << "<th>Carpark Number</th>" << endl;
            //Php::out << "<th>Facility Name</th>" << endl;
            Php::out << "<th>Total Spaces</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("carpark_id") << "'>" << endl;            
            Php::out << "<td>" + res->getString("carpark_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("carpark_number") + "</td>" << endl;
            //Php::out << "<td>" + res->getString("facility_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("total_spaces") + "</td>" << endl;

            Php::out << "<td>" << std::endl;
            if (res->getInt("status") == 1)
                Php::out << "<button type='button' class='btn btn-danger carpark-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success carpark-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;

            Php::out << "<button type='button' class='btn btn-info carpark-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        //Php::out<<"</table>"<<endl;	
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showCarparkList", e.what());
    }

}

vector<string> explode(const string &str,const string &delimiter)
{
    vector<string> arr;

    int strleng = str.length();
    int delleng = delimiter.length();
    if (delleng==0)
        return arr;//no change

    int i=0;
    int k=0;
    while( i<strleng )
    {
        int j=0;
        while (i+j<strleng && j<delleng && str[i+j]==delimiter[j])
            j++;
        if (j==delleng)//found delimiter
        {
            arr.push_back(  str.substr(k, i-k) );
            i+=delleng;
            k=i;
        }
        else
        {
            i++;
        }
    }
    arr.push_back(  str.substr(k, i-k) );
    return arr;
}

void showSettingList() {
    try {
//        int i,j;        
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_settings where display_order>0 order by display_order,settings_id asc");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;           
            Php::out << "<th>Setting Name</th>" << endl;
            Php::out << "<th>Setting value</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("settings_id") << "' data-name='" << res->getString("setting_name") << "'>" << endl;            
            Php::out << "<td>" + res->getString("setting_label") + "</td>" << endl;
//            if(res->getString("setting_type")=="dropdown")
//                {
//		Php::out<<"<td>"<<endl;               
//                bool exitLoop = false;
//                vector<string> main_list = explode(res->getString("setting_options"),",");
//                for(i=0;i<(signed)main_list.size() && !exitLoop;i++)
//                {
//                    vector<string> sub_list = explode(main_list[i],":");
//                    for(j=0;j<(signed)sub_list.size();j++)
//                    {
//                        if(sub_list[j]==res->getString("setting_value"))
//                        {
//                            Php::out<<sub_list[j+1]<<endl;
//                            exitLoop = true;
//                            break;
//                        }
//                    }
//                }		             
//                }
//            else
                Php::out << "<td>" + res->getString("setting_value") + "</td>" << endl;

            Php::out << "<td>" << std::endl;

            if(res->getInt("settings_control")==2)
                {
                if (res->getInt("setting_status") == 1)
                    Php::out << "<button type='button' class='btn btn-danger setting-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                else
                    Php::out << "<button type='button' class='btn btn-success setting-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
                }
            if(res->getInt("settings_control")!=0)
                Php::out << "<button type='button' class='btn btn-info setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }       
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showSettingList", e.what());
    }
}

void showMaintenanceSettingsList() {
    string id = "0";
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from device_maintenance");
        if (res->rowsCount() > 0) {

            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;            
            Php::out << "<th>Setting Name</th>" << endl;
            Php::out << "<th>Setting value</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;

            while (res->next()) {
                id = res->getString("id");
                Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;                
                Php::out << "<td>" + res->getString("setting_label") + "</td>" << endl;
                Php::out << "<td>" + res->getString("setting_value") + "</td>" << endl;
                Php::out << "<td><button type='button' class='btn btn-info maintenance-setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
                Php::out << "</td>" << std::endl;
                Php::out << "</tr>" << endl;
            }
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showMaintenanceSettingsList", e.what());
    }
}

void showCloudUploadSettings() {
    try {
        string id = "0", dc = "";
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from pxcloud_upload_settings");
        if (res->rowsCount() > 0) {           
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;            
            Php::out << "<th>Table</th>" << endl;
            Php::out << "<th>Dayclosure</th>" << endl;            
            Php::out << "<th>Upload Limit</th>" << endl;
            Php::out << "<th>Time Interval</th>" << endl;
            Php::out << "<th>Cloud Upload Date</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;

            while (res->next()) {
                id = res->getString("id");
                Php::out << "<tr data-id='" << id << "' >" << endl;
                Php::out << "<td>" + res->getString("label") + "</td>" << endl;
                
                if (res->getString("day_closure") == "1") {
                    dc = "checked";
                } else {
                    dc = "";
                }
                Php::out << "<td><input type='checkbox' id='dc" + id + "'  " + dc + " onclick='return false;'></td>";
                //Php::out<<"<td id='task"+id+"'>"+res->getString("task")+"</td>"<<endl;  
                Php::out << "<td id='limit" + id + "'>" + res->getString("upload_row_limit") + "</td>" << endl;
                Php::out << "<td id='interval" + id + "'>" + res->getString("time_interval") + "</td>" << endl;
                Php::out << "<td>" + res->getString("cloud_upload_date_time") + "</td>" << endl;
                Php::out << "<td>" << std::endl;
                if (res->getInt("enabled") == 1)
                    Php::out << "<button type='button' class='btn btn-danger upload-setting-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                else
                    Php::out << "<button type='button' class='btn btn-success upload-setting-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
                Php::out << "<button type='button' class='btn btn-info upload-setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
                Php::out << "</td>" << std::endl;
                Php::out << "</tr>" << endl;
            }
            //Php::out << "</table>" << std::endl;	
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showSettingList", e.what());
    }
}

void showCloudDownloadSettings() {
    try {
        string id = "0", dc = "";
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from pxcloud_download_settings");
        if (res->rowsCount() > 0) {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            //Php::out << "<th>No</th>" << endl;
            Php::out << "<th>Table</th>" << endl;
            Php::out << "<th>Dayclosure</th>" << endl;
            // Php::out<<"<th>Task</th>"<<endl;	
            Php::out << "<th>Download Limit</th>" << endl;
            Php::out << "<th>Time Interval</th>" << endl;
            Php::out << "<th>Cloud Download Date</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;

            while (res->next()) {
                id = res->getString("id");
                Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
               // Php::out << "<td>" + res->getString("id") + "</td>" << endl;
                Php::out << "<td>" + res->getString("label") + "</td>" << endl;
                if (res->getString("day_closure") == "1") {
                    dc = "checked";
                } else {
                    dc = "";
                }
                Php::out << "<td><input type='checkbox' id='dc" + id + "'  " + dc + " onclick='return false;'></td>";
                Php::out << "<td id='limit" + id + "'>" + res->getString("download_row_limit") + "</td>" << endl;
                Php::out << "<td id='interval" + id + "'>" + res->getString("time_interval") + "</td>" << endl;
                Php::out << "<td>" + res->getString("cloud_download_date_time") + "</td>" << endl;
                Php::out << "<td>" << std::endl;
                if (res->getInt("enabled") == 1)
                    Php::out << "<button type='button' class='btn btn-danger download-setting-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                else
                    Php::out << "<button type='button' class='btn btn-success download-setting-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;

                Php::out << "<button type='button' class='btn btn-info download-setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
                Php::out << "</td>" << std::endl;
                Php::out << "</tr>" << endl;
            }
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showSettingList", e.what());
    }
}

int occuranceOfComma(string str) {
    int n = 0;
    for (int i = 0; i < (signed)str.length(); i++) {
        if (str[i] == ',')
            n++;
    }
    return n;
}

void showProductList() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_products order by product_id desc");

        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << std::endl;
            //Php::out << "<th>No</th>" << std::endl;
            Php::out << "<th>Product Name</th>" << std::endl;
            Php::out << "<th>Product Type</th>" << std::endl;
            Php::out << "<th>Validity Days</th>" << std::endl;
            Php::out << "<th>Valid From</th>" << std::endl;
            Php::out << "<th>Valid To</th>" << std::endl;
            Php::out << "<th></th>" << std::endl;
            Php::out << "</tr>" << std::endl;
            Php::out << "</thead>" << std::endl;

            //int no = 1;
            while (res->next()) {
                Php::out << "<tr data-id='" << res->getString("product_id") << "'>" << std::endl;
                //Php::out << "<td>" << no << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("product_name") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("product_type") << "</td>" << std::endl;
                if (res->getString("product_type") == "Validation") {
                    if (occuranceOfComma(res->getString("validity_days")) == 6)
                        Php::out << "<td>All Days</td>" << std::endl;
                    else
                        Php::out << "<td>" << res->getString("validity_days") << "</td>" << std::endl;
                } else
                    Php::out << "<td>" << res->getString("product_value") << "</td>" << std::endl;

                Php::out << "<td>" << res->getString("start_date") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("expiry_date") << "</td>" << std::endl;
                if (res->getInt("status") == 1)
                    Php::out << "<td><button class='btn btn-danger product-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                else
                    Php::out << "<td><button class='btn btn-success product-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;

                Php::out << "<button type='button' class='btn btn-info product-edit'><i class='fas fa-edit'></i></button></td>" << std::endl;
                Php::out << "</tr>" << std::endl;
                //no++;
            }
        }
        delete res;
        delete stmt;
        delete con;
    } catch (exception &e) {
        writeException("showProductList", e.what());
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

string interfaceDropdown(string type) {
    string html = "";
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();

        res = stmt->executeQuery("select id,interface_name from interface_settings where interface_type='" + type + "'");
        html = html + "<option value='0'>Select interface</option>";
        while (res->next())
            html = html + "<option value='" + res->getString("id") + "'>" + res->getString("interface_name") + "</option>";
        delete res;
        delete stmt;
        delete con;

    } catch (const std::exception& e) {
        writeException("interfaceDropdown", e.what());
    }
    return html;
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

void parkingZoneDropdown(Php::Value json) {
    try {
        string carpark=json["carpark_number"];
        if(carpark!="")
            {
            con = General.mysqlConnect(ServerDB);
            stmt = con->createStatement();
            res = stmt->executeQuery("select * from system_parking_zone where carpark_number in (" + carpark + ")");
            while (res->next()) 
                Php::out << "<option carpark_number='" << res->getString("carpark_number") << "' value='" << res->getString("parking_zone_number") << "'>" << res->getString("parking_zone_name") << "</option>" << std::endl;
            

            delete res;
            delete stmt;
            delete con;
            }
    } catch (const std::exception& e) {
        writeException("parkingZoneDropdown", e.what());
    }
}

void operatorDropdown() {
    try {
        
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_users");
        while (res->next()) 
            Php::out << "<option  value='" << res->getString("user_id") << "'>" << res->getString("operator_name") << "</option>" << std::endl;


        delete res;
        delete stmt;
        delete con;
            
    } catch (const std::exception& e) {
        writeException("operatorDropdown", e.what());
    }
}

void discountDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from revenue_discounts");
        while (res->next()) {
            Php::out << "<option value='" << res->getString("discount_id") << "'>" << res->getString("discount_name") << "</option>" << std::endl;
        }

        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("carparkDropdown", e.what());
    }
}

void validationProductDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_products where product_type='Validation'");
        Php::out<<"<option value='0'>Offline</option>"<<endl;
        while (res->next()) {
            Php::out << "<option value='" << res->getString("product_id") << "'>" << res->getString("product_name") << "</option>" << std::endl;
        }

        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("validationProductDropdown", e.what());
    }
}

void deviceDropdown(Php::Value json) {
    try {
        string type=json["type"];
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
    } catch (const std::exception& e) {
        writeException("deviceDropdown", e.what());
    }
}

void validatorDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from system_validators");
        while (res->next()) {
            Php::out << "<option value='" << res->getString("user_id") << "'>" << res->getString("display_name") << "</option>" << std::endl;
        }

        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("validatorDropdown", e.what());
    }
}

void parkingRateDropdown() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from revenue_parking_rate_label");
        if (res->next()) {
            Php::out << "<option value='parking_rates1'>" << res->getString("parking_rate1") << "</option>" << std::endl;
            Php::out << "<option value='parking_rates2'>" << res->getString("parking_rate2") << "</option>" << std::endl;
            Php::out << "<option value='parking_rates3'>" << res->getString("parking_rate3") << "</option>" << std::endl;
            Php::out << "<option value='parking_rates4'>" << res->getString("parking_rate4") << "</option>" << std::endl;
            Php::out << "<option value='parking_rates5'>" << res->getString("parking_rate5") << "</option>" << std::endl;
        }

        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("parkingRateDropdown", e.what());
    }
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

Php::Value insertUpdateSystemProduct(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "0")
            prep_stmt = con->prepareStatement("insert into system_products(product_name,start_date,expiry_date,validity_days,validity_time_slots,product_type,product_value,description,carparks,date_validity,validation_type,product_price,facility_number,user_id,status)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
        else
            prep_stmt = con->prepareStatement("update system_products set product_name=?,start_date=?,expiry_date=?,validity_days=?,validity_time_slots=?,product_type=?,product_value=?,description=?,carparks=?,date_validity=?,validation_type=?,product_price=?,facility_number=? where product_id=?");

        prep_stmt->setString(1, ToString(json["product_name"]));
        if (stoi(json["date_validity"]) == 1 || ToString(json["product_type"]) == "contract_parking") {
            prep_stmt->setString(2, ToString(json["start_date"]));
            prep_stmt->setString(3, ToString(json["expiry_date"]));
        } else {
            prep_stmt->setNull(2, 0);
            prep_stmt->setNull(3, 0);
        }
        prep_stmt->setString(4, ToString(json["validity_days"]));
        prep_stmt->setString(5, ToString(json["validity_time_slots"]));
        prep_stmt->setString(6, ToString(json["product_type"]));
        prep_stmt->setString(7, ToString(json["product_value"]));
        prep_stmt->setString(8, ToString(json["description"]));
        prep_stmt->setString(9, ToString(json["carparks"]));
        prep_stmt->setString(10, ToString(json["date_validity"]));
        prep_stmt->setString(11, ToString(json["validation_type"]));
        prep_stmt->setString(12, ToString(json["product_price"]));
        prep_stmt->setString(13, ToString(json["facility_number"]));
        if (ToString(json["id"]) == "0")
            prep_stmt->setString(14, ToString(json["user_id"]));
        else
            prep_stmt->setString(14, ToString(json["id"]));


        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("insertUpdateValidationProduct", e.what());
    }
    return msg;
}

Php::Value insertUpdateDicount(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "0")
            prep_stmt = con->prepareStatement("insert into revenue_discounts(discount_name,discount_type,discount_option,discount_value,carpark_number,status)VALUES(?,?,?,?,?,1)");
        else
            prep_stmt = con->prepareStatement("update revenue_discounts set discount_name=?,discount_type=?,discount_option=?,discount_value=?,carpark_number=? where discount_id=?");

        prep_stmt->setString(1, ToString(json["discount_name"]));
        prep_stmt->setString(2, ToString(json["discount_type"]));
        prep_stmt->setString(3, ToString(json["discount_option"]));
        prep_stmt->setString(4, ToString(json["discount_value"]));
        prep_stmt->setString(5, ToString(json["carpark_number"]));
        if (ToString(json["id"]) != "0")
            prep_stmt->setString(6, ToString(json["id"]));

        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("insertUpdateDicount", e.what());
    }
    return msg;
}

Php::Value UpdateExportSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("Update DataExportSettings set export_flag=?,ssh=?,ftp=?,ssh_interface_id=?,ftp_interface_id=? where id = ?");

        prep_stmt->setInt(1, json["export_flag"]);
        prep_stmt->setInt(2, json["ssh"]);
        prep_stmt->setInt(3, json["ftp"]);
        prep_stmt->setInt(4, json["ssh_interface_id"]);
        prep_stmt->setInt(5, json["ftp_interface_id"]);
        prep_stmt->setInt(6, json["id"]);

        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("UpdateExportSettings", e.what());
    }
    return msg;
}

Php::Value insertUpdateDynamicParkingRate(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        string date = json["date"];
        prep_stmt = con->prepareStatement("insert into revenue_dynamic_parking_rates(parking_rate_label,datetime,day,rate_type,parking_rate,status)VALUES(?,?,?,?,?,1)");

        prep_stmt->setString(1, ToString(json["name"]));
        if (date != "")
            prep_stmt->setString(2, date);
        else
            prep_stmt->setNull(2, 0);
        prep_stmt->setString(3, ToString(json["day"]));
        prep_stmt->setString(4, ToString(json["type"]));
        prep_stmt->setString(5, ToString(json["parkingRate"]));

        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("insertUpdateDynamicParkingRate", e.what());
    }
    return msg;
}

Php::Value updateParkingRateName(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        string parking_rate_name = json["parking_rate_name"];
        string parking_rate_label = json["parking_rate_label"];
        prep_stmt = con->prepareStatement("update revenue_parking_rate_label set parking_rate" + parking_rate_name + "='" + parking_rate_label + "'");
        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("updateParkingRateName", e.what());
    }
    return msg;
}

Php::Value insertUpdateManualMovementReason(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "0")
            prep_stmt = con->prepareStatement("insert into manual_movements_reasons(reason,status)VALUES(?,1)");
        else
            prep_stmt = con->prepareStatement("update manual_movements_reasons set reason=? where id=?");

        prep_stmt->setString(1, ToString(json["reason"]));
        if (ToString(json["id"]) != "0")
            prep_stmt->setString(2, ToString(json["id"]));

        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("insertUpdateManualMovementReason", e.what());
    }
    return msg;
}

void showInterfaceSettings() {
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from interface_settings");

        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << std::endl;
            Php::out << "<th>Interface Name</th>" << std::endl;
            Php::out << "<th>Type</th>" << std::endl;
            Php::out << "<th>Host</th>" << std::endl;
            Php::out << "<th>Username</th>" << std::endl;
           //Php::out << "<th>Password</th>" << std::endl;
            Php::out << "<th>FTP Url</th>" << std::endl;
            Php::out << "<th>SSH Path</th>" << std::endl;
            Php::out << "<th><button type='button' class='btn btn-info interface-add' title='Add interface'><i class='fas fa-plus'></i></button></th>" << std::endl;
            Php::out << "</tr>" << std::endl;
            Php::out << "</thead>" << std::endl;

            int no = 1;
            while (res->next()) {
                Php::out << "<tr data-id='" << res->getString("id") << "'>" << std::endl;
                Php::out << "<td>" << res->getString("interface_name") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("interface_type") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("host") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("username") << "</td>" << std::endl;
               // Php::out << "<td>" << res->getString("password") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("url") << "</td>" << std::endl;
                Php::out << "<td>" << res->getString("folder_path") << "</td>" << std::endl;
                Php::out << "<td><button type='button' class='btn btn-info interface-edit' title='Edit' data-text='Edit'><i class='fas fa-edit'></i></button></td>" << std::endl;
                Php::out << "</tr>" << std::endl;
                no++;
            }
        }
        delete res;
        delete stmt;
        delete con;
    } catch (exception &e) {
        writeException("showInterfaceSettings", e.what());
    }
}

Php::Value insertUpdateInterfaceSettings(Php::Value json) {
    string msg = "Failed";
    try {
        con = General.mysqlConnect(ServerDB);
        if (ToString(json["id"]) == "0")
            prep_stmt = con->prepareStatement("insert into interface_settings(interface_name,interface_type,host,username,password,url,folder_path)VALUES(?,?,?,?,?,?,?)");
        else
            prep_stmt = con->prepareStatement("update interface_settings set interface_name=?,interface_type=?,host=?,username=?,password=?,url=?,folder_path=? where id=?");

        prep_stmt->setString(1, ToString(json["name"]));
        prep_stmt->setString(2, ToString(json["interface_type"]));
        prep_stmt->setString(3, ToString(json["host"]));
        prep_stmt->setString(4, ToString(json["username"]));
        prep_stmt->setString(5, ToString(json["password"]));
        prep_stmt->setString(6, ToString(json["url"]));
        prep_stmt->setString(7, ToString(json["folder_path"]));

        if (ToString(json["id"]) != "0")
            prep_stmt->setString(8, ToString(json["id"]));


        if (!prep_stmt->execute())
            msg = "Successfull";
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        writeException("insertUpdateInterfaceSettings", e.what());
    }
    return msg;
}

std::vector<std::string> split(const std::string& s, char delimiter)                                                                                                                          
    {                                                                                                                                                                                             
    std::vector<std::string> splits;                                                                                                                                                           
    std::string split;                                                                                                                                                                         
    std::istringstream ss(s);                                                                                                                                                                  
    while (std::getline(ss, split, delimiter))
       splits.push_back(split);                                                                                                                                                                

    return splits;                                                                                                                                                                             
    }

void getSettingsInputBox(Php::Value json)
{
    string id=json["id"];  
    string setting_value = json["setting_value"];  
    string setting_name = json["setting_name"];
    string selected="";
    int i=0;
    string option_val = "";
    string option_name = "";
    try{
        if(setting_name=="rfid_reader_port")
        { 
            Php::out<<"<select name='rfid_reader_port' id='"+id+"' value = '"+setting_value+"'>"<<endl;
            Php::out<<"<option value=''></option>"<<endl;
            DIR *dir;
            struct dirent *ent;
            if ((dir = opendir ("/dev/")) != NULL) {
              // print all the files and directories within directory 
              while ((ent = readdir (dir)) != NULL) {
                std::string fname = ent->d_name;
                if(fname.find("tty") != std::string::npos)
                {
                    if(setting_value==fname)
                    {
                        selected = "selected";
                    }
                    else
                    {
                        selected ="";
                    }
                    Php::out<<"<option value='"+fname+"' "+selected+">"+fname+"</option>"<<endl;
                }     
              }
              closedir (dir);
            } 
            Php::out<<"</select>"<<endl;       
        }
        else
            {
            con = General.mysqlConnect(ServerDB);
            stmt=con->createStatement();
            res=stmt->executeQuery("Select setting_type,setting_options,setting_name from system_settings where setting_name = '"+setting_name+"'");
            if(res->next())
                {
                if(res->getString("setting_type")=="dropdown")
                    {
                    
                    Php::out<<"<select name='"+setting_name+"' id='setting"+id+"' value = '"+setting_value+"'>"<<endl;
                    auto list = split(res->getString("setting_options"),',');
                    for(auto data:list)
                        {
                        i=0;
                        selected="";
                        auto option_list = split(data,':');
                        for(auto option:option_list)
                            {   
                            if(i==0)
                               option_val = option;
                            else if(i==1)
                               option_name = option;
                            i++;
                            }
                        Php::out<<setting_value<<endl;
                        Php::out<<option_val<<endl;
                        if(setting_value==option_name)
                            {
                            selected = "selected";
                            }
                        Php::out<<"<option value='"+option_val+"' "+selected+">"+option_name+"</option>"<<endl;               
                        }
                    Php::out<<"</select>"<<endl;                      
                    }
                else if(res->getString("setting_type")=="")                    
                    Php::out<<"<input type = 'text' id = 'setting"+id+"'  value = '"+setting_value+"'>"<<endl;                    
                else
                    Php::out<<"<input type = '"<<res->getString("setting_type")<<"' id = 'setting"+id+"'  value = '"+setting_value+"'>"<<endl;
                    
                    
            }
            delete res;    
            delete stmt;
            delete con; 
        }
    }
    catch(const std::exception& e)
    {
        writeException("editSettings",e.what());
    }

}

Php::Value parcxSettings(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:response = insertUpdateFacility(data);
            break;
        case 2:showFacilityList();
            break;
        case 3:response = enableDisable("system_facility", "facility_id", "status", data);
            break;
        case 4:showCarparkList();
            break;
        case 5:facilityDropdown();
            break;
        case 6:response = insertUpdateCarpark(data);
            break;
        case 7:response = enableDisable("system_carparks", "carpark_id", "status", data);
            break;
        case 8:response = getDetails("system_facility", "facility_id", data);
            break;
        case 9:response = getDetails("system_carparks", "carpark_id", data);
            break;
        case 10:showDeviceList();
            break;
        case 11:response = enableDisable("system_devices", "id", "device_enabled", data);
            break;
        case 12:carparkDropdown();
            break;
        case 13:parkingRateDropdown();
            break;
        case 14:deviceDropdown(data);
            break;
        case 15:response = insertUpdateDevice(data);
            break;
        case 16:response = getDetails("system_devices", "id", data);
            break;
        case 17:showSettingList();
            break;
        case 18:response = insertUpdateSettings(data);
            break;
        case 19:response = enableDisable("system_settings", "settings_id", "setting_status", data);
            break;
        case 20:validatorDropdown();
            break;
        case 21:validationProductDropdown();
            break;
        case 22:showMaintenanceSettingsList();
            break;
        case 23:response = insertUpdateMaintenanceSettings(data);
            break;
        case 24:response = enableDisable("device_maintenance", "id", "enabled", data);
            break;
        case 25:showCloudUploadSettings();
            break;
        case 26:response = updateCloudUploadSettings(data);
            break;
        case 27:response = enableDisable("pxcloud_upload_settings", "id", "enabled", data);
            break;
        case 28:showCloudDownloadSettings();
            break;
        case 29:response = updateCloudDownloadSettings(data);
            break;
        case 30:response = enableDisable("pxcloud_download_settings", "id", "enabled", data);
            break;
        case 31:showProductList();
            break;
        case 32:response = enableDisable("system_products", "product_id", "status", data);
            break;
        case 33:response = insertUpdateSystemProduct(data);
            break;
        case 34:response = getDetails("system_products", "product_id", data);
            break;
        case 35:discountDropdown();
            break;
        case 36:operatorDropdown();
            break;
        case 37:showDiscountList();
            break;
        case 38:response = enableDisable("revenue_discounts", "discount_id", "status", data);
            break;
        case 39:response = getDetails("revenue_discounts", "discount_id", data);
            break;
        case 40:response = insertUpdateDicount(data);
            break;
        case 41:showManualMovementReasons();
            break;
        case 42:response = insertUpdateManualMovementReason(data);
            break;
        case 43:response = getDetails("manual_movements_reasons", "id", data);
            break;
        case 44:response = enableDisable("manual_movements_reasons", "id", "status", data);
            break;
        case 45:showDynamicParkingRates();
            break;
        case 46:response = enableDisable("revenue_dynamic_parking_rates", "id", "status", data);
            break;
        case 47:response = insertUpdateDynamicParkingRate(data);
            break;
        case 48:response = updateParkingRateName(data);
            break;
        case 49:showExportSettings();
            break;
        case 50:response = interfaceDropdown("ssh");
            break;
        case 51:response = interfaceDropdown("ftp");
            break;
        case 52:response = UpdateExportSettings(data);
            break;
        case 53:showInterfaceSettings();
            break;
        case 54:response = getDetails("interface_settings", "id", data);
            break;
        case 55:response = insertUpdateInterfaceSettings(data);
            break;
        case 56:getSettingsInputBox(data);
            break;
        case 57:parkingZoneDropdown(data);
            break;
    }
    return response;    
}

void addParcxUserActivity(Php::Parameters &params)
    {
    try
        {
        string activity_message = params[0];
        string user_name=params[1];
        string user_id=params[2];
        string input=params[3];
        
        con = General.mysqlConnect(ReportingDB);        
        prep_stmt = con->prepareStatement("insert into user_activity(activity_message,input,user_name,user_id,date_time)VALUES(?,?,?,?,CURRENT_TIMESTAMP)");       
        prep_stmt->setString(1, activity_message);
        prep_stmt->setString(2, input);
        prep_stmt->setString(3, user_name);                
        prep_stmt->setString(4, user_id);                

        prep_stmt->executeUpdate();            
        delete prep_stmt;
        delete con;
        }
    catch(const std::exception& e)
        {
        writeException("addParcxUserActivity",e.what());
        }
    }

extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_Settings", "1.0");
        extension.add<parcxV2Configuration>("parcxSettings");
        extension.add<addParcxUserActivity>("addParcxUserActivity");
        return extension;
    }
}
