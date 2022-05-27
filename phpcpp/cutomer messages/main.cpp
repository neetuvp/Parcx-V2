//make -B
//sudo make install
//service apache2 restart

#include <phpcpp.h>
#include<iostream>
#include<stdlib.h>
#include<fstream>
#include<time.h>
#include<ctime>

#include<libssh/libssh.h>
#include<libssh/sftp.h>
#include <fcntl.h>

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
#define MAX_XFER_BUF_SIZE 10240

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
    Php::out<<txt<<endl;
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
    } catch (const std::exception& e) {
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
        string label=data["label"];
        string category=data["category"];
        prep = conn->prepareStatement("Insert into customer_messages (device_type,message,media_path,label,category)values(?,?,?,?,?)");

        prep->setInt(1, device_type);
        prep->setString(2, message);
        prep->setString(3, media_path);
        prep->setString(4, label);
        prep->setString(5, category);
        prep->executeUpdate();

        delete prep;


        string id = "0";
        res = stmt->executeQuery("select id from customer_messages where device_type = " + to_string(device_type) + " and message = '" + message + "'");
        if (res->next())
            id = res->getString("id");
        delete res;

        result["id"] = id;

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

        sql = sql + " where id=" + id;
        if (stmt->executeUpdate(sql)) {
            result["text"] = "success";
            result["media"] = "success";
        }

        writeException("InsertRecord", sql);
        delete stmt;
        delete conn;
    } catch (const std::exception& e) {
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
    } catch (const std::exception& e) {
        writeException("GetMessageLanguages", e.what());
    }
    return result;
}

void GetMessageTextByLanguage(Php::Value data) {
    int device_type = data["type"];
    string language = data["language"];
    int category=data["category"];
    try {
        //int n = 0;
        string id = "0";
        string media_path = "";
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("Select setting_name,setting_value from system_settings where setting_name in('customer_media_path')");
        if (res->next()) {
            media_path = res->getString("setting_value");
        }
        delete res;
        res = stmt->executeQuery("Select id,message,label," + language + "_line1," + language + "_line2," + language + "_line3,media_path from customer_messages where status =1 and device_type = " + to_string(device_type)+" and category="+to_string(category));
        if (res->rowsCount() > 0) {
            Php::out << "<div class='card'>" << endl;
            Php::out << "<div class='card-body'> " << endl;
            Php::out << "<table id='RecordsTable' class='table  table-bordered jspdf-table'>" << endl;
            Php::out << "<thead class='thead-light'><tr>" << endl;
            //Php::out << "<th>No</th>" << endl;
            Php::out << "<th>Message</th>" << endl;
            Php::out << "<th>" + language + " Line 1</th>" << endl;
            Php::out << "<th>" + language + " Line 2</th>" << endl;
            Php::out << "<th>" + language + " Line 3</th>" << endl;
            Php::out << "<th>Image</th>" << endl;            
            Php::out << "<th></th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            //n = 1;

            while (res->next()) {
                id = res->getString("id");
                Php::out << "<tr>" << endl;
                //Php::out << "<td>" + to_string(n) + "</td>" << std::endl;
                Php::out << "<td id = 'message" + id + "'>" + res->getString("label") + "</td>" << std::endl;
                Php::out << "<td id = '" + language + "1" + id + "'>" + res->getString(language + "_line1") + "</td>" << std::endl;
                Php::out << "<td id = '" + language + "2" + id + "'>" + res->getString(language + "_line2") + "</td>" << std::endl;
                Php::out << "<td id = '" + language + "3" + id + "'>" + res->getString(language + "_line3") + "</td>" << std::endl;
                Php::out << "<td id = 'media_path" + id + "'><img class='openModal openImageDialog" + id + "' src='" + media_path + "/" + res->getString("media_path") + "' alt='Img' width='100' height='100' data-toggle='modal' data-target='#uploadModal' data-backdrop='false' data-id=" + id + "></td>" << std::endl;                
                
                Php::out << "<td id = 'editdivfixed" + id + "'>" << std::endl;
                Php::out<<"<button type='button' class='btn btn-info' title='Edit' onclick = 'EditMessage(" + id + ")'><i class='fas fa-edit'></i></button>"<<endl;
                //Php::out << "<input class='btn btn-info btn-block' id ='editmessage" + id + "' type='submit'  value='Edit' onclick = 'EditMessage(" + id + ")' id='" + id + "' >" << std::endl;
                Php::out << "</td></tr>" << std::endl;
                //n++;
            }
            Php::out << "</tbody></table>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
        }

        delete res;
        delete stmt;
        delete conn;
    } catch (const std::exception& e) {
        writeException("GetMessageTextByLanguage", e.what());
    }


}

