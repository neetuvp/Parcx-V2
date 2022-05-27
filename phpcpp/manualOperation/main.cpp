/* Standard C++ includes */
#include <stdlib.h>
#include <iostream>
#include<fstream>
#include<sstream>
#include<ctime>
#include<phpcpp.h>

#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <unistd.h>
#include<stdio.h>
#include<string>
#include<cstring>

#include "mysql_connection.h"
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>

#define SERVER "DBServer"
#define USER "parcxservice"
#define PASSWORD "1fromParcx!19514"
#define DATABASE_SERVER "parcx_server"
#define DATABASE_REPORTING "parcx_reporting"
#define DATABASE_DASHBOARD "parcx_dashboard"

using namespace std;
//current date

string currentDate() {
    time_t now = time(NULL);
    std::tm* timeinfo;
    char buffer[80];
    std::time(&now);
    timeinfo = std::localtime(&now);
    std::strftime(buffer, 80, "%Y-%m-%d", timeinfo);
    string datetime(buffer);
    return datetime;
}//end of .currentDate

//current dateTime

string currentTime() {
    time_t now = time(NULL);
    std::tm* timeinfo;
    char buffer[80];
    std::time(&now);
    timeinfo = std::localtime(&now);
    std::strftime(buffer, 80, "%H:%M:%S", timeinfo);
    string datetime(buffer);
    return datetime;
}//end of .currentDateTime

//write exception

void write_log(string txt) {
    ofstream fp;
    fp.open("/opt/parcx/Logs/WebApplication/ApplicationLogs/PX-ManualOperation-" + currentDate() + ".log", ios::app);
    fp << currentTime() << "\t" << txt << endl;
    fp.close();
}//end of .writeException

long timeId() {
    struct tm tm;
    string startDate = "2018-01-01 00:00:00";

    strptime(startDate.c_str(), "%Y-%m-%d %H:%M:%S", &tm);
    time_t t = mktime(&tm);

    time_t now = time(NULL);
    long seconds = difftime(now, t);
    return seconds;
}

Php::Value sendDataToPort(Php::Parameters &params) {
    string response = "Error";
    try {

        string ip = params[0];
        int port = params[1];
        string message = params[2];
        int sockfd; // socket file descriptor 	
        struct sockaddr_in serv_addr;
        struct hostent *server;

        sockfd = socket(AF_INET, SOCK_STREAM, 0); // generate file descriptor 
        if (sockfd < 0) {
            write_log("ERROR opening socket " + ip + ":" + to_string(port));
            return response;
        }

        server = gethostbyname(ip.c_str()); //the ip address (or server name) of the listening server.
        if (server == NULL) {
            write_log("ERROR, no such host " + ip + ":" + to_string(port));
            return response;
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
            write_log("ERROR connecting " + ip + ":" + to_string(port));
            return response;
        }

        /* write to port*/
        int n;
        char * wbuff;
        string str = message + "\n";
        wbuff = (char *) str.c_str(); // convert from string to c string, has to have \0 terminal 

        n = write(sockfd, wbuff, strlen(wbuff));
        if (n < 0)
            write_log("Cannot write to socket");
        else
            write_log(message + " -command send to " + ip + ":" + to_string(port));

        //read from port
        //set read delay		
        tv.tv_sec = 2;
        tv.tv_usec = 0;
        setsockopt(sockfd, SOL_SOCKET, SO_RCVTIMEO, (const char*) &tv, sizeof tv);

        char rbuff[256];
        n = read(sockfd, rbuff, sizeof (rbuff)); // read from socket and store the msg into buffer	
        if (n < 0)
            write_log("Cannot read from socket");
        else
            write_log("Response:" + string(rbuff));
        close(sockfd);
        return string(rbuff);
    } catch (exception &e) {
        write_log(e.what());
        return response;
    }
}

//mysql connection

sql::Connection* mysqlConnectServer() {
    sql::Connection *con = NULL;
    try {
        sql::Driver *driver;
        driver = get_driver_instance();
        con = driver->connect(SERVER, USER, PASSWORD);
        con->setSchema(DATABASE_SERVER);
    } catch (exception &e) {
        write_log(e.what());
    }
    return con;
}//end of .mysql_Connect

sql::Connection* mysqlConnectReporting() {
    sql::Connection *con = NULL;
    try {
        sql::Driver *driver;
        driver = get_driver_instance();
        con = driver->connect(SERVER, USER, PASSWORD);
        con->setSchema(DATABASE_REPORTING);
    } catch (exception &e) {
        write_log(e.what());
    }
    return con;
}//end of .mysql_Connect

sql::Connection* mysqlConnectDashboard() {
    sql::Connection *con = NULL;
    try {
        sql::Driver *driver;
        driver = get_driver_instance();
        con = driver->connect(SERVER, USER, PASSWORD);
        con->setSchema(DATABASE_DASHBOARD);
    } catch (exception &e) {
        write_log(e.what());
    }
    return con;
}

