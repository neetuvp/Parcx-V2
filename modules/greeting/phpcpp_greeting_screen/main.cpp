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
    General.writeLog("WebApplication/ApplicationLogs/PX-GreetingScreen-" + General.currentDateTime(dateFormat), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-GreetingScreen-" + General.currentDateTime(dateFormat), function, message);
    writeLog(function, "Exception: " + message);
}

void writeLogService(string function, string message) {
    General.writeLog("Services/ApplicationLogs/PX-GreetingScreen-" + General.currentDateTime(dateFormat), function, message);
}

void writeExceptionService(string function, string message) {
    General.writeLog("Services/ExceptionLogs/PX-GreetingScreen-" + General.currentDateTime(dateFormat), function, message);
    writeLogService(function, "Exception: " + message);
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


Php::Value uploadMedia(Php::Value data)
{
    Php::Value response;
    string filename = toString(data["name"]);
    filename = removeSpecialCharactersfromText(filename);
    filename = to_string(generateUniqueId())+"_"+filename;
    string from = toString(data["from"]);
    string dest = string(MediaPath)+"/"+filename;
    double size = data["size"];
    int error = data["error"];
    string type = toString(data["type"]);
    string result = "",op_status="Failed";
    writeLog("uploadMedia","size:"+to_string(size/ (1024 * 1024 * 1024))+" GB");
    writeLog("uploadMedia","type:"+string(type));
    if(size > 1024 * 1024 * 1024)
    {
        result ="File size is >1 GB" ;
        response["status"] = 400;
        response["message"]=result;
    }
    else
    {
        string extension = filename.substr(filename.find_last_of(".") + 1);
        
        if(extension == "mp4" ||  extension == "MP4" ||  extension == "gif" ||  extension == "GIF" || extension == "png" ||  extension == "PNG" || extension == "jpg" ||  extension == "JPG" || extension == "jpeg" ||  extension == "JPEG" || extension == "lottie" || extension == "json" ||  extension == "JSON")  {
            if(error>0)
            {
                result ="File Upload Error:"+to_string(error) ;
                response["status"] = 400;
                response["message"]=result;
            }
            else
            {
                system(("sudo cp -p "+from+" "+dest).c_str());
                system(("sudo chmod 777 "+dest).c_str());
                result ="Success" ;
		op_status="Success";
                writeLog("uploadMedia",result);
                response["status"] = 200;
                response["message"]=result;
                //string file_name = to_string(generateUniqueId())+"_"+filename;
                response["file_name"] = filename;
                
            }
        } 
        else 
        {
            //writeLog("uploadMedia","Invalid Extension");
            result ="Invalid Extension" ;
            response["status"] = 400;
            response["message"]=result;
        }
    }
    writeLog("uploadMedia",result);
    logUserActivity("Greeting Stage", "Upload Media , Result: " + result, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), result);
    return response;
    
}

