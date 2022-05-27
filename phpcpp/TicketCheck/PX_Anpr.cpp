#include "PX_GeneralOperations.h"
#include "PX_Anpr.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

using namespace std;
extern GeneralOperations General;
extern string query;

void writeAnprLog(string function, string message) {
    General.writeLog("Services/PXTicketCheck/ApplicationLog-PX-TicketCheck-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeAnprException(string function, string message) {
    General.writeLog("Services/PXTicketCheck/ExceptionLog-PX-TicketCheck-" + General.currentDateTime("%Y-%m-%d"), function, message);
    writeAnprLog(function, "Exception: " + message);
}

Php::Value Anpr::getEntryPlateDetails(int id) {
    Php::Value response;
    response["entry_plate_captured_status"] = "false";
    response["entry_plate_number"]="###";
    response["entry_camera_id"]="0";
    try {
        writeAnprLog("getEntryPlateDetails", "Id:" + to_string(id));
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ReportingDB);
        if (con != NULL) {
            query = "SELECT * from plates_captured where id = " + to_string(id);
            stmt = con->createStatement();
            res = stmt->executeQuery(query);
            if (res->next()) {
                response["entry_plate_captured_status"] = "true";
                response["entry_camera_id"] = string(res->getString("camera_device_number"));
                response["entry_plate_captured_id"] = string(res->getString("id"));
                response["entry_plate_number"] = string(res->getString("plate_number"));
                response["entry_plate_type"] = string(res->getString("plate_type"));
                response["entry_plate_area"] = string(res->getString("plate_area"));
                response["entry_plate_country"] = string(res->getString("plate_country"));
                response["entry_plate_image_name"] = string(res->getString("plate_image_name"));
                writeAnprLog("getEntryPlateDetails", "Plate: " + res->getString("plate_number") + "\tImage: " + res->getString("plate_image_name") + "\n");
            }
            else
                writeAnprLog("getEntryPlateDetails", "No Plate captured\n");
            con->close();
            delete con;
        }
    }    catch (const std::exception& e) {
        writeAnprException("getEntryPlateDetails", e.what());
    }
    return response;
}

Php::Value Anpr::getPlateDetails(int cameraId, int delay, int id) {
    Php::Value response;
    response["result"] = "plate_unavailable";
    response["result_description"] = "Plate unavailable";
    response["plate_captured_id"] = "0";
    response["current_plate_number"] = "###";  
    response["plate_image_name"]="";           
    try {
        writeAnprLog("getPlateDetails", "cameraId:" + to_string(cameraId));
        if (cameraId == 0 && id == 0)
            return response;
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ReportingDB);
        if (con != NULL) {
            if (id != 0)
                query = "SELECT * from plates_captured where id=" + to_string(id);
            else if (delay > 0)
                query = "SELECT * from plates_captured where camera_device_number = '" + to_string(cameraId) + "' and capture_date_time > DATE_ADD(NOW(),INTERVAL -" + to_string(delay) + " SECOND) and capture_date_time < DATE_ADD(NOW(),INTERVAL " + to_string(delay) + " SECOND)  order by id desc limit 1";
            else
                query = "SELECT * from plates_captured where camera_device_number = '" + to_string(cameraId) + "' and capture_date_time > DATE_ADD(NOW(),INTERVAL -10 SECOND) and capture_date_time < DATE_ADD(NOW(),INTERVAL 10 SECOND)  order by id desc limit 1";

            stmt = con->createStatement();
            res = stmt->executeQuery(query);
            if (res->next()) {
                response["result"] = "plate_available";
                response["result_description"] = "Plate available";
                response["plate_captured_id"] = string(res->getString("id"));
                response["plate_number"] = string(res->getString("plate_number"));
                response["current_plate_number"] = string(res->getString("plate_number"));
                response["plate_type"] = string(res->getString("plate_type"));
                response["plate_area"] = string(res->getString("plate_area"));
                response["plate_country"] = string(res->getString("plate_country"));
                response["plate_image_name"] = string(res->getString("plate_image_name"));
                writeAnprLog("getPlateDetails", "id:" + res->getString("id") + "\tPlate: " + res->getString("plate_number") + "\tImage: " + res->getString("plate_image_name") + "\n");
            }
            else
                writeAnprLog("getPlateDetails", "No Plate captured\n");
            con->close();
            delete con;
        }
    }    catch (const std::exception& e) {
        writeAnprException("getPlateDetails", e.what());
    }
    return response;
}

Php::Value Anpr::getAnprSettings(int facility, int carpark) {
    Php::Value response;
    try {
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        if (con != NULL) {
            query = "SELECT * from access_anpr_checks where carpark_number=" + to_string(carpark) + " and facility_number=" + to_string(facility);

            stmt = con->createStatement();
            res = stmt->executeQuery(query);
            if (res->next()) {
                response["check_plate_type"] = res->getInt("check_plate_type");
                response["check_plate_country"] = res->getInt("check_plate_country");
                response["check_plate_area"] = res->getInt("check_plate_area");
                response["check_plate_prefix"] = res->getInt("check_plate_prefix");
                response["check_plate_number"] = res->getInt("check_plate_number");
                response["check_partial_plate_number"] = res->getInt("check_partial_plate_number");
                response["check_plate_number_begining"] = res->getInt("check_plate_number_begining");
                response["check_plate_number_end"] = res->getInt("check_plate_number_end");
                response["allow_O_Q_mismatch"] = res->getInt("allow_O_Q_mismatch");
            } else
                response = "";

            con->close();
            delete con;
        }
    }    catch (const std::exception& e) {
        writeAnprException("getAnprSettings", e.what());
        writeAnprException("getAnprSettings", query);
    }
    return response;
}