Php::Value InsertManualMovements(Php::Parameters &params) {
    string msg = "0";
    try {
        string description = params[0];
        string movement_type = params[1];
        string reason = params[2];
        string device_number = params[3];
        string device_name = params[4];
        string carpark_number = params[5];
        string operator_name = params[6];
        sql::Connection *con;
        con = mysqlConnectReporting();
        sql::PreparedStatement *prep_stmt;
        prep_stmt = con->prepareStatement("INSERT into parking_movements_manual(device_number,device_name,carpark_number,operator_name,action,reason,description,date_time,movement_type)VALUES(?,?,?,?,?,?,?,CURRENT_TIMESTAMP,?)");
        prep_stmt->setString(1, device_number);
        prep_stmt->setString(2, device_name);
        prep_stmt->setString(3, carpark_number);
        prep_stmt->setString(4, operator_name);
        prep_stmt->setString(5, description);
        prep_stmt->setString(6, reason);
        prep_stmt->setString(7, description);
        prep_stmt->setString(8, movement_type);
        if (!prep_stmt->execute())
            msg = "1";

        con->close();
        prep_stmt->close();
        delete prep_stmt;
        delete con;
    } catch (exception &e) {
        write_log(e.what());
        cout << e.what();
    }
    return msg;
}

Php::Value changeOperationMode(Php::Parameters &params) {
    string msg = "0";
    try {                        
        string device_number = params[0];
        string operation_mode = params[1];  
        
        sql::Connection *con;
        con = mysqlConnectServer();
        sql::Statement *stmt;        
        stmt=con->createStatement();
        stmt->executeUpdate("update system_devices set mode_of_operation="+operation_mode+" where device_number="+device_number);
        
        msg = "1";

        con->close();
        stmt->close();
        delete stmt;
        delete con;
    } catch (exception &e) {
        write_log(e.what());
        cout << e.what();
    }
    return msg;
}

inline string toString(Php::Value param)
	{
	string value=param;
	return value;
	}

Php::Value getLabels(string lang,string label)
    {    
    sql::Connection *con;
    sql::Statement *stmt;
    Php::Value result;
    string query;
    try
        {  
        if (label.find(',') != std::string::npos)
            {
            stringstream ss(label);
            string substr;
            label="";
            while (getline(ss, substr, ',')) 
                {                        
                label=label+"'"+substr+"'"+",";
                }
            label=label.substr(0,label.size()-1);
            query= "select "+lang+",message,english from web_application_labels where message in("+label+")";            
            }
        else
            query= "select "+lang+",message,english from web_application_labels where message='"+label+"'";	
           
       // Php::out<<query<<endl;
        sql::ResultSet *res; 

        con = mysqlConnectServer();
	stmt = con->createStatement();
        
        res=stmt->executeQuery(query);
        
        if(res->rowsCount()==1)
            {
            if(res->next())
                {
                if(res->getString(lang)!="")
                    label=res->getString(lang);
                else
                    res->getString("english");                
                }
            result=label;
            }
        else
            {
            while(res->next())                
                {
                if(res->getString(lang)!="")
                    result[res->getString("message").asStdString()]=res->getString(lang).asStdString();                
                else
                    result[res->getString("message").asStdString()]=res->getString("english").asStdString();                
                }
            }
        delete res;
	delete stmt;
	delete con;	
        return result;
        }
    catch(const std::exception& e)
        {
            //writeParkingReportException("getApplicationLabels",e.what());	
            //writeLog("WebApplication/ApplicationLogs/PX-RevenueReport-"+currentDateTime("%Y-%m-%d"),"getApplicationLabels",e.what());   
        }
    return result;  
    }