Php::Value updateStage(Php::Value data)
{
    string result = "Failed",op_status="Failed";
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt=NULL;    
    try
        {
        string query_string="";     
        con= General.mysqlConnect(ServerDB); 
        query_string = "Update greeting_screen set message_line1=?,m1_font_family=?,m1_font_size=?,m1_font_color=?,message_line2=?,m2_font_family=?,m2_font_size=?,m2_font_color=?,message_line3=?,m3_font_family=?,m3_font_size=?,m3_font_color=?,description=?,bg_file=?,animation_file=?,title=?,animation_type=?,bg_type=?,bg_color=?,bg_opacity=?,last_updated_date_time=NOW() where stage_id=? and schedule=? and known_customer=?";          
            
        prep_stmt = con->prepareStatement(query_string);
        prep_stmt->setString(1,toString(data["message_line1"]));
        prep_stmt->setString(2,toString(data["m1_font_family"]));
        prep_stmt->setInt(3,data["m1_font_size"]);
        prep_stmt->setString(4,toString(data["m1_font_color"]));

        prep_stmt->setString(5,toString(data["message_line2"]));
        prep_stmt->setString(6,toString(data["m2_font_family"]));
        prep_stmt->setInt(7,data["m2_font_size"]);
        prep_stmt->setString(8,toString(data["m2_font_color"]));

        prep_stmt->setString(9,toString(data["message_line3"]));
        prep_stmt->setString(10,toString(data["m3_font_family"]));
        prep_stmt->setInt(11,data["m3_font_size"]);
        prep_stmt->setString(12,toString(data["m3_font_color"]));

        prep_stmt->setString(13,toString(data["description"]));
        prep_stmt->setString(14,toString(data["bg_file"]));
        prep_stmt->setString(15,toString(data["animation_file"]));
        prep_stmt->setString(16,toString(data["title"]));
        prep_stmt->setString(17,toString(data["animation_type"]));
       // prep_stmt->setInt(18,data["timeout"]);
        prep_stmt->setString(18,toString(data["bg_type"]));
        prep_stmt->setString(19,toString(data["bg_color"]));
        prep_stmt->setInt(20,data["bg_opacity"]);
       // prep_stmt->setInt(22,data["auto_stage_change"]);
        //prep_stmt->setInt(23,data["next_stage_id"]);
        prep_stmt->setString(21,toString(data["stage_id"]));
        prep_stmt->setInt(22,data["schedule"]);
        prep_stmt->setInt(23,data["known_customer"]);
        

        prep_stmt->executeUpdate();
        
        query_string = "Update greeting_screen set auto_stage_change=?,next_stage_id=?,timeout_period=? where stage_id=? and known_customer=?";
        prep_stmt = con->prepareStatement(query_string);
        prep_stmt->setInt(1,data["auto_stage_change"]);
        prep_stmt->setInt(2,data["next_stage_id"]);
        prep_stmt->setInt(3,data["timeout"]);
        prep_stmt->setString(4,toString(data["stage_id"]));
        prep_stmt->setInt(5,data["known_customer"]);
        prep_stmt->executeUpdate();

        result = "Success";
        op_status="Failed";
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
    {
        writeException("updateStage",e.what());
        delete con;
        result = "Failed";
    }
    logUserActivity("Greeting Stage", "Update Greeting Stage , Result: " + result, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), result);
        return result;
    
}

Php::Value getStageDetails(Php::Value data) {
	
    Php::Value response;
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    int customer_type = data["known_customer"];
    try {      
        con = General.mysqlConnect(ServerDB);
	//writeException("getStageDetails", "select a.*,b.stage_title from greeting_screen as a join greeting_stages as b on a.stage_id = b.stage_id where a.stage_id ='"+toString(data["stage_id"])+"' and a.schedule='"+toString(data["schedule"])+"' and a.known_customer="+to_string(customer_type)+" and b.customer_type="+to_string(customer_type));
        prep_stmt = con->prepareStatement("select a.*,b.stage_title from greeting_screen as a join greeting_stages as b on a.stage_id = b.stage_id where a.stage_id =? and a.schedule=? and a.known_customer=? and b.customer_type=?");
        prep_stmt->setString(1, toString(data["stage_id"]));
        prep_stmt->setString(2, toString(data["schedule"]));
        prep_stmt->setInt(3, customer_type);
	prep_stmt->setInt(4, customer_type);
        res = prep_stmt->executeQuery();
        if (res->next()) {	
            sql::ResultSetMetaData *res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();
            for (int i = 1; i <= columns; i++)
                response[res_meta -> getColumnLabel(i)] = string(res->getString(i));
        }
	
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("getStageDetails", e.what());
        delete con;
    }
    return response;
}