Php::Value UpdateImagePath(Php::Value data) {
    Php::Value result = 0;
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        string id = data["id"];
        string media_path = data["media_path"];
        prep = conn->prepareStatement("Update customer_messages set media_path = ? where id=?");

        prep->setString(1, media_path);
        prep->setString(2, id);

        if (prep->executeUpdate()) {
            result["success"] = 1;
        }

        delete prep;
        delete stmt;
        delete conn;
    } catch (const std::exception& e) {
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
    } catch (const std::exception& e) {
        writeException("GetCustomerMessageSettings", e.what());
    }
    return result;
}

void showDeviceListForSynchMessage() {
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("select * from system_devices where device_category<5");
        if (res->rowsCount() > 0) {
            Php::out << "<thead>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Device Number</th>" << endl;
            Php::out << "<th>Device Name</th>" << endl;
            Php::out << "<th>Device IP</th>" << endl;
            Php::out << "<th>Device Media Path</th>" << endl;
            Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
            Php::out << "<td>" + res->getString("device_number") + "</td>" << endl;
            Php::out << "<td>" + res->getString("device_name") + "</td>" << endl;
            Php::out << "<td>" + res->getString("device_ip") + "</td>" << endl;
            Php::out << "<td>" + res->getString("media_path") + "</td>" << endl;
            Php::out << "<td>" << std::endl;
            Php::out << "<button type='button' class='btn btn-warning synch-messages-btn'>Synch Messages</button>" << std::endl;
            Php::out << "<button type='button' class='btn btn-warning synch-images-btn'>Synch Images</button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }
        delete res;
        delete stmt;
        delete conn;
    } catch (const std::exception& e) {
        writeException("showDeviceListForSynchMessage", e.what());
    }

}

Php::Value synchMessages(Php::Value data) {
    string message = "";
    string id = data["id"];
    string ip, media_path, device_name, device_category;
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("select device_category,device_ip,device_name,media_path  from system_devices where id=" + id);
        if (res->next()) {
            ip = res->getString("device_ip");
            device_name = res->getString("device_name");
            media_path = res->getString("media_path");
            device_category = res->getString("device_category");
        }
        if (stoi(device_category) == 1 || stoi(device_category) == 2)
            device_category = "1,2";


        sql::Driver *driver;
        sql::Connection *con_local = NULL;
        sql::Statement *stmt_local;
        sql::PreparedStatement *prep_stmt;

        driver = get_driver_instance();

        try {
            con_local = driver->connect(ip, "parcxservice", "1fromParcx!19514");
            if(device_category=="1,2")                                
                con_local->setSchema("ParcxTerminal");                
            else if(device_category=="3")                
                con_local->setSchema("ParcxCashier");                                
            else                
                con_local->setSchema("parcxAPM");
                
                
            writeLog("synchMessage", "Connected to " + ip);

            stmt_local = con_local->createStatement();
            stmt_local->executeUpdate("Truncate table customer_messages");
            res = stmt->executeQuery("Select * from customer_messages where device_type in (" + device_category + ")");
            while (res->next()) {                
                    prep_stmt = con_local->prepareStatement("Insert into customer_messages(id,message,media_path,english_line1,english_line2,english_line3,arabic_line1,arabic_line2 ,arabic_line3,spanish_line1,spanish_line2,spanish_line3,status) values(?,?,?,?,?,?,?,?,?,?,?,?,?)");
                
                prep_stmt->setString(1, res->getString("id"));
                prep_stmt->setString(2, res->getString("message"));
                prep_stmt->setString(3, res->getString("media_path"));
                prep_stmt->setString(4, res->getString("english_line1"));
                prep_stmt->setString(5, res->getString("english_line2"));
                prep_stmt->setString(6, res->getString("english_line3"));
                prep_stmt->setString(7, res->getString("arabic_line1"));
                prep_stmt->setString(8, res->getString("arabic_line2"));
                prep_stmt->setString(9, res->getString("arabic_line3"));
                prep_stmt->setString(10, res->getString("spanish_line1"));
                prep_stmt->setString(11, res->getString("spanish_line2"));
                prep_stmt->setString(12, res->getString("spanish_line3"));
                prep_stmt->setString(13, res->getString("status"));
                prep_stmt->executeUpdate();
                delete prep_stmt;
            }
            delete stmt_local;

            message = device_name + "(" + ip + "):Success";
            writeLog("synchMessage", "Success " + ip);
            delete stmt;
            delete con_local;
            delete res;
        } catch (exception &e) {
            writeException(ip + ":synchMessage", e.what());
            message = device_name + "(" + ip + ") : Failed";
            message=message+"\n"+e.what();
        }
        delete conn;

        
    } catch (exception &e) {
        writeException("synchMessage", e.what());
        message=message+"\n"+e.what();
    }
    return message;
}

