#include "PX_GeneralOperations.h"
#include "PX_ParkingReport.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include <phpcpp.h>
#include <cmath>
#include <iostream>
#include <sstream>
#include "validation.h"
#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateTimeFormat "%Y-%m-%d %H:%M:%S"

using namespace std;
extern GeneralOperations General;
Validation validations;
string query;
string entry_type, parking_duration;
string image, croppedImage, sceneImage, datefolder;
string current_date_time;
sql::ResultSet *res, *img, *res2;
size_t n;
int i = 0;

sql::Connection *rCon, *con;
sql::Statement *rStmt, *stmt;

void writeParkingReportLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-ParkingReport-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeParkingReportException(string function, string message) {
    Php::out << message << endl;
    Php::out << query << endl;
    General.writeLog("WebApplication/ExceptionLogs/PX-ParkingReport-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

string ArrayToString(Php::Value json) {

    stringstream ss;
    //ss << "{";
    for (auto &iter : json)
        //ss << "\"" << iter.first << "\":\"" << iter.second << "\",";
        ss <<iter.second << ",";
    string response = ss.str();
    response = response.substr(0, response.size() - 1);
    //response = response.substr(0, response.size() - 1) + "}";
    return response;
}

inline string toString(Php::Value param) {
    string value = param;
    return value;
}

void ParkingReport::openTransactionReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string device = json["device"];
        string val = json["plate_number"];
        string delay = json["parking_duration"];
        string lang = json["language"];
        string from=json["from"];
        string to=json["to"];
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "device_name,transactions,date_time,plate_number,ticket_id,exit_grace_period,image,entry_type,payment_date_time,entry_date_time,no_records,duration,amount_paid,entry_grace_period";
        Php::Value label = General.getLabels(lang, labels);

        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from open_transactions where movement_type=1 ";
        
        if(from != "" && to!="")
            query=query+" AND entry_date_time between '" + from + "' and '" + to + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ")";
        if (delay != "") {
            if (delay == "0")
                query = query + " AND entry_date_time>=DATE_SUB(NOW(),INTERVAL 1 Day)";
            else
                query = query + " AND entry_date_time<=DATE_SUB(NOW(),INTERVAL " + delay + " Day)";

        }
        if (val.length() > 0)
            query = query + " AND(plate_number like '%" + val + "%' OR ticket_id like '%" + val + "%')";

        query = query + " ORDER BY  entry_date_time desc";

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {

            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;


            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            Php::out << "<th>" << label["device_name"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_number"] << "</th>" << endl;
            Php::out << "<th>" << label["ticket_id"] << "</th>" << endl;
            Php::out << "<th>" << label["entry_type"] << "</th>" << endl;
            Php::out << "<th>" << label["entry_date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["duration"] << "</th>" << endl;
            Php::out << "<th>" << label["entry_grace_period"] << "</th>" << endl;
            Php::out << "<th>" << label["amount_paid"] << "</th>" << endl;
            Php::out << "<th>" << label["payment_date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["exit_grace_period"] << "</th>" << endl;
            Php::out << "<th>" << label["image"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            while (res->next()) {
                if (res->getInt("entry_type") == 1)
                    entry_type = "Short Term";
                if (res->getInt("entry_type") == 2)
                    entry_type = "Access";
                if (res->getInt("entry_type") == 3)
                    entry_type = "Reservation";

                croppedImage = "";
                sceneImage = "";

                if (res->getString("plate_captured_id") != "") {
                    query = "select * from plates_captured where id=" + res->getString("plate_captured_id");
                    img = rStmt->executeQuery(query);

                    if (img->next()) {
                        image = img->getString("plate_image_name");
                        while ((n = image.find(" ")) != std::string::npos)
                            image.replace(n, 1, "%20");
                        while ((n = image.find("#")) != std::string::npos)
                            image.replace(n, 1, "%23");

                        datefolder = img->getString("capture_date_time");
                        datefolder = datefolder.substr(0, 10);

                        croppedImage = img->getString("camera_device_number") + "/" + datefolder + "/Crop_" + image;
                        sceneImage = img->getString("camera_device_number") + "/" + datefolder + "/Scene_" + image;
                        delete img;
                    }

                }


                int *duration = General.differenceDateTime(current_date_time, res->getString("entry_date_time"), dateTimeFormat);
                if (duration[1] > 0)
                    parking_duration = to_string(duration[1]) + " D " + to_string(duration[2]) + " H " + to_string(duration[3]) + " M";
                else
                    parking_duration = to_string(duration[2]) + " H " + to_string(duration[3]) + " M";

                Php::out << "<tr>" << endl;
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td>" + entry_type + "</td>" << endl;
                Php::out << "<td>" + res->getString("entry_date_time") + "</td>" << endl;
                Php::out << "<td>" + parking_duration + "</td>" << endl;
                Php::out << "<td>" + res->getString("entry_grace_period") + "</td>" << endl;
                Php::out << "<td>" + res->getString("total_amount_paid") + "</td>" << endl;
                Php::out << "<td>" + res->getString("last_payment_date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("max_exit_date_time") + "</td>" << endl;
                Php::out << "<td><img src='" + toString(json["image_location"]) + "/" + croppedImage + "' width='100' height='50' data-value ='" + sceneImage + "' data-toggle='modal' data-target='#image-modal' alt='" + res->getString("plate_number") + "'></td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("openTransactionReport", e.what());
    }
}


void ParkingReport::parkingMovementsReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string device = json["device"];
        string val = json["plate_number"];
        string lang = json["language"];
        string from=json["from"];
        string to=json["to"];
        
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "transactions,date_time,plate_number,ticket_id,device_name,no_records";
        Php::Value label = General.getLabels(lang, labels);


        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parking_movements where date_time is not null";
        if (from.length() > 0)
            query = query +" AND date_time between '" + from + "' and '" + to + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ")";
        if (val.length() > 0)
            query = query + " AND(plate_number like '%" + val + "%' OR ticket_id like '%" + val + "%')";

        query = query + " ORDER BY  date_time desc";        
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            
            
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << toString(label["transactions"]) << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << toString(label["date_time"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["plate_number"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["ticket_id"]) << "</th>" << endl;            
            Php::out << "<th>" << toString(label["device_name"]) << "</th>" << endl;            

            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {                
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;                
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;                
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("parkingMovementsReport", e.what());
    }
}

void ParkingReport::liveParking(Php::Value json) {
    try {
        string lang = json["language"];
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "date_time,result,device_name,no_records,ticket_id,plate_number";
        Php::Value label = General.getLabels(lang, labels);


        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parcx_dashboard.ticket_check order by id desc limit 50";
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {

            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            Php::out << "<th>" << toString(label["date_time"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["ticket_id"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["plate_number"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["device_name"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["result"]) << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;

            while (res->next()) {
                Php::out << "<tr>" << endl;
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;
                Php::out << "<td> " + res->getString("result_description") + " </td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("liveParking", e.what());
    }
}

void ParkingReport::blacklistReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string device = json["device"];
        string lang = json["language"];
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "ticket_id,transactions,date_time,device_name,blacklisting_reason,description,no_records";
        Php::Value label = General.getLabels(lang, labels);


        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parking_blacklist where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ")";
        query = query + " ORDER BY  date_time desc";

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << toString(label["transactions"]) << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << toString(label["date_time"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["ticket_id"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["device_name"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["blacklisting_reason"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["description"]) << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {               
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;
                Php::out << "<td>" + res->getString("blacklisting_reason") + "</td>" << endl;
                Php::out << "<td>" + res->getString("blacklisting_description") + "</td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("blacklistReport", e.what());
    }
}

void ParkingReport::validationReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string validator = json["validator"];
        string product = json["product"];
        string val = json["plate_number"];
        string lang = json["language"];

        string labels = "transactions,date_time,ticket_id,validator,product,plate_number,no_records";
        Php::Value label = General.getLabels(lang, labels);


        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parking_validation where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (validator.length() > 0)
            query = query + " AND validator_id in(" + validator + ")";
        if (product.length() > 0)
            query = query + " AND product_id in(" + product + ")";
        if (val.length() > 0)
            query = query + " AND(plate_number like '%" + val + "%' OR ticket_id like '%" + val + "%')";

        query = query + " ORDER BY  date_time desc";

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << label["date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["ticket_id"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_number"] << "</th>" << endl;            
            Php::out << "<th>" << label["product"] << "</th>" << endl;                        
            Php::out << "<th>" << label["validator"] << "</th>" << endl;
            
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
           
            while (res->next()) {           
                Php::out << "<tr>" << endl;           
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;  
                Php::out << "<td>" + res->getString("product_name") + "</td>" << endl;
                Php::out << "<td>" + res->getString("validator_name") + "</td>" << endl;                
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("parkingMovementsReport", e.what());
    }
}

void ParkingReport::accessReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string device = json["device"];
        string val = json["plate_number"];
        string option = json["option"];        
        string lang = json["language"];
        string user_name = "";
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "add_to_whitelist,date_time,customer_name,plate_number,tag,status,device_name,carpark_name,ticket_id,no_records,transactions";
        Php::Value label = General.getLabels(lang, labels);

        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parking_movements_access where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ")";
        if (option.length() > 0)
            query = query + " AND status_message like '%" + option + "%'";
        if (val.length() > 0)
            query = query + " AND(plate_number like '%" + val + "%' OR ticket_id like '%" + val + "%' OR tag like '%" + val + "%')";


        query = query + " ORDER BY  date_time desc";
        //Php::out<<query<<endl;
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            con = General.mysqlConnect(ServerDB);
            stmt = con->createStatement();
            query = "select * from access_whitelist";
            res2 = stmt->executeQuery(query);

            delete stmt;
            con->close();
            delete con;

            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;


            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << label["date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["customer_name"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_number"] << "</th>" << endl;
            Php::out << "<th>" << label["ticket_id"] << "</th>" << endl;
            Php::out << "<th>" << label["tag"] << "</th>" << endl;
            Php::out << "<th>" << label["status"] << "</th>" << endl;
            Php::out << "<th>" << label["device_name"] << "</th>" << endl;
            Php::out << "<th>" << label["carpark_name"] << "</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            i = 0;
            string status, tag;
            int whitelist_added;
            while (res->next()) {
                i++;
                status = res->getString("status_message");
                Php::out << "<tr>" << endl;
                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                res2->beforeFirst();
                user_name = "";
                while (res2->next()) {
                    if (res->getString("ticket_id") == res2->getString("ticket_id")) {
                        user_name = res2->getString("customer_name");
                        break;
                    }
                }
                Php::out << "<td>" + user_name + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td>" + res->getString("tag") + "</td>" << endl;
                Php::out << "<td>" + res->getString("status_message") + "</td>" << endl;
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;
                Php::out << "<td>" + res->getString("carpark_name") + "</td>" << endl;
                tag = res->getString("tag");
                whitelist_added = res->getInt("whitelist_added");
                if (status == "TAG UNKNOWN" && whitelist_added == 0 && tag.length() > 2) {
                    //Php::out<<"<td><input data-toggle='modal' data-target='#addToWhiteList' type='botton' class='btn btn-warning btnWhitelistAdd' value='Add to whitelist' data-tag='"<<res->getString("tag")<<"' data-id="<<res->getInt("id")<<"></td>";                        
                    Php::out << "<td><button type='button' class='col btn btn-warning btnWhitelistAdd' data-tag='" << res->getString("tag") << "' data-id=" << res->getInt("id") << ">"<<label["add_to_whitelist"]<<"</button></td>" << std::endl;
                } else
                    Php::out << "<td></td>" << endl;

                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        delete res2;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("accessReport", e.what());
    }
}

void ParkingReport::trackTicket(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string val = json["plate_number"];
        string type = json["type"];
        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from parking_movements where movement_type=1 and date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";

        if (val.length() > 0)
            query = query + " AND(plate_number like '%" + val + "%' OR ticket_id like '%" + val + "%')";

        query = query + " ORDER BY  date_time desc";

        //Php::out<<query<<endl;

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>Transactions</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;


            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "<th>Ticket Id</th>" << endl;
            Php::out << "<th>Plate Number</th>" << endl;
            Php::out << "<th>Entry Date Time</th>" << endl;
            Php::out << "<th>Entry Device</th>" << endl;
            Php::out << "<th>Exit Date Time</th>" << endl;
            Php::out << "<th>Exit Device</th>" << endl;
            Php::out << "<th>Carpark Name</th>" << endl;

            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            i = 0;
            while (res->next()) {
                i++;
                Php::out << "<tr>" << endl;
                Php::out << "<td>" << i << "</td>" << endl;
                Php::out << "<td>" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td> " + res->getString("device_name") + " </td>" << endl;
                if (res->getString("ticket_id") != "") {
                    query = "Select * from parking_movements where movement_type=2 and ticket_id = '" + res->getString("ticket_id") + "' and date_time>'" + res->getString("date_time") + "'";
                    res2 = rStmt->executeQuery(query);
                    if (res2->next()) {
                        Php::out << "<td>" + res2->getString("date_time") + "</td>" << endl;
                        Php::out << "<td> " + res2->getString("device_name") + " </td>" << endl;
                        delete res2;
                    } else {
                        Php::out << "<td></td><td></td>" << endl;
                    }

                }
                Php::out << "<td>" + res->getString("carpark_name") + "</td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
        } else {
            Php::out << "<div class='card p-3'>No record found</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("parkingMovementsReport", e.what());
    }
}

void ParkingReport::visitorFrequencyReport(Php::Value json) {
    string visit_type = json["visit_type"];
    string lang = json["language"];
    Php::Value validation_response;
    sql::PreparedStatement *prep_stmt;
    try 
        {      
        
        validation_response = validations.DataValidation(visit_type,1,20,3,1);
        if(validation_response["result"]==false)
        {
            Php::out<<"<div class='card p-3'>Validation Error in Visit Type : "<<toString(validation_response["reason"]) << "</div>" << endl;
            return;
        }


        string labels = "customer_name,plate_number,ticket_id,no_records,entries,tag,category,last_7_days,last_30_days,last_60_days,last_90_days,last_120_days,last_150_days,last_180_days";
        Php::Value label = General.getLabels(lang, labels);
        rCon = General.mysqlConnect(ReportingDB);
        
        if (visit_type == "all")
            query = "Select * from visitor_frequency where last_visited_date>=( CURDATE() - INTERVAL 180 DAY )";
        else
            query = "Select * from visitor_frequency where category = ? and last_visited_date>=( CURDATE() - INTERVAL 180 DAY )";

        prep_stmt = rCon->prepareStatement(query);
        if (visit_type != "all")
            prep_stmt->setString(1,visit_type);
        res = prep_stmt->executeQuery();
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << toString(label["entries"]) << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            
            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            if (visit_type != "contract")
                Php::out << "<th>" + toString(label["plate_number"]) + "</th>" << endl;
            Php::out << "<th>" + toString(label["ticket_id"]) + "</th>" << endl;
            if(visit_type!="shortterm")	            
              Php::out<<"<th>"+toString(label["customer_name"])+"</th>"<<endl;

            Php::out << "<th>" + toString(label["last_7_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_30_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_60_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_90_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_120_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_150_days"]) + "</th>" << std::endl;
            Php::out << "<th>" + toString(label["last_180_days"]) + "</th>" << std::endl;
            if (visit_type == "all")
                Php::out << "<th>" + toString(label["category"]) + "</th>" << std::endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
           

            while (res->next()) {
                Php::out << "<tr>" << std::endl;              
                if (visit_type != "contract")
                    Php::out << "<td>" + res->getString("plate_number") + "</td>" << std::endl;
                if (res->getString("category") == "contract")
                    Php::out << "<td onclick='tdclick(" + res->getString("ticket_id") + ");'>" + res->getString("ticket_id") + "</td>" << std::endl;
                else
                    Php::out << "<td>" + res->getString("ticket_id") + "</td>" << std::endl;

                if(visit_type!="shortterm")	            
                    Php::out<<"<td>"+res->getString("customer_name")+"</td>"<<endl;
                
                Php::out << "<td>" + res->getString("d7") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d30") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d60") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d90") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d120") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d150") + "</td>" << std::endl;
                Php::out << "<td>" + res->getString("d180") + "</td>" << std::endl;
                if (visit_type == "all")
                    Php::out << "<td>" + res->getString("category") + "</td>" << std::endl;
                Php::out << "</tr>" << std::endl;
                
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete res;
        delete prep_stmt;
        delete rCon;


    } catch (const std::exception& e) {
        writeParkingReportException("visitorFrequencyReport", e.what());
    }
}

void ParkingReport::getPlatesfromTicket(Php::Value json) {
    string ticket_id = json["ticket_id"];
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        query = "select plate_number from access_whitelist where ticket_id='" + ticket_id + "'";
        res = stmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            while (res->next()) {
                Php::out << res->getString("plate_number") << endl;
                Php::out << "</br>" << endl;
            }
        } else {
            Php::out << "No plates found" << endl;
        }
        delete res;
        delete stmt;
        delete con;

    }    catch (const std::exception& e) {
        writeParkingReportException("getPlatesfromTicket", e.what());
    }
}

void ParkingReport::visitorFrequencyRealTimeReport(Php::Value json) {
    string from_date = json["fromDate"];
    string to_date = json["toDate"];
    string visit_type = json["visit_type"];    
    string search_text = json["search_text"];
    string lang = json["language"];
    try {                

        string labels = "customer_name,plate_number,date_time,tag,device_name,ticket_id,no_of_visits,no_records,entries";
        Php::Value label = General.getLabels(lang, labels);

        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();

        if (visit_type == "shortterm") {
            if (search_text == "")
                query = "Select count(plate_number) as count,plate_number from parking_movements where date_time>='" + from_date + "' and date_time<='" + to_date + "' and plate_number!='' and movement_type=1 group by plate_number order by count desc";
            else
                query = "Select count(plate_number) as count,plate_number from parking_movements where date_time>='" + from_date + "' and date_time<='" + to_date + "' and movement_type=1 and plate_number='" + search_text + "'";
        } else {
            if (search_text == "")
                query = "Select count(ticket_id) as count,plate_number,ticket_id,tag from parking_movements_access where date_time>='" + from_date + "' and date_time<='" + to_date + "' and movement_type=1 group by ticket_id order by count desc";
            else
                query = "Select count(ticket_id) as count,plate_number,ticket_id,tag from parking_movements_access where date_time>='" + from_date + "' and date_time<='" + to_date + "' and movement_type=1  and (plate_number='" + search_text + "' or ticket_id='" + search_text + "' or tag='" + search_text + "' )";
        }

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << toString(label["entries"]) << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            if (visit_type == "shortterm")
                Php::out << "<div class='card col-md-6'>" << endl;
            else
                Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            
            Php::out << "<th>" << toString(label["plate_number"]) << "</th>" << std::endl;

            if (visit_type == "contract") {
                Php::out << "<th>" << toString(label["customer_name"]) << "</th>" << std::endl;
                Php::out << "<th>" << toString(label["ticket_id"]) << "</th>" << std::endl;
                Php::out << "<th>" << toString(label["tag"]) << "</th>" << std::endl;
            }

            Php::out << "<th>" << toString(label["no_of_visits"]) << "</th>" << std::endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            n = 1;

            while (res->next()) {
                Php::out << "<tr>" << std::endl;                
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << std::endl;
                if (visit_type == "contract") {
                    query="select customer_name from parcx_server.access_whitelist where ticket_id='"+res->getString("ticket_id")+"'";
                    res2 = rStmt->executeQuery(query);
                    if(res2->next())
                        Php::out << "<td>" + res2->getString("customer_name") + "</td>" << std::endl;
                    else
                        Php::out << "<td></td>" << std::endl;
                    delete res2;
                    Php::out << "<td>" + res->getString("ticket_id") + "</td>" << std::endl;
                    Php::out << "<td>" + res->getString("tag") + "</td>" << std::endl;
                }
                Php::out << "<td>" + res->getString("count") + "</td>" << std::endl;
                Php::out << "</tr>" << std::endl;
                n++;
            }
            Php::out << "</tbody></table>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete res;
        delete rStmt;
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("visitorFrequencyRealTimeReport", e.what());
    }
}

void ParkingReport::watchdogNetworkLogs(Php::Value json) {
    
    try {
        string from = json["from"];
        string to = json["to"];
        string lang = json["language"];
        string carpark = json["carpark"];
        string device = json["device"];
        
        string labels = "device_name,ip_address,connection_lost,connection_restored,carpark_name,no_records";
        Php::Value label = General.getLabels(lang, labels);
        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from watchdog_network_logs where connection_failure between '" + from + "' AND '" + to + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number in(" + carpark + ")";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ")";
        
        query = query + "ORDER BY connection_failure ASC";

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
                    
            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            
            Php::out << "<th>" << label["device_name"] << "</th>" << endl;
            Php::out << "<th>" << label["ip_address"] << "</th>" << endl;
            Php::out << "<th>" << label["connection_lost"] << "</th>" << endl;
            Php::out << "<th>" << label["connection_restored"] << "</th>" << endl;
            Php::out << "<th>" << label["carpark_name"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            while (res->next()) {
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("device_name") + "</td>" << endl;
                Php::out << "<td>" + res->getString("device_ip") + "</td>" << endl;
                Php::out << "<td> " + res->getString("connection_failure") + " </td>" << endl;
                Php::out << "<td> " + res->getString("connection_restored") + " </td>" << endl;
                Php::out << "<td> " + res->getString("carpark_name") + " </td>" << endl;
                Php::out << "</tr>" << endl;
                
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("watchdogNetworkLogs", e.what());
    }
}

void ParkingReport::watchDogDeviceAlarms(Php::Value json) {
    try {
        string from = json["from"];
        string to = json["to"];
        string device = json["device"];
        string severity = json["severity"];
        string lang = json["language"];
        
        string labels = "date_time,device_name,severity,description,high,medium,low,no_records";
        Php::Value label = General.getLabels(lang, labels);
        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from watchdog_device_alarms where alarm_date_time between '" + from + "' AND '" + to + "'";
        if (device.length() > 0)
            query = query + " AND device_number in(" + device + ") ";
        if (severity != "0")
            query = query + " AND alarm_severity " + severity + " ";
        query = query + "ORDER BY alarm_date_time DESC";

        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;

            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << label["date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["device_name"] << "</th>" << endl;
            Php::out << "<th>" << label["severity"] << "</th>" << endl;
            Php::out << "<th>" << label["description"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            string temp = "";
            while (res->next()) {
                if (res->getInt("alarm_severity") >= 1 && res->getInt("alarm_severity") <= 3)
                    temp = "<span class='badge badge-danger'>" + toString(label["high"]) + "</span>";
                else if (res->getInt("alarm_severity") >= 4 && res->getInt("alarm_severity") <= 7)
                    temp = "<span class='badge badge-warning'>" + toString(label["medium"]) + "</span>";
                else if (res->getInt("alarm_severity") >= 8 && res->getInt("alarm_severity") <= 10)
                    temp = "<span class='badge badge-secondary'>" + toString(label["low"]) + "</span>";
                Php::out << "<tr>" << endl;
                
                Php::out << "<td>" + res->getString("alarm_date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("device_name") + "</td>" << endl;
                Php::out << "<td> " + temp + " </td>" << endl;
                Php::out << "<td> " + res->getString("alarm_description") + " </td>" << endl;
                Php::out << "</tr>" << endl;
               
            }
            Php::out << "</tbody></table></div></div>" << endl;
            
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("watchDogDeviceAlarms", e.what());
    }
}

void ParkingReport::manualmovementReport(Php::Value json) {
    try {
        string carpark = json["carpark"];
        string device=json["device"];
        int operation = json["operation"];
        string lang = json["language"];
        int limit = json["limit"];

        string labels = "date_time,operator_name,action,reason,description,device_name,no_records,transactions";
        Php::Value label = General.getLabels(lang, labels);

        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();

        if (limit > 0)
            query = "select * from parking_movements_manual ORDER BY  date_time desc LIMIT " + to_string(limit);
        else {
            query = "select * from parking_movements_manual where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
            if (carpark.length() > 0)
                query = query + " AND carpark_number in(" + carpark + ")";
            
            if (device.length() > 0)
                query = query + " AND device_number in(" + device + ")";

            if (operation>0)
                {
                if(operation==1)
                    query = query + " AND action='Barrier Open'";
                else if(operation==2)
                    query = query + " AND action like 'Barrier Open%' and action like '%server'";
                else if(operation==3)
                    query = query + " AND Description='External input barrier open'";
                else if(operation==4)
                    query = query + " AND action like 'Barrier close%'";
                else if(operation==5)
                    query = query + " AND action like 'Free Passage Mode%'";
                else if(operation==6)
                    query = query + " AND action like 'Standard Operation Mode%'";
                else if(operation==7)
                    query = query + " AND action like 'Lane Closed Mode%'";
                }


            query = query + " ORDER BY  date_time desc";
        }


        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            if (limit == 0) {
                Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
                Php::out << "<div class='col-lg-3 col-6'>" << endl;
                Php::out << "<div class='small-box bg-success'>" << endl;
                Php::out << "<div class='inner'>" << endl;
                Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
                Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
                Php::out << "</div>" << endl;
                Php::out << "</div>" << endl;
                Php::out << "</div>" << endl;
                Php::out << "</div>" << endl;
            }

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            
            Php::out << "<th>" << label["date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["device_name"] << "</th>" << endl;
            Php::out << "<th>" << label["operator_name"] << "</th>" << endl;
            Php::out << "<th>" << label["action"] << "</th>" << endl;
            Php::out << "<th>" << label["reason"] << "</th>" << endl;
            Php::out << "<th>" << label["description"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;

            
            while (res->next()) {                
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("device_name") + "</td>" << endl;
                Php::out << "<td> " + res->getString("operator_name") + " </td>" << endl;
                Php::out << "<td>" + res->getString("action") + "</td>" << endl;
                Php::out << "<td>" + res->getString("reason") + "</td>" << endl;
                Php::out << "<td>" + res->getString("description") + "</td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {

            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("manualmovmentReport", e.what());
    }
}

string str_replace(string str, string find_str, string replace_str) {
    int length = str.size();
    int x = 0;
    string str_result;

    while (x != length) {
        string letter = str.substr(x, 1);
        if (letter == find_str) {
            letter = replace_str;
        }
        str_result = str_result + letter;
        x = x + 1;
    }

    return str_result;
}

void ParkingReport::platescapturedReport(Php::Value json) {    
    string lang = json["language"];
    string ImageURL = json["imageurl"];
    string device = json["device"];
    try {


        string labels = "confidence_rate,transactions,date_time,plate_number,plate_area,plate_country,cropped_image,full_image,camera_name,no_records";
        Php::Value label = General.getLabels(lang, labels);

        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from plates_captured where capture_date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";

        if (device.length() > 0)
            query = query + " and camera_device_number in(" + device + ")";

        if (toString(json["plate"]) != "")
            query = query + " and plate_number like '%" + toString(json["plate"]) + "%'";

        if (toString(json["type"]) != "")
            query = query + " and plate_type = '" + toString(json["type"]) + "'";

        if (toString(json["area"]) != "")
            query = query + " and plate_area = '" + toString(json["area"]) + "'";

        if (toString(json["country"]) != "")
            query = query + " and plate_country = '" + toString(json["country"]) + "'";

        query = query + " order by capture_date_time desc";


        res = rStmt->executeQuery(query);
        
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << label["camera_name"] << "</th>" << endl;
            Php::out << "<th>" << label["date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_number"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_area"] << "</th>" << endl;
            Php::out << "<th>" << label["plate_country"] << "</th>" << endl;
            Php::out << "<th>" << label["confidence_rate"] << "</th>" << endl;
            Php::out << "<th>" << label["cropped_image"] << "</th>" << endl;
            Php::out << "<th>" << label["full_image"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;            
            string plate_image, plate_date;
            while (res->next()) 
                {                
                Php::out << "<tr>" << endl;               
                Php::out << "<td>" << endl;

                Php::out << res->getString("camera_name") << endl;

                plate_image = res->getString("plate_image_name");
                plate_image = str_replace(plate_image, " ", "%20");
                plate_image = str_replace(plate_image, "#", "%23");

                plate_date = res->getString("capture_date_time");
                plate_date = plate_date.substr(0, 10);


                Php::out << "</td>" << endl;
                Php::out << "<td>" + res->getString("capture_date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td> " + res->getString("plate_area") + " </td>" << endl;
                Php::out << "<td>" + res->getString("plate_country") + "</td>" << endl;
                Php::out << "<td>" + res->getString("confidence_rate") + "</td>" << endl;
                Php::out << "<td><img src='" + ImageURL + "\\" + res->getString("camera_device_number") + "\\" + plate_date + "\\Crop_" + plate_image + "' width='100' ; height='50' alt='"+res->getString("plate_number")+"' ;></td>" << endl;
                Php::out << "<td><input type='button' data-value = " + ImageURL + "\\" + res->getString("camera_device_number") + "\\" + plate_date + "\\Scene_" + plate_image + " data-toggle='modal' data-target='#image-modal' value='View' class='btn btn-link'></td>" << endl;

                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {

            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("platescapturedReport", e.what());
    }
}

void ParkingReport::platesCorrectedReport(Php::Value json) {
    
    string lang = json["language"];
    string ImageURL = json["imageurl"];
    string device = json["device"];
    try {


        string labels = "reason,operator_name,transactions,entry_date_time,initial_plate_number,corrected_plate_number,corrected_date_time,plate_country,cropped_image,full_image,camera_name,no_records";
        Php::Value label = General.getLabels(lang, labels);

        current_date_time = General.currentDateTime(dateTimeFormat);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from plates_captured where plate_corrected_date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "' and initial_plate_number is not NULL";

        if (device.length() > 0)
            query = query + " and camera_device_number in(" + device + ")";

        query = query + " order by capture_date_time desc";


        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>" << label["transactions"] << "</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << label["camera_name"] << "</th>" << endl;
            Php::out << "<th>" << label["entry_date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["initial_plate_number"] << "</th>" << endl;
            Php::out << "<th>" << label["corrected_plate_number"] << "</th>" << endl;
            Php::out << "<th>" << label["corrected_date_time"] << "</th>" << endl;
            Php::out << "<th>" << label["reason"] << "</th>" << endl;
            Php::out << "<th>" << label["operator_name"] << "</th>" << endl;
            Php::out << "<th>" << label["cropped_image"] << "</th>" << endl;
            Php::out << "<th>" << label["full_image"] << "</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;            
            while (res->next()) {            
                Php::out << "<tr>" << endl;  
                Php::out << "<td>" + res->getString("camera_name") + "</td>" << endl;
                
                string plate_image = res->getString("plate_image_name");
                plate_image = str_replace(plate_image, " ", "%20");
                plate_image = str_replace(plate_image, "#", "%23");

                string plate_date = res->getString("capture_date_time");
                plate_date = plate_date.substr(0, 10);
               
                Php::out << "<td>" + res->getString("capture_date_time") + "<input type='hidden' id = 'date" + to_string(i) + "' value = '" + plate_date + "'><input type='hidden' id = 'camera_no" + to_string(i) + "' value = '" + res->getString("camera_device_number") + "'><input type='hidden' id = 'plate_image" + to_string(i) + "' value = '" + res->getString("plate_image_name") + "'></td>" << endl;
                Php::out << "<td>" + res->getString("initial_plate_number") + "</td>" << endl;
                Php::out << "<td>" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td> " + res->getString("plate_corrected_date_time") + " </td>" << endl;     
                Php::out << "<td> " + res->getString("correction_type") + " </td>" << endl;                
                Php::out << "<td>" + res->getString("user_name") + "</td>" << endl;
                Php::out << "<td><img src='" + ImageURL + "\\" + res->getString("camera_device_number") + "\\" + plate_date + "\\Crop_" + plate_image + "' width='100' ; height='50' ;></td>" << endl;
                Php::out << "<td><input type='button' data-value = " + to_string(i) + " data-toggle='modal' data-target='#image-modal' value='View' class='btn btn-link'></td>" << endl;

                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {

            Php::out << "<div class='card p-3'>" << label["no_records"] << "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("platescapturedReport", e.what());
    }
}

void ParkingReport::getanprImage(Php::Value json) {
    string plate_image = json["plate_image"];

    plate_image = str_replace(plate_image, " ", "%20");
    plate_image = str_replace(plate_image, "#", "%23");
    string camera_id = json["camera_no"];
    string plate_date = json["date"];
    string ImageURL = json["imageurl"];
    Php::out << "<div>" << endl;
    //int html_data+='<img src ="'.ImageURL.'/default.jpg" width="100%"; height="500";>';
    Php::out << "<img src ='" + ImageURL + "\\" + camera_id + "\\" + plate_date + "\\Scene_" + plate_image + "' width='100%'; height='700';>" << endl;
    ;
    Php::out << "</div>" << endl;


}

string Addtime(string sdate) {
    char out[30];
    string addtime;
    struct tm tm;
    strptime(sdate.c_str(), "%Y-%m-%d %H:%M:%S", &tm);    
    tm.tm_sec = tm.tm_sec + 3600;
    //time_t t = mktime(&tm);
    mktime(&tm);
    strftime(out, 30, "%Y-%m-%d %H:%M:%S", &tm);
    string stime(out);
    addtime = stime;
    return addtime;
}

string AddDate(string sdate) {
    char out[30];
    string adddate;

    struct tm tm;
    strptime(sdate.c_str(), "%Y-%m-%d %H:%M:%S", &tm);    
    tm.tm_sec += 86400;
    //time_t t = mktime(&tm);
    mktime(&tm);
    strftime(out, 30, "%Y-%m-%d %H:%M:%S", &tm);
    string stime(out);
    adddate = stime;


    return adddate;


}

string getDayClosureStartTime() {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    string dayclosure_start = "00:00:00";
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        string query = "select setting_value from system_settings where setting_name = 'dayclosure_start_time'";
        res = stmt->executeQuery(query);
        if (res->next()) {
            dayclosure_start = res->getString("setting_value");
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeParkingReportException("getDayClosureStartTime", e.what());
    }
    return dayclosure_start;
}

void ParkingReport::trafficReport(Php::Value json) {
    int show_date_by_report = 0;
    Php::Value datearr, day_report;
    int entry_count[24];
    int exit_count[24];
    int access_entry[24];
    int access_exit[24];
    int manualopen_count[24];
    int manualclose_count[24];
   
    try {
        string from = json["fromDate"]; //"2020-12-01 00:00:00";
        string to = json["toDate"]; //"2020-12-30 00:00:00";
        string carpark = json["carpark"];
        string weekdays = json["weekdays"];
        string dayclosure = getDayClosureStartTime(); 
        string temp_date;
        string lang = json["language"];
       
        int *d = General.differenceDateTime(to, from, "%Y-%m-%d");       
        string labels = "traffic_report,report_by_date,entries,short_term,exits,manual_operation,entry_exit,report_date,shortterm_entries,shortterm_exits,contract_entries,contract_exits,manual_entry,manual_exit,from,to,total,no_records";
        Php::Value label = General.getLabels(lang, labels);        
        datearr[0] = from.substr(0, 10);

        i = 1;
        temp_date = from + " 00:00:00";
        string date_add;
        string from1;
                
        while (i <= d[1]) {            
            date_add = AddDate(temp_date);
            from1 = date_add.substr(0, 10);
            datearr[i] = from1;            
            temp_date = date_add;
            i++;
        }
        for (int k = 0; k < datearr.size(); k++) {
            string key = datearr[k];
            day_report[key]["short_term_entry"] = 0;
            day_report[key]["short_term_exit"] = 0;
            day_report[key]["access_entry"] = 0;
            day_report[key]["access_exit"] = 0;
            day_report[key]["manual_entry"] = 0;
            day_report[key]["manual_exit"] = 0;
        }
      

        if (d[1] > 1) {
            show_date_by_report = 1;
        }



        //string current_date = General.currentDateTime("%Y-%m-%d %H:%M:%S");
        int total_entry = 0;
        int total_exit = 0;
        int total_mopen = 0;
        int total_mclose = 0;
        int access_entry_total = 0;
        int access_exit_total = 0;
        

        for (i = 0; i < 24; i++) {
            entry_count[i] = 0;
            exit_count[i] = 0;
            manualopen_count[i] = 0;
            manualclose_count[i] = 0;
        
            access_entry[i] = 0;
            access_exit[i] = 0;
        }





        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select * from summary_parking_movements where  report_date between '" + from + "' and '" + to + "'";
        if (carpark.length() > 0)
            query = query + " AND carpark_number IN (" + carpark + ")";

        if (weekdays.length() > 0)
            query = query + " AND report_day IN (" + weekdays + ")";

        res = rStmt->executeQuery(query);        
        int category;
        int movement_type;
        string day, field;
        int temp = 0;
        string  summary = "", header = "", html_data = "";
        if (res->rowsCount() > 0) {
            while (res->next()) {
                category = res->getInt("category");
                movement_type = res->getInt("movement_type");
                day = res->getString("report_date");


                switch (category) {
                    case 1:
                        if (movement_type == 1) {
                            temp = day_report[day]["short_term_entry_count"];
                            day_report[day]["short_term_entry_count"] = res->getInt("total_count") + temp;
                        }
                        if (movement_type == 2) {
                            temp = day_report[day]["short_term_exit_count"];
                            day_report[day]["short_term_exit_count"] = res->getInt("total_count") + temp;
                        }
                        break;
                    case 2:
                        if (movement_type == 1) {
                            temp = day_report[day]["access_entry_count"];
                            day_report[day]["access_entry_count"] = res->getInt("total_count") + temp;
                        }
                        if (movement_type == 2) {
                            temp = day_report[day]["access_exit_count"];
                            day_report[day]["access_exit_count"] = res->getInt("total_count") + temp;
                        }
                        break;
                    case 3:
                        if (movement_type == 1 || movement_type == 3) {
                            temp = day_report[day]["manual_entry_count"];
                            day_report[day]["manual_entry_count"] = res->getInt("total_count") + temp;
                        }
                        if (movement_type == 2 || movement_type == 4) {
                            temp = day_report[day]["manual_exit_count"];
                            day_report[day]["manual_exit_count"] = res->getInt("total_count") + temp;
                        }
                        break;
                }


                i = 0;

                while (i < 24) {
                    field = "h" + to_string(i) + "to" + to_string((i + 1));
                    switch (category) {
                        case 1:
                            if (movement_type == 1) {
                                entry_count[i] = res->getInt(field) + entry_count[i];
                                total_entry = res->getInt(field) + total_entry;
                            }
                            if (movement_type == 2) {
                                exit_count[i] = res->getInt(field) + exit_count[i];
                                total_exit = total_exit + res->getInt(field);
                            }
                            break;

                        case 2:
                            if (movement_type == 1) {
                                access_entry[i] = res->getInt(field) + access_entry[i];
                                access_entry_total = access_entry_total + res->getInt(field);
                            }
                            if (movement_type == 2) {
                                access_exit[i] = res->getInt(field) + access_exit[i];
                                access_exit_total = access_exit_total + res->getInt(field);
                            }
                            break;

                        case 3:
                            if (movement_type == 1 || movement_type == 3) {
                                manualopen_count[i] = res->getInt(field) + manualopen_count[i];
                                total_mopen = total_mopen + res->getInt(field);
                            }
                            if (movement_type == 2 || movement_type == 4) {
                                manualclose_count[i] = res->getInt(field) + manualclose_count[i];
                                total_mclose = res->getInt(field) + total_mclose;
                            }
                            break;

                        

                    }
                    i++;
                }

            }

            summary = "<div class='row mb-4 jspdf-graph'>";
            summary += "<div class='col-lg-3 col'>";
            summary += "<div class='small-box bg-success'>";
            summary += "<div class='inner'>";
            summary += "<h3>" + to_string(total_entry) + "/" + to_string(access_entry_total) + "</h3>";
            summary += "     <p>" + toString(label["entries"]) + "<br>[" + toString(label["short_term"]) + "]</p>";
            summary += "    </div>";
            summary += "    <div class='icon'>";
            summary += "    <i class='ion ion-pie-graph'></i>";
            summary += "   </div>";
            summary += " </div>";
            summary += " </div>";
            summary += " <div class='col-lg-3 col'>";
            summary += "  <div class='small-box bg-success'>";
            summary += "    <div class='inner'>";
            summary += "     <h3>" + to_string(total_exit) + "/" + to_string(access_exit_total) + "</h3>";
            summary += "    <p>" + toString(label["exits"]) + "<br>[" + toString(label["short_term"]) + "]</p>";
            summary += "  </div>";
            summary += "  <div class='icon'>";
            summary += "  <i class='ion ion-pie-graph'></i>";
            summary += "  </div>";
            summary += "</div>";
            summary += " </div>";

            summary += "   <div class='col-lg-3 col'>";
            summary += "<div class='small-box bg-success'>";
            summary += "  <div class='inner'>";
            summary += "     <h3>" + to_string(total_mopen) + "/" + to_string(total_mclose) + "</h3>";
            summary += "   <p>" + toString(label["manual_operation"]) + "<br>" + toString(label["entry_exit"]) + "</p>";
            summary += " </div>";
            summary += " <div class='icon'>";
            summary += " <i class='ion ion-pie-graph'></i>";
            summary += " </div>";
            summary += "</div>";
            summary += "</div>";

            summary += "</div> <!--End. Row -->";

            Php::out << summary << endl;

            // End . Traffic Summary 
            
            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
         
            //Traffic report
            header = "<div class='header pl-0 mt-4 mb-3'>" + toString(label["traffic_report"]) + "</div>";
            header += "<table id='TABLE_2' class='table  table-bordered jspdf-table RecordsTableclass'>";
            header += "<thead class='thead-light'><tr>";

            header += "<th>" + toString(label["from"]) + "</th>";
            header += "<th>" + toString(label["to"]) + "</th>";
            header += "<th>" + toString(label["shortterm_entries"]) + "</th>";
            header += "<th>" + toString(label["shortterm_exits"]) + "</th>";
            header += "<th>" + toString(label["contract_entries"]) + "</th>";
            header += "<th>" + toString(label["contract_exits"]) + "</th>";
            header += "<th>" + toString(label["manual_entry"]) + "</th>";
            header += "<th>" + toString(label["manual_exit"]) + "</th>";

            header += "</tr></thead>";

            html_data = header;
            html_data += "<tbody>";

            Php::out << html_data << endl;

            html_data = "";
            i = 0;
            string first_hour = dayclosure.substr(0, 2);
            int h = stoi(first_hour);
            string time_from = "2020-01-01 " + dayclosure;
            string time_to;
            int entryData[24];
            int exitData[24];
            string hour_data="";
            while (i < 24) {
                // data for chart                

                entryData[i] = entry_count[i] + access_entry[i];
                exitData[i] = exit_count[i] + access_exit[i];               
                
                time_to = Addtime(time_from);                
                hour_data=time_from.substr(11, 5)+"\n"+time_to.substr(11, 5);
              
                html_data += "<span display='none' id='chartData_" + to_string(i) + "' data-hour='"+hour_data+"' data-entry='" + to_string(entryData[i]) + "' data-exit='" + to_string(exitData[i]) + "'></span>";

                html_data += "<tr>";

                html_data += "<td>" + time_from.substr(11, 8) + " </td>";
                html_data += "<td>" + time_to.substr(11, 8) + " </td>";
                html_data += "<td>" + to_string(entry_count[i]) + " </td>";
                html_data += "<td>" + to_string(exit_count[i]) + " </td>";
                html_data += "<td>" + to_string(access_entry[i]) + " </td>";
                html_data += "<td>" + to_string(access_exit[i]) + " </td>";
                html_data += "<td>" + to_string(manualopen_count[i]) + " </td>";
                html_data += "<td>" + to_string(manualclose_count[i]) + " </td>";

                html_data += "</tr>";

                time_from = time_to;

                i++;
                h++;
                if (h == 23) {
                    h = 0;
                }
            }

            html_data += "<tr>";

            html_data += "<td>" + toString(label["total"]) + "</td>";
            html_data += "<td></td>";
            html_data += "<td>" + to_string(total_entry) + "</td>";
            html_data += "<td>" + to_string(total_exit) + "</td>";
            html_data += "<td>" + to_string(access_entry_total) + "</td>";
            html_data += "<td>" + to_string(access_exit_total) + "</td>";
            html_data += "<td>" + to_string(total_mopen) + "</td>";
            html_data += "<td>" + to_string(total_mclose) + "</td>";

            html_data += "</tr></tbody>";

            html_data += "</table>";
           
            Php::out << html_data << endl;
            
            
            /*Date by report*/
            if (show_date_by_report == true) {                
                header = "<div class='header pl-0 mt-4 mb-3'>" + toString(label["report_by_date"]) + "</div>";
                header += "<table id='TABLE_1' class='table  table-bordered jspdf-table RecordsTableclass'>";
                header += "<thead class='thead-light'><tr><th>" + toString(label["report_date"]) + "</th>";
                header += "<th>" + toString(label["shortterm_entries"]) + "</th>";
                header += "<th>" + toString(label["shortterm_exits"]) + "</th>";
                header += "<th>" + toString(label["contract_entries"]) + "</th>";
                header += "<th>" + toString(label["contract_exits"]) + "</th>";
                header += "<th>" + toString(label["manual_entry"]) + "</th>";
                header += "<th>" + toString(label["manual_exit"]) + "</th>";
                header += "</tr></thead>";                
                html_data = header;
                html_data += "<tbody>";



                for (int k = datearr.size() - 1; k >= 0; k--) {
                    html_data += "<tr>";
                    string key = datearr[k];
                    html_data += "<td>" + key + "</td>";
                    if (toString(day_report[key]["short_term_entry_count"]) == "") {
                        day_report[key]["short_term_entry_count"] = 0;
                    }
                    if (toString(day_report[key]["short_term_exit_count"]) == "") {
                        day_report[key]["short_term_exit_count"] = 0;
                    }
                    if (toString(day_report[key]["access_entry_count"]) == "") {
                        day_report[key]["access_entry_count"] = 0;
                    }
                    if (toString(day_report[key]["access_exit_count"]) == "") {
                        day_report[key]["access_exit_count"] = 0;
                    }
                    if (toString(day_report[key]["manual_entry_count"]) == "") {
                        day_report[key]["manual_entry_count"] = 0;
                    }
                    if (toString(day_report[key]["manual_exit_count"]) == "") {
                        day_report[key]["manual_exit_count"] = 0;
                    }
                    html_data += "<td>" + toString(day_report[key]["short_term_entry_count"]) + "</td>";
                    html_data += "<td>" + toString(day_report[key]["short_term_exit_count"]) + " </td>";
                    html_data += "<td>" + toString(day_report[key]["access_entry_count"]) + " </td>";
                    html_data += "<td>" + toString(day_report[key]["access_exit_count"]) + " </td>";
                    html_data += "<td>" + toString(day_report[key]["manual_entry_count"]) + " </td>";
                    html_data += "<td>" + toString(day_report[key]["manual_exit_count"]) + " </td>";
                    html_data += "</tr>";
                }




                html_data += "<tr>";
                html_data += "<td>" + toString(label["total"]) + "</td>";
                html_data += "<td>" + to_string(total_entry) + "</td>";
                html_data += "<td>" + to_string(total_exit) + "</td>";
                html_data += "<td>" + to_string(access_entry_total) + "</td>";
                html_data += "<td>" + to_string(access_exit_total) + "</td>";
                html_data += "<td>" + to_string(total_mopen) + "</td>";
                html_data += "<td>" + to_string(total_mclose) + "</td>";
                html_data += "</tr>";

                html_data += "</table><br>";
                Php::out << html_data << endl;
            }

            Php::out << "</div></div>" << endl;

        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }
        //echo html_data;   

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;

    } catch (const std::exception& e) {
        writeParkingReportException("trafficReport", e.what());
    }

}



int* get_parking_duration(string fromdate, string todate, string carpark, string category, string weekdays, int* total_count) {
    //static int total_count[30];
    string field, query_string;
    int i = 0;
    int j = 0;
    int k = 1;

    rCon = General.mysqlConnect(ReportingDB);
    rStmt = rCon->createStatement();
    query_string = "select sum(m0to30) as m0to30,sum(m30to60) as m30to60";

    i = 1;
    while (i < 24) {
        query_string += ",sum(h" + to_string(i) + "to" + to_string((i + 1)) + ") as h" + to_string(i) + "to" + to_string((i + 1));
        i++;
    }
    k = 1;
    while (k < 3) {
        query_string += ",sum(d" + to_string(k) + "to" + to_string((k + 1)) + ") as d" + to_string(k) + "to" + to_string((k + 1));
        k++;
    }

    query_string += ",max(maximum_parking_duration) as max_parking_duration,min(nullif(minimum_parking_duration,0)) as min_parking_duration,avg(average_parking_duration) as avg_parking_duration  from parking_duration where report_date between '" + fromdate + "' AND '" + todate + "' and category='" + category + "'";

    if (carpark.length() > 0) {
        if (carpark.length() > 0)
            query_string = query_string + " AND carpark_number in(" + carpark + ")";

    }

    if (weekdays.length() > 0)
        query_string = query_string + " AND report_day IN (" + weekdays + ")";

    res = rStmt->executeQuery(query_string);
    //Php::out<<query_string<<endl;

    if (res->next()) {

        total_count[0] = res->getInt("m0to30");

        total_count[1] = res->getInt("m30to60");
        i = 1;
        j = 2;
        //get count for hours
        while (i < 24) {
            field = "h" + to_string(i) + "to" + to_string((i + 1));
            //Php::out<<field<<":"<<res->getInt(field)<<endl;
            total_count[j] = res->getInt(field);
            i++;
            j++;
        }

        //get count for days      
        k = 1;
        while (k < 3) {
            field = "d" + to_string(k) + "to" + to_string((k + 1));
            total_count[j] = res->getInt(field);
            k++;
            j++;
        }
        total_count[j++] = res->getInt("max_parking_duration");
        total_count[j++] = res->getInt("min_parking_duration");
        total_count[j++] = res->getInt("avg_parking_duration");

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;

        return total_count;

    } else {
        i = 0;
        while (i < 15) {
            total_count[i] = 0;
            i++;
        }

        return total_count;
    }


} // End get parking duration

void ParkingReport::parkingDuration(Php::Value json) {  
    string duration_label[29];
    string duration_chart_label[29];
    int contract[30];
    int shortterm[30];
    int max_dur_short_term;
    int max_dur_contract;
    int min_dur_short_term;
    int min_dur_contract;
    int avg_dur_short_term;
    int avg_dur_contract;

    int count1 = 0, count2 = 0;
    int i = 0;
    int j = 0;
    string html_data = "";
    double temp = 0, temp2;
    int temp1, temp3;
    string header = "";

    try {
        string from = json["fromDate"]; //"2020-12-01 00:00:00";
        string to = json["toDate"]; //"2020-12-30 00:00:00";
        string carpark = json["carpark"];
        string weekdays = json["weekdays"];       
        string lang = json["language"];


        string labels = "hours,minutes,days,maximum_parking_duration,minimum_parking_duration,average_parking_duration,shortterm,contract,shortterm_parkers,contract_parkers,duration,no_records";
        Php::Value label = General.getLabels(lang, labels);





        int* count_shortterm = get_parking_duration(from, to, carpark, "shortterm", weekdays, shortterm);        
        for (int n = 0; n < 30; n++) {            
            count1 += count_shortterm[n];
        }

        int* count_contract = get_parking_duration(from, to, carpark, "longterm", weekdays, contract);
        for (n = 0; n < 30; n++) {            
            count2 += count_contract[n];
        }
                

        //CREATE THE DURATION LABELS 
        duration_label[0] = "0 - 30 "+toString(label["minutes"]);
        duration_label[1] = "30 - 60 "+toString(label["minutes"]);

        duration_chart_label[0] = "0 - 30\n"+toString(label["minutes"]);
        duration_chart_label[1] = "30 - 60\n"+toString(label["minutes"]);

        i = 2;

        while (i <= 24) {
            duration_label[i] = to_string((i - 1)) + " - " + to_string(i) + " "+toString(label["hours"]);
            duration_chart_label[i] = to_string((i - 1)) + " - " + to_string(i) + "\n" + toString(label["hours"]);
            i++;
        }

        j = 1;
        while (j < 3) {
            duration_label[i] = to_string(j) + " - " + to_string((j + 1)) + " "+toString(label["days"]);
            duration_chart_label[i] = to_string(j) + " - " + to_string((j + 1)) + "\n" + toString(label["days"]);
            i++;
            j++;
        }

        if ((count1 > 0) || (count2 > 0)) {

            html_data = "";

            // create display minutes/hours text for info box

            max_dur_short_term = count_shortterm[27];
            max_dur_contract = count_contract[27];
            min_dur_short_term = count_shortterm[28];
            min_dur_contract = count_contract[28];
            avg_dur_short_term = count_shortterm[29];
            avg_dur_contract = count_contract[29];


           

            int display_values[] = {max_dur_short_term, max_dur_contract, min_dur_short_term, min_dur_contract, avg_dur_short_term, avg_dur_contract};

            // print_r($display_values);
            string display_values_str[6];
            for (i = 0; i < 6; i++) {

                if (display_values[i] < 60) {
                    display_values_str[i] = to_string(display_values[i]) + "m";
                } else {                    

                    temp = (double) display_values[i] / 60; // 737/60 = 12.28                    

                    temp1 = floor(temp); // 12 hours
                    

                    temp2 = temp - temp1; // 12.28 - 12 = 0.28
                    

                    temp3 = floor(temp2 * 60); // 0.28 * 60 = 17 minutes
                    

                    display_values_str[i] = to_string(temp1) + "h " + to_string(temp3) + "m";
                    
                }
            }

            // print_r($display_values);

            header += "<div class='row mb-4 jspdf-graph text-center'>";
            header += "<div class='col-md-4 pl-0'>";
            header += "<!-- small card -->";
            header += "<div class='small-box bg-success'>";
            header += "<h5 class='small-box-footer text-white'>" + toString(label["maximum_parking_duration"]) + "</h5>";
            header += "<div class='inner row'>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[0] + "</h3>";
            header += "<h5>" + toString(label["shortterm"]) + "</h5>";
            header += "</div>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[1] + "</h3>";
            header += "<h5>" + toString(label["contract"]) + "</h5>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "<div class'col-md-4'>";
            header += "<!-- small card -->";
            header += "<div class='small-box bg-success'>";
            header += "<h5 class='small-box-footer text-white'>" + toString(label["minimum_parking_duration"]) + "</h5>";
            header += "<div class='inner row'>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[2] + "</h3>";
            header += "<h5>" + toString(label["shortterm"]) + "</h5>";
            header += "</div>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[3] + "</h3>";
            header += "<h5>" + toString(label["contract"]) + "</h5>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "<div class='col-md-4 pr-0'>";
            header += "<!-- small card -->";
            header += "<div class='small-box bg-success'>";
            header += "<h5 href='#' class='small-box-footer text-white'>" + toString(label["average_parking_duration"]) + "</h5>";
            header += "<div class='inner row'>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[4] + "</h3>";
            header += "<h5>" + toString(label["shortterm"]) + "</h5>";
            header += "</div>";
            header += "<div class='col'>";
            header += "<h3>" + display_values_str[5] + "</h3>";
            header += "<h5>" + toString(label["contract"]) + "</h5>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "</div>";
            header += "</div>";

            header += "<div class='card col-md-6'><div class='card-body'>";

            // begin header



            header += "<table id='RecordsTable' class='table  table-bordered jspdf-table'>";
            header += "<thead class='thead-light'><tr>";

            header += "<th>" + toString(label["duration"]) + "</th>";
            header += "<th>" + toString(label["shortterm_parkers"]) + "</th>";
            header += "<th>" + toString(label["contract_parkers"]) + "</th>";

            header += "</tr></thead>";

            html_data += header;

            html_data += "<tbody>";

            i = 0;

            while (i <= (30 - 4)) {

                // data for chart

                html_data += "<span display='none' id='chartData_" + to_string(i) + "' data-duration='" + duration_chart_label[i] + "' data-short-term='" + to_string(count_shortterm[i]) + "' data-contract='" + to_string(count_contract[i]) + "'></span>";

                // end / data for chart

                html_data += "<tr>";

                html_data += "<td>" + duration_label[i] + "</td>";
                html_data += "<td>" + to_string(count_shortterm[i]) + "</td>";
                html_data += "<td>" + to_string(count_contract[i]) + "</td>";

                html_data += "</tr>";

                i++;
            }

            html_data += "</tbody</table>";

            html_data += "</div></div>";

        } else {
            html_data = "<div class='card p-3'>" + toString(label["no_records"]) + "</div>";
        }
        Php::out << html_data << endl;

    } catch (const std::exception& e) {
        writeParkingReportException("parkingDuration", e.what());
    }
} //END. Show parking duration report 


void ParkingReport::userActivity(Php::Value json) {
    try {
        string operator_id = json["operator"];        
        string lang = json["language"];
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "transactions,date_time,operator_name,activity";
        Php::Value label = General.getLabels(lang, labels);


        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
     
        query = "select * from user_activity where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (operator_id.length() > 0)
            query = query + " AND user_id in(" + operator_id + ")";

        query = query + " ORDER BY  date_time desc";        
        
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            
            
       

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << toString(label["date_time"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["activity"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["operator_name"]) << "</th>" << endl;            
           

            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {                
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("activity_message") + "</td>" << endl;
                Php::out << "<td>" + res->getString("user_name") + "</td>" << endl;                                
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("userActivity", e.what());
    }
}

void ParkingReport::systemJournal(Php::Value json) {
    try {
        string operator_id = json["operator"];        
        string lang = json["language"];
        current_date_time = General.currentDateTime(dateTimeFormat);

        string labels = "transactions,date_time,operator_name,activity,category,status";
        Php::Value label = General.getLabels(lang, labels);


        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
     
        query = "select * from system_journal where date_time between '" + toString(json["from"]) + "' and '" + toString(json["to"]) + "'";
        if (operator_id.length() > 0)
            query = query + " AND user_id in(" + operator_id + ")";

        query = query + " ORDER BY  date_time desc";        
        
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            
            
       

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>" << toString(label["date_time"]) << "</th>" << endl;
	    Php::out << "<th>" << toString(label["category"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["activity"]) << "</th>" << endl;
            Php::out << "<th>" << toString(label["operator_name"]) << "</th>" << endl;            
            Php::out << "<th>" << toString(label["status"]) << "</th>" << endl;  

            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {                
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("date_time") + "</td>" << endl;
		Php::out << "<td>" + res->getString("category") + "</td>" << endl;
                Php::out << "<td>" + res->getString("description") + "</td>" << endl;
                Php::out << "<td>" + res->getString("user_name") + "</td>" << endl;         
		Php::out << "<td>" + res->getString("status") + "</td>" << endl;                                
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>" + toString(label["no_records"]) + "</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeParkingReportException("systemJournal", e.what());
    }
}

void ParkingReport::interfaceReport(Php::Value data)
{
	sql::Connection *con=NULL;
    	sql::PreparedStatement *prep_stmt=NULL;
	sql::ResultSet *res;  
	string query = "";  
	try {
        string interface_type = toString(data["type"]);
        string from = toString(data["from"]);
	string to = toString(data["to"]);
	string interface_name = toString(data["interface"]);
        
        con= General.mysqlConnect(ReportingDB); 
	query = "select * from interface_access_request where request_date_time between ? and ?";
	if(interface_name!="")
	{
		query += " and interface_name in ("+interface_name+")";
	}
	if(interface_type!="")
	{
		query += " and request_type = "+interface_type;
	}
	query+= " order by request_date_time desc";
	prep_stmt = con->prepareStatement(query);
        prep_stmt->setString(1,from);
	prep_stmt->setString(2,to);
	
        res = prep_stmt->executeQuery();
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row mb-4 jspdf-graph'>" << endl;
            Php::out << "<div class='col-lg-3 col-6'>" << endl;
            Php::out << "<div class='small-box bg-success'>" << endl;
            Php::out << "<div class='inner'>" << endl;
            Php::out << "<h3>" << res->rowsCount() << "</h3>" << endl;
            Php::out << "<h6>Transactions</h6>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'>" << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;            
            Php::out << "<th>DateTime</th>" << endl;
            Php::out << "<th>Interface</th>" << endl;
            Php::out << "<th>Request DateTime</th>" << endl;
            Php::out << "<th>Response DateTime</th>" << endl;
            Php::out << "<th>Type</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {               
                Php::out << "<tr  data-toggle='modal' data-target='#request-details-modal' id='access_record' access_id="+res->getString("id")+">" << endl;                
                Php::out << "<td>" + res->getString("request_date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("interface_name") + "</td>" << endl;
                Php::out << "<td> " + res->getString("request_date_time") + " </td>" << endl;
                Php::out << "<td>" + res->getString("response_date_time") + "</td>" << endl;
		if(res->getInt("request_type")==0) 
                	Php::out << "<td>Incoming</td>" << endl;
		else if(res->getInt("request_type")==1) 
                	Php::out << "<td>Outgoing</td>" << endl;
		else
			Php::out<<"<td></td>"<<endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table></div></div>" << endl;
        } else {
            Php::out << "<div class='card p-3'>No Records Found</div>" << endl;
        }

	delete res;
        delete prep_stmt;
        con->close();
        delete con;
    } catch (const std::exception& e) {
        writeParkingReportException("interfaceReport", e.what());
    }

}
Php::Value ParkingReport::getinterfaceRequestDetails(Php::Value json)
    {
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res;
    Php::Value response;  
    string query;
    try
        {
        string id=json["id"];        
        con= General.mysqlConnect(ReportingDB); 
        stmt=con->createStatement();
        query="select * from interface_access_request where id="+id;
        res=stmt->executeQuery(query);
        if(res->next())
            {
            sql::ResultSetMetaData *res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();   
            for (int i = 1; i <= columns; i++) 							
                    response[res_meta -> getColumnLabel(i)]=string(res->getString(i));				
            }
        delete res;    
        delete stmt;
        delete con;
        }
    catch(const std::exception& e)
        {
        writeParkingReportException("getinterfaceRequestDetails",e.what());
        } 
    return response;       
    }