void showAdvertisementVideos(Php::Value data) {
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    try {
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from greeting_screen_advertisement where stage_id=? and known_customer=? order by id desc");        
        prep_stmt->setString(1, toString(data["stage"]));
	prep_stmt->setString(2, toString(data["known_customer"]));
        res = prep_stmt->executeQuery();        
       
            Php::out<<"<table  class='table  table-bordered ' id='table_videos'>"<<std::endl;
            
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out << "<tr>" << endl;
            Php::out << "<th>Media</th>" << endl;
            Php::out << "<th>Start Date</th>" << endl;
            Php::out << "<th>Expiry Date</th>" << endl;
            Php::out << "<th></th>"<< endl;
            Php::out << "<th><button type='button' class='btn btn-info advt-video-add-btn' title='Add Video'><i class='fas fa-plus'></i></button></th>" << endl;            
            Php::out << "</tr>" << endl;
            Php::out << "</thead>" << std::endl;
            
            while (res->next()) {
                Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
                Php::out << "<td>" + res->getString("file_name") + "</td>" << endl;
                Php::out << "<td>" + res->getString("start_date") + "</td>" << endl;
                Php::out << "<td>" + res->getString("expiry_date") + "</td>" << endl;
                if(res->getString("file_type")=="video/mp4")
                    Php::out<<"<td><video width='100' controls='controls' preload='metadata'><source src='"+string(DisplayMediaPath)+"/"+res->getString("file_name")+"#t=0.5' type='video/mp4'></video></td>"<<endl;
                else
                    Php::out<<"<td><img width='100' src='"+string(DisplayMediaPath)+"/"+res->getString("file_name")+"'></td>"<<endl;
                Php::out << "<td>" << std::endl;
                if (res->getInt("status") == 1)
                    Php::out << "<button type='button' class='btn btn-danger ad-video-enable-disable-btn' data-text='Disable' title='Disable'><i class='fas fa-stop-circle'></i></button>" << std::endl;
                else
                    Php::out << "<button type='button' class='btn btn-success ad-video-enable-disable-btn' data-text='Enable' title='Enable'><i class='fas fa-play-circle'></i></button>" << std::endl;            

                Php::out << "<button type='button' class='btn btn-info ad-video-edit' title='Edit' data-text='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
                Php::out << "</td>" << std::endl;
                Php::out << "</tr>" << endl;
                }
          Php::out<<"</table>"<<std::endl;   
        
        
               
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("showAdvertisementVideos", e.what());
        delete con;
    }

}

Php::Value enabledisableAdVideo(Php::Value json) {
    string msg = "Failed",op_status="Failed";
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;   
    try {
        int status = json["status"];
        int id = json["id"];                 
        con = General.mysqlConnect(ServerDB);
        string query = "update greeting_screen_advertisement set status=? where id=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,status);
        prep_stmt->setInt(2,id);
        int n = prep_stmt->executeUpdate();
        if(n>0)
        {
            msg = "Successfull";
	    op_status = "Success";
        }
        
        delete prep_stmt;
        delete con;
    
    }    catch (const std::exception& e) {
        writeException("enabledisableAdVideo", e.what());
        delete con;
    }
    logUserActivity("Greeting Stage", "Enable/Disable Ad Video, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}


Php::Value insertUpdateAdVideo(Php::Value data)
{
    string result = "Failed",op_status = "Failed";
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt=NULL;    
    try
        {
        string id=data["id"];
        string query_string="";
        
        
        con= General.mysqlConnect(ServerDB); 
        if(id=="")
            {
            query_string = "insert into greeting_screen_advertisement(start_date,expiry_date,file_name,stage_id,file_type,status,known_customer)values(?,?,?,?,?,1,?)";           
            prep_stmt = con->prepareStatement(query_string);                  
            prep_stmt->setString(3,toString(data["file_name"]));
            prep_stmt->setInt(4,data["stage"]);
            prep_stmt->setString(5,toString(data["file_type"]));
	    prep_stmt->setString(6,toString(data["known_customer"]));
            }
        else
            {
            query_string = "Update greeting_screen_advertisement set start_date=?,expiry_date=? where id=?";            
            prep_stmt = con->prepareStatement(query_string);      
            prep_stmt->setString(3,id);
            }
                                           
        prep_stmt->setString(1,toString(data["start_date"]));
        prep_stmt->setString(2,toString(data["expiry_date"]));
                
        prep_stmt->executeUpdate();
        result = "Success";
        op_status = "Success";
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
    {
        writeException("insertUpdateAdVideo",e.what());
        delete con;
    }
    logUserActivity("Greeting Screen", "Insert/Update Ad Video, Result: " + result, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), result);
        return result;
    
}

void getfontfamilyDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select font_family from greeting_font_styles";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        if(res->next())
        {
            vector<string>font_array = splitstring(res->getString("font_family"),','); //video labels
            for(string font:font_array) 
            {
                Php::out << "<option value='"<<font<<"'>"<<font<<"</option>" << std::endl; 
            }
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getfontfamilyDropdown", e.what());
        delete con;
    }
}

void getfontsizeDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select font_size from greeting_font_styles";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        if(res->next())
        {
            vector<string>font_array = splitstring(res->getString("font_size"),','); //video labels
            for(string font:font_array) 
            {
                Php::out << "<option value="<<font<<">"<<font<<"</option>" << std::endl; 
            }
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getfontsizeDropdown", e.what());
        delete con;
    }
}

void getfontcolorDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select font_color from greeting_font_styles";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        if(res->next())
        {
            vector<string>font_array = splitstring(res->getString("font_color"),','); //video labels
            for(string font:font_array) 
            {
                Php::out << "<option value="<<font<<">"<<font<<"</option>" << std::endl; 
            }
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getfontcolorDropdown", e.what());
        delete con;
    }
}

void getStagesDropdown()
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;  
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select distinct stage_title,stage_id from greeting_stages";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            Php::out << "<option value="<<res->getInt("stage_id")<<">"<<res->getString("stage_title")<<"</option>" << std::endl; 
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getStagesDropdown", e.what());
        delete con;
    }
}

/*void getStagesList(Php::Value data)
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    bool left_aligned=false;
    string left_class="";
    int type=data["type"];
    int edit_access = data["edit"];  
    int delete_access = data["delete"];
    string status_column;
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select * from greeting_stages";
        if(type==1){
           // query += " where known_customer_status > 0 and known_customer_order>0 order by known_customer_order";
            query += " order by known_customer_order=0,known_customer_order";
            status_column  = "known_customer_status";
        }
        else{
            //query += " where unknown_customer_status > 0 and unknown_customer_order>0 order by unknown_customer_order";
            query += " order by unknown_customer_order=0,unknown_customer_order";
            status_column  = "unknown_customer_status";
        }
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            if(left_aligned==true)
            {
                left_class="left-aligned";
                left_aligned=false;
            }
            else
            {
                left_class="";
                left_aligned=true;
            }
            Php::out<<"<article class='timeline-entry "+left_class+"'>"<<endl;
            Php::out<<"<div class='timeline-entry-inner'>"<<endl;
            Php::out<<"<div class='dropdown'>"<<endl;
            if(res->getInt(status_column)==1)
                Php::out<<"<div class='timeline-icon "+res->getString("bg_color")+" stage'><i class='"+res->getString("stage_icon")+"'></i>"<<endl;
            else
                Php::out<<"<div class='timeline-icon gray stage'><i class='"+res->getString("stage_icon")+"'></i>"<<endl;
            Php::out<<"<div class='dropdown-content'>"<<endl;
	    if(edit_access==1)
            	Php::out<<"<div class='btn btn-block btn-default edit-btn' data-id='"+res->getString("stage_id")+"'>Edit</div>"<<endl;
            Php::out<<"<div class='btn btn-block btn-default preview-btn' data-id='"+res->getString("stage_id")+"1'>Preview</div>"<<endl;
	    if(delete_access==1){
		    if(res->getInt(status_column)==1)
		        Php::out<<"<div class='btn btn-block btn-default enable-disable-btn' data-id='"+res->getString("stage_id")+"'>Disable</div>"<<endl;
		    else
		        Php::out<<"<div class='btn btn-block btn-default enable-disable-btn' data-id='"+res->getString("stage_id")+"'>Enable</div>"<<endl;
	    }
            Php::out<<"</div>"<<endl;
            Php::out<<"</div>"<<endl;
            Php::out<<"</div>"<<endl;
            if(res->getInt(status_column)==1)
                Php::out<<"<div class='timeline-label "+res->getString("bg_color")+"'><h4 class='timeline-title' id='title-"+res->getString("stage_id")+"'>"+res->getString("stage_title")+"</h4>"<<endl;
            else
                Php::out<<"<div class='timeline-label gray'><h4 class='timeline-title' id='title-"+res->getString("stage_id")+"'>"+res->getString("stage_title")+"</h4>"<<endl;
            Php::out<<"</div>"<<endl;
            Php::out<<"</div>"<<endl;
            Php::out<<"</article>"<<endl;
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getStagesList", e.what());
        delete con;
    }
}*/


