//make -B
//sudo make install
//service apache2 restart

#include <phpcpp.h>
#include<iostream>
#include<stdlib.h>
#include<fstream>
#include<time.h>
#include<ctime>
#include "mysql_connection.h"
#include "mysql_driver.h"
#include <mysql.h>
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include<cppconn/prepared_statement.h>

using namespace std;
sql::Connection *conn;
sql::Driver *driver;
sql::Statement *stmt;
sql::ResultSet *res;
sql::PreparedStatement *prep;
sql::ResultSetMetaData *rm;

#define DBServer "DBServer"
#define DBUsername "parcxservice"
#define DBPassword "1fromParcx!19514"
#define DATABASE "parcx_server"

sql::Connection* mySQLConnect() {

    driver = get_driver_instance();
    conn = driver->connect(DBServer, DBUsername, DBPassword);
    conn->setSchema(DATABASE);
    return conn;
}

string currentDateTime() {
    time_t now = time(NULL);
    std::tm* timeinfo;
    char buffer[80];
    std::time(&now);
    timeinfo = std::localtime(&now);
    std::strftime(buffer, 80, "%d-%m-%Y %H:%M:%S", timeinfo);
    string datetime(buffer);
    return datetime;
}

void writeException(string function, string txt) {
    ofstream fp;
    fp.open("/opt/parcx/Logs/WebApplication/ExceptionLogs/PX-CustomerMessages.log", ios::app);
    fp << currentDateTime() << " " << function << ":: " << txt << endl;
    fp.close();
}//end of .writeException

void writeLog(string function, string txt) {
    ofstream fp;
    fp.open("/opt/parcx/Logs/WebApplication/ApplicationLogs/PX-CustomerMessages.log", ios::app);
    fp << currentDateTime() << " " << function << ":: " << txt << endl;
    fp.close();
}//end of .writeException

Php::Value UpdateRecord(Php::Value data) {
    Php::Value result = 0;
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();        
        int id = data["id"];
        string language = data["language"];
        string line1 = data["line1"];
        string line2 = data["line2"];
        string line3 = data["line3"];
        prep = conn->prepareStatement("Update customer_messages set " + language + "_line1=?," + language + "_line2=?," + language + "_line3=? where id = ?");

        prep->setString(1, line1);
        prep->setString(2, line2);
        prep->setString(3, line3);
        prep->setInt(4, id);

        if (prep->executeUpdate()) {
            result["success"] = 1;
        }

        delete prep;
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("UpdateRecord", e.what());
    }
    return result;

}

Php::Value InsertRecord(Php::Value data) {
    Php::Value result;
    string sql = "";
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();        
        int device_type = data["device_type"];
        string message = data["message"];
        string media_path = data["media_path"];       
        prep = conn->prepareStatement("Insert into customer_messages (device_type,message,media_path)values(?,?,?)");

        prep->setInt(1, device_type);
        prep->setString(2, message);
        prep->setString(3, media_path);
        prep->executeUpdate();
        
        delete prep;
        
           
        sql = "Update customer_messages set ";
        res = stmt->executeQuery("Show columns from customer_messages where FIELD like '%_line%'");
        int n = res->rowsCount();
        int i = 0;
        while (res->next()) {

            string field = res->getString("Field");

            string val = data[field];            
            sql = sql + field + " = '" + val + "'";

            if (i < (n - 1)) {
                sql = sql + ", ";
            }
            i++;
        }
        delete res;

        sql = sql + " where device_type = " + to_string(device_type) + " and message = '" + message + "'";       
        if (stmt->executeUpdate(sql)) {
            result["text"] = "success";
            result["media"] = "success";    
        }

       writeException("InsertRecord",sql);
        delete stmt;
        delete conn;
        }    
    catch (const std::exception& e) {
        writeException("InsertRecord", e.what());
    }
    return result;
}



Php::Value GetMessageLanguages(Php::Parameters &params) {
    Php::Value result;
    string temp;
    int i = 0;
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("Show columns from`customer_messages` where FIELD like '%_line1'");
        while (res->next()) {
            temp = res->getString("Field");
            temp = temp.substr(0, temp.size() - 6);
            result[i] = temp;
            i++;
        }
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("GetMessageLanguages", e.what());
    }
    return result;
}

