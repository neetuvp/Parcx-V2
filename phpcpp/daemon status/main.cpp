//make -B
//sudo make install
//service apache2 restart
//Edit   sudo nano /etc/sudoers to allow sudo access to www-data  Add at the end:-       www-data ALL=(ALL)NOPASSWD:ALL(ubuntu)
//daemon ALL=(ALL)NOPASSWD:ALL for  centos

#include <phpcpp.h>
#include<iostream>
#include<stdlib.h>
#include<fstream>
#include<time.h>
#include<ctime>
#include <dirent.h>

#include <sys/types.h>
#include <signal.h>
#include "mysql_connection.h"
#include "mysql_driver.h"
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include<cppconn/prepared_statement.h>
#include "PX_GeneralOperations.h"

using namespace std;
sql::Connection *conn;
sql::Driver *driver;
sql::Statement *stmt;
sql::ResultSet *res;
sql::PreparedStatement *prep;
sql::ResultSetMetaData *rm;
GeneralOperations general;
#define DBServer "DBServer"
#define DBUsername "parcxservice"
#define DBPassword "1fromParcx!19514"
#define DATABASE "parcx_server"
#define DATABASE_REPORTING "parcx_reporting"

void writeLog(string function, string msg) {
    general.writeLog("WebApplication/ApplicationLogs/PX-DaemonStatus-" + general.currentDateTime("%Y-%m-%d"), function, msg);
}//end of .writeLog

void writeException(string function, string msg) {
    general.writeLog("WebApplication/ExceptionLogs/PX-DaemonStatus-" + general.currentDateTime("%Y-%m-%d"), function, msg);
}//end of .writeException

 bool has_suffix(string str,string suffix)
        {
        if(str.length()>=suffix.length())
            {       
            if(suffix.compare(str.substr(str.length()-suffix.length()))==0)
                return true;
            }    
        return false;
        }
 
int getProcIdByName(string procName) {
    int pid = -1;

    // Open the /proc directory
    DIR *dp = opendir("/proc");
    if (dp != NULL) {
        // Enumerate all entries in directory until process found
        struct dirent *dirp;
        while (pid < 0 && (dirp = readdir(dp))) {
            // Skip non-numeric entries
            int id = atoi(dirp->d_name);
            if (id > 0) {
                // Read contents of virtual /proc/{pid}/cmdline file
                string cmdPath = string("/proc/") + dirp->d_name + "/cmdline";
                
                ifstream cmdFile(cmdPath.c_str());
                string cmdLine;
                getline(cmdFile, cmdLine);
                if (!cmdLine.empty()) 
                    {
                    if(cmdLine.find(procName)!=string::npos)
                        {
                        size_t pos = cmdLine.rfind('\0');
                        if (pos != string::npos)
                            cmdLine = cmdLine.substr(0, pos);

                        if(has_suffix(cmdLine,procName)==true)
                            {
                            pid=id;                                                                                 
                            break;
                            }
                        }                    
                    }
                }   
            }
        }
    closedir(dp);
    return pid;
    }

int getProcIdByName2(string procName) {
    int pid = -1;

    // Open the /proc directory
    DIR *dp = opendir("/proc");
    if (dp != NULL) {
        // Enumerate all entries in directory until process found
        struct dirent *dirp;
        while (pid < 0 && (dirp = readdir(dp))) {
            // Skip non-numeric entries
            int id = atoi(dirp->d_name);
            if (id > 0) {
                // Read contents of virtual /proc/{pid}/cmdline file
                string cmdPath = string("/proc/") + dirp->d_name + "/cmdline";
                ifstream cmdFile(cmdPath.c_str());
                string cmdLine;
                getline(cmdFile, cmdLine);
                if (!cmdLine.empty()) {
                    // Keep first cmdline item which contains the program path
                    size_t pos = cmdLine.find('\0');
                    if (pos != string::npos)
                        cmdLine = cmdLine.substr(0, pos);
                    // Keep program name only, removing the path
                    pos = cmdLine.rfind('/');
                    if (pos != string::npos)
                        cmdLine = cmdLine.substr(pos + 1);
                    // Compare against requested process name
                    if (procName == cmdLine)
                        pid = id;
                }
            }
        }
    }

    closedir(dp);
    return pid;
}



