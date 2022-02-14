#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include<phpcpp.h>
#include <netdb.h>
#include <sstream>
#include <utility>
#include <iomanip>

#define ServerDB "v2_parcx_server"
#define ReportingDB "v2_parcx_reporting"
#define DashboardDB "v2_parcx_dashboard"
#define dateFormat "%Y-%m-%d"
#define IMG "/parcx-V02-2021/dist/img"
using namespace std;
GeneralOperations General;
sql::Connection *rCon, *con;
sql::Statement *rStmt, *stmt;
;
sql::ResultSet *res, *res2;
string query;
string image, croppedImage, sceneImage, datefolder;
size_t n;
string plate_review_confidence_rate = "0";
int update;

void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-Dashboard-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-Dashboard-" + General.currentDateTime("%Y-%m-%d"), function, message);
    Php::out << message << endl;
    writeLog(function, "Exception: " + message);
}

string toString(Php::Value param) {
    string value = param;
    return value;
}

void getPlateCorrectionRequiredTable(Php::Value data) {
    try {
        string lang = data["language"];
        string labels="plate_number,entry_date_time,confidence_rate,action,no_plate_for_correction";
        Php::Value label=General.getLabels(lang,labels);

        if (plate_review_confidence_rate == "0") {
            con = General.mysqlConnect(ServerDB);
            stmt = con->createStatement();
            query = "select setting_value from system_settings where setting_name='plate_review_confidence_rate'";
            res = stmt->executeQuery(query);
            if (res->next())
                plate_review_confidence_rate = res->getString("setting_value");            
            delete res;
            delete stmt;
            delete con;
        }
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "select a.id,a.plate_number,plate_image_name,capture_date_time,camera_device_number,confidence_rate,entry_date_time from plates_captured a INNER JOIN open_transactions b ON a.id=b.plate_captured_id where initial_plate_number is null and (b.plate_number like '%no plate%'  or confidence_rate<=" + plate_review_confidence_rate + " or b.plate_number like '%noplate%')";
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out <<"<table id='TABLE_1' class='table  table-bordered  jspdf-table RecordsTableclass'>" <<endl;
            Php::out << "<thead class='thead-light'>" << endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>"<<label["plate_number"]<<"</th>" << endl;
            Php::out << "<th>"<<label["entry_date_time"]<<"</th>" << endl;
            Php::out << "<th>"<<label["confidence_rate"]<<"</th>" << endl;
            //Php::out<<"<th>Plate Image</th>"<<endl;		
            Php::out << "<th>"<<label["action"]<<"</th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << endl;
            Php::out << "<tbody>" << endl;
            while (res->next()) {
                image = res->getString("plate_image_name");
                while ((n = image.find(" ")) != std::string::npos)
                    image.replace(n, 1, "%20");
                while ((n = image.find("#")) != std::string::npos)
                    image.replace(n, 1, "%23");

                datefolder = res->getString("capture_date_time");
                datefolder = datefolder.substr(0, 10);

                croppedImage = res->getString("camera_device_number") + "/" + datefolder + "/Crop_" + image;
                sceneImage = res->getString("camera_device_number") + "/" + datefolder + "/Scene_" + image;

                Php::out << "<tr>" << endl;
                Php::out << "<td >" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td >" + res->getString("entry_date_time") + "</td>" << endl;
                Php::out << "<td ><span class='badge bg-danger'>" + res->getString("confidence_rate") + "</span></td>" << endl;
                //Php::out<<"<td ><img src='http://localhost/ANPR/Images/Cropped/"+croppedImage+"' width='100' height='50'></td>"<<endl;                
                Php::out << "<td><button type='button' class='btn  btn-info' data-toggle='modal' data-target='#UpdatePlateModal' data-plate='" + res->getString("plate_number") + "' data-id='" + res->getString("id") + "' data-update='0' data-value ='" + sceneImage + "'><i class='fas fa-edit'></i></button></td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
        } else {
            Php::out << "<div class='p-3'>"<<label["no_plate_for_correction"]<<"</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    }    catch (const std::exception& e) {
        writeException("getPlateCorrectionRequiredTable", e.what());
        writeException("getPlateCorrectionRequiredTable", query);
    }
}



void getPlateMismatchTable(Php::Value data) {
    try {  
        string lang = data["language"];
        string labels="device_name,date_time,ticket_id,entry_plate_number,exit_plate_number,action,no_plate_mismatch";
        Php::Value label=General.getLabels(lang,labels);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "SELECT  * FROM plates_mismatch T1 JOIN (Select device_number, max(id) id from plates_mismatch where date_time >= DATE_SUB(NOW(),INTERVAL 1 HOUR) and dismiss=0 and entry_plate_captured_id>0 and exit_plate_captured_id>0 group by device_number) T2 on T1.device_number = T2.device_number and T1.id = T2.id ORDER by T1.id desc";
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out <<"<table id='TABLE_2' class='table  table-bordered  jspdf-table RecordsTableclass'>" <<endl;
            Php::out << "<thead class='thead-light'>" << endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>"<<label["date_time"]<<"</th>" << endl;           
            Php::out << "<th>"<<label["device_name"]<<"</th>" << endl;           
            Php::out << "<th>"<<label["ticket_id"]<<"</th>" << endl;           
            Php::out << "<th>"<<label["entry_plate_number"]<<"</th>" << endl;
            Php::out << "<th>"<<label["exit_plate_number"]<<"</th>" << endl;            
            Php::out << "<th>"<<label["action"]<<"</th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << endl;
            Php::out << "<tbody>" << endl;
            while (res->next()) {                

                Php::out << "<tr>" << endl;
                Php::out << "<td >" + res->getString("date_time") + "</td>" << endl;
                Php::out << "<td >" + res->getString("device_name") + "</td>" << endl;
                Php::out << "<td >" + res->getString("ticket_id") + "</td>" << endl;
                Php::out << "<td >" + res->getString("entry_plate_number") + "</td>" << endl;
                Php::out << "<td >" + res->getString("exit_plate_number") + "</td>" << endl;                
                Php::out << "<td><button type='button' class='btn  btn-info btn-plate-mismatch'  data-id='" + res->getString("id") + "'><i class='fas fa-edit'></i></button></td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
        } else {
            Php::out << "<div class='p-3'>"<<label["no_plate_mismatch"]<<"</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    }    catch (const std::exception& e) {
        writeException("getPlateMismatchTable", e.what());
        writeException("getPlateMismatchTable", query);
    }
}


Php::Value correctPlateNumber(Php::Value data) {
    try {
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        update = data["update"];
        if (update == 0)
            query = "update plates_captured set correction_type='Entry plate correction',plate_corrected_date_time=CURRENT_TIMESTAMP,initial_plate_number='" + toString(data["plate_number"]) + "',plate_number='" + toString(data["corrected_plate_number"]) + "',user_id='" + toString(data["user_id"]) + "',user_name='" + toString(data["user_name"]) + "' where id='" + toString(data["id"]) + "'";
        else if (update == 1)
            query = "update plates_captured set correction_type='Entry plate correction',plate_corrected_date_time=CURRENT_TIMESTAMP,plate_number='" + toString(data["corrected_plate_number"]) + "',user_id='" + toString(data["user_id"]) + "',user_name='" + toString(data["user_name"]) + "' where id='" + toString(data["id"]) + "'";
        else
            query = "update plates_captured set correction_type='Entry plate correction',plate_corrected_date_time=CURRENT_TIMESTAMP,initial_plate_number='" + toString(data["plate_number"]) + "',user_id='" + toString(data["user_id"]) + "',user_name='" + toString(data["user_name"]) + "' where id='" + toString(data["id"]) + "'";
        rStmt->executeUpdate(query);

        if (update == 0 || update == 1) {
            query = "update open_transactions set plate_number='" + toString(data["corrected_plate_number"]) + "' where plate_captured_id='" + toString(data["id"]) + "'";
            rStmt->executeUpdate(query);
        }
        delete rStmt;
        rCon->close();
        delete rCon;
    } catch (const std::exception& e) {
        writeException("correctPlateNumber", e.what());
        return "Cant correct plate";
    }
    
    return "Plate corrected";

}

void getPlateCorrectedTable(Php::Value data) {
    try {
        string lang = data["language"];
        string labels="plate_number,corrected_plate_number,entry_date_time,confidence_rate,username,action,no_plates_corrected";
        Php::Value label=General.getLabels(lang,labels);
        rCon = General.mysqlConnect(ReportingDB);
        rStmt = rCon->createStatement();
        query = "SELECT capture_date_time,a.id,initial_plate_number,a.plate_number,plate_corrected_date_time,user_name,plate_image_name,camera_device_number,confidence_rate,entry_date_time FROM plates_captured a,open_transactions b where a.id=b.plate_captured_id and initial_plate_number is not null";
        res = rStmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            Php::out <<"<table id='TABLE_3' class='table  table-bordered  jspdf-table RecordsTableclass'>" <<endl;
           Php::out << "<thead class='thead-light'>" << endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>"<<label["plate_number"]<<"</th>" << endl;
            Php::out << "<th>"<<label["corrected_plate_number"]<<"</th>" << endl;
            Php::out << "<th>"<<label["entry_date_time"]<<"</th>" << endl;
            Php::out << "<th>"<<label["confidence_rate"]<<"</th>" << endl;
            //Php::out<<"<th>Plate Image</th>"<<endl;	
            Php::out << "<th>"<<label["username"]<<"</th>" << endl;
            Php::out << "<th>"<<label["action"]<<"</th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << endl;
            Php::out << "<tbody>" << endl;
            while (res->next()) {
                image = res->getString("plate_image_name");
                while ((n = image.find(" ")) != std::string::npos)
                    image.replace(n, 1, "%20");
                while ((n = image.find("#")) != std::string::npos)
                    image.replace(n, 1, "%23");

                datefolder = res->getString("capture_date_time");
                datefolder = datefolder.substr(0, 10);

                croppedImage = res->getString("camera_device_number") + "/" + datefolder + "/Crop_" + image;
                sceneImage = res->getString("camera_device_number") + "/" + datefolder + "/Scene_" + image;


                Php::out << "<tr>" << endl;
                Php::out << "<td >" + res->getString("initial_plate_number") + "</td>" << endl;
                Php::out << "<td >" + res->getString("plate_number") + "</td>" << endl;
                Php::out << "<td >" + res->getString("entry_date_time") + "</td>" << endl;
                Php::out << "<td ><span class='badge bg-danger'>" + res->getString("confidence_rate") + "</span></td>" << endl;
                // Php::out<<"<td ><img src='http://localhost/ANPR/Images/Cropped/"+croppedImage+"' width='100' height='50'></td>"<<endl;                
                Php::out << "<td >" + res->getString("user_name") + "</td>" << endl;
                Php::out << "<td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#UpdatePlateModal' data-plate='" + res->getString("plate_number") + "' data-update='1' data-id='" + res->getString("id") + "' data-value ='" + sceneImage + "'><i class='fas fa-edit'></i></button></td>" << endl;
                Php::out << "</tr>" << endl;
            }
            Php::out << "</tbody></table>" << endl;
        } else {
            Php::out << "<div class='p-3'>"<<label["no_plates_corrected"]<<"</div>" << endl;
        }

        delete rStmt;
        delete res;
        rCon->close();
        delete rCon;
    }    catch (const std::exception& e) {
        writeException("getPlateCorrectedTable", e.what());
        writeException("getPlateCorrectedTable", query);
    }
}



int sendDataToPort(string ip, int port, string message) {

    try {
        int sockfd; // socket file descriptor 	
        struct sockaddr_in serv_addr;
        struct hostent *server;

        sockfd = socket(AF_INET, SOCK_STREAM, 0); // generate file descriptor 
        if (sockfd < 0) {
            writeException("sendDataToPort", "ERROR opening socket " + ip + ":" + to_string(port));
            return 0;
        }

        server = gethostbyname(ip.c_str()); //the ip address (or server name) of the listening server.
        if (server == NULL) {
            writeException("sendDataToPort", "ERROR, no such host " + ip + ":" + to_string(port));
            return 0;
        }

        bzero((char *) &serv_addr, sizeof (serv_addr));
        serv_addr.sin_family = AF_INET;
        bcopy((char *) server->h_addr, (char *) &serv_addr.sin_addr.s_addr, server->h_length);
        serv_addr.sin_port = htons(port);
        struct timeval tv;
        tv.tv_sec = 2;
        tv.tv_usec = 0;
        setsockopt(sockfd, SOL_SOCKET, SO_SNDTIMEO, (const char*) &tv, sizeof tv);

        if (connect(sockfd, (struct sockaddr *) &serv_addr, sizeof (serv_addr)) < 0) {
            writeException("sendDataToPort", "ERROR connecting " + ip + ":" + to_string(port));
            return 0;
        }

        /* write to port*/
        int n;
        char * wbuff;
        string str = message + "\n";
        wbuff = (char *) str.c_str(); // convert from string to c string, has to have \0 terminal 

        n = write(sockfd, wbuff, strlen(wbuff));
        if (n < 0)
            writeException("sendDataToPort", "Cannot write to socket");
        else
            writeException("sendDataToPort", message + " -command send to " + ip + ":" + to_string(port));

        close(sockfd);
    } catch (exception &e) {
        writeException("sendDataToPort", e.what());
    }
    return 1;
}

void correctPlateMismatch(Php::Value data) {
    try {
        string id = data["id"];
        con = General.mysqlConnect(ReportingDB);
        stmt = con->createStatement();
        query = "select * from plates_mismatch where id=" + id;
        res = stmt->executeQuery(query);
        if (res->next()) {
            string entry_id = res->getString("entry_plate_captured_id");
            string exit_id = res->getString("exit_plate_captured_id");
            string device_number = res->getString("device_number");
            string user_id = data["user_id"];
            string user_name = data["user_name"];
            string plate_number1 = data["plate_number1"];
            string plate_number2 = data["plate_number2"];
            string corrected_plate_number1 = data["corrected_plate_number1"];
            string corrected_plate_number2 = data["corrected_plate_number2"];
            if (plate_number1 != corrected_plate_number1) {
                query = "update plates_captured set correction_type='Exit plate mismatch',plate_corrected_date_time=CURRENT_TIMESTAMP,initial_plate_number='" + plate_number1 + "',plate_number='" + corrected_plate_number1 + "',user_id='" + user_id + "',user_name='" + user_name + "' where id='" + entry_id + "'";
                stmt->executeUpdate(query);
                query = "update open_transactions set plate_number='" + corrected_plate_number1 + "' where plate_captured_id='" + entry_id + "'";
                rStmt->executeUpdate(query);
            }
            if (plate_number2 != corrected_plate_number2) {
                query = "update plates_captured set correction_type='Exit plate mismatch',plate_corrected_date_time=CURRENT_TIMESTAMP,initial_plate_number='" + plate_number2 + "',plate_number='" + corrected_plate_number2 + "',user_id='" + user_id + "',user_name='" + user_name + "' where id='" + exit_id + "'";
                stmt->executeUpdate(query);
            }
            delete res;
            query = "select device_ip from "+string(ServerDB)+".system_devices where device_number=" + device_number;
            res = stmt->executeQuery(query);
            if (res->next()) {
                string ip = res->getString("device_ip");
                string message = "C0" + exit_id + "C1P0" + corrected_plate_number2 + "P1";
                if (sendDataToPort(ip, 8091, message) == 1)
                    query = "update plates_mismatch set dismiss=2 where id=" + id;
                else
                    query = "update plates_mismatch set dismiss=1 where id=" + id;

                stmt->executeUpdate(query);

                delete res;
            }
        }


        delete stmt;
        con->close();
        delete con;
    } catch (const std::exception& e) {
        writeException("correctPlateMismatch", e.what());
    }

}

Php::Value getMismatchPlateDetails(Php::Value data) {
    Php::Value response;
    try {
        string id = data["id"];
        string entry_id, exit_id;
        con = General.mysqlConnect(ReportingDB);
        stmt = con->createStatement();
        query = "select * from plates_mismatch where id=" + id;
        res = stmt->executeQuery(query);
        if (res->next()) {
            entry_id = res->getString("entry_plate_captured_id");
            exit_id = res->getString("exit_plate_captured_id");
            response["result_description"] = string(res->getString("result_description"));
        }
        query = "select* from plates_captured where id=" + entry_id;
        res = stmt->executeQuery(query);
        if (res->next()) {
            response["entry_plate_number"] = string(res->getString("plate_number"));
            image = res->getString("plate_image_name");
            while ((n = image.find(" ")) != std::string::npos)
                image.replace(n, 1, "%20");
            while ((n = image.find("#")) != std::string::npos)
                image.replace(n, 1, "%23");

            datefolder = res->getString("capture_date_time");
            datefolder = datefolder.substr(0, 10);

            sceneImage = res->getString("camera_device_number") + "/" + datefolder + "/Scene_" + image;
            response["entry_image"] = sceneImage;
            delete res;
        }
        query = "select* from plates_captured where id=" + exit_id;
        res = stmt->executeQuery(query);
        if (res->next()) {
            response["exit_plate_number"] = string(res->getString("plate_number"));
            image = res->getString("plate_image_name");
            while ((n = image.find(" ")) != std::string::npos)
                image.replace(n, 1, "%20");
            while ((n = image.find("#")) != std::string::npos)
                image.replace(n, 1, "%23");

            datefolder = res->getString("capture_date_time");
            datefolder = datefolder.substr(0, 10);

            sceneImage = res->getString("camera_device_number") + "/" + datefolder + "/Scene_" + image;
            response["exit_image"] = sceneImage;
            delete res;
        }
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("getMismatchPlateDetails", e.what());
    }
    return response;
}

Php::Value hourlyOccupancyReport(Php::Value data) {
    sql::PreparedStatement *prep_stmt;
    try {
        string fld = "";
        int carpark = data["carpark"];
        int facility = data["facility"];
        string type = data["type"];
        Php::Array total_count;
        int i = 0;
        int j = 0;
        con = General.mysqlConnect(DashboardDB);
        
        query = "select";
        while (i < 24) {
            query += " h" + std::to_string(i) + "to" + std::to_string((i + 1));
            if (i < 23) {
                query += ",";
            }
            i++;
        }


        query += " from hourly_occupancy where type=? and data='realtime' and carpark_number=? and facility_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setString(1,type);
        prep_stmt->setInt(2,carpark);
        prep_stmt->setInt(3,facility);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            i = 0;
            j = 0;

            while (i < 24) {
                total_count[j] = res->getInt("h" + std::to_string(i) + "to" + std::to_string((i + 1)));
                i++;
                j++;
            }
        } else {
            i = 0;
            while (i < 24) {
                total_count[i] = 0;
                i++;
            }
        }

        delete res;
        delete prep_stmt;
        delete con;

        return total_count;
    } catch (const std::exception& e) {
        writeException("hourlyOccupancyReport", e.what());
        writeException("hourlyOccupancyReport", query);
        return "";
    }
}//End Hourly Occupancy

