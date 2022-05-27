#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>
#include <iostream>
#include <sstream>
#include <cppconn/prepared_statement.h>
#include "validation.h"
#include<vector>
#include <algorithm>
#include <string>
#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

#define MANDATORY "Mandatory"
#define MAXLENGTH "Maximum Length Exceeded"
#define CONSTRAINT "Invalid Character"
#define MediaPath "/opt/lampp/htdocs/parcx/modules/greeting/Media"
#define DisplayMediaPath "/parcx/modules/greeting/Media"
using namespace std;
GeneralOperations General;
Validation validation;
string validation_failed = "Validation Failed\n";
void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-StationConfig-" + General.currentDateTime(dateFormat), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-StationConfig-" + General.currentDateTime(dateFormat), function, message);
    writeLog(function, "Exception: " + message);
}


string toString(Php::Value param) {
    string value = param;
    return value;
}

std::vector<std::string> splitstring(const std::string& s, char delimiter) {
    std::vector<std::string> splits;
    std::string split;
    std::istringstream ss(s);
    while (std::getline(ss, split, delimiter)) {
        splits.push_back(split);
    }
    return splits;
}

int generateUniqueId()
{    							    		    
   struct tm tm;
    string startDate = "2020-01-01 00:00:00";

    strptime(startDate.c_str(), "%Y-%m-%d %H:%M:%S", &tm);
    time_t t = mktime(&tm);

    time_t now = time(NULL);
    long seconds = difftime(now, t);
    return seconds;
}

std::string random_string( size_t length )
{
    auto randchar = []() -> char
    {
        const char charset[] =
        "0123456789"
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
        "abcdefghijklmnopqrstuvwxyz";
        const size_t max_index = (sizeof(charset) - 1);
        return charset[ rand() % max_index ];
    };
    std::string str(length,0);
    std::generate_n( str.begin(), length, randchar );
    return str;
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


string removeSpecialCharactersfromText(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
         if (!(str[i]==32))
          {
            continue;
          }
      else
      {
        char c = str[i];
        std::replace(str.begin(), str.end(), c, '_');
      }
    }
    return str;
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


void getControllerDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;  
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select * from system_devices where device_category=13";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            Php::out << "<option value="<<res->getInt("device_number")<<">"<<res->getString("device_name")<<"</option>" << std::endl; 
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getControllerDropdown", e.what());
        delete con;
    }
}

void getCameraDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;  
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select * from system_devices where device_category=8";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            Php::out << "<option value="<<res->getInt("device_number")<<">"<<res->getString("device_name")<<"</option>" << std::endl; 
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getCameraDropdown", e.what());
        delete con;
    }
}