void SFTPUpload(ssh_session ssh, string sshfile, string filename, string local_path) {
    string localfile = local_path + "/" + filename;
    int access_type = O_WRONLY | O_CREAT | O_TRUNC;
    sftp_session sftp = sftp_new(ssh);
    if (sftp == NULL) {
        writeLog("SFTPUpload", "Error allocating SFTP session");
        writeLog("SFTPUpload", ssh_get_error(ssh));

    } else {
        writeLog("SFTPUpload", "Allocated SFTP session");
    }
    int rc1 = sftp_init(sftp);
    if (rc1 != SSH_OK) {
        writeLog("SFTPUpload", "Error initializing SFTP session");
    } else {
        writeLog("SFTPUpload", "Initialized SFTP session");
    }
    string f = sshfile + "/" + filename;
    writeLog("SFTPUpload", "SSH Folder:" + f);
    
    sftp_file file = sftp_open(sftp, f.c_str(), access_type, S_IRWXU);
    if (file == NULL) {
        writeLog("SFTPUpload", "Can't open file for writing : ");
        writeLog("SFTPUpload", ssh_get_error(ssh));
    } else {        

        ifstream fin(localfile, ios::binary);
        if (fin) {
            while (fin) {
                char buffer[MAX_XFER_BUF_SIZE];
                fin.read(buffer, sizeof (buffer));
                if (fin.gcount() > 0) {
                    ssize_t nwritten = sftp_write(file, buffer, fin.gcount());
                    if (nwritten != fin.gcount()) {
                        writeLog("SFTPUpload", "Can't write data to file");
                        writeLog("SFTPUpload", ssh_get_error(ssh));                        
                        sftp_close(file);
                    }
                }
            }
            fin.close();
        } else {
            writeLog("SFTPUpload", "Write Failed");
        }

    }
    sftp_free(sftp);
}

int extractImages(ssh_session session,string path) {
    ssh_channel channel;
    int rc;
    char buffer[256];
    int nbytes;
    channel = ssh_channel_new(session);
    if (channel == NULL)
        return SSH_ERROR;
    rc = ssh_channel_open_session(channel);
    if (rc != SSH_OK) {
        ssh_channel_free(channel);
        return rc;
    }
    string command= "cd "+path+" && unzip -o media.zip && rm media.zip";
    //writeLog("extractImages",command);
    rc = ssh_channel_request_exec(channel,command.c_str());
    if (rc != SSH_OK) {
        ssh_channel_close(channel);
        ssh_channel_free(channel);
        return rc;
    }
    nbytes = ssh_channel_read(channel, buffer, sizeof (buffer), 0);
    while (nbytes > 0) {
        if (write(1, buffer, nbytes) != (unsigned int) nbytes) {
            ssh_channel_close(channel);
            ssh_channel_free(channel);
            return SSH_ERROR;
        }
        nbytes = ssh_channel_read(channel, buffer, sizeof (buffer), 0);
    }
    if (nbytes < 0) {
        ssh_channel_close(channel);
        ssh_channel_free(channel);
        return SSH_ERROR;
    }
    ssh_channel_send_eof(channel);
    ssh_channel_close(channel);
    ssh_channel_free(channel);
    return SSH_OK;
}