Php::Value averageHourlyOccupancyReport(Php::Value data) {
    sql::PreparedStatement *prep_stmt;
    try {
        string fld = "";
        int carpark = data["carpark"];
        string facility = data["facility"];
        int option = data["seloption"];

        Php::Array total_count;
        int i = 0;
        int j = 0;
        con = General.mysqlConnect(ReportingDB);
        
        query = "select";
        while (i < 24) {
            query += " avg(h" + std::to_string(i) + "to" + std::to_string((i + 1)) + ") as h" + std::to_string(i) + "to" + std::to_string((i + 1));
            if (i < 23)
                query += ",";
            i++;
        }

        query += " from hourly_occupancy where carpark_number=?";
        if (option == 0)
            query += " and report_date=(DATE(NOW()) + INTERVAL -7 DAY)";
        if (option == 1)
            query += " and report_date between (DATE(NOW()) + INTERVAL -7 DAY) and CURRENT_DATE";
        if (option == 2)
            query += " and report_date<=CURRENT_DATE";

        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,carpark);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            i = 0;
            j = 0;

            while (i < 24) {
                total_count[j] = (int) res->getDouble("h" + std::to_string(i) + "to" + std::to_string((i + 1)));
                i++;
                j++;
            }
        } else {
            i = 0;
            while (i < 24) {
                total_count[i] = 0;
                i++;
            }
        }

        delete res;
        delete prep_stmt;
        delete con;

        return total_count;
    } catch (const std::exception& e) {
        writeException("averageHourlyOccupancyReport", e.what());
        writeException("averageHourlyOccupancyReport", query);
        return "";
    }
}//End Average Hourly Occupancy

Php::Value getFacilityFeatures() {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    Php::Value data;
    try {
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        query = "select setting_name,setting_value from system_settings where setting_name in('currency','decimal_places','vat_percentage','facility_name','address_line1','address_line2','vat_id','cloud_enabled')";
        res = stmt->executeQuery(query);
        while (res->next()) {
            data[res->getString("setting_name")] = res->getString("setting_value").asStdString();
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("getFacilityFeatures", e.what());

    }
    return data;
}

void showliverevenueCarpark(Php::Value data) {
    string facility_number = data["facility_number"];
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    int carpark_number;
    string html = "";
    try {
        string lang = data["language"];
        string labels = "total_payment_devices,gross_amount,parking_fee,lost_fee,product_sale_amount,vat_amount,discount_amount,more";
        Php::Value label = General.getLabels(lang, labels);
        con = General.mysqlConnect(DashboardDB);
        query = "select facility_number,carpark_number,carpark_name,count(*) as devices,sum(gross_amount) as gross_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue where facility_number=? group by carpark_number";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,stoi(facility_number));
        res = prep_stmt->executeQuery();
        if (res->rowsCount() == 1) {
            res->first();
            carpark_number = res->getInt("carpark_number");
            html = "<input type='hidden' id='facility_number' value='" + facility_number + "'>";
            html += "<input type='hidden' id='carpark_number' value='" + to_string(carpark_number) + "'>";
            Php::out << html << endl;
            //this->get_revenue_summary(facility_number,carpark_number);
        } else {
            Php::Value facility = getFacilityFeatures();
            html = "<input type='hidden' id='facility_number' value='" + facility_number + "'>";
            while (res->next()) {
                html += "<div class='finance-carpark col-md-4'>";
                html += "<div class='card '>";
                html += "<div class='card-body box-profile'>";
                html += "<h3 class='profile-username text-center'>" + res->getString("carpark_name") + "</h3>";
                html += "<p class='text-muted text-center'>" + res->getString("carpark_number") + "</p>";
                html += "<canvas facility-number='" + res->getString("facility_number") + "' carpark-number='" + res->getString("carpark_number") + "' class='donutChart' style='min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;'></canvas>";

                html += "<ul class='list-group list-group-unbordered mb-3 mt-3'>";
                html += "<li class='list-group-item'><b>" + toString(label["total_payment_devices"]) + "</b> <a class='float-right'>" + res->getString("devices") + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["gross_amount"]) + "</b> <a class='float-right'>" + res->getString("gross_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["parking_fee"]) + "</b> <a class='float-right'>" + res->getString("parking_fee") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["lost_fee"]) + "</b> <a class='float-right'>" + res->getString("lost_fee") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["product_sale_amount"]) + "</b> <a class='float-right'>" + res->getString("product_sale_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["vat_amount"]) + "</b> <a class='float-right'>" + res->getString("vat_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["discount_amount"]) + "</b> <a class='float-right'>" + res->getString("discount_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "</ul>";
                html += "<button type='button' class='show-carpark-details btn btn-block bg-secondary-gradient' facility_number='" + res->getString("facility_number") + "' carpark_number='" + res->getString("carpark_number") + "'>" + toString(label["more"]) + " <i class='fa fa-arrow-circle-right'></i></button>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
            }
            Php::out << html << endl;

        }
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("showliverevenueCarpark", e.what());
        writeException("showliverevenueCarpark", query);
    }
}