void GetMessageTextByLanguage(Php::Value data) {    
    int device_type = data["type"];
    string language = data["language"];
    try {
        int n = 0;
        string id = "0";
        conn = mySQLConnect();
        stmt = conn->createStatement();        
        res = stmt->executeQuery("Select id,message," + language + "_line1," + language + "_line2," + language + "_line3,media_path from customer_messages where status =1 and device_type = " + to_string(device_type));
        if (res->rowsCount() > 0) {
            Php::out << "<input type='hidden' name = 'device_type' id = 'device_type' value='" + to_string(device_type) + "'>" << std::endl;
            Php::out << "<input type='hidden' name = 'message_language' id = 'message_language' value='" + language + "'>" << std::endl;
            Php::out << "<div class='card card-primary mb-4 col-md-12 p-0' id='active_messages' data-status='active_messages'>" << std::endl;
            Php::out << "<div class='card-header'>" << std::endl;
            Php::out << "<div class='d-flex justify-content-between align-items-center'>" << std::endl;
            Php::out << "<div class='col'>No</div>" << std::endl;
            Php::out << "<div class='col-2'>Message</div>" << std::endl;
            Php::out << "<div class='col-2'>" + language + " Line 1</div>" << std::endl;
            Php::out << "<div class='col-2'>" + language + " Line 2</div>" << std::endl;
            Php::out << "<div class='col-2'>" + language + " Line 3</div>" << std::endl;
            Php::out << "<div class='col'>Image</div>" << std::endl;
            //Php::out<<"<div class='col'>Upload</div>"<< std::endl;
            //Php::out<<"<div class='col'></div>"<< std::endl;
            Php::out << "<div class='col'></div>" << std::endl;
            Php::out << "<div class='col'></div></div></div>" << std::endl;
            n = 1;

            while (res->next()) {
                id = res->getString("id");
                Php::out << "<div class='table-view'>" << std::endl;
                Php::out << "<div class='card-text'>" << std::endl;
                Php::out << "<div class='d-flex justify-content-between align-items-center'>" << std::endl;
                Php::out << "<div class='col'>" + to_string(n) + "</div>" << std::endl;
                Php::out << "<div class='col-2' id = 'message" + id + "'>" + res->getString("message") + "</div>" << std::endl;
                Php::out << "<div class='col-2' id = '" + language + "1" + id + "'>" + res->getString(language + "_line1") + "</div>" << std::endl;
                Php::out << "<div class='col-2' id = '" + language + "2" + id + "'>" + res->getString(language + "_line2") + "</div>" << std::endl;
                Php::out << "<div class='col-2' id = '" + language + "3" + id + "'>" + res->getString(language + "_line3") + "</div>" << std::endl;
                //Php::out<< "<div class='col' id = 'media_path"+id+"'><a target='_blank' href='"+res->getString("media_path")+"'><img src='"+res->getString("media_path")+"' alt='Img'></a></div>"<< std::endl;
                Php::out << "<div class='col' id = 'media_path" + id + "'><img class='openModal openImageDialog" + res->getString("message") + "' src='" + res->getString("media_path") + "' alt='Img' width='100' height='100' data-toggle='modal' data-target='#uploadModal' data-backdrop='false' data-id=" + res->getString("message") + "></div>" << std::endl;

                //Php::out << "<div class='col'><input data-toggle='modal' data-target='#uploadModal' data-backdrop='false' class='btn btn-info btn-edit-image' id ='editimage"+id+"'   type='button'  value='Upload'  data-id='"+res->getString("message")+"'  style='visibility:hidden' ></div>"<< std::endl;
                Php::out << "<div class='col'>" << std::endl;
                //Php::out<< "<input type='file' id='myfile' name='myfile'>"<< std::endl;
                Php::out << "<input class='btn btn-danger btn-block btn-cancel-parking' id ='cancelbtn" + id + "'  type='submit'  value='Cancel' onclick='CancelEdit(" + id + ")' style='visibility:hidden'/>" << std::endl;
                Php::out << "</div>" << std::endl;
                Php::out << "<div class='col' id = 'editdivfixed" + id + "'>" << std::endl;
                Php::out << "<input class='btn btn-info btn-block btn-edit-parking' id ='editmessage" + id + "' type='submit'  value='Edit' onclick = 'EditMessage(" + id + ")' id='" + id + "' >" << std::endl;
                Php::out << "</div>" << std::endl;
                Php::out << "</div>" << std::endl;
                Php::out << "</div>" << std::endl;
                Php::out << "</div>" << std::endl;
                n++;
            }
            Php::out << " </div>" << std::endl;
        }

        delete res;
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("GetMessageTextByLanguage", e.what());
    }


}

Php::Value UpdateImagePath(Php::Value data) {
    Php::Value result = 0;
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        string message = data["message"];
        string media_path = data["media_path"];
        prep = conn->prepareStatement("Update customer_messages set media_path = ? where message=?");

        prep->setString(1, media_path);
        prep->setString(2, message);

        if (prep->executeUpdate()) {
            result["success"] = 1;
        }

        delete prep;
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("UpdateImagePath", e.what());
    }
    return result;
}

Php::Value GetCustomerMessageSettings() {
    Php::Value result;

    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("Select setting_name,setting_value from system_settings where setting_name in('customer_message_limit','customer_media_upload_limit','customer_media_path')");
        while (res->next()) {
            if (res->getString("setting_name") == "customer_message_limit") {
                result["message_limit"] = res->getString("setting_value").asStdString();
            } else if (res->getString("setting_name") == "customer_media_upload_limit") {
                result["upload_limit"] = res->getString("setting_value").asStdString();
            } else if (res->getString("setting_name") == "customer_media_path") {
                result["media_path"] = res->getString("setting_value").asStdString();
            }
        }
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("GetCustomerMessageSettings", e.what());
    }
    return result;
}

Php::Value parcxCustomerMessageSettings(Php::Parameters &params)
    {
    Php::Value data=params[0];      
    int task=data["task"];   
    Php::Value response;
    switch (task)
        {
        case 1:GetMessageTextByLanguage(data);break;
        case 2:response=UpdateRecord(data);break;
        case 3:response=InsertRecord(data);break;
        case 4:response=UpdateImagePath(data);break;
        case 5:response=GetCustomerMessageSettings();break;        
        }
    return response;
    }

extern "C" {  
    PHPCPP_EXPORT void *get_module() {              
        static Php::Extension extension("PX_CustomerMessages", "1.0");
        extension.add<parcxCustomerMessageSettings>("parcxCustomerMessageSettings");        
        extension.add<GetMessageLanguages>("GetMessageLanguages");                                       
        return extension;
    }
}