void getStagesList(Php::Value data)
{
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    bool left_aligned=false;
    string left_class="";
    int stage_id = 0,last_stage_id = 0;
    //int type=data["type"];
    int edit_access = data["edit"];  
    int delete_access = data["delete"];
    string customer_icon = "";
    try 
    {
        con = General.mysqlConnect(ServerDB);
        string query = "select * from greeting_stages order by stage_id";
        prep_stmt = con->prepareStatement(query);
        res = prep_stmt->executeQuery();
        while(res->next())
        {
	    stage_id = res->getInt("stage_id");
	    if(stage_id != last_stage_id)
	    {
		    if(left_aligned==true)
		    {
		        left_class="left-aligned";
		        left_aligned=false;
		    }
		    else
		    {
		        left_class="";
		        left_aligned=true;
		    }
	            last_stage_id = stage_id;
	    }
	    
	    if(res->getInt("customer_type")==0)
	    {
		customer_icon = "fa fa-question-circle";
	    }
	    else if(res->getInt("customer_type")==1)
	    {
		customer_icon = "fa fa-user-circle";
            }
 	    else
	    {
		customer_icon = "";
            }
	    
	    Php::out<<"<article class='timeline-entry "+left_class+"'>"<<endl;
	    Php::out<<"<div class='timeline-entry-inner'>"<<endl;
	    Php::out<<"<div class='dropdown'>"<<endl;
	    if(res->getInt("status")==1)
	        Php::out<<"<div class='timeline-icon stage fg-white' style='background-color:"+res->getString("bg_color")+";'><i class='"+res->getString("stage_icon")+"'></i>"<<endl;
	    else
	        Php::out<<"<div class='timeline-icon gray stage'><i class='"+res->getString("stage_icon")+"'></i>"<<endl;
	    Php::out<<"<div class='dropdown-content'>"<<endl;
	    if(edit_access==1)
	    	Php::out<<"<div class='btn btn-block btn-default edit-btn' data-id='"+res->getString("stage_id")+"' data-customer="+res->getString("customer_type")+" >Edit</div>"<<endl;
	    Php::out<<"<div class='btn btn-block btn-default preview-btn' data-id='"+res->getString("stage_id")+"1' data-customer="+res->getString("customer_type")+">Preview</div>"<<endl;
	    if(delete_access==1){
		    if(res->getInt("status")==1)
			Php::out<<"<div class='btn btn-block btn-default enable-disable-btn' data-id='"+res->getString("stage_id")+"' data-customer="+res->getString("customer_type")+">Disable</div>"<<endl;
		    else
			Php::out<<"<div class='btn btn-block btn-default enable-disable-btn' data-id='"+res->getString("stage_id")+"' data-customer="+res->getString("customer_type")+">Enable</div>"<<endl;
	    }
	    //Php::out<<"<div class='minicolors minicolors-theme-bootstrap minicolors-position-bottom minicolors-position-left minicolors-focus'><input type='text' id='hue-demo' class='form-control demo minicolors-input' data-control='hue' value='#ff6161' size='7'><span class='minicolors-swatch minicolors-sprite minicolors-input-swatch'><span class='minicolors-swatch-color' style='background-color: rgb(255, 97, 97); opacity: 1;'></span></span><div class='minicolors-panel minicolors-slider-hue' style='display: block;'><div class='minicolors-slider minicolors-sprite'><div class='minicolors-picker' style='top: 150px;'></div></div><div class='minicolors-opacity-slider minicolors-sprite'><div class='minicolors-picker'></div></div><div class='minicolors-grid minicolors-sprite' style='background-color: rgb(255, 0, 0);'><div class='minicolors-grid-inner'></div><div class='minicolors-picker' style='top: 0px; left: 93px;'><div></div></div></div></div></div>"<<endl;
	     Php::out<<"<div class='mt-2 bgcolordiv'><input type='color' class='bgcolor' data-id ="+res->getString("id")+" value='"+res->getString("bg_color")+"' style='height:50px;'></div>"<<endl;
	    //Php::out<<"<div class='btn btn-block btn-default'><input type='text' id='demo' class='demo' value='"+res->getString("bg_color")+"' style='height:30px;'></div>"<<endl;

	    Php::out<<"</div>"<<endl;
	    Php::out<<"</div>"<<endl;
	    Php::out<<"</div>"<<endl;
	    if(res->getInt("status")==1)
	        Php::out<<"<div class='timeline-label fg-white' style='background-color:"+res->getString("bg_color")+";border-color:"+res->getString("bg_color")+"'><h4 class='timeline-title' id='title-"+res->getString("stage_id")+"'><i class='"+customer_icon+"'></i>"+res->getString("stage_title")+"</h4>"<<endl;
	    else
	        Php::out<<"<div class='timeline-label gray'><h4 class='timeline-title' id='title-"+res->getString("stage_id")+"'><i class='"+customer_icon+"'></i>"+res->getString("stage_title")+"</h4>"<<endl;
	    Php::out<<"</div>"<<endl;
	    Php::out<<"</div>"<<endl;
	    Php::out<<"</article>"<<endl;
	    
        }
        delete res;
        delete prep_stmt;
        delete con;
    
    }    
    catch (const std::exception& e) {
        writeException("getStagesList", e.what());
        delete con;
    }
}