void showliverevenueFacility(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    int facility_number;
    int carparks;
    Php::Value request;
    string html = "";
    try {
        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select count(distinct carpark_number) as carparks,facility_number,facility_name,count(*) as devices,sum(gross_amount) as gross_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue group by facility_number";
        res = stmt->executeQuery(query);
        string lang = data["language"];
        string labels = "total_payment_devices,gross_amount,parking_fee,lost_fee,product_sale_amount,vat_amount,discount_amount,more";
        Php::Value label = General.getLabels(lang, labels);
        if (res->rowsCount() == 1) {
            res->next();
            facility_number = res->getInt("facility_number");
            carparks = res->getInt("carparks");

            if (carparks > 1) {
                html = "<input type='hidden' id='facility_number' value='" + to_string(facility_number) + "'>";
                Php::out << html << endl;
            } else {
                request["facility_number"] = facility_number;
                showliverevenueCarpark(request);

            }
        } else if (res->rowsCount() > 1) {
            Php::Value facility = getFacilityFeatures();
            html = "<input type='hidden' id='vat' value='" + toString(facility["vat_percentage"]) + "'>";
            while (res->next()) {
                html += "<div class='finance-facility col-md-4'>";
                html += "<div class='card '>";
                html += "<div class='card-body box-profile'>";
                html += "<h3 class='profile-username text-center'>" + res->getString("facility_name") + "</h3>";
                html += "<p class='text-muted text-center'>" + res->getString("facility_number") + "</p>";
                html += "<canvas facility-number='" + res->getString("facility_number") + "' class='donutChart' style='min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;'></canvas>";

                html += "<ul class='list-group list-group-unbordered mb-3 mt-3'>";
                html += "<li class='list-group-item'><b>" + toString(label["total_payment_devices"]) + "</b> <a class='float-right'>" + res->getString("devices") + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["gross_amount"]) + "</b> <a class='float-right'>" + res->getString("gross_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["parking_fee"]) + "</b> <a class='float-right'>" + res->getString("parking_fee") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["lost_fee"]) + "</b> <a class='float-right'>" + res->getString("lost_fee") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["product_sale_amount"]) + "</b> <a class='float-right'>" + res->getString("product_sale_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["vat_amount"]) + "</b> <a class='float-right'>" + res->getString("vat_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "<li class='list-group-item'><b>" + toString(label["discount_amount"]) + "</b> <a class='float-right'>" + res->getString("discount_amount") + " " + toString(facility["currency"]) + "</a></li>";
                html += "</ul>";
                html += "<button type='button' class='show-facility-details btn btn-block bg-secondary-gradient' facility_number='" + res->getString("facility_number") + "'>" + toString(label["more"]) + " <i class='fa fa-arrow-circle-right'></i></button>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
            }

            Php::out << html << endl;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("showliverevenueFacility", e.what());
        writeException("showliverevenueFacility", query);
    }

}

Php::Value liveRevenueFacility() {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;

    Php::Value row;
    Php::Array request;
    string html = "";
    try {
        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select facility_number,sum(gross_amount) as gross_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue group by facility_number";
        res = stmt->executeQuery(query);
        int i = 0;
        while (res->next()) {
            row["facility_number"] = res->getString("facility_number").asStdString();
            row["gross_amount"] = res->getString("gross_amount").asStdString();
            row["parking_fee"] = res->getString("parking_fee").asStdString();
            row["vat_amount"] = res->getString("vat_amount").asStdString();
            row["lost_fee"] = res->getString("lost_fee").asStdString();
            row["product_sale_amount"] = res->getString("product_sale_amount").asStdString();
            request[i] = row;
            i++;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("liveRevenueFacility", e.what());
        writeException("liveRevenueFacility", query);
    }
    return request;

}

Php::Value liveRevenueCarpark(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;

    Php::Value row;
    Php::Array request;
    string html = "";
    string facility_number = data["facility_number"];
    try {
        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select carpark_number,sum(gross_amount) as gross_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue where facility_number=" + facility_number + " group by carpark_number";
        res = stmt->executeQuery(query);
        int i = 0;
        while (res->next()) {
            row["carpark_number"] = res->getString("carpark_number").asStdString();
            row["gross_amount"] = res->getString("gross_amount").asStdString();
            row["parking_fee"] = res->getString("parking_fee").asStdString();
            row["vat_amount"] = res->getString("vat_amount").asStdString();
            row["lost_fee"] = res->getString("lost_fee").asStdString();
            row["product_sale_amount"] = res->getString("product_sale_amount").asStdString();
            request[i] = row;
            i++;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("liveRevenueCarpark", e.what());
        writeException("liveRevenueCarpark", query);
    }
    return request;
}

string SetDoublePrecision(double amt, int precision) {
    std::string amt_string = "0";
    std::ostringstream streamObj;
    // Set Fixed -Point Notation
    streamObj << std::fixed;

    // Set precision digits
    streamObj << std::setprecision(precision);

    //Add double to stream
    streamObj << amt;
    // Get string from output string stream
    amt_string = streamObj.str();
    std::cout << amt_string << std::endl;

    return amt_string;

}

void getRevenueSummary(Php::Value data) {
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    int carpark_number = data["carpark_number"];
    int facility_number = data["facility_number"];
    Php::Value facility = getFacilityFeatures();
    int d = facility["decimal_places"];
    string currency = toString(facility["currency"]);

    string html_data = "", header = "", data_status = "";
    try {
        string lang = data["language"];
        string labels = "transaction_count,vat_amount,reservation_amount,total_revenue,parking_fee,lost_fee,product_sale_amount,vat_amount,discount_amount,device_name,total_revenue,payable_entries,lost_ticket_count,discount_count,last_transaction";
        Php::Value label = General.getLabels(lang, labels);

        con = General.mysqlConnect(DashboardDB);
        query = "select sum(parking_fee) as parking_fee,sum(lost_ticket_fee) as lost_fee,sum(gross_amount) as earnings,sum(reservation_amount) as reservation_amount,sum(product_sale_amount) as product_sale,sum(discount_amount) as discount_amount from parking_revenue where carpark_number=? and facility_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,carpark_number);
        prep_stmt->setInt(2,facility_number);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            html_data = "<div class='row mb-4 jspdf-graph'>";
            html_data += "<div class='col'>";
            html_data += "<div class='small-box bg-success'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("earnings"), d) + "</h3>";
            html_data += "<p>" + toString(label["total_revenue"]) + "</p>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";


            html_data += "<div class='col'>";
            html_data += "<div class='small-box box-color'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("parking_fee"), d) + "</h3>";
            html_data += "<p>" + toString(label["parking_fee"]) + "</p>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";

            html_data += "<div class='col'>";
            html_data += "<div class='small-box box-color'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("lost_fee"), d) + "</h3>";
            html_data += "<p>" + toString(label["lost_fee"]) + "</p>";
            html_data += "</div>";
            html_data += "<div class='icon'>";
            html_data += "<i class='ion ion-stats-bars'></i>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";

            html_data += "<div class='col'>";
            html_data += "<div class='small-box box-color'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("product_sale"), d) + "</h3>";
            html_data += "<p>" + toString(label["product_sale_amount"]) + "</p>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";


            html_data += "<div class='col'>";
            html_data += "<div class='small-box box-color'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("reservation_amount"), d) + "</h3>";
            html_data += "<p>" + toString(label["reservation_amount"]) + "</p>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";


            html_data += "<div class='col'>";
            html_data += "<!-- small box -->";
            html_data += "<div class='small-box box-color'>";
            html_data += "<div class='inner'>";
            html_data += "<h3>" + currency + " " + SetDoublePrecision(res->getDouble("discount_amount"), d) + "</h3>";
            html_data += "<p>" + toString(label["discount_amount"]) + "</p>";
            html_data += "</div>";
            html_data += "</div>";
            html_data += "</div>";

            html_data += "</div><!-- Row End -->";
            html_data += "</div>";
        }


        Php::out << html_data << endl;

        html_data = "";
        header = "<div class='card'>";

        header += "<table class='jspdf-table table table-bordered' data-status='table-view'>";
        header += "<thead class='thead-light'><tr>";

        header += "<th>" + toString(label["device_name"]) + "</th>";
        header += "<th>" + toString(label["total_revenue"]) + "</th>";
        header += "<th>" + toString(label["parking_fee"]) + "</th>";
        header += "<th>" + toString(label["product_sale_amount"]) + "</th>";
        header += "<th>" + toString(label["reservation_amount"]) + "</th>";
        header += "<th>" + toString(label["vat_amount"]) + "</th>";
        header += "<th>" + toString(label["lost_fee"]) + "</th>";
        header += "<th>" + toString(label["discount_amount"]) + "</th>";
        header += "<th>" + toString(label["transaction_count"]) + "</th>";
        //		header += "<th>"+toString(label["lost_ticket_count"])+"</th>";
        //		header += "<th>"+toString(label["discount_count"])+"</th>";


        header += "</tr></thead>";
        html_data = header;


        query = "select * from parking_revenue where carpark_number=? and facility_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,carpark_number);
        prep_stmt->setInt(2,facility_number);
        res = prep_stmt->executeQuery();
        html_data += "<tbody>";

        while (res->next()) {
            data_status = "all";
            switch (res->getInt("device_type")) {
                case 3:
                    data_status = "manual-cashier";
                    break;
                case 4:
                    data_status = "payonfoot-machine";
                    break;
                case 5:
                    data_status = "handheld-pos";
                    break;
            }

            html_data += "<tr data-status='" + data_status + "'>";

            html_data += "<td class='text-left text-dark table-row-detail'>";
            html_data += "<h6>";
            if (res->getInt("network_status") == 1) 
                html_data += "<i class='fas fa-server text-success' title='Online'></i> ";
             else 
                html_data += "<i class='fas fa-server text-danger' title='Offline'></i> ";
            
            html_data += res->getString("device_name") + "</h6>";
            html_data += "<small class='form-text text-muted'>" + toString(label["last_transaction"]) + ":" + res->getString("last_transaction") + " </small>";
            html_data += "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("gross_amount"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("parking_fee"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("product_sale_amount"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("reservation_amount"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("vat_amount"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("lost_ticket_fee"), d) + "</td>";
            html_data += "<td>" + SetDoublePrecision(res->getDouble("discount_amount"), d) + "</td>";
            html_data += "<td>" + res->getString("total_transaction_count") + "</td>";
            //                        html_data += "<td>" + res->getString("payable_entries_count")+ "</td>";
            //		        html_data += "<td>" + res->getString("lost_ticket_count")+ "</td>";
            //		        html_data += "<td>" + res->getString("discount_count")+ "</td>";
            html_data += "</tr>";
        }

        html_data += "</tbody>";
        html_data += "</table></div>";
        Php::out << html_data << endl;

        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getRevenueSummary", e.what());
        writeException("getRevenueSummary", query);
    }

}

Php::Value revenueLastdays() {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    Php::Array arr;
    Php::Value rev;
    try {
        con = General.mysqlConnect(ReportingDB);
        stmt = con->createStatement();
        //query = "select report_date,report_day,sum(gross_amount) as gross_amount from summary_daily_revenue where report_date between DATE(NOW())-INTERVAL 7 DAY and DATE(NOW())- INTERVAL 1 DAY group by report_date ";
        query = "select report_date,report_day,sum(gross_amount) as gross_amount from summary_daily_revenue where report_date between DATE(NOW())-INTERVAL 7 DAY and DATE(NOW())- INTERVAL 1 DAY group by report_date ";

        res = stmt->executeQuery(query);
        int i = 0;
        while (res->next()) {
            rev["day"] = res->getString("report_day").asStdString();
            rev["amount"] = res->getString("gross_amount").asStdString();
            rev["date"] = res->getString("report_date").asStdString();
            arr[i] = rev;
            i++;
        }
        delete res;
        delete stmt;
        delete con;

        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select sum(gross_amount) as gross_amount from parking_revenue";
        res = stmt->executeQuery(query);
        if (res->next()) {
            rev["day"] = "Today";
            rev["amount"] = res->getString("gross_amount").asStdString();
            rev["date"] = General.currentDateTime("%Y-%m-%d");
            arr[i] = rev;
        }
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("revenueLastdays", e.what());
        writeException("revenueLastdays", query);
    }
    return arr;
}

Php::Value liveRevenueDevice(Php::Value data) {
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;

    Php::Value row;
    int facility_number = data["facility_number"];
    int carpark_number = data["carpark_number"];
    try {
        con = General.mysqlConnect(DashboardDB);
        //query = "select sum(gross_amount) as gross_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue where facility_number="+facility_number+" and carpark_number="+carpark_number;
        query = "select sum(discount_amount) as discount_amount,sum(gross_amount) as gross_amount,sum(reservation_amount) as reservation_amount,sum(parking_fee-discount_amount) as parking_fee,sum(vat_amount) as vat_amount,sum(lost_ticket_fee+admin_fixed_charges+ticket_replacement_fee) as lost_fee,sum(discount_amount) as discount_amount,sum(product_sale_amount) as product_sale_amount from parking_revenue where facility_number=? and carpark_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,facility_number);
        prep_stmt->setInt(2,carpark_number);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            row["gross_amount"] = res->getString("gross_amount").asStdString();
            row["parking_fee"] = res->getString("parking_fee").asStdString();
            row["vat_amount"] = res->getString("vat_amount").asStdString();
            row["lost_fee"] = res->getString("lost_fee").asStdString();
            row["product_sale_amount"] = res->getString("product_sale_amount").asStdString();
            row["reservation_amount"] = res->getString("reservation_amount").asStdString();
        }
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("liveRevenueDevice", e.what());
        writeException("liveRevenueDevice", query);
    }
    return row;
}

string chooseBgColor(float current_level, int type) {
    string bg = "", color = "";
    //type 1 = gauge , type 2 = progress and badge
    if (current_level <= 50) {
        bg = "success";
        color = "#28a745"; // Green 
    } else if ((current_level > 50) && (current_level <= 75)) {
        bg = "warning";
        color = "#ffcd3c"; //Yellow
    } else if (current_level > 75) {
        bg = "danger";
        color = "#dc3545"; //red
    }

    if (type == 1)
        return color;
    else if (type == 2)
        return bg;
    else
        return bg;
}

void getOccupancyDevice(Php::Value data) {
    int carpark_number = data["carpark_number"];
    int facility_number = data["facility_number"];
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;


    string html_data = "";
    float gauge_percentage = 0;
    float shortterm_occupancy = 0, access_occupancy = 0, reservation_occupancy = 0;
    try {
        string lang = data["language"];
        string labels = "shortterm,access,reservation,more,manual,entries,exits,occupancy,current_total";
        Php::Value label = General.getLabels(lang, labels);
        con = General.mysqlConnect(DashboardDB);
        query = "select * from counters where counter_type=1 and facility_number=? AND carpark_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,facility_number);
        prep_stmt->setInt(2,carpark_number);
        res = prep_stmt->executeQuery();



        res->first();

        if (res->getInt("total_spaces") > 0)
            gauge_percentage = (res->getInt("current_level") / res->getInt("total_spaces")) * 100;
        if (res->getInt("total_shortterm_spaces") > 0)
            shortterm_occupancy = res->getInt("shortterm_current_level")*100 / res->getInt("total_shortterm_spaces");
        if (res->getInt("total_access_spaces") > 0)
            access_occupancy = res->getInt("access_current_level")*100 / res->getInt("total_access_spaces");
        if (res->getInt("total_reservation_spaces") > 0)
            reservation_occupancy = res->getInt("reservation_current_level")*100 / res->getInt("total_reservation_spaces");


        html_data += "<div class='container-fluid'>";
        html_data += "<div class='row mb-2'>";
        html_data += "<div class='col-sm-6'>";
        html_data += "<h1 id='gauge1_name'>" + res->getString("carpark_name") + "</h1>";
        //html_data += "<h5>' . _SESSION['facility_name'] . '</h5>";
        html_data += "</div>";
        html_data += "</div>";
        html_data += "</div>";

        //The Occupancy Gauge - Total 
        html_data += "<div class='col-md-4'>";
        html_data += " <div class='chart-box text-center'>";
        html_data += "<p class='text-center chart-header' id='auge1_name'></p>";
        html_data += " <input id='carpark' type='hidden' class='knob' data-thickness='0.3' data-angleArc='250' data-angleOffset='-125'value='" + to_string(gauge_percentage) + "' data-width='250' data-height='150' data-fgColor='" + chooseBgColor(gauge_percentage, 1) + "' data-readonly='true'>";
        html_data += "<p class='gauge-val' id='gauge-value'><span>" + res->getString("current_level") + "</span>/<span>" + res->getString("total_spaces") + "</span></p>";
        html_data += "  </div>";
        html_data += "  </div>"; //col md 4
        //Occupancy progress bar by type 

        html_data += "<div class='col-md-4'>";
        html_data += "<div class='card'>";
        html_data += "<div class='card-body p-0'>";
        html_data += "<table class='table table-striped'>";
        html_data += "<tr  style='height: 25%'>";
        html_data += "<th style='width: 10px'></th>";
        html_data += "<th style='width: 50%'>" + toString(label["occupancy"]) + "</th>";
        html_data += "<th style='width: 20%'>" + toString(label["current_total"]) + "</th>";
        html_data += "</tr>";

        html_data += "<tr>";
        html_data += "<td>" + toString(label["shortterm"]) + "</td>";
        html_data += "<td>";
        html_data += "<div class='progress progress-xs'>";
        html_data += " <div id='progress-shortterm' class='progress-bar bg-" + chooseBgColor(shortterm_occupancy, 2) + "' style='width: " + to_string(shortterm_occupancy) + "%'></div>";
        html_data += "</div>";
        html_data += "</td>";
        html_data += "<td><span id='badge-shortterm' class='badge bg-" + chooseBgColor(shortterm_occupancy, 2) + "'>" + res->getString("shortterm_current_level") + "/" + res->getString("total_shortterm_spaces") + "</span></td>";
        html_data += "</tr>";

        html_data += "<tr>";
        html_data += "<td>" + toString(label["access"]) + "</td>";
        html_data += "<td>";
        html_data += "<div class='progress progress-xs'>";

        html_data += " <div id='progress-access' class='progress-bar bg-" + chooseBgColor(access_occupancy, 2) + "' style='width: " + to_string(access_occupancy) + "%'></div>";
        html_data += "</div>";
        html_data += "</td>";
        html_data += "   <td><span id='badge-access' class='badge bg-" + chooseBgColor(access_occupancy, 2) + "'>" + res->getString("access_current_level") + "/" + res->getString("total_access_spaces") + "</span></td>";
        html_data += "  </tr>";

        html_data += "  <tr>";
        html_data += "  <td>" + toString(label["reservation"]) + "</td>";
        html_data += "  <td>";
        html_data += "  <div class='progress progress-xs'>";

        html_data += " <div id='progress-reservation' class='progress-bar bg-" + chooseBgColor(reservation_occupancy, 2) + "' style='width: " + to_string(reservation_occupancy) + "%'></div>";
        html_data += "</div>";
        html_data += "</td>";
        html_data += "   <td><span id='badge-reservation' class='badge bg-" + chooseBgColor(reservation_occupancy, 2) + "'>" + res->getString("reservation_current_level") + "/" + res->getString("total_reservation_spaces") + "</span></td>";
        html_data += "  </tr>";


        html_data += "  </table>";
        html_data += " </div>";

        html_data += "  </div>";
        html_data += "  </div>"; //col md 4
        //Entries and Exits By type
        html_data += "<div class='col-md-4'>";
        html_data += "<div class='card'>";
        html_data += "<div class='card-body p-0'>";
        html_data += "<table class='table table-striped'>";
        html_data += "  <tr>";
        html_data += "   <th></th>";
        html_data += "   <th>" + toString(label["entries"]) + "</th>";
        html_data += "   <th>" + toString(label["exits"]) + "</th>";
        html_data += "  </tr>";
        html_data += "  <tr>";
        html_data += "   <td>" + toString(label["shortterm"]) + "</td>";
        html_data += "   <td id='shortterm-entry'>" + res->getString("shortterm_entry") + "</td>";
        html_data += "   <td id='shortterm-exit'>" + res->getString("shortterm_exit") + "</td>";
        html_data += "  </tr>";
        html_data += "  <tr>";
        html_data += "   <td>" + toString(label["access"]) + "</td>";
        html_data += "   <td id='access-entry'>" + res->getString("access_entry") + "</td>";
        html_data += "   <td id='access-exit'>" + res->getString("access_exit") + "</td>";
        html_data += "  </tr>";

        html_data += "  <tr>";
        html_data += "   <td>" + toString(label["reservation"]) + "</td>";
        html_data += "   <td id='reservation-entry'>" + res->getString("reservation_entry") + "</td>";
        html_data += "   <td id='reservation-exit'>" + res->getString("reservation_exit") + "</td>";
        html_data += "  </tr>";

        html_data += "  <tr>";
        html_data += "   <td>" + toString(label["manual"]) + "</td>";
        html_data += "   <td id='manual-entry'>" + res->getString("total_manual_entry") + "</td>";
        html_data += "   <td id='manual-exit'>" + res->getString("total_manual_exit") + "</td>";
        html_data += "  </tr>";


        html_data += "  </table>";
        html_data += " </div>";

        html_data += "  </div>";
        html_data += "  </div>"; //col md 4     
        Php::out << html_data << endl;
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getOccupancyDevice", e.what());
        writeException("getOccupancyDevice", query);
    }
}

void getOccupancyCarpark(Php::Value data) {

    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;

    string html_data = "", color;
    int facility_number = data["facility_number"];
    int carpark_number;
    float gauge_value;
    try {
        string lang = data["language"];
        string labels = "reservation,more,shortterm,access";
        Php::Value label = General.getLabels(lang, labels);
        con = General.mysqlConnect(DashboardDB);
        query = "select last_updated_datetime,carpark_name,carpark_number,total_spaces,current_level,shortterm_current_level,total_shortterm_spaces,access_current_level,total_access_spaces,reservation_current_level,total_reservation_spaces from counters where counter_type=1 and facility_number=? ORDER BY carpark_number ASC";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,facility_number);
        res = prep_stmt->executeQuery();
        cout << "RowCount: " << res->rowsCount() << endl;
        if (res->rowsCount() == 1) {
            //If only one carpark show the car park list directly 
            res->next();
            carpark_number = res->getInt("carpark_number");

            html_data = "<input type='hidden' id='facility_number' value='" + to_string(facility_number) + "'>";
            html_data += "<input type='hidden' id='carpark_number' value='" + to_string(carpark_number) + "'>";
            Php::out << html_data << endl;
        } else {
            string html_data = "";
            float occupancy_percentage = 0, i;

            while (res->next()) {

                gauge_value = (res->getInt("current_level") / res->getInt("total_spaces")) * 100;

                color = chooseBgColor(gauge_value, 1);

                html_data += " <div class='col-lg-3 col-sm-6 col-xs-12'>";
                html_data += " <div class='chart-box text-center'>";
                html_data += "<h1 class='text-left chart-header' id='gauge1_name'>" + res->getString("carpark_name") + "</h1>";
                html_data += " <input  type='hidden' class='knob' data-thickness='0.3' data-angleArc='250' data-angleOffset='-125' value='" + to_string(gauge_value) + "' data-width='250' data-height='150' data-fgColor='" + color + "' data-readonly='true'>";
                html_data += "<p class='gauge-val' ><span>" + res->getString("current_level") + "</span>/<span>" + res->getString("total_spaces") + "</span></p>";
                html_data += "<div class='card-body'>";


                html_data += "<div id='row'>";
                html_data += "<div class='col-5 text-left' style='float:left;' id='category'>" + toString(label["shortterm"]) + "</div>";
                html_data += " <div class='col text-right' >" + res->getString("shortterm_current_level") + "/" + res->getString("total_shortterm_spaces") + "</div>";
                if (res->getInt("total_shortterm_spaces") > 0)
                    occupancy_percentage = (res->getInt("shortterm_current_level")* 100 / res->getInt("total_shortterm_spaces"));
                else
                    occupancy_percentage = 0;


                html_data += "</div>"; //end row
                html_data += "<div class='progress mb-3'>";
                html_data += "<div class='progress-bar bg-" + chooseBgColor(occupancy_percentage, 2) + "' id='shortterm-progress-" + to_string(i) + "' role='progressbar' aria-valuenow='20' aria-valuemin='0' aria-valuemax='100' style='width:" + to_string(occupancy_percentage) + "%'>";
                html_data += "</div>"; //end progress-bar
                html_data += "</div>"; // end progress mb-3

                html_data += "<div id='row'>";
                html_data += "<div class='col-5 text-left' style='float:left;' id='category'>" + toString(label["access"]) + "</div>";
                html_data += "<div class='col text-right' >" + res->getString("access_current_level") + "/" + res->getString("total_access_spaces") + "</div>";
                if (res->getInt("total_access_spaces") > 0)
                    occupancy_percentage = (res->getInt("access_current_level")*100 / res->getInt("total_access_spaces"));
                else
                    occupancy_percentage = 0;

                html_data += "</div>"; //end row count
                html_data += " <div class='progress mb-3'>";
                html_data += "<div class='progress-bar bg-" + chooseBgColor(occupancy_percentage, 2) + "' id='access-progress-" + to_string(i) + "' role='progressbar' aria-valuenow='20' aria-valuemin='0' aria-valuemax='100' style='width:" + to_string(occupancy_percentage) + "%'>";
                html_data += "</div>"; //end row
                html_data += "</div>";

                html_data += "<div id='row'>";
                html_data += "<div class='col-5 text-left' style='float:left;' id='category'>" + toString(label["reservation"]) + " </div>";
                html_data += "<div class='col text-right' >" + res->getString("reservation_current_level") + "/" + res->getString("total_reservation_spaces") + "</div>";
                html_data += "</div>"; //end row count
                html_data += " <div class='progress mb-3'>";

                if (res->getInt("total_reservation_spaces") > 0)
                    occupancy_percentage = (res->getInt("reservation_current_level") *100 / res->getInt("total_reservation_spaces"));
                else
                    occupancy_percentage = 0;


                html_data += "<div class='progress-bar bg-" + chooseBgColor(occupancy_percentage, 2) + "' id='reservation-progress-" + to_string(i) + "' role='progressbar' aria-valuenow='20' aria-valuemin='0' aria-valuemax='100' style='width:" + to_string(occupancy_percentage) + "%'>";
                html_data += "</div>"; //end row
                html_data += "</div>";
                // html_data +="</div>";//end row

                html_data += "</div>";
                html_data += "<button type='button' class='btn btn-block bg-secondary-gradient show-carpark-details' facility_number='" + to_string(facility_number) + "' carpark_number='" + res->getString("carpark_number") + "'>" + toString(label["more"]) + " <i class='fa fa-arrow-circle-right'></i></button>";
                html_data += "</div>";

                html_data += "</div>";
                i++;
            }//wend

            Php::out << html_data << endl;
        }//end if   
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getOccupancyCarpark", e.what());
    }

}