void listManualOperation(Php::Parameters &params) {
    try {
        string lang = params[0];
        string disabled;
        sql::Connection *con;

        string labels = "open_barrier,close_barrier,restart,lane_closed_mode,free_passage_mode,standard_operation_mode,standard_operation_mode_from_server,standard_operation_mode_reason,barrier_open_from_server,free_passage_mode_from_server,free_passage_mode_reason,lane_closed_mode_from_server,lane_closed_mode_reason,restart_machine_from_server,barrier_close_from_server,close_barrier_reason,open_barrier_reason";
        Php::Value label = getLabels(lang, labels);
        
        con = mysqlConnectDashboard();
        sql::PreparedStatement *prep_stmt;
        sql::ResultSet *res;
        prep_stmt = con->prepareStatement("select * from watchdog_device_status where device_type in (1,2,6,7)");
        res = prep_stmt->executeQuery();

        while (res->next()) {
            Php::out << "<div class='table-view'>" << std::endl;
            Php::out << "<div class='card-text'>" << std::endl;
            Php::out << "<div class='d-flex justify-content-between align-items-center'>" << std::endl;
            Php::out << "<div class='col'>";
            if (res->getInt("device_network_status") == 1) {
                disabled = "";
                Php::out << "<div class='dot-indicator bg-success-gradient' data-toggle='tooltip' data-placement='top' title='Online'></div>";
            } else {
                disabled = "disabled";
                Php::out << "<div class='dot-indicator bg-danger-gradient' data-toggle='tooltip' data-placement='top' title='Currently Off line'></div>";
            }

            Php::out << res->getString("device_name") << "</div>" << std::endl;
            Php::out << "<div class='col'>" << res->getString("device_ip") << "</div>" << std::endl;
            Php::out << "<input type='hidden' device_type='" << res->getString("device_type") << "' id='device_details_" << res->getString("device_number") << "' device_ip='" << res->getString("device_ip") << "' device_name='" << res->getString("device_name") << "' carpark_number='" << res->getString("carpark_number") << "'>" << std::endl;
            if (res->getInt("device_type") == 6 || res->getInt("device_type") == 7) {
                Php::out << "<div class='col'></div>" << std::endl;
                Php::out << "<div class='col'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-open-barrier btn-block'  value='Open Barrier1' id='" << res->getString("device_number") << "'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-close-barrier btn-block'   value='Close Barrier1' id='" << res->getString("device_number") << "'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-open-barrier btn-block'   value='Open Barrier2' id='" << res->getString("device_number") << "'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-close-barrier btn-block'  value='Close Barrier2' id='" << res->getString("device_number") << "'></div>" << std::endl;
            } else {
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-open-barrier btn-block' value='"<<label["open_barrier"]<<"' id='" << res->getString("device_number") << "' data-value = 'Open Barrier' data-description='"<<label["barrier_open_from_server"]<<"' data-reason='"<<label["open_barrier_reason"]<<"'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-close-barrier btn-block'  value='"<<label["close_barrier"]<<"' id='" << res->getString("device_number") << "' data-value = 'Close Barrier' data-description='"<<label["barrier_close_from_server"]<<"' data-reason='"<<label["close_barrier_reason"]<<"'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-restart-machine btn-block' value='"<<label["restart"]<<"' id='" << res->getString("device_number") << "' data-value = 'Restart' data-description='"<<label["restart_machine_from_server"]<<"' data-reason='"<<label["restart"]<<"'></div>" << std::endl;
                //Php::out << "<div class='col'><input type='submit' "<<disabled<<" class='btn btn-outline-info btn-barrier-status btn-block' value='Get Barrier Status' id='" << res->getString("device_number") <<"' data-value = 'Get Barrier Status'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-close-lane btn-block' value='"<<label["lane_closed_mode"]<<"' id='" << res->getString("device_number") << "' data-value = 'Lane Closed Mode' data-description='"<<label["lane_closed_mode_from_server"]<<"' data-reason='"<<label["lane_closed_mode_reason"]<<"'></div>" << std::endl;
                //Php::out << "<div class='col'><input type='submit' "<<disabled<<" class='btn btn-outline-info btn-open-lane btn-block' value='Lane Opened Mode' id='" << res->getString("device_number") <<"' data-value = 'Lane Opened Mode'></div>" << std::endl;	
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-free-passage btn-block'  value='"<<label["free_passage_mode"]<<"' id='" << res->getString("device_number") << "' data-value = 'Free Passage Mode' data-description='"<<label["free_passage_mode_from_server"]<<"' data-reason='"<<label["free_passage_mode_reason"]<<"'></div>" << std::endl;
                Php::out << "<div class='col'><input type='submit' " << disabled << " class='btn btn-outline-info btn-standard-operation btn-block' value='"<<label["standard_operation_mode"]<<"' id='" << res->getString("device_number") << "' data-description='"<<label["standard_operation_mode_from_server"]<<"' data-reason='"<<label["standard_operation_mode_reason"]<<"' data-value = 'Standard Operation Mode'></div>" << std::endl;
            }
            Php::out << "</div>" << std::endl;
            Php::out << "</div>" << std::endl;
            Php::out << "</div>" << std::endl;
        }

        con->close();
        res->close();
        prep_stmt->close();
        delete prep_stmt;
        delete con;
        delete res;
    } catch (exception &e) {
        //write to log file
        write_log(e.what());
        cout << e.what();
    }
}




extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_ManualOperation", "1.0");
        extension.add<sendDataToPort>("sendDataToPort");
        extension.add<listManualOperation>("listManualOperation");
        extension.add<InsertManualMovements>("InsertManualMovements");
        extension.add<changeOperationMode>("changeOperationMode");
        return extension;
    }
}