Php::Value enabledisableStage(Php::Value json) {
    string msg = "Failed",op_status="Failed";
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt; 
    string query="";
    try {
        int status = json["status"];
        int stage_id = json["stage_id"];    
        int customer_type = json["customer_type"];    
        con = General.mysqlConnect(ServerDB);
        if(customer_type==0)
        {
            query = "update greeting_stages set status=? where stage_id=? and customer_type=?";
        }
        else
        {
            query = "update greeting_stages set status=? where stage_id=? and customer_type=?";
        }
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,status);
        prep_stmt->setInt(2,stage_id);
	prep_stmt->setInt(3,customer_type);
        int n = prep_stmt->executeUpdate();


        if(customer_type==0)
        {
            query = "update greeting_screen set status=? where stage_id=? and known_customer=0";
        }
        else
        {
            query = "update greeting_screen set status=? where stage_id=? and known_customer=1";
        }
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,status);
        prep_stmt->setInt(2,stage_id);
        prep_stmt->executeUpdate();
        if(n>0)
        {
            msg = "Successfull";
	    op_status = "Success";
        }
        
        delete prep_stmt;
        delete con;
    
    }    catch (const std::exception& e) {
        writeException("enabledisableStage", e.what());
        delete con;
    }
    logUserActivity("Greeting Stage", "Enable/Disable Stage, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}

Php::Value contentService()
{
    Php::Value response,row,greeting,ads;
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    sql::ResultSetMetaData *res_meta;
    int i=0,columns=0,j;
    string columname;
    try {      
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from greeting_screen");
        
        res = prep_stmt->executeQuery();
        while (res->next()) {
            res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();
            for (j = 1; j<= columns; j++){
                columname=res_meta->getColumnTypeName(j);
                if(columname.find("INT")!=std::string::npos)
                    row[res_meta -> getColumnLabel(j)] = res->getInt(j);
                else
                    row[res_meta -> getColumnLabel(j)] = string(res->getString(j));
                }
                greeting[i] = row;
                i++;
        }
        response["greeting"] = greeting;

        row="";
        prep_stmt = con->prepareStatement("select * from greeting_screen_advertisement");        
        res = prep_stmt->executeQuery();
        i=0;
        while (res->next()) {
            res_meta = res -> getMetaData();
            columns = res_meta -> getColumnCount();
            for (j = 1; j<= columns; j++){
                columname=res_meta->getColumnTypeName(j);
                if(columname.find("INT")!=std::string::npos)
                    row[res_meta -> getColumnLabel(j)] = res->getInt(j);
                else
                    row[res_meta -> getColumnLabel(j)] = string(res->getString(j));
                }
                ads[i] = row;
                i++;
        }
        response["ads"] = ads;
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeExceptionService("contentService", e.what());
        delete con;
    }
    return response;

}

Php::Value updateStageColor(Php::Value data)
{
    string result = "Failed",op_status = "Failed";
    sql::Connection *con=NULL;
    sql::PreparedStatement *prep_stmt=NULL;    
    try
        {
        int id=data["id"];
	string hex_color = data["hex"];
        string query_string="";
        
        
        con= General.mysqlConnect(ServerDB); 
        
	query_string = "Update greeting_stages set bg_color=? where id=?";            
	prep_stmt = con->prepareStatement(query_string);      
	prep_stmt->setString(1,hex_color);
        prep_stmt->setInt(2,id);
                
        prep_stmt->executeUpdate();
        result = "Success";
        op_status = "Success";
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
    {
        writeException("updateStageColor",e.what());
        delete con;
    }
    logUserActivity("Greeting Screen", "Update Stage Color, Result: " + result, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), result);
        return result;
    
}


void interfaceReport(Php::Value data)
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
            Php::out << "<th>Request</th>" << endl;
            Php::out << "<th>Response</th>" << endl;
            Php::out << "<th>Type</th>" << endl;
            Php::out << "</tr></thead>" << endl;
            Php::out << "<tbody>" << endl;
            
            while (res->next()) {               
                Php::out << "<tr>" << endl;                
                Php::out << "<td>" + res->getString("request_date_time") + "</td>" << endl;
                Php::out << "<td>" + res->getString("interface_name") + "</td>" << endl;
                Php::out << "<td> " + res->getString("request") + " </td>" << endl;
                Php::out << "<td>" + res->getString("response") + "</td>" << endl;
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
        writeException("interfaceReport", e.what());
    }

}



Php::Value parcxGreetingScreen(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:response = uploadMedia(data);           
            break;
        case 2:response = updateStage(data);
            break;
        case 3:response = getStageDetails(data);
            break;
        case 4:showAdvertisementVideos(data);
            break;
        case 5:response=enabledisableAdVideo(data);
            break;
        case 6:response=insertUpdateAdVideo(data);
            break;
        case 7:getfontfamilyDropdown();
            break;
        case 8:getfontsizeDropdown();
            break;
        case 9:getfontcolorDropdown();
            break;
        case 10:getStagesDropdown();
            break;
        case 11:getStagesList(data);
            break;
        case 12:response=enabledisableStage(data);
            break;
        case 13: response = contentService();
            break;
	case 14: response = updateStageColor(data);
	    break;
	case 15:interfaceReport(data);
	    break;
	
        
    }
    return response;
}


extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_GreetingScreen", "1.0");
        extension.add<parcxGreetingScreen>("parcxGreetingScreen");
        return extension;
    }
}