void getOccupancyFacility(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;

    int facility_number;
    string html_data = "";
    float gauge_value;
    try {
        string lang = data["language"];
        string labels = "discount_amount,more";
        Php::Value label = General.getLabels(lang, labels);
        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select facility_number,carpark_name,total_spaces,current_level,last_updated_datetime from counters where counter_type=0 ORDER BY dashboard_order ASC";
        res = stmt->executeQuery(query);
        if (res->rowsCount() == 1) {
            res->next();
            facility_number = res->getInt("facility_number");


            query = "select * from counters where counter_type=1 and facility_number=" + to_string(facility_number) + " ORDER BY carpark_number ASC";
            res = stmt->executeQuery(query);
            int rows = res->rowsCount();

            if (rows > 1) {
                html_data = "<input type='hidden' id='facility_number' value='" + to_string(facility_number) + "'>";
                Php::out << html_data << endl;
            } else {
                data["facility_number"] = facility_number;
                getOccupancyCarpark(data);
            }
        } else {
            html_data = "";
            while (res->next()) {

                gauge_value = (res->getInt("current_level") / res->getInt("total_spaces")) * 100;
                html_data += " <div class='col-lg-3 col-sm-6 col-xs-12'>";
                html_data += "<div class='chart-box text-center'>";
                html_data += "<p class='text-center chart-header' id='gauge1_name'>" + res->getString("carpark_name") + "</p>";
                html_data += " <input  type='hidden' class='knob' data-thickness='0.3' data-angleArc='250' data-angleOffset='-125' value='" + to_string(gauge_value) + "' data-width='250' data-height='150' data-fgColor='" + chooseBgColor(gauge_value, 1) + "' data-readonly='true'>";
                html_data += "<p class='gauge-val' ><span>" + res->getString("current_level") + "</span>/<span>" + res->getString("total_spaces") + "</span></p>";
                html_data += "<div class='card-body'>";
                html_data += "</div>";
                html_data += "<button type='button' class='btn btn-block btn-outline-secondary btn-sm show-facility-details' facility_number=" + res->getString("facility_number") + ">" + toString(label["more"]) + " <i class='fa fa-arrow-circle-right'></i></button>";
                html_data += "</div>";
                html_data += "</div>";
            }//wend  
            Php::out << html_data << endl;
        }//end if  
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getOccupancyFacility", e.what());        
    }
}