void SSHUpload(string local_path, string path, string filename,string ssh_host,string ssh_username,string ssh_password) {
    ssh_session ssh = ssh_new();
    if (ssh == NULL)
        writeLog("SSHUpload", "SSH NULL");
    else
        writeLog("SSHUpload", "SSH Successful");
    
    ssh_options_set(ssh, SSH_OPTIONS_HOST, ssh_host.c_str());
    int rc = ssh_connect(ssh);
    if (rc != SSH_OK) {
        writeLog("SSHUpload", "Error connecting to host:");
        writeLog("SSHUpload", ssh_get_error(ssh));
    }

    rc = ssh_userauth_password(ssh, ssh_username.c_str(), ssh_password.c_str());
    if (rc != SSH_AUTH_SUCCESS) {
        writeLog("SSHUpload", "Error authenticating with password:" + ssh_username);
        writeLog("SSHUpload", ssh_get_error(ssh));

    } else {
        writeLog("SSHUpload", "SSH Connected to remote site :" + ssh_host);
        SFTPUpload(ssh, path, filename, local_path);
        extractImages(ssh,path);
    }

    ssh_disconnect(ssh);
    ssh_free(ssh);
    
}

Php::Value synchImages(Php::Value data) {
    string message = "Success";
    string id = data["id"];
    string ip, device_media_path, device_name, device_category,media_path,images="";
    try {
        conn = mySQLConnect();
        stmt = conn->createStatement();
        res = stmt->executeQuery("select device_category,device_ip,device_name,media_path  from system_devices where id=" + id);
        if (res->next()) {
            ip = res->getString("device_ip");
            device_name = res->getString("device_name");
            device_media_path = res->getString("media_path");
            device_category = res->getString("device_category");
        }
        
        string ssh_username="parcx";
        string ssh_password="Parcx123!";
        
        if (stoi(device_category) == 1 || stoi(device_category) == 2)            
            device_category = "1,2";
                       
        
        res=stmt->executeQuery("select setting_value from system_settings where setting_name='customer_media_path'");
        if(res->next())
            {
            media_path=res->getString("setting_value");
            media_path="/opt/lampp/htdocs"+media_path;
            }
        
        res=stmt->executeQuery("select distinct(media_path) from customer_messages where device_type in ("+device_category+")");
        
        while(res->next())
            {
            images=images+" "+res->getString("media_path");                
            }
        delete conn;
        
        writeLog("synchImages","device_category: "+device_category);
        writeLog("synchImages","Images: "+images);
        string command="cd "+media_path+"&& rm -f media.zip && zip media.zip "+images;
        
        
        system(command.c_str());
        
        
        
        
        SSHUpload(media_path.c_str(),device_media_path.c_str(),"media.zip",ip,ssh_username,ssh_password);    
        
        return message;
    } catch (exception &e) {
        writeException("synchMessage", e.what());
        return message;
    }
}

Php::Value parcxCustomerMessageSettings(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:GetMessageTextByLanguage(data);
            break;
        case 2:response = UpdateRecord(data);
            break;
        case 3:response = InsertRecord(data);
            break;
        case 4:response = UpdateImagePath(data);
            break;
        case 5:response = GetCustomerMessageSettings();
            break;
        case 6:showDeviceListForSynchMessage();
            break;
        case 7:response = synchMessages(data);
            break;
        case 8:response = synchImages(data);
            break;

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