void getDaemonList(Php::Value data) {
    int option=data["option"];
    try {
        int  pid = 0;
        string id = "0";
        conn = general.mysqlConnect(DATABASE);
        stmt = conn->createStatement();
        if(option==0)
            res = stmt->executeQuery("Select * from daemon_status where status=1");
        else
            res = stmt->executeQuery("Select * from daemon_status");
        if (res->rowsCount() > 0) {

            
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
           
            Php::out << "<th>Daemon</th>" << endl;            
            Php::out << "<th></th>" << endl;               
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;

            

            while (res->next()) {
                id = res->getString("id");

                Php::out << "<tr data-id='" << res->getString("daemon_name") << "'>" << endl;                
                Php::out << "<td>" + res->getString("daemon_label") + "</td>" << endl;  
                if(option==1)
                    {
                    Php::out << "<td>" << std::endl;
                    if (res->getInt("status") == 1)
                        Php::out << "<button type='button' class='btn btn-danger damon-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                    else
                        Php::out << "<button type='button' class='btn btn-success damon-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
                    Php::out << "</td>" << std::endl;
                    }
                else
                    {
                    pid = getProcIdByName(res->getString("daemon_name"));
                    if (pid == -1) {                           
                            Php::out << "<td>" << std::endl;
                            if (res->getInt("status") == 1)
                                Php::out << "<button class='btn btn-success btn-start-daemon' id ='startbtn" + id + "'  onclick=startDaemon('" + res->getString("daemon_name") + "')><i class='fas fa-play-circle'></i>Start</button>" << std::endl;
                            Php::out << "</td>" << std::endl;

                        } else {
                            Php::out << "<td>" << std::endl;
                            Php::out << "<button class='btn btn-danger btn-stop-daemon' id ='stopbtn" + id + "'    onclick=stopDaemon('" + res->getString("daemon_name") + "')><i class='fas fa-stop-circle'></i>Stop</button>" << std::endl;                           
                            Php::out << "<button class='btn btn-success btn-restart-daemon' title='Restart' id ='restartbtn" + id + "'  onclick=restartDaemon('" + res->getString("daemon_name") + "') ><i class='fas fa-sync'></i></button>" << std::endl;
                            Php::out << "</td>" << std::endl;
                        }
                    }
                    Php::out << "</tr>" << endl;


                                         
            }
            //Php::out << " </table>" << std::endl;
        }
                

            delete res;
            delete stmt;
            delete conn;
        } catch (const std::exception& e) {
            writeException("getDaemonList", e.what());
        }
    }

    Php::Value stopDaemon(string daemon) {
        Php::Value result;
        result = "failed";
        string command;
        writeLog("stopDaemon", "Daemon:" + daemon);
        try {
            int pid = getProcIdByName(daemon);
            if (pid > 0) {
                command = "sudo kill " + to_string(pid);
                int n = system(command.c_str());
                result = n;
            } else {
                result = "Process does not exist";
            }
        } catch (const std::exception& e) {
            writeException("stopDaemon", e.what());
        }
        return result;
    }

    string GetPath(string daemon) {
        string path = "";
        sql::Connection *conn;
        sql::Statement *stmt;
        sql::ResultSet *res;
        try {
            conn = general.mysqlConnect(DATABASE);
            stmt = conn->createStatement();
            res = stmt->executeQuery("Select path from daemon_status where daemon_name ='" + daemon + "'");
            if (res->next()) {
                path = res->getString("path");
            }
            delete res;
            delete stmt;
            delete conn;
        } catch (sql::SQLException &e) {
            writeException("GetPath", e.what());
        }
        return path;
    }
       

    Php::Value startDaemon(string daemon) {
        Php::Value result;
        result = "failed";
        string command, path;
        writeLog("startDaemon", "Daemon:" + daemon);
        try {
            int pid = getProcIdByName(daemon);
            if (pid == -1) {
                path = GetPath(daemon);
                //command = "cd /opt/parcx/Daemons && sudo ./"+daemon;            
                if(has_suffix(daemon,".jar"))
                    command = "cd " + path + " && nohup java -jar " + daemon+" &";
                else
                    command = "cd " + path + " && sudo ./" + daemon;
                writeLog("startDaemon", "Command:" + command);
                int n = system(command.c_str());
                result = n;
            } else {
                result = "Process already exists";
            }
        } catch (const std::exception& e) {
            writeException("startDaemon", e.what());
        }
        return result;
    }
    
    void reprocessDayClosureReport(Php::Value json)
        {
        
        string daemon_name="";
        try
            {

            
            string reprocess_date = json["reprocess_date"];

            conn = general.mysqlConnect(DATABASE_REPORTING);
            stmt = conn->createStatement();
           
            stmt->executeUpdate("Update parking_duration set flag = 1 where report_date='"+reprocess_date+"'");
            
            stmt->executeUpdate("Update summary_parking_movements set flag = 1 where report_date='"+reprocess_date+"'");
            
            stmt->executeUpdate("Update summary_daily_revenue set reproccessing_flag = 1 where report_date='"+reprocess_date+"'");

            res = stmt->executeQuery("Select path,daemon_name from parcx_server.daemon_status where daemon_label ='Parcx Dayclosure Daemon'");
            if(res->next())                
                daemon_name=res->getString("daemon_name");
                                
            delete res;
            delete stmt;
            delete conn;

            if(daemon_name!="")
                {
                stopDaemon(daemon_name);
                startDaemon(daemon_name);
                }
        }
    catch(const std::exception& e)
        {
        writeException("reprocessDayClosureReport",e.what());
        }
    }
    
    
    Php::Value enableDisableDaemon(Php::Value json) {
    string msg = "Failed";
    
        sql::Connection *conn;
        sql::PreparedStatement *prep_stmt;        
        try {            
            conn = general.mysqlConnect(DATABASE);      
            string status=json["status"];
            string daemon_name=json["daemon_name"];
            prep_stmt = conn->prepareStatement("update daemon_status set status=? where daemon_name=?");
            prep_stmt->setString(1, status);
            prep_stmt->setString(2, daemon_name);
            if (!prep_stmt->execute())
                msg = "Successfull";           
            delete prep_stmt;
            delete conn;
            if(status=="0")
                stopDaemon(daemon_name);
            else
                startDaemon(daemon_name);
        } catch (sql::SQLException &e) {
            writeException("enableDisableDaemon", e.what());
        }
      
    return msg;
}

    Php::Value parcxDaemonStatus(Php::Parameters & params) {
        Php::Value response;
        try {
            Php::Value data = params[0];
            int task = data["task"];

            string daemon = data["daemon"];
            switch (task) {
                case 1:
                    getDaemonList(data);
                    break;
                case 2:
                    response = stopDaemon(daemon);
                    break;
                case 3:
                    response = startDaemon(daemon);
                    break;
                case 4:
                    response = stopDaemon(daemon);
                    response = startDaemon(daemon);
                    break;
                case 5:
                    response = enableDisableDaemon(data);
                    break; 
                case 6:reprocessDayClosureReport(data);
                    break;
            }
        } catch (const std::exception& e) {
            writeException("PX_DaemonStatus", e.what());
        }
        return response;

    }

    extern "C" {

        PHPCPP_EXPORT void *get_module() {
            static Php::Extension extension("PX_DaemonStatus", "1.0");
            extension.add<parcxDaemonStatus>("parcxDaemonStatus");
            return extension;
        }
    }