int get_alarm_count(int facility, int carpark, string device, int alarmseverity1, int alarmseverity2) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    int rows = 0;
    query = "select count(*) as count from watchdog_device_alarms where alarm_code%2=1 and dismiss=0 and alarm_severity between " + to_string(alarmseverity1) + " and " + to_string(alarmseverity2) + " and alarm_date_time>=DATE_ADD(CURDATE(),INTERVAL -7 DAY)";

    if (facility > 0)
        query += " and facility_number='" + to_string(facility) + "'";
    if (carpark > 0)
        query += " and carpark_number=" + to_string(carpark);
    if (device != "0")
        query += " and device_number in (" + device + ")";

    query += " GROUP by device_number,alarm_code";

    con = General.mysqlConnect(ReportingDB);
    stmt = con->createStatement();
    res = stmt->executeQuery(query);
    rows = res->rowsCount();
    delete res;
    delete stmt;
    delete con;
    return rows;
}

std::vector<std::string> explode(char delim, std::string const & s) {
    std::vector<std::string> result;
    std::istringstream iss(s);
    std::string token;
    while (iss.good()) {
        getline(iss, token, delim);
        result.push_back(token);
    }
    return result;
}

bool in_array(string num, std::vector<std::string> arr) {
    bool result = false;
    int arraySize = arr.size();
    for (int i = 0; i < arraySize; i++) {
        if (arr[i] == num) {
            result = true;
            break;
        }
    }
    return result;
}

string get_device_category_name(int device_type) {
    string category = "";
    switch (device_type) {
        case 1:
            category = "Entry Column";
            break;
        case 2:
            category = "Exit Column";
            break;
        case 3:
            category = "Cashier POS";
            break;
        case 4:
            category = "APM";
            break;
        case 5:
            category = "Handheld POS";
            break;
        case 6:
            category = "Controller";
            break;
        case 7:
            category = "Controller";
            break;
        case 8:
            category = "Camera";
            break;
        case 9:
            category = "VMS";
            break;
        case 11:
            category = "Validators";
            break;
    }
    return category;
}