void showPumpSettings(Php::Value data)
    {
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res;  
    int edit_access = data["edit"];
    int delete_access = data["delete"];
    try
    {
        Php::Value role;
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        
        res=stmt->executeQuery("select * from fueling_station_pump_settings");
        if(res->rowsCount()>0)
            {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th>Pump Number</th>"<<endl;
            Php::out<<"<th>Pump Name</th>"<<endl; 
            Php::out<<"<th>Pump Monitor Port</th>"<<endl;
            Php::out<<"<th>Controller Id</th>"<<endl;   
	    if(edit_access==1 ||delete_access==1)    
            	Php::out<<"<th></th>"<<endl;
            //Php::out<<"<th></th>"<<endl;
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
            {
            Php::out<<"<tr data-id='"<<res->getString("id")<<"'>"<<endl;                       
            Php::out<<"<td>"+res->getString("pump_number")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("pump_name")+"</td>"<<endl;            
            Php::out<<"<td>"+res->getString("pump_monitor_port")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("controller_id")+"</td>"<<endl;
            if(edit_access==1 ||delete_access==1){ 
            Php::out << "<td>"<< std::endl;
	    if(delete_access==1)
                {
		    if(res->getInt("status")==1)
		        Php::out << "<button type='button' class='btn btn-danger pump-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
		        //Php::out << "<button type='button' class='col btn btn-danger pump-enable-disable-btn'>Disable</button>"<< std::endl;
		    else
		        Php::out << "<button type='button' class='btn btn-success pump-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
		        //Php::out << "<button type='button' class='col btn btn-success pump-enable-disable-btn'>Enable</button>"<< std::endl;
		   // Php::out << "</td>"<< std::endl;
		}
		
		if(edit_access==1)
                {   //Php::out << "<td>"<< std::endl;
		    Php::out << "<button type='button' class='btn btn-info pump-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
		    //Php::out << "<button type='button' class='col btn btn-info pump-edit' ><i class='fas fa-edit'></i>Edit</button>"<< std::endl; 
		}           
		    Php::out << "</td>"<< std::endl;
		

	    }
            Php::out<<"</tr>"<<endl;	
            }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete stmt;
		delete con;  
    }
    catch(const std::exception& e)
    {
        writeException("showPumpSettings",e.what());
    }
    
    }


Php::Value getpumpDetails(Php::Value json)
    {
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res;
    Php::Value response;  
    string query;
    try
        {
        string id=json["id"];        
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        query="select * from fueling_station_pump_settings where id="+id;
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
        writeException("getpumpDetails",e.what());
        } 
    return response;       
    }

Php::Value insertUpdatePump(Php::Value json)
{
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res;
    string query="",msg = "Failed",op_status="Failed";    
    try
        {
        string id=json["id"];
        string pump_number=json["pump_number"];
        string pump_name=json["pump_name"];
        string waiting_delay=json["waiting_delay"];
        string waiting_timeout=json["waiting_timeout"];
        string monitor_pin=json["monitor_pin"];
        string pump_monitor_port=json["pump_monitor_port"];
        string camera_1=json["camera_1"];
        string camera_2=json["camera_2"];
        string cms_id=json["cms_id"];
        string controller_id=json["controller_id"];
        string call_vehicle_found_camera=json["call_vehicle_found_camera"];
        if(cms_id=="")
        {
            cms_id = "0";
        }
        if(camera_1 == "")
        {
            camera_1 = "0";
        }
        if(camera_2 == "")
        {
            camera_2 = "0";
        }
        
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();                
        if(id=="")
            {
            query="select pump_number from fueling_station_pump_settings where pump_number='"+pump_number+"'";
            res=stmt->executeQuery(query);
            if(res->next())
                {
                msg="Pump number already exists.Try with another pump number";
                delete res;
                delete stmt;
                delete con;
                return msg;
                }
            }
        
        if(id=="")
            query="insert into fueling_station_pump_settings (pump_number,pump_name,waiting_delay,monitor_pin,call_vehicle_found_camera,waiting_timeout,cms_id,camera_1,camera_2,pump_monitor_port,controller_id,status)values("+pump_number+",'"+pump_name+"',"+waiting_delay+","+monitor_pin+","+call_vehicle_found_camera+","+waiting_timeout+","+cms_id+","+camera_1+","+camera_2+","+pump_monitor_port+","+controller_id+",1)";
        else
            query="update fueling_station_pump_settings set pump_number="+pump_number+",pump_name='"+pump_name+"',waiting_delay="+waiting_delay+",monitor_pin="+monitor_pin+",call_vehicle_found_camera="+call_vehicle_found_camera+",waiting_timeout="+waiting_timeout+",cms_id="+cms_id+",camera_1="+camera_1+",camera_2="+camera_2+",pump_monitor_port="+pump_monitor_port+",controller_id="+controller_id+",last_updated_datetime=NOW() where id="+id;
        stmt->executeUpdate(query);
        
        delete stmt;
        delete con;
        msg = "Successfull";
	op_status = "Success";
        }
    catch(const std::exception& e)
        {
        writeException("insertUpdatePump",query);
        writeException("insertUpdatePump",e.what());
       // Php::out<<query<<endl;
        } 
	logUserActivity("insertUpdatePump", "Insert/Update Pump Setting, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}

Php::Value enableDisablePump(Php::Value json)
    {
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    string query="";
    string msg = "Failed",op_status="Failed";;    
    try
        {
        int status=json["status"];
        int id=json["id"];
        con= General.mysqlConnect(ServerDB); 
	query="update fueling_station_pump_settings set status=? where id=?";
        prep_stmt=con->prepareStatement(query);
        prep_stmt->setInt(1,status);
	prep_stmt->setInt(2,id);
        prep_stmt->executeUpdate();        
        delete prep_stmt;
        delete con;       
        msg = "Successfull";
	op_status="Success";	
        }
    catch(const std::exception& e)
        {
        writeException("enableDisablePump",e.what());
        }
	logUserActivity("Enable/Disable Pump", "Enable/Disable Pump Setting, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
    }


void showFuelStationSettings(Php::Value data) {
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res,*res1;
    int edit_access = data["edit"];
    int delete_access = data["delete"];
    try {       
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("select * from fueling_station_settings  order by id");
        if (res->rowsCount() > 0) {
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;           
            Php::out << "<th>Setting Name</th>" << endl;
            Php::out << "<th>Setting Value</th>" << endl;
	    if(edit_access==1 ||delete_access==1)
            	Php::out << "<th></th>" << endl;
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
        }
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "' data-name='" << res->getString("setting_name") << "'>" << endl;            
            Php::out << "<td>" + res->getString("setting_label") + "</td>" << endl;
            if(res->getInt("setting_type")==0)
                Php::out << "<td data-val="+ res->getString("setting_value")+">" + res->getString("setting_value") + "</td>" << endl;
            else if(res->getInt("setting_type")==1)
            {
                if(res->getString("setting_value")=="1")
                    Php::out << "<td data-val='1'>Yes</td>" << endl;
                else
                    Php::out << "<td data-val='0'>No</td>" << endl;
            }
            else if(res->getInt("setting_type")==2)
            {
                res1 = stmt->executeQuery("Select interface_name from interface_settings where id = "+res->getString("setting_value"));
                
                if(res1->next())
                    Php::out << "<td data-val="+ res->getString("setting_value")+">" +res1->getString("interface_name")+"</td>" << endl;

                delete res1;
            }
	    if(edit_access==1 ||delete_access==1){ 
		    Php::out << "<td>" << std::endl;
		if(delete_access==1){
		    if (res->getInt("status") == 1)
		        Php::out << "<button type='button' class='btn btn-danger setting-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
		    else
		        Php::out << "<button type='button' class='btn btn-success setting-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;
		}
		if(edit_access==1)     
		    Php::out << "<button type='button' class='btn btn-info setting-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
		    Php::out << "</td>" << std::endl;
	    }
            Php::out << "</tr>" << endl;
        }       
        delete res;
        delete stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showFuelStationSettings", e.what());
    }
}


Php::Value enableDisableSetting(Php::Value json)
    {
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    string query,msg = "Failed";   
    string op_status = "Failed";
 
    try
        {
        int status=json["status"];
        int id=json["id"];
        con= General.mysqlConnect(ServerDB); 
	query="update fueling_station_settings set status=? where id=?";
        prep_stmt=con->prepareStatement(query);
        prep_stmt->setInt(1,status);
	prep_stmt->setInt(2,id);

        prep_stmt->executeUpdate();        
        delete prep_stmt;
        delete con;       
        msg = "Successfull";
	op_status = "Success";
	if(status==1)
            logUserActivity("Fuel Station Settings", "Setting Enabled, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
        else if(status==0)
            logUserActivity("User Roles", "User Role Disabled, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
		
        }
    catch(const std::exception& e)
        {
        writeException("enableDisableSetting",e.what());
        }
    return msg;
    }

void getSettingsInputBox(Php::Value json)
{
    sql::Connection *con=NULL;
    sql::Statement *stmt;
    sql::ResultSet *res;
    string id=json["id"];  
    string setting_value = json["setting_value"];  
    string setting_name = json["setting_name"];
    string selected= "",yes_selected="",no_selected="";
    string option_val = "";
    string option_name = "";
    try{
        
        con = General.mysqlConnect(ServerDB);
        stmt=con->createStatement();
        res=stmt->executeQuery("Select setting_type,setting_name from fueling_station_settings where setting_name = '"+setting_name+"'");
        int n = res->rowsCount();
        res->next();
        if(n>0)
        {
            if(res->getString("setting_type")=="0")
            {
                Php::out<<"<input type = 'text' id = 'setting"+id+"'  value = '"+setting_value+"'>"<<endl;                   
            }
            else if(res->getString("setting_type")=="1")
            {
                selected="";
                Php::out<<"<select name='"+setting_name+"' id='setting"+id+"' value = '"+setting_value+"'>"<<endl;
                if(setting_value=="1")
                {
                    yes_selected = "selected";
                    no_selected = "";
                }
                else if(setting_value=="0")
                {
                    yes_selected = "";
                    no_selected = "selected";
                }
                Php::out<<"<option value='1' "+yes_selected+">Yes</option>"<<endl;               
                Php::out<<"<option value='0' "+no_selected+">No</option>"<<endl;               
                Php::out<<"</select>"<<endl;                      
            }
            else if(res->getString("setting_type")=="2")
            {
                res=stmt->executeQuery("Select id,interface_name from interface_settings");
                Php::out<<"<select name='"+setting_name+"' id='setting"+id+"' value = '"+setting_value+"'>"<<endl;
                while(res->next())
                {
                    selected="";
                    if(setting_value==res->getString("id"))
                    {
                       selected = "selected"; 
                    }
                    Php::out<<"<option value='"+res->getString("id")+"' "+selected+">"+res->getString("interface_name")+"</option>"<<endl;    
                }
                Php::out<<"</select>"<<endl;  
            }
        }
        
        delete res;    
        delete stmt;
        delete con; 
        
    }
    catch(const std::exception& e)
    {
        writeException("getSettingsInputBox",e.what());
    }

}

Php::Value UpdateFuelSettings(Php::Value json)
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    //sql::ResultSet *res;
    string query="",msg = "Failed",op_status="Failed";    
    try
        {
            string id=json["id"];
            string setting_value=json["setting_value"];

            con = General.mysqlConnect(ServerDB);
	    query="update fueling_station_settings set setting_value=? where id=?";
            prep_stmt = con->prepareStatement(query);  
	    prep_stmt->setString(1,setting_value);
	    prep_stmt->setInt(2,stoi(id));              
            
            prep_stmt->executeUpdate();

            delete prep_stmt;
            delete con;
            msg = "Successfull";
        }
    catch(const std::exception& e)
        {
        writeException("UpdateFuelSettings",query);
        writeException("UpdateFuelSettings",e.what());
        //Php::out<<query<<endl;
        } 
    	logUserActivity("Update Fuel Setting", "UpdateFuelSettings, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}

Php::Value parcxStationConfiguration(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:getControllerDropdown();
            break;
        case 2:getCameraDropdown();
            break;
        case 3:showPumpSettings(data);
            break;
        case 4:response=getpumpDetails(data);
            break; 
        case 5:response=insertUpdatePump(data);
            break;
        case 6:response=enableDisablePump(data);
            break;
        case 7: showFuelStationSettings(data);
            break;
        case 8:response=enableDisableSetting(data);
            break;
        case 9:getSettingsInputBox(data);
            break;
        case 10:response = UpdateFuelSettings(data);
            break;
    }
    return response;
}


extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_StationConfiguration", "1.0");
        extension.add<parcxStationConfiguration>("parcxStationConfiguration");
        return extension;
    }
}