void getDeviceStatusbyCarpark(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    sql::ResultSet *rs;
    sql::PreparedStatement *prep_stmt;
    string lang=data["language"];
    int facility_number = data["facility_number"];
    int carpark_number = 0, devices = 0;
    string html = "", carpark_name = "",facility_name, status = "";
    int online = 0;
    int offline = 0;
    string labels = "";
    
    Php::Value device_category;    
    std::vector<std::string> n_status;    
    con = General.mysqlConnect(DashboardDB);
    stmt = con->createStatement();

    query="select id,category_label from "+string(ServerDB)+".system_device_category";
    res = stmt->executeQuery(query);
    while(res->next())       
        {
        device_category[res->getString("id").asStdString()]=res->getString("category_label").asStdString();                        
        labels=labels+res->getString("category_label").asStdString()+",";        
        }
    delete res;
    delete stmt;
    labels=labels+"total_devices,alarms,more,network_status,online,offline,warnings,critical_alarms";
        
    Php::Value label = General.getLabels(lang, labels);
    
    query = "select carpark_number,carpark_name,facility_name from watchdog_device_status where facility_number=? group by carpark_number";
    prep_stmt = con->prepareStatement(query);
    prep_stmt->setString(1,to_string(facility_number));
    res = prep_stmt->executeQuery();
    int rows = res->rowsCount();
    if (rows == 1) {
        if (res->next()) {
            carpark_number = res->getInt("carpark_number");
            delete res;
            delete prep_stmt;
            delete con;
            html = "<input type='hidden' id='facility_number' value='" + to_string(facility_number) + "'>";
            html += "<input type='hidden' id='carpark_number' value='" + to_string(carpark_number) + "'>";
            Php::out << html << endl;
        }

    } else {
        while (res->next()) {
            carpark_number = res->getInt("carpark_number");
            carpark_name = res->getString("carpark_name");
            facility_name=res->getString("facility_name");

            query = "select count(*) as devices from watchdog_device_status where facility_number=? and carpark_number=?";
            prep_stmt = con->prepareStatement(query);
            prep_stmt->setString(1,to_string(facility_number));
            prep_stmt->setString(2,to_string(carpark_number));
            rs = prep_stmt->executeQuery();
            if (rs->next()) {
                devices = rs->getInt("devices");
            }
            delete rs;


            query = "select device_network_status,count(*) as status from watchdog_device_status where facility_number=? and carpark_number=? GROUP by device_network_status";
            prep_stmt = con->prepareStatement(query);
            prep_stmt->setString(1,to_string(facility_number));
            prep_stmt->setString(2,to_string(carpark_number));
            rs = prep_stmt->executeQuery();
            online = 0;
            offline = 0;
            while (rs->next()) {
                if (rs->getInt("device_network_status") == 0)
                    offline = rs->getInt("status");
                else
                    online = rs->getInt("status");
                    


            }
            delete rs;

            html = "<div class='col-md-3'>";
            html += "<div class='card'>";
            html += "<div class='card-body box-profile'>";
            html += "<h3 class='profile-username text-center'>" + carpark_name + "</h3>";
            html += "<p class='text-muted text-center'>" + facility_name + "</p>";
            html += "<ul class='list-group list-group-unbordered mb-3'>";
            html += "<li class='list-group-item'><b>"+ toString(label["total_devices"]) +"</b> <a class='float-right'>" + to_string(devices) + "</a></li>";

            html += "<li class='list-group-item'><b>"+ toString(label["network_status"]) +"</b>";
            html += "<a class='float-right'>";
            html += "<span class='nav-link p-1'>";
            html += "<span class='fa-stack fa-1x' title='"+ toString(label["online"]) +"'>";
            html += "<i class='fa fa-circle fa-stack-2x text-success'></i>";
            html += "<strong class='fa-stack-1x text-white'>" + to_string(online) + "</strong>";
            html += "</span>";
            html += "<span class='fa-stack fa-1x' title='"+ toString(label["offline"]) +"'>";
            html += "<i class='fa fa-circle fa-stack-2x text-danger'></i>";
            html += "<strong class='fa-stack-1x text-white'>" + to_string(offline) + "</strong>";
            html += "</span></span></a></li>";

            string str_online = "<i class='fas fa-server text-success' title='"+ toString(label["online"]) +"'></i>";
            string str_offline = "<i class='fas fa-server text-danger' title='"+ toString(label["offline"]) +"'></i>";

            query = "SELECT group_concat(device_number) device_number,group_concat(device_network_status) device_network_status,device_type FROM watchdog_device_status where facility_number=? and carpark_number=? group by device_type";

            prep_stmt = con->prepareStatement(query);
            prep_stmt->setString(1,to_string(facility_number));
            prep_stmt->setString(2,to_string(carpark_number));
            rs = prep_stmt->executeQuery();

            while (rs ->next()) {                               
                string category=device_category[rs->getInt("device_type")];
                n_status = explode(',', rs->getString("device_network_status"));
                if (in_array("0", n_status))
                    status = str_offline;
                else
                    status = str_online;

                html += "<li class='list-group-item'><b>" + status + " " + toString(label[category]) + "</b><a class='float-right'>";
                html += "<span class='nav-link p-1'>";
                html += "<span class='fa-stack fa-1x' title='"+ toString(label["warnings"]) +"'>";
                html += "<i class='fa fa-circle fa-stack-2x text-warning'></i>";
                html += "<strong class='fa-stack-1x text-dark'>" + to_string(get_alarm_count(facility_number, carpark_number, rs->getString("device_number"), 6, 20)) + "</strong>";
                html += "</span>";
                html += "<span class='fa-stack fa-1x' title='"+ toString(label["critical_alarms"]) +"'>";
                html += "<i class='fa fa-circle fa-stack-2x text-danger'></i>";
                html += "<strong class='fa-stack-1x text-white'>" + to_string(get_alarm_count(facility_number, carpark_number, rs->getString("device_number"), 0, 5)) + "</strong>";
                html += "</span></span>";
                html += "</a></li>";
            }
            delete rs;
            html += "</ul>";

            html += "<button type='button' class='show-carpark-details btn btn-block bg-secondary-gradient' facility_number='" + to_string(facility_number) + "' carpark_number='" + to_string(carpark_number) + "'>"+ toString(label["more"]) +" <i class='fa fa-arrow-circle-right'></i></button>";
            html += "</div></div></div>";
            Php::out << html << endl;

        }        
        delete res;
        delete prep_stmt;
        delete con;
    }
}

void getDeviceStatusbyFacility(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    sql::ResultSet *rs;
    sql::PreparedStatement *prep_stmt;
    string html = "";
    int facility_number = 0;
    int carparks = 0, online = 0, offline = 0, devices = 0;
    string facility_name = "";
    Php::Value request;
    try {
        string lang = data["language"];
        string labels = "total_carparks,total_devices,alarms,more,network_status,online,offline,warnings,critical_alarms";
        Php::Value label = General.getLabels(lang, labels);

        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select facility_number,facility_name,count(DISTINCT carpark_number) as carparks from watchdog_device_status group by facility_number";
        res = stmt->executeQuery(query);
        if (res->rowsCount() > 0) {
            if (res->rowsCount() == 1) {
                if (res->next()) {
                    facility_number = res->getInt("facility_number");
                    carparks = res->getInt("carparks");

                    if (carparks > 1) {
                        html = "<input type='hidden' id='facility_number' value='" + to_string(facility_number) + "'>";
                        Php::out << html << endl;
                    } else {
                        request["facility_number"] = facility_number;
                        getDeviceStatusbyCarpark(request);
                    }
                }
            } else {

                while (res->next()) {
                    facility_number = res->getInt("facility_number");
                    facility_name = res->getString("facility_name");

                    query = "select count(*) as devices,count(distinct carpark_number) as carparks from watchdog_device_status where facility_number=?";                 
                    prep_stmt = con->prepareStatement(query);
                    prep_stmt->setString(1, to_string(facility_number));
                    rs = prep_stmt->executeQuery();
                    if (rs->next()) {
                        devices = rs->getInt("devices");
                        carparks = rs->getInt("carparks");
                    }
                    delete rs;

                    query = "select device_network_status,count(*) as status from watchdog_device_status where facility_number=? GROUP by device_network_status";
                    prep_stmt = con->prepareStatement(query);
                    prep_stmt->setString(1, to_string(facility_number));
                    rs = prep_stmt->executeQuery();
                    online = 0;
                    offline = 0;
                    while (rs->next()) {
                        if (rs->getInt("device_network_status") == 0)
                            offline = rs->getInt("status");
                        else
                            online = rs->getInt("status");
                    }
                    delete rs;
                    delete prep_stmt;
                    html = "<div class='col-md-3'>";
                    html += "<div class='card'>";
                    html += "<div class='card-body box-profile'>";
                    html += "<h3 class='profile-username text-center'>" + facility_name + "</h3>";
                    html += "<p class='text-muted text-center'>" + to_string(facility_number) + "</p>";
                    html += "<ul class='list-group list-group-unbordered mb-3'>";
                    html += "<li class='list-group-item'><b>" + toString(label["total_carparks"]) + "</b> <a class='float-right'>" + to_string(carparks) + "</a></li>";
                    html += "<li class='list-group-item'><b>" + toString(label["total_devices"]) + "</b> <a class='float-right'>" + to_string(devices) + "</a></li>";

                    html += "<li class='list-group-item'><b>" + toString(label["network_status"]) + "</b>";
                    html += "<a class='float-right'>";
                    html += "<span class='nav-link p-1'>";
                    html += "<span class='fa-stack fa-1x' title='" + toString(label["online"]) + "'>";
                    html += "<i class='fa fa-circle fa-stack-2x text-success'></i>";
                    html += "<strong class='fa-stack-1x text-white'>" + to_string(online) + "</strong>";
                    html += "</span>";
                    html += "<span class='fa-stack fa-1x' title='" + toString(label["offline"]) + "'>";
                    html += "<i class='fa fa-circle fa-stack-2x text-danger'></i>";
                    html += "<strong class='fa-stack-1x text-white'>" + to_string(offline) + "</strong>";
                    html += "</span></span></a></li>";

                    html += "<li class='list-group-item'>";
                    html += "<b>" + toString(label["alarms"]) + "</b>";
                    html += "<a class='float-right'>";
                    html += "<span class='nav-link p-1'>";
                    html += "<span class='fa-stack fa-1x' title='" + toString(label["warnings"]) + "'>";
                    html += "<i class='fa fa-circle fa-stack-2x text-warning'></i>";
                    html += "<strong class='fa-stack-1x text-dark'>" + to_string(get_alarm_count(facility_number, 0, "0", 6, 20)) + "</strong>";
                    html += "</span>";
                    html += "<span class='fa-stack fa-1x' title='" + toString(label["critical_alarms"]) + "'>";
                    html += "<i class='fa fa-circle fa-stack-2x text-danger'></i>";
                    html += "<strong class='fa-stack-1x text-white'>" + to_string(get_alarm_count(facility_number, 0, "0", 0, 5)) + "</strong>";
                    html += "</span></span></a></li></ul>";

                    html += "<button type='button' class='show-facility-details btn btn-block bg-secondary-gradient' facility_number='" + to_string(facility_number) + "'>" + toString(label["more"]) + " <i class='fa fa-arrow-circle-right'></i></button>";
                    html += "</div></div></div>";
                    Php::out << html << endl;
                }

            }
        }
        delete res;
        delete stmt;
        delete con;

    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getDeviceStatusbyFacility", e.what());
    }

}

void getAlarmList(Php::Value data) {
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    int device_number = data["device_number"];
    int facility_number = data["facility_number"];
    int carpark_number = data["carpark_number"];
    string lang = data["language"];
    string labels = "device_name,alarm_severity,report_date,alarm_code,description,comment,dismiss";
    Php::Value label = General.getLabels(lang, labels);
    
    string html_data = "";
    string button = "", dismiss = "";
    try {
        con = General.mysqlConnect(ReportingDB);
        query = "select * from watchdog_device_alarms where alarm_code%2=1 and dismiss=0 and alarm_date_time>=DATE_ADD(CURDATE(),INTERVAL - 7 DAY)";
        if (facility_number > 0)
            query += " and facility_number="+to_string(facility_number);
        if (carpark_number > 0)
            query += " and carpark_number="+to_string(carpark_number);
        if (device_number > 0)
            query += " and device_number in ("+to_string(device_number)+")";

        query += " GROUP by device_number,alarm_code";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        if (res->rowsCount() > 0) {
            html_data = "";

            html_data += "<table id='RecordsTable' class='table table-bordered jspdf-table'>";
            html_data += "<thead class='thead-light'><tr>";
            if (device_number == 0)
                html_data += "<th>"+ toString(label["device_name"]) +"</th>";
            html_data += "<th>"+ toString(label["alarm_severity"]) +"</th>";
            html_data += "<th>"+ toString(label["report_date"]) +"</th>";
            html_data += "<th>"+ toString(label["alarm_code"]) +"</th>";
            html_data += "<th>"+ toString(label["description"]) +"</th>";
            html_data += "<th>"+ toString(label["comment"]) +"</th>";

            html_data += "<th></th>";

            html_data += "</tr>";
            html_data += "</thead>";
            html_data += "<tbody>";


            while (res->next()) {
                if (device_number == 0)
                    html_data += "<td>" + res->getString("device_name") + "</td>";
                html_data += "<td>";
                button = "";
                if (res->getInt("alarm_severity") < 6)
                    button = "danger";
                if ((res->getInt("alarm_severity") >= 6))
                    button = "warning";
                html_data += "<div class='dot-indicator bg-" + button + "-gradient'></div>" + res->getString("alarm_severity") + "</td>";
                html_data += "<td>" + res->getString("alarm_date_time") + "</td>";
                html_data += "<td>" + res->getString("alarm_code") + "</td>";
                html_data += "<td>" + res->getString("alarm_description") + "</td>";
                dismiss = "";
                if (res->getInt("dismiss") == 1)
                    dismiss = "Dismissed";
                html_data += "<td>" + dismiss + "</td>";
                if (data["dismiss"] == 1)
                    html_data += " <td></td>";
                else
                    html_data += "<td><button type='button' class='btn btn-danger btn-dismis-alarm' id='" + res->getString("id") + "' title='"+ toString(label["dismiss"]) +"'><i class='fas fa-window-close'></i></button></td>";

                html_data += " </tr>";
            }
            html_data += "</tbody></table>";
        } else
            html_data = "<strong>No alarms</strong>";
        delete res;
        delete prep_stmt;
        delete con;
        Php::out << html_data << endl;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getAlarmList", e.what());
    }
}

void dismissAlarm(Php::Value data) {
    sql::Connection *con;
    sql::ResultSet *res;
    sql::PreparedStatement *prep_stmt;
    int id = data["id"];
    try {
        con = General.mysqlConnect(ReportingDB);
        query = "select alarm_code,device_number from watchdog_device_alarms where id=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1, id);
        res = prep_stmt->executeQuery();
        if (res->next()) {
            int device_number = res->getInt("device_number");
            int code = res->getInt("alarm_code");
            query = "update watchdog_device_alarms set dismiss=1 where device_number=? and alarm_code=?";
            prep_stmt = con->prepareStatement(query);
            prep_stmt->setInt(1,device_number);
            prep_stmt->setInt(2,code);
            prep_stmt->executeUpdate();
        }
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("dismissAlarm", e.what());
    }
}

void getDeviceStatusbyDevice(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    sql::PreparedStatement *prep_stmt;
    Php::Value device;
    Php::Array devicearr;
    string html_data, data_status, category, image_url;
    int i = 0, j = 0;
    int facility_number = data["facility_number"];
    int carpark_number = data["carpark_number"];
    string lang = data["language"];
    string labels = "category,device_number,ip_address,more,notifications,alarms,online,offline";
    Php::Value label = General.getLabels(lang, labels);
    int device_network_status = 1;

    try {
        Php::Value facility = getFacilityFeatures();
        int cloud_enabled = facility["cloud_enabled"];

        if (cloud_enabled == 1) {
            //            string url = "www.parcxcloud.com";
            //            string pingcmd = "ping -w 2 ";
            //            pingcmd += url;
            //            int pingres = system(pingcmd.c_str());
            //
            //            if (pingres == 0) 
            //               device_network_status=1;
            //            else
            //                device_network_status=0;
        }

        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        query = "select * from "+string(ServerDB)+".system_device_category";
        res = stmt->executeQuery(query);
        while (res->next()) {
            device["type"] = res->getInt("id");
            device["name"] = string(res->getString("category_name"));
            device["image"] = string(res->getString("image_url"));
            device["group"] = string(res->getString("group"));
            devicearr[i] = device;
            i++;
        }
        delete res;



        if (cloud_enabled == 1) {
            query = "select max(cloud_upload_date_time) as cloud_upload_date_time from "+string(ServerDB)+".pxcloud_upload_settings";
            res = stmt->executeQuery(query);
            res->next();
            string cloud_upload_date_time = res->getString("cloud_upload_date_time");
            delete res;

            query = "select max(cloud_download_date_time) as cloud_download_date_time from "+string(ServerDB)+".pxcloud_download_settings";
            res = stmt->executeQuery(query);
            res->next();
            string cloud_download_date_time = res->getString("cloud_download_date_time");
            delete res;


            html_data += "<div class='col-lg-3 col-md-6 block-data' data-device-status='" + to_string(device_network_status) + " data-number='0' data-type='0' data-img='" + image_url + "' data-status='" + data_status + "' >";
            html_data += "<div class='card p-2'>";

            html_data += "<div class='card-header'>";
            html_data += "<div class='nav-item d-flex justify-content-between align-items-center'>";

            html_data += "<h3 class='card-title'> Parcx Cloud</h3>";
            html_data += "<span>";





            if (device_network_status == 1) {
                html_data += "<span class='float-right ml-3 header-icon'>";
                html_data += "<i class='fas fa-server text-success' title='"+toString(label["online"])+"'></i>";
                html_data += "</span>";
                html_data += "</span>";
            } else {
                html_data += "<span class='float-right ml-3 header-icon'>";
                html_data += "<i class='fas fa-server text-danger' title=''"+toString(label["offline"])+"''></i>";
                html_data += "</span>";
                html_data += "</span>";
            }



            html_data += "</div>";
            html_data += "</div>";

            html_data += "<div class='card-body p-0'>";
            html_data += "<div class='row no-gutters'>";

            html_data += "<div class='col-4 block-view-img my-auto text-center'><img class='img-fluid' src='"+string(IMG)+"/icon/device_icons/cloud.jpg'></div>";

            html_data += "<div class='col-8'>";
            html_data += "<ul class='nav flex-column'>";
            html_data += "<li class='nav-item mt-2'>";
            html_data += "<span class='nav-link'><i class='fa fa-upload'></i>";
            html_data += "<span class='float-right'>" + cloud_upload_date_time + "</span>";
            html_data += "</span>";
            html_data += "</li>";

            html_data += "<li class='nav-item mt-2'>";
            html_data += "<span class='nav-link'><i class='fa fa-download'></i>";
            html_data += "<span class='float-right'>" + cloud_download_date_time + "</span>";
            html_data += "</span>";
            html_data += "</li>";





            html_data += "</ul>";
            html_data += "</div>";

            html_data += "</div>"; // close row
            html_data += "</div>"; // close card-body

            html_data += "</div>";
            html_data += "</div>";
            Php::out << html_data << endl;
        }


        query = "select a.*,b.mode_of_operation from watchdog_device_status a,"+string(ServerDB)+".system_devices b where a.device_number=b.device_number and a.facility_number=? and a.carpark_number=? order by device_type,device_number";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,facility_number);
        prep_stmt->setInt(2,carpark_number);
        res = prep_stmt->executeQuery();

        while (res->next()) {
            html_data = "";
            i = res->getInt("device_type");
            for (j = 0; j < (signed)devicearr.size(); j++) {
                if (devicearr[j]["type"] == i) {
                    data_status = toString(devicearr[j]["group"]);
                    category = toString(devicearr[j]["name"]);
                    image_url = toString(devicearr[j]["image"]);
                    break;
                }
            }



            html_data += "<div class='col-lg-3 col-md-6 block-data' data-device-status='" + res->getString("device_network_status") + "' data-number='" + res->getString("device_number") + "' data-type='" + to_string(i) + "' data-img='" + image_url + "' data-status='" + data_status + "' data-toggle='modal' data-target='#error-log-modal'>";
            html_data += "<div class='card p-2'>";

            html_data += "<div class='card-header'>";
            html_data += "<div class='nav-item d-flex justify-content-between align-items-center'>";

            html_data += "<h3 class='card-title'>" + res->getString("device_name") + "</h3>";
            html_data += "<span>";


            html_data += "<span class='p-1'>";
            html_data += "<span class='fa-stack fa-1x' title='warnings'>";
            html_data += "<i class='fa fa-circle fa-stack-2x text-warning'></i>";
            html_data += "<strong class='fa-stack-1x text-dark'>" + to_string(get_alarm_count(facility_number, carpark_number, res->getString("device_number"), 6, 20)) + "</strong>";
            html_data += "</span>";

            html_data += "<span class='fa-stack fa-1x' title='critical alarms'>";
            html_data += "<i class='fa fa-circle fa-stack-2x text-danger' title='critical alarms'></i>";
            html_data += "<strong class='fa-stack-1x text-white'>" + to_string(get_alarm_count(facility_number, carpark_number, res->getString("device_number"), 0, 5)) + "</strong>";
            html_data += "</span>";
            html_data += "</span>";


            if (res->getInt("device_network_status") == 1) {
                html_data += "<span class='float-right ml-3 header-icon'>";
                html_data += "<i class='fas fa-server text-success' title='Server Connectivity'></i>";
                html_data += "</span>";
                html_data += "</span>";
            } else {
                html_data += "<span class='float-right ml-3 header-icon'>";
                html_data += "<i class='fas fa-server text-danger' title='Server Connectivity'></i>";
                html_data += "</span>";
                html_data += "</span>";
            }



            html_data += "</div>";
            html_data += "</div>";

            html_data += "<div class='card-body p-0'>";
            html_data += "<div class='row no-gutters'>";

            html_data += "<div class='col-4 block-view-img my-auto text-center'><img class='img-fluid' src='"+string(IMG)+"/icon/device_icons/" + image_url + "'></div>";

            html_data += "<div class='col-8'>";
            html_data += "<ul class='nav flex-column'>";
            html_data += "<li class='nav-item'>";
            html_data += "<span class='nav-link'>";
            html_data += "<span class='float-right device_category'>" + category + "</span>";
            html_data += "</span>";
            html_data += "</li>";

            html_data += "<li class='nav-item'>";
            html_data += "<span class='nav-link'>";
            html_data += "<span class='float-right device_ip'>" + res->getString("device_ip") + "</span>";
            html_data += "</span>";
            html_data += "</li>";


            html_data += "<li class='nav-item'>";
            html_data += "<span class='nav-link'>";
            if (res->getInt("mode_of_operation") == 0)
                html_data += "<span class='float-right'>Standard Operation Mode</span>";
            if (res->getInt("mode_of_operation") == 1)
                html_data += "<span class='float-right'>Free Passage Mode</span>";
            if (res->getInt("mode_of_operation") == 2)
                html_data += "<span class='float-right'>Lane Closed Mode</span>";
            html_data += "</span>";
            html_data += "</li>";


            html_data += "</ul>";
            html_data += "</div>";

            html_data += "</div>"; // close row
            html_data += "</div>"; // close card-body

            html_data += "</div>";
            html_data += "</div>";
            Php::out << html_data << endl;
        }
        Php::out << "<input type='hidden' value='" +toString(label["alarms"])+" : "+ to_string(get_alarm_count(facility_number, carpark_number, "0", 0, 20)) + "' id='alarm_count'>" << endl;
        delete res;
        delete stmt;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        Php::out << e.what() << endl;
        writeException("getDeviceStatusbyDevice", e.what());
    }
}

void deviceDetails(Php::Value data) {
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    string device_number = data["device_number"];
    string facility_number = data["facility_number"];
    string carpark_number = data["carpark_number"];
    string category = data["device_category"];
    string lang = data["language"];
    string labels = "active,de_active,category,device_number,ip_address,more,notifications,presence_loop_status,last_presence_loop_active,last_presence_loop_deactive,safety_loop_status,last_safety_loop_active,last_safety_loop_deactive,last_shift_id,operator_name,shift_status,shift_earnings";
    Php::Value label = General.getLabels(lang, labels);
    string html_data = "";
    try {
        con = General.mysqlConnect(DashboardDB);
        query = "select * from watchdog_device_status where device_number=? and facility_number=? and carpark_number=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,stoi(device_number));
        prep_stmt->setString(2,facility_number);
        prep_stmt->setInt(3,stoi(carpark_number));
        res = prep_stmt->executeQuery();

        if (res->next()) {
            if (res->getInt("device_network_status") == 1)
                html_data += "<h4 class='mb-3'>" + res->getString("device_name") + "<span class='float-right header-icon'><i class='fas fa-server text-success' title='Server Connectivity'></i></span></h4> ";
            else
                html_data += "<h4 class='mb-3'>" + res->getString("device_name") + "<span class='float-right header-icon'><i class='fas fa-server text-danger' title='Server Connectivity'></i></span></h4> ";
            html_data += "<hr><table class='w-100'> ";
            html_data += "<tr><td>" + toString(label["category"]) + "</td><td>" + category + "</td></tr> ";
            html_data += "<tr><td>" + toString(label["device_number"]) + "</td><td>" + res->getString("device_number") + "</td></tr> ";
            html_data += "<tr><td>" + toString(label["ip_address"]) + "</td><td>" + res->getString("device_ip") + "</td></tr> ";
            if (res->getInt("device_type") == 1 || res->getInt("device_type") == 2) {
                if (res->getInt("presence_loop_status") == 1)
                    html_data += "<tr><td>" + toString(label["presence_loop_status"]) + "</td><td><span class='dot-indicator bg-success-gradient  title='Active'></span>" + toString(label["active"]) + "</td></tr> ";
                else
                    html_data += "<tr><td>" + toString(label["presence_loop_status"]) + "</td><td><span class='dot-indicator bg-danger-gradient  title='Deactive'></span>" + toString(label["de_acive"]) + "</td></tr> ";

                html_data += "<tr><td>" + toString(label["last_presence_loop_active"]) + ":  " + res->getString("last_presence_loop_active") + "</td><td>" + toString(label["last_presence_loop_deactive"]) + ": " + res->getString("last_presence_loop_deactive") + "</td></tr> ";
                if (res->getInt("safety_loop_status") == 1)
                    html_data += "<tr><td>" + toString(label["safety_loop_status"]) + "</td><td><span class='dot-indicator bg-success-gradient  title='Active'></span>" + toString(label["active"]) + "</td></tr> ";
                else
                    html_data += "<tr><td>" + toString(label["safety_loop_status"]) + "</td><td><span class='dot-indicator bg-danger-gradient  title='Deactive'></span>" + toString(label["de_active"]) + "</td></tr> ";

                html_data += "<tr><td>" + toString(label["last_safety_loop_active"]) + ": " + res->getString("last_safety_loop_active") + "</td><td>" + toString(label["last_safety_loop_deactive"]) + ": " + res->getString("last_safety_loop_deactive") + "</td></tr> ";
            }
            if (res->getInt("device_type") == 3 || res->getInt("device_type") == 5) {
                
                query = "select * from "+string(ReportingDB)+".revenue_shifts where device_number=? and facility_number=? and carpark_number=? order by last_updated_date_time desc limit 1";
                prep_stmt = con->prepareStatement(query);
                prep_stmt->setInt(1,stoi(device_number));
                prep_stmt->setString(2,facility_number);
                prep_stmt->setInt(3,stoi(carpark_number));
                res = prep_stmt->executeQuery();
                if (res->next()) {
                    Php::Value fdata = getFacilityFeatures();
                    int d = fdata["decimal_places"];
                    string currency = fdata["currency"];

                    if (res->getInt("shift_status") == 1)
                        html_data += "<tr><td>" + toString(label["shift_status"]) + "</td><td><span class='dot-indicator bg-success-gradient  title='Open'></span>Open</td></tr> ";
                    else
                        html_data += "<tr><td>" + toString(label["shift_status"]) + "</td><td><span class='dot-indicator bg-danger-gradient  title='Closed'></span>Closed</td></tr> ";
                    html_data += "<tr><td>" + toString(label["operator_name"]) + "</td><td>" + res->getString("operator_name") + "</td></tr> ";
                    html_data += "<tr><td><strong>" + toString(label["shift_earnings"]) + "</td><td>" + currency + " " + SetDoublePrecision(res->getDouble("shift_earnings"), d) + "</td></tr> ";
                }
            }
            html_data += "</table> ";

        }
        delete res;
        delete prep_stmt;
        delete con;
        Php::out << html_data << endl;
    } catch (const std::exception& e) {
        writeException("deviceDetails", e.what());
    }
}

void cashlevels(Php::Value data) {
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    int device_number = data["device_number"];
    int facility_number = data["facility_number"];
    int carpark_number = data["carpark_number"];
    string html_data = "";
    int total = 0, apm_total = 0;
    string lang = data["language"];
    string labels = "denomination,recycler_level,cashbox_level,total";
    Php::Value label = General.getLabels(lang, labels);
    try {
        con = General.mysqlConnect(DashboardDB);
        
        query = "SELECT a.device_number,a.device_name,a.denomination,a.current_level as recycler_current_level,b.current_level as cashbox_current_level FROM apm_recycle_levels as a join apm_cashbox_levels as b on a.denomination=b.denomination and a.device_number=b.device_number and a.carpark_number=b.carpark_number and a.facility_number=b.facility_number and a.device_number= ? and a.carpark_number =? and a.facility_number=? order by denomination asc";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,device_number);
        prep_stmt->setInt(2,carpark_number);
        prep_stmt->setInt(3,facility_number);
        res = prep_stmt->executeQuery();
        if (res->rowsCount() > 0) {
            html_data = "<table id='cash_level_table' class='table table-bordered jspdf-table'>";
            html_data += "<thead class='thead-light'><tr><th></th>";
            html_data += "<th>" + toString(label["denomination"]) + "</th>";
            html_data += "<th>" + toString(label["recycler_level"]) + "</th>";
            html_data += "<th>" + toString(label["cashbox_level"]) + "</th>";
            html_data += "<th>" + toString(label["total"]) + "</th>";
            html_data += "</tr></thead>";
            html_data += "<tbody>";

            apm_total = 0;
            Php::Value fdata = getFacilityFeatures();
            int d = fdata["decimal_places"];
            string currency = fdata["currency"];
            while (res->next()) {
                html_data += "<tr>";
                html_data += "<td><img width='100' height='42' src='"+string(IMG)+"/currency/" + res->getString("denomination") + currency + ".jpg'></td>";
                html_data += "<td>" + res->getString("denomination") + "</td>";
                html_data += "<td>" + res->getString("recycler_current_level") + "</td>";
                html_data += "<td>" + res->getString("cashbox_current_level") + "</td>";
                total = res->getInt("denomination") * res->getInt("cashbox_current_level") + res->getInt("denomination") * res->getInt("recycler_current_level");
                html_data += "<td>" + to_string(total) + "</td>";
                html_data += "</tr>";
                apm_total = apm_total + total;
            }
            //if (html_data != "")
                //html_data += "<tr><td  colspan='4'><h3 class='float-right'>" + toString(label["total"]) + "</h3></td><td><h3>" + SetDoublePrecision(apm_total, d) + "</h3></td></tr>";
            html_data += "</tbody></table>";
            Php::out << "<h3>" + toString(label["total"]) + " : " + SetDoublePrecision(apm_total, d) + "</h3>"<< endl;
            


        }

        delete res;
        delete prep_stmt;
        delete con;
        Php::out << html_data << endl;
    } catch (const std::exception& e) {
        writeException("cashlevels", e.what());

    }

}

void getDeviceCategoryTab(Php::Value data) {
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    
    string lang = data["language"];
    string labels = "all_devices,entry_exit_columns,payment_machines,controllers,camera,validator";
    Php::Value label = General.getLabels(lang, labels);
    try {
        con = General.mysqlConnect(DashboardDB);
        stmt = con->createStatement();
        string html_data="";
        html_data+="<div class='tab-link active' data-target='all'>"+toString(label["all_devices"])+"</div>";
        
        query = "SELECT device_type FROM watchdog_device_status where device_type in(1,2)";
        res = stmt->executeQuery(query);
        if(res->next())
            html_data += "<div class='tab-link' data-target='columns'>"+toString(label["entry_exit_columns"])+"</div>";
        delete res;
        
        query = "SELECT device_type FROM watchdog_device_status where device_type in(3,4,5)";
        res = stmt->executeQuery(query);
        if(res->next())
            html_data+="<div class='tab-link' data-target='payment_machines'>"+toString(label["payment_machines"])+"</div>";
        delete res;
        
        query = "SELECT device_type FROM watchdog_device_status where device_type in(6,7)";
        res = stmt->executeQuery(query);
        if(res->next())
            html_data+="<div class='tab-link' data-target='controllers'>"+toString(label["controllers"])+"</div>";
        delete res;
        
        query = "SELECT device_type FROM watchdog_device_status where device_type in(8)";
        res = stmt->executeQuery(query);
        if(res->next())
            html_data+="<div class='tab-link' data-target='camera'>"+toString(label["camera"])+"</div>";
        delete res;
        
        query = "SELECT device_type FROM watchdog_device_status where device_type in(11)";
        res = stmt->executeQuery(query);
        if(res->next())
            html_data+="<div class='tab-link' data-target='validator'>"+toString(label["validator"])+"</div>";
        delete res;
             
        Php::out<<html_data<<endl;
        delete stmt;
        delete con;        
    } catch (const std::exception& e) {
        writeException("getDeviceCategoryTab", e.what());

    }

}

Php::Value parcxV2Dashboard(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:getPlateCorrectionRequiredTable(data);
            break;
        case 2:response=correctPlateNumber(data);
            break;
        case 3:getPlateCorrectedTable(data);
            break;
        case 4:response = hourlyOccupancyReport(data);
            break;
        case 5:response = averageHourlyOccupancyReport(data);
            break;
        case 6:getPlateMismatchTable(data);
            break;
        case 7:response = getMismatchPlateDetails(data);
            break;
        case 8:correctPlateMismatch(data);
            break;
        case 9:response = General.getApplicationLabels(data);
            break;
        case 10:getDeviceStatusbyFacility(data);
            break;
        case 11:getDeviceStatusbyCarpark(data);
            break;
        case 12:getDeviceStatusbyDevice(data);
            break;
        case 13:deviceDetails(data);
            break;
        case 14:cashlevels(data);
            break;
        case 15:getAlarmList(data);
            break;
        case 16:dismissAlarm(data);
            break;
        case 17:getDeviceCategoryTab(data);
            break;
        case 20:showliverevenueFacility(data);
            break;
        case 21:response = liveRevenueFacility();
            break;
        case 22:showliverevenueCarpark(data);
            break;
        case 23:response = liveRevenueCarpark(data);
            break;
        case 24:getRevenueSummary(data);
            break;
        case 25:response = revenueLastdays();
            break;
        case 26:response = liveRevenueDevice(data);
            break;
        case 27:getOccupancyFacility(data);
            break;
        case 28:getOccupancyCarpark(data);
            break;
        case 29:getOccupancyDevice(data);
            break;
    }
    return response;
}


extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_V2_Dashboard", "1.0");
        extension.add<parcxV2Dashboard>("parcxV2Dashboard");
        return extension;
    }
}
