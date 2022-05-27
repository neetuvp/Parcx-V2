#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>
#include <iostream>
#include <fstream>
#include <sstream>
#include <cppconn/prepared_statement.h>
#include "validation.h"
#include<vector>
#include <string>
#include <sys/stat.h>
#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

#define MANDATORY "Mandatory"
#define MAXLENGTH "Maximum Length Exceeded"
#define CONSTRAINT "Invalid Character"
#define MediaPath "/parcx/Media/cms"
#define AbsMediaPath "/opt/lampp/htdocs/parcx/Media/cms"
#define ThumbnailPath "../../Media/cms/thumbnail"
using namespace std;
GeneralOperations General;
Validation validation;
string validation_failed = "Validation Failed\n";
void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-ContentManagement-" + General.currentDateTime(dateFormat), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-ContentManagement-" + General.currentDateTime(dateFormat), function, message);
    writeLog(function, "Exception: " + message);
}


string toString(Php::Value param) {
    string value = param;
    return value;
}

std::vector<int> split_videolist(const std::string& s, char delimiter) {
    std::vector<int> splits;
    std::string split;
    std::istringstream ss(s);
    while (std::getline(ss, split, delimiter)) {
        splits.push_back(stoi(split));
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


void showPlayListSettings(Php::Value data)
    {
    try
        {
        Php::Value role;
        sql::Connection *con;
        sql::Statement *stmt;            
        sql::ResultSet *res;
	int edit_access = data["edit"];
	int delete_access = data["delete"];
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from playlist_settings order by playlist_status desc");
        if(res->rowsCount()>0)
        {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th>Playlist</th>"<<endl;
            Php::out<<"<th>Description</th>"<<endl;        
            
            //if(edit_access==1 ||delete_access==1)
                Php::out<<"<th></th>"<<endl;		           
           
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
        }
        while(res->next())
        {
            Php::out<<"<tr data-id='"<<res->getString("id")<<"'>"<<endl;                       
            Php::out<<"<td>"+res->getString("playlist_name")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("playlist_description")+"</td>"<<endl;            
            Php::out << "<td>"<< std::endl;
            Php::out << "<button type='button' class='btn btn-info playlist-view' title='View Playlist' data-text='View Playlist'><i class='fas fa-eye'></i></button>"<< std::endl;  
            if(edit_access==1 ||delete_access==1){
               
                if(delete_access==1)
                {
                    if(res->getInt("playlist_status")==1)
                        Php::out << "<button type='button' class='btn btn-danger playlist-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
                    else
                        Php::out << "<button type='button' class='btn btn-success playlist-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;
                }
                if(edit_access==1)
                {
                    Php::out << "<button type='button' class='btn btn-info playlist-edit' title='Edit'><i class='fas fa-edit'></i></button>"<< std::endl;            
                }  
            }
            Php::out << "</td>"<< std::endl;
            Php::out<<"</tr>"<<endl;	
        }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete stmt;
        delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showPlayListSettings",e.what());
        }
    
    }

Php::Value enabledisablePlaylist(Php::Value json) {
    string msg = "Failed";
    string op_status="Failed";
    try {
        int status = json["status"];
        int id = json["id"];
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt;                    
        con = General.mysqlConnect(ServerDB);
        string query = "update playlist_settings set playlist_status=? where id=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,status);
        prep_stmt->setInt(2,id);
        int n = prep_stmt->executeUpdate();
        if(n>0)
        {
            msg = "Successfull";
	    op_status = "Success";
            query = "update device_playlist set status=? where playlist_id=?";
            prep_stmt = con->prepareStatement(query);
            prep_stmt->setInt(1,status);
            prep_stmt->setInt(2,id);
            prep_stmt->executeUpdate();
        }

        delete prep_stmt;
        delete con;
    
    }    catch (const std::exception& e) {
        writeException("enabledisablePlaylist", e.what());
    }
    logUserActivity("Playlist Setting", "Enable/Disable Playlist Setting, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    
    return msg;
}

void getPlaylistVideos(Php::Value data)
    {
    try
        {
        Php::Value role;
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt;            
        sql::ResultSet *res;
        int id = data["id"];
        int type = data["type"]; //type =1 for view else edit
        int i=0;
        string width = "100";
        con= General.mysqlConnect(ServerDB); 
        //prep_stmt=con->prepareStatement("SELECT *, (SELECT GROUP_CONCAT(media_label) FROM media_library WHERE FIND_IN_SET(id, playlist_videos)) AS media_label,(SELECT GROUP_CONCAT(media_name) FROM media_library WHERE FIND_IN_SET(id, playlist_videos)) AS media_name FROM playlist_settings where id=?");
        prep_stmt=con->prepareStatement("SELECT playlist_videos FROM playlist_settings where id=?");
        prep_stmt->setInt(1,id);
        res=prep_stmt->executeQuery();
        
        if(res->next())
        {
            string ids="";   
            ids = res->getString("playlist_videos");
           // vector<string>video_array = split_videolist(res->getString("media_label"),','); //video labels
           // vector<string>video_name = split_videolist(res->getString("media_name"),','); //video name
           
           
            
                if(ids=="")
                {
                    Php::out<<"<div class='row'><div class='col'>Playlist is empty</div></div>"<<endl;
                }
                else
                {
                    prep_stmt = con->prepareStatement("SELECT * from media_library where id in("+ids+") ORDER BY FIND_IN_SET(id, '"+ids+"')");
                    res=prep_stmt->executeQuery();
                    while(res->next())
                    {
                        if(type==1) //for view
                        {
                            Php::out<<"<div class='btn btn-block btn-lg bg-gray'>"<<endl;
                            width="150";
                            //Php::out<<video_array[i]<<endl;    
                        }
                        else//for edit view
                        {
                            Php::out<<"<div class='nodrop btn btn-block btn-info btn-lg bg-gray play-video' id = v"+res->getString("id")+" data-path='"+string(MediaPath)+"/"+res->getString("media_name")+"'>"<<endl;
                            width="100";
                        }

                        Php::out<<"<div class='d-flex justify-content-between'>";
                        //Php::out<<"<div><video width='150' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='"+res->getString("media_type")+"'></video></div>"<<endl;
                        if (res->getString("media_type").find("image") != std::string::npos) {
                            Php::out<<"<img src='"+string(MediaPath)+"/"+res->getString("media_name")+"'  style='width:"+width+"px'>"<<endl;
                        }
                        else if (res->getString("media_type").find("video") != std::string::npos) 
                        {
                            if(type==1) //for view
                            {
                                if(res->getString("media_type")=="video/mp4")
                                {
                                    Php::out<<"<video width='"+width+"' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/mp4'></video>"<<endl;
                                }
                                else if(res->getString("media_type")=="video/webm")
                                {
                                    Php::out<<"<video width='"+width+"' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/webm'></video>"<<endl;
                                }
                                else if(res->getString("media_type")=="video/ogg")
                                {
                                    Php::out<<"<video width='"+width+"' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/ogg'></video>"<<endl;
                                }
                                else if(res->getString("media_type")=="video/x-msvideo")
                                {
                                    Php::out<<"<video width='"+width+"' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/avi'></video>"<<endl;

                                }
                            }
                            else
                            {
                                Php::out<<"<img src='"+string(ThumbnailPath)+"/"+res->getString("media_name")+".png'  style='width:100px'>"<<endl;
                            }

                        }
                        else if (res->getString("media_type").find("audio") != std::string::npos) 
                        {
                            Php::out<<"<audio style='width: "+width+"px;' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='audio/ogg'></audio>"<<endl;
                        }
                        Php::out<<"<div>"<<res->getString("media_label")<<"</div>"<<endl;
                        Php::out<<"</div></div>"<<endl;
                        i++;

                    }
                }
                
            
            
            
        }
        else
        {
            if(type==1)
            {
                Php::out<<"<div class='row'><div class='col'>Playlist is empty</div></div>"<<endl;
            }
        }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("getPlaylistVideos",e.what());
        }
    
    }

void getVideoslibrary(Php::Value data)
    {
    try
        {
        Php::Value role;
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt;            
        sql::ResultSet *res;
        string id = data["playlist_id"];
        string query="";
        con= General.mysqlConnect(ServerDB); 
        if(id!="")
        {
            string ids="";
            prep_stmt=con->prepareStatement("Select playlist_videos from playlist_settings where id=?");
            prep_stmt->setInt(1,stoi(id));
            res=prep_stmt->executeQuery();
            if(res->next())
            {
                ids = res->getString("playlist_videos");
            }
            if(ids=="")
            {
                prep_stmt=con->prepareStatement("Select * from media_library where media_status=1");
            }
            else
            {
                prep_stmt=con->prepareStatement("Select * from media_library where media_status=1 and id not in ("+ids+")");
            }
            
        }
        else{
            prep_stmt=con->prepareStatement("Select * from media_library where media_status=1");
        }
        res=prep_stmt->executeQuery();
        if(res->rowsCount()>0)
        {
           // Php::out<<"<table width='100%' class='jspdf-table'><tbody class='table-view'>"<<endl;
            
            while(res->next())
            {
                Php::out<<"<div class='nodrop btn btn-block btn-info btn-lg bg-gray play-video' id = v"+res->getString("id")+" data-path='"+string(MediaPath)+"/"+res->getString("media_name")+"'>"<<endl;
                Php::out<<"<div class='d-flex justify-content-between'>";
                //Php::out<<"<div><video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/mp4'></video></div>"<<endl;
                if (res->getString("media_type").find("image") != std::string::npos) {
                    Php::out<<"<img src='"+string(MediaPath)+"/"+res->getString("media_name")+"'  style='width:100px'>"<<endl;
                }
                else if (res->getString("media_type").find("video") != std::string::npos) 
                {
                    /*if(res->getString("media_type")=="video/mp4")
                    {
                        Php::out<<"<video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/mp4'></video>"<<endl;
                    }
                    else if(res->getString("media_type")=="video/webm")
                    {
                        Php::out<<"<video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/webm'></video>"<<endl;
                    }
                    else if(res->getString("media_type")=="video/ogg")
                    {
                        Php::out<<"<video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/ogg'></video>"<<endl;
                    }
                    else if(res->getString("media_type")=="video/x-msvideo")
                    {
                        Php::out<<"<video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/avi'></video>"<<endl;
                       
                    }*/
                    Php::out<<"<img src='"+string(ThumbnailPath)+"/"+res->getString("media_name")+".png'  style='width:100px'>"<<endl;

                }
                else if (res->getString("media_type").find("audio") != std::string::npos) 
                {
                    Php::out<<"<audio style='width: 100px;' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='audio/ogg'></audio>"<<endl;
                }
                
                Php::out<<"<div style='word-break: break-all;'>"<<res->getString("media_label")<<"</div>"<<endl;    
                Php::out<<"</div></div>"<<endl;
                	
            }
        }
        else
        {
            Php::out<<"<div class='row'><div class='col'>No Videos found</div></div>"<<endl;
        }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("getVideoslibrary",e.what());
        }
    
    }



Php::Value savePlaylist(Php::Value data)
{
    string msg = "Failed",op_status="Failed";    
    try
    {
        Php::Value role;
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt;            
        string playlist_name = data["playlist_name"];
        string video_list = data["video_list"];
        string description = data["description"];
        string id=data["id"];
        con= General.mysqlConnect(ServerDB); 
        if(id=="")
            prep_stmt=con->prepareStatement("Insert into playlist_settings(playlist_name,playlist_description,playlist_videos)values(?,?,?)");
        else
        {
            prep_stmt=con->prepareStatement("Update playlist_settings set playlist_name=?,playlist_description=?,playlist_videos=? where id = ?");
            prep_stmt->setString(4,id);
        }
        prep_stmt->setString(1,playlist_name);
        prep_stmt->setString(2,description);
        prep_stmt->setString(3,video_list);
            
        int n=prep_stmt->executeUpdate();
        if(n>=0)
        {
            msg = "Successfull";
	    op_status="Success";
        }
         
        delete prep_stmt;
        delete con;  
    }
    catch(const std::exception& e)
    {
        writeException("savePlaylist",e.what());
        msg = "Failed";
    }
    logUserActivity("Playlist Setting", "Insert/Update Playlist Setting, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), msg);
    return msg;
}

void screenDropdown(Php::Value data)
{
    try
    {
        sql::Connection *con;
        sql::Statement *stmt;            
        sql::ResultSet *res;
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select device_number,device_name from system_devices where device_category=12");
        
        while(res->next())
        {
           Php::out << "<option value='" << res->getString("device_number") << "'>" << res->getString("device_name") << "</option>" << std::endl;
        }	
        delete res;    
        delete stmt;
        delete con;  
    }
    catch(const std::exception& e)
    {
        writeException("screenDropdown",e.what());
    } 
}

void screenList(Php::Value data)
{
    try
    {
        //string html_data="";
        sql::Connection *con;
        sql::Statement *stmt;            
        sql::ResultSet *res,*res1;
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from system_devices where device_category=12 and device_enabled=1");
        //Php::out<<"select * from system_devices where device_category=12 and device_enabled=1"<<endl;
        if(res->rowsCount()>0)
            {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th></th>"<<endl;
            Php::out<<"<th>Screen Number</th>"<<endl; 
            Php::out<<"<th>Screen Name</th>"<<endl;
            Php::out<<"<th>IP Address</th>"<<endl; 
            
           // if(edit_access==1 ||delete_access==1)
                Php::out<<"<th></th>"<<endl;		           
           
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
        {
            Php::out<<"<tr data-id='"<<res->getString("device_number")<<"'>"<<endl; 
            //Php::out<<"<td><video width='150' controls='controls' preload='metadata'><source src='/parcx/Media/cms/Video1.mp4#t=0.5' type='video/mp4'></video></td>"<<endl;
           res1=stmt->executeQuery("SELECT *,lower(media_type) as type FROM "+string(ReportingDB)+".player_report where media_status='Playing' and device_number="+res->getString("device_number")+" order by play_datetime desc limit 1");
            if(res1->next())
            {
                if(res1->getString("type")=="mp4" || res1->getString("type")=="avi" || res1->getString("type")=="webm" || res1->getString("type")=="ogg" )
                {
                    //Php::out<<"<td><video width='150' controls='controls' preload='metadata'><source src='/parcx/Media/cms/"+res1->getString("media_name")+"#t=0.5' type='video/"+res1->getString("type")+"'></video></td>"<<endl;
                    Php::out<<"<td><img src = '/parcx/Media/cms/thumbnail/"+res1->getString("media_name")+".png' width='150'></td>"<<endl;
                }
                else
                {
                    Php::out<<"<td><img src = '/parcx/Media/cms/"+res1->getString("media_name")+"' width='150'></td>"<<endl;
                }
            }
            else
            {
                Php::out<<"<td><video width='150' controls='controls' preload='metadata'><source src='/parcx/Media/cms/Video1.mp4#t=0.5' type='video/mp4'></video></td>"<<endl;
            }
            delete res1;
            Php::out<<"<td>"+res->getString("device_number")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("device_name")+"</td>"<<endl;    
            Php::out<<"<td>"+res->getString("device_ip")+"</td>"<<endl;    
            Php::out << "<td>"<< std::endl;
            Php::out << "<button class='btn btn-info manage-playlist-button' data-id='"+res->getString("device_number")+"' data-name='"+res->getString("device_name")+"'>Manage Playlist</button>"<< std::endl;  
            /*if(res->getInt("playlist_status")==1)
                Php::out << "<button type='button' class='btn btn-danger playlist-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success playlist-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;*/
            
            
            Php::out << "</td>"<< std::endl;
            Php::out<<"</tr>"<<endl;	
        }
        //Php::out<<html_data<<endl;
        delete res;    
        delete stmt;
        delete con;  
    }
    catch(const std::exception& e)
    {
        writeException("screenList",e.what());
    } 
}

void insertScreenPlayList(Php::Value data)
    {
    string op_status="Failed",msg="Failed";
    try
        {
        string query_string="";
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt=NULL;    
        sql::ResultSet *res;
        int device_id = data["device_id"];
        Php::Value playlists = data["playlists"];
        //Php::out<<playlists.size()<<endl;

        con= General.mysqlConnect(ServerDB); 
        
        prep_stmt = con->prepareStatement("Update device_playlist set status = 0 where device_number = ?");
        prep_stmt->setInt(1,device_id);
        prep_stmt->executeUpdate();
        delete prep_stmt;
        for(int j=0;j<playlists.size();j++)
        {
            //writeException("insertScreenPlayList","here");
            prep_stmt = con->prepareStatement("Select playlist_id from device_playlist where device_number = ? and playlist_id = ?");
            prep_stmt->setInt(1,device_id);
            prep_stmt->setInt(2,playlists[j]["id"]);
            res = prep_stmt->executeQuery();
            if(res->next())
            {
                //writeException("insertScreenPlayList","update");
                query_string = "Update device_playlist set playlist_name=?,start_date=?,end_date=?,start_time=?,end_time=?,repeat_videos=?,last_updated_date_time=NOW(),status=1 where playlist_id=? and device_number=?";
            }
            else
            {
                //writeException("insertScreenPlayList","insert");
                query_string = "Insert into device_playlist(playlist_name,start_date,end_date,start_time,end_time,repeat_videos,playlist_id,device_number) values (?,?,?,?,?,?,?,?)";
                
            }
            
            prep_stmt = con->prepareStatement(query_string);
            prep_stmt->setString(1,toString(playlists[j]["name"]));
            prep_stmt->setString(2,toString(playlists[j]["start_date"]));
            prep_stmt->setString(3,toString(playlists[j]["end_date"]));
            prep_stmt->setString(4,toString(playlists[j]["start_time"]));
            prep_stmt->setString(5,toString(playlists[j]["end_time"]));
            prep_stmt->setInt(6,playlists[j]["repeat"]);
            prep_stmt->setInt(7,playlists[j]["id"]);
            prep_stmt->setInt(8,device_id);
            
            prep_stmt->executeUpdate();
	    msg="Successfull";
        }
    
        delete prep_stmt;
        delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("insertScreenPlayList",e.what());
        }
        logUserActivity("Device Playlist", "Insert/Update Device Playlist, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), msg);
    }

string removeSpecialCharactersfromText(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
       // cout<<i<<":"<<str[i]<<endl;
       //Allowed chars - Uppercase and Lowercase alphabets,numbers,curly brackets,space,comma,dot
      //if ((str[i]>=48 && str[i]<=57)||(str[i]>=65 && str[i]<=90)||(str[i]>=97 && str[i]<=122)||str[i]==46)
        if (!(str[i]==32))
          {
             // cout<<"cont."<<str[i]<<endl;
            continue;
          }
      else
      {
        //cout<<"String contains special character.\n";
        //flag=1;
        char c = str[i];
        //str.erase(std::find(str.begin(), str.end(), c));
        //i=i-1;
        std::replace(str.begin(), str.end(), c, '_');
      }
    }
    return str;
}

Php::Value uploadMedia(Php::Value data)
{
    string filename = toString(data["name"]);
    string filename_new = removeSpecialCharactersfromText(filename);
    string from = toString(data["from"]);
    //string dest = "../../Media/cms/"+filename_new;
    string dest = string(AbsMediaPath)+"/"+filename_new;
    double size = data["size"];
    int error = data["error"];
    string type = toString(data["type"]);
    string duration = data["duration"];
    string image_timeout="0",result = "",op_status="Failed";
    writeLog("uploadMedia","**********************************");
    writeLog("uploadMedia","filename:"+filename_new);
    writeLog("uploadMedia","size:"+to_string(size/ (1024 * 1024))+" MB");
    writeLog("uploadMedia","duration:"+duration);
    writeLog("uploadMedia","type:"+string(type));
    try{
        if(size > 200 * 1024 * 1024)
        {
            result ="File size is >200 MB" ;
        }
        else
        {
            string extension = filename.substr(filename.find_last_of(".") + 1);
            if(extension == "mp4" || extension == "avi" || extension == "MP4" || extension == "AVI" || extension == "webm" || extension == "WEBM" || extension == "jpeg" || extension == "png" || extension == "jpg" || extension == "gif" || extension == "ogg" || extension == "OGG" ||extension == "bmp" || extension == "BMP")  {
                if(error>0)
                {
                    result ="File Upload Error:"+to_string(error) ;
                }
                else
                {
                    int n = system(("sudo cp -p "+from+" "+dest).c_str());
                    writeLog("uploadMedia","Upload result"+to_string(n));
                    if(n==0)
                    {
                        system(("sudo chmod 777 "+dest).c_str());
                        result ="Success" ;
                        op_status="Success";
                        writeLog("uploadMedia",result);
                        sql::Connection *con;
                        sql::Statement *stmt;  
                        if (type.find("image") != std::string::npos) {
                            image_timeout = duration;
                            duration = "00:00:00";
                        }
                        else
                        {
                            image_timeout ="0";
                        }
                        try
                        { 
                            con= General.mysqlConnect(ServerDB); 
                            stmt=con->createStatement();
                            n = stmt->executeUpdate("Insert into media_library(media_name,media_label,media_type,media_duration,media_size,image_timeout) values ('"+filename_new+"','"+filename+"','"+type+"','"+duration+"',"+to_string(size/(1024*1024))+","+image_timeout+")");

                            delete stmt;
                            delete con;
                            if(n>0)
                            {
                                result = "Success";
                            }
                            else
                            {
                                result = "Failed to create a record";
				op_status = "Failed";
                            }
                        }
                    catch(const std::exception& e)
                        {
                            result ="DB Error: Failed" ;
			    op_status="Failed";
                            writeException("uploadMedia",e.what());
                        }
                    }
                    else
                    {
                        result ="Upload Failed" ;
                        writeException("uploadMedia","Failed to Upload");
                    }
                }
            } 
            else 
            {
                //writeLog("uploadMedia","Invalid Extension");
                result ="Invalid File Extension" ;
            }
        }
        writeLog("uploadMedia",result);
    }
    catch(const std::exception& e)
    {
        result ="Upload Failed" ;
        writeException("uploadMedia",e.what());
    }
    return result;
    logUserActivity("Upload Media", "UploadMedia file, Result: " + result, data["login_user_id"], toString(data["login_user_name"]), op_status, toString(data["request_ip"]), arrayToString(data), result);
}

void showMediaLibraryList(Php::Value data)
    {
    try
        {
        Php::Value role;
        sql::Connection *con;
        sql::Statement *stmt;            
        sql::ResultSet *res;
	int add_access = data["add"];
    	int delete_access = data["delete"];
        string video_type;
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from media_library order by media_status desc");
        if(res->rowsCount()>0)
        {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr><th>"<<endl;
	    if(add_access==1)
            	Php::out<<"<button type='button' class='btn btn-success upload-media-btn' title='Upload Media'><i class='fas fa-plus'></i></button>"<<endl;
            Php::out<<"</th><th>Media</th>"<<endl;
            Php::out<<"<th>Type</th>"<<endl;      
            Php::out<<"<th>Duration</th>"<<endl;      
            Php::out<<"<th>Size</th>"<<endl;      
            
           //if(delete_access==1)
            Php::out<<"<th></th>"<<endl;		           
           
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
        }
        else
        {
		if(add_access==1)	
            		Php::out<<"<button type='button' class='btn btn-success upload-media-btn' title='Upload Media'><i class='fas fa-plus'></i></button></br></br>"<<endl;
		else
			Php::out<<"No records found"<<endl;
        }
        while(res->next())
        {
            Php::out<<"<tr data-id='"<<res->getString("id")<<"'>"<<endl;   
            if (res->getString("media_type").find("image") != std::string::npos) {
                Php::out<<"<td><img src='"+string(MediaPath)+"/"+res->getString("media_name")+"'  style='width:100px'></td>"<<endl;
            }
            else if (res->getString("media_type").find("video") != std::string::npos) 
            {
                if(res->getString("media_type")=="video/mp4")
                {
                    video_type = "video/mp4";
                    
                    //Php::out<<"<td><video width='100' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='video/mp4'></video></td>"<<endl;
                }
                else if(res->getString("media_type")=="video/webm")
                {
                    video_type = "video/webm";
                    
                }
                else if(res->getString("media_type")=="video/ogg")
                {
                    video_type = "video/ogg";
                    
                }
                else if(res->getString("media_type")=="video/x-msvideo")
                {
                    video_type = "video/avi";
                   
                   // Php::out<<"<td><object data='"+string(MediaPath)+"/"+res->getString("media_name")+"' width='100' height='250'> <param name='src' value='"+string(MediaPath)+"/"+res->getString("media_name")+"' /> <embed type='video/x-ms-wm' src='"+string(MediaPath)+"/"+res->getString("media_name")+"' width='100' height='250'></embed> </object></td>"<<endl;
                }
                Php::out<<"<td><img src='"+string(ThumbnailPath)+"/"+res->getString("media_name")+".png'  style='width:100px' class = 'play-video' data-path='"+string(MediaPath)+"/"+res->getString("media_name")+"' data-type='"+res->getString("media_type")+"'></td>"<<endl;
                //Php::out<<"<td><video width='100' controls='controls' preload='none' poster='"+string(ThumbnailPath)+"/"+res->getString("media_name")+".png'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"' type='"+video_type+"'></video></td>"<<endl;
                
            }
            else if (res->getString("media_type").find("audio") != std::string::npos) 
            {
                Php::out<<"<td><audio style='width: 100px;' controls='controls' preload='metadata'><source src='"+string(MediaPath)+"/"+res->getString("media_name")+"#t=0.5' type='audio/ogg'></audio></td>"<<endl;
            }
            else
            {
                Php::out<<"<td></td>"<<endl;
            }
            Php::out<<"<td>"+res->getString("media_label")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("media_type")+"</td>"<<endl; 
            if (res->getString("media_type").find("image") != std::string::npos) {
                 Php::out<<"<td>"+res->getString("image_timeout")+"</td>"<<endl;
            }
            else
            {
                Php::out<<"<td>"+res->getString("media_duration")+"</td>"<<endl;
            }
            Php::out<<"<td>"+res->getString("media_size")+" MB</td>"<<endl;  
            Php::out << "<td>"<< std::endl;
            Php::out << "<button type='button' class='btn btn-info play-video' title='View Video' data-text='View Video' data-path='"+string(MediaPath)+"/"+res->getString("media_name")+"' data-type='"+res->getString("media_type")+"'><i class='fas fa-eye'></i></button>"<< std::endl;  
            if(delete_access==1){
		    if(res->getInt("media_status")==1)
		        Php::out << "<button type='button' class='btn btn-danger video-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
		    else
		        Php::out << "<button type='button' class='btn btn-success video-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;
            }
            Php::out << "</td>"<< std::endl;
            Php::out<<"</tr>"<<endl;	
        }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete stmt;
        delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showMediaLibraryList",e.what());
        }
    
    }

void removeMediafromPlaylist(int media_id,int playlist_id,string playlist_videos)
{
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt; 
    string playlist_updated="";
    try {
        vector<int>video_array = split_videolist(playlist_videos,','); //video labels
        for(int id:video_array) 
        {
            if(id==media_id)
                continue;
            else
            {
                playlist_updated = playlist_updated+to_string(id)+",";
            }
        }
        if (!playlist_updated.empty()) {
            playlist_updated.pop_back();
        }

        con = General.mysqlConnect(ServerDB);
        string query = "update playlist_settings set playlist_videos=? where id=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setString(1,playlist_updated);
        prep_stmt->setInt(2,playlist_id);
        prep_stmt->executeUpdate();
    }    
    catch (const std::exception& e) {
        writeException("removeMediafromPlaylist", e.what());
    }
}

Php::Value enabledisableMedia(Php::Value json) {
    string msg = "Failed",op_status="Failed";
    try {
        int status = json["status"];
        int id = json["id"];
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt; 
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        string query = "update media_library set media_status=? where id=?";
        prep_stmt = con->prepareStatement(query);
        prep_stmt->setInt(1,status);
        prep_stmt->setInt(2,id);
        int n = prep_stmt->executeUpdate();
        if(n>0)
        {
            msg = "Successfull";
	    op_status = "Success";
            query = "SELECT * FROM playlist_settings where FIND_IN_SET(?, playlist_videos)";
            prep_stmt = con->prepareStatement(query);
            prep_stmt->setInt(1,id);
            res = prep_stmt->executeQuery();
            while(res->next())
            {
                removeMediafromPlaylist(id,res->getInt("id"),res->getString("playlist_videos"));
            }
            delete res;
        }
        
        delete prep_stmt;
        delete con;
    
    }    catch (const std::exception& e) {
        writeException("enabledisableMedia", e.what());
    }
    logUserActivity("Enable/Disable Media", "Enable/Disable Media, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
    
    return msg;
}

Php::Value getDetailsbyId(string table, string id, Php::Value data) {
    Php::Value response;
    try {
        sql::Connection *con;
        sql::PreparedStatement *prep_stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from " + table + " where " + id + "=?");
        prep_stmt->setString(1, toString(data["id"]));
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
        writeException("getDetailsbyId", e.what());
    }
    return response;
}

string getScreenPlaylist(int device_number)
{
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    string id,html_data="",checked="";
    try {
        
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from device_playlist where status = 1 and device_number ="+to_string(device_number)+" order by start_date");
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            id = res->getString("playlist_id");
            html_data+="<div class='btn btn-block btn-info btn-lg bg-gray playlist-right' id='v"+id+"' data-playlist='"+id+"' >";
            html_data+="<div class='row'>";
            html_data+="<fieldset>";
            html_data+="<span class='float-left mr-3 hidden' id='span-cbox-"+id+"'><input type='checkbox' class = 'playlist-checkbox' id = 'c"+id+"' data-id='"+id+"' value='p"+id+"'></checkbox></span>";
            html_data+="<span class='float-left mr-1' id='span-del-"+id+"'><button class='btn btn-danger btn-sm del-playlist mr-1' value='delete"+id+"' data-id='"+id+"'><i class='fas fa-minus'></i></button></span>";
            html_data+="<span class='float-left mr-3' id='span-edit-"+id+"'><button class='btn btn-info btn-sm edit-playlist mr-1' value='edit"+id+"' data-id='"+id+"'><i class='fas fa-edit'></i></button></span>";
            html_data+="<label id = 'l"+id+"'>"+res->getString("playlist_name")+"</label>";
            html_data+="</fieldset>";
            html_data+="</div>";
            html_data+="<div class='row center'>";
            html_data+="<fieldset class='pl-options mt-2 hidden' id='pl-options-"+id+"'>";
            html_data+="<p><input type='text' id='date-p"+id+"' class='form-control scheduledate' autocomplete='off' placeholder='Choose Date Range' value = '"+res->getString("start_date")+" - "+res->getString("end_date")+"'></p>";
            html_data +="<div class='input-group'><input type='text' id='start-time"+id+"' class='form-control schedulestarttime timepicker' autocomplete='off' placeholder='Choose Start Time'  value='"+res->getString("start_time")+"'><input type='text' id='end-time"+id+"' class='form-control scheduleendtime timepicker' autocomplete='off' placeholder='Choose End Time' value='"+res->getString("end_time")+"'></div>";
            if(res->getInt("repeat_videos")==1)
                checked = "checked";
            else
                checked = "";
            html_data+="<p><span class='float-left'><input type='checkbox' id='repeat-p"+id+"' "+checked+"><label for=''>Repeat</label>";
            html_data+="</span></p></br>";
            html_data+="<p class='mt-2'><span class='text-center'><button class='btn btn-success add-playlist' value='add"+id+"' data-id='"+id+"'><i class='fas fa-plus'></i></button></span></p>";
            html_data+="</fieldset>";
            html_data+="</div>";
            html_data+="<details class='small'>";
            html_data+="<summary class='playlist-details-div' data-id='"+id+"'>View List</summary>";
            html_data+="<div id ='playlist-details-"+id+"' class='playlist-details d-inline'>";
            html_data+="</div>";
            html_data+="</details>";
            html_data+="</div>";
        }
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("getScreenPlaylist", e.what());
    }
    return html_data;
}

void manageScreenPlaylist(Php::Value data)
{
    sql::Connection *con;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    string html_data="";
    int device_number = data["device_id"];
    string device_name = data["device_name"];
    int edit = data["edit"];
    string id,playlist_excluded="";
    html_data +="<div id='playlist-div' class='row card card-body'>";
                 
    html_data +="<div class='row  mb-3'><div class='col h5'>";
    html_data +="<label for=''>Select Playlist for "+device_name+"</label>";
    html_data +="</div></div>";
    html_data +="<div class='row  mb-1'>";
    html_data +="<div class='col'><h4>PLAYLIST LIBRARY</h4></div>";
    html_data +="<div class='col p-2'></div>";
    html_data +="<div class='col'><h4>SCREEN PLAYLISTS</h4></div>";
    html_data +="</div>";
    html_data +="<div class='row'>";
    html_data +="<div class='col form-group form-control scroll-smooth' id='left' ondrop='drop(event)' ondragover='allowDrop(event)'>";
    try {
        
        con = General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("Select GROUP_CONCAT(playlist_id) as playlist_id from device_playlist where device_number=? and status=1");
        prep_stmt->setInt(1,device_number);
        res = prep_stmt->executeQuery();
        if(res->next())
        {
            playlist_excluded = res->getString("playlist_id");
        }
        delete res;
        if(playlist_excluded=="")
        {
            prep_stmt = con->prepareStatement("select * from playlist_settings where playlist_status=1");
        }
        else
        {
            prep_stmt = con->prepareStatement("select * from playlist_settings where id not in ("+playlist_excluded+") and playlist_status=1");
        }
        
        res = prep_stmt->executeQuery();
        while(res->next())
        {
            id = res->getString("id");
            html_data +="<div class='btn btn-block btn-info btn-lg bg-gray' id='v"+id+"' data-playlist='"+id+"'>";
            html_data +="<div class='row'>";
            html_data +="<fieldset><span class='float-left mr-3' id='span-cbox-"+id+"'><input type='checkbox' class = 'playlist-checkbox' id = 'c"+id+"' data-id='"+id+"' value='p"+id+"'></checkbox></span><span class='float-left mr-1 hidden' id='span-del-"+id+"'><button class='btn btn-danger btn-sm del-playlist mr-1' value='delete"+id+"' data-id='"+id+"'><i class='fas fa-minus'></i></button></button></span>";
            html_data += "<span class='float-left mr-3 hidden' id='span-edit-"+id+"'><button class='btn btn-info btn-sm edit-playlist mr-1' data-id='"+id+"'><i class='fas fa-edit'></i></button></button></span>";
            html_data += "<label id = 'l"+id+"'>"+res->getString("playlist_name")+"</label></fieldset>";
            html_data +="</div>";
            html_data +="<div class='row center'>";
            html_data +="<fieldset class='pl-options mt-2' id='pl-options-"+id+"'><p><input type='text' id='date-p"+id+"' class='form-control scheduledate' autocomplete='off' placeholder='Choose Date Range'></p>";
            html_data +="<div class='input-group'><input type='text' id='start-time"+id+"' class='form-control schedulestarttime timepicker' autocomplete='off' placeholder='Choose Start Time' value='' data-id="+id+"><input type='text' id='end-time"+id+"' class='form-control scheduleendtime timepicker' autocomplete='off' placeholder='Choose End Time' value=''  data-id="+id+"></div>";
            //html_data+="<input type='text' id='start-time"+id+"' class='form-control schedulestarttime' autocomplete='off' placeholder='Choose Start Time' value = '"+res->getString("start_time")+"'> <input type='text' id='end-time"+id+"' class='form-control scheduleendtime' autocomplete='off' placeholder='Choose End Time' value = '"+res->getString("end_time")+"'>";
            html_data+="<p><span class='float-left mt-1'><input type='checkbox' id='repeat-p"+id+"'><label for=''>Repeat</label></span></p></br><p class='mt-2'><span class='text-center'><button class='btn btn-success add-playlist' value='add"+id+"' data-id='"+id+"'><i class='fas fa-plus'></i></button></span></p></fieldset>";
            html_data +="</div>";
            html_data +="<details class='small'>";
            html_data +="<summary class='playlist-details-div' data-id='"+id+"'>View List</summary>";
            html_data +="<div id ='playlist-details-"+id+"' class='playlist-details d-inline'></div>";
            html_data +="</details>";
            html_data +="</div>";
        }
        delete res;
        delete prep_stmt;
        delete con;
    } catch (const std::exception& e) {
        writeException("manageScreenPlaylist", e.what());
    }

    html_data +="</div><div class='p-2'>";
    html_data +="</div>";

    html_data +="<div class='col form-group form-control scroll-smooth' id='right' ondrop='drop(event)' ondragover='allowDrop(event)'>";
    html_data +=getScreenPlaylist(device_number);
    html_data +="</div>";                   
    html_data +="</div>"; 
    if(edit==1)
	html_data +="<input type='submit' class='signUp btn btn-block btn-info mt-2 btn-lg' value='Save' id='save-screen-playlist' data-screen='"+to_string(device_number)+"'>";
    html_data +="</div>";
    Php::out<<html_data<<endl;

}

const std::string b64decode(const void* data, const size_t &len)
{
   // static const char* B64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

    static const int B64index[256] =
    {
        0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,
        0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,
        0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  62, 63, 62, 62, 63,
        52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 0,  0,  0,  0,  0,  0,
        0,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9,  10, 11, 12, 13, 14,
        15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 0,  0,  0,  0,  63,
        0,  26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51
    };
    if (len == 0) return "";

    unsigned char *p = (unsigned char*) data;
    size_t j = 0,
        pad1 = len % 4 || p[len - 1] == '=',
        pad2 = pad1 && (len % 4 > 2 || p[len - 2] != '=');
    const size_t last = (len - pad1) / 4 << 2;
    std::string result(last / 4 * 3 + pad1 + pad2, '\0');
    unsigned char *str = (unsigned char*) &result[0];

    for (size_t i = 0; i < last; i += 4)
    {
        int n = B64index[p[i]] << 18 | B64index[p[i + 1]] << 12 | B64index[p[i + 2]] << 6 | B64index[p[i + 3]];
        str[j++] = n >> 16;
        str[j++] = n >> 8 & 0xFF;
        str[j++] = n & 0xFF;
    }
    if (pad1)
    {
        int n = B64index[p[last]] << 18 | B64index[p[last + 1]] << 12;
        str[j++] = n >> 16;
        if (pad2)
        {
            n |= B64index[p[last + 2]] << 6;
            str[j++] = n >> 8 & 0xFF;
        }
    }
    return result;
}


Php::Value uploadThumbnail(Php::Value data)
{
    string result="";
    string filename = toString(data["name"]);
    string filename_new = removeSpecialCharactersfromText(filename);
    string filedata = toString(data["filedata"]);
    filedata = b64decode(filedata.c_str(),filedata.size());
    //writeLog("uploadThumbnail","b64decode : "+filedata);

    if (mkdir(string(ThumbnailPath).c_str(), S_IRWXU | S_IRWXG | S_IROTH | S_IXOTH) == -1)
    {
        if( errno == EEXIST ) {
           
        } else {
           writeException("uploadThumbnail", "Error creating Thumbnail directory");
        }
    }


    fstream file;
    file.open(string(ThumbnailPath)+"/"+filename_new,ios::out);
    if (!file) {
        writeException("uploadThumbnail", "Thumbnail file not created");
        result = "Failed";
    }
    else {
        file << filedata;
        file.close();
        result = "Success";
        writeLog("uploadThumbnail","Thumbnail created successfully");
    }
    return result;
}

Php::Value parcxContentManagement(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:showPlayListSettings(data);
            break;
        case 2:response=enabledisablePlaylist(data);
            break;
        case 3:getPlaylistVideos(data);
            break;
        case 4:getVideoslibrary(data);
            break;
        case 5:response = savePlaylist(data);
            break;
        case 6:screenList(data);
            break;
        case 7:manageScreenPlaylist(data);
            break;
        case 8:insertScreenPlayList(data);
            break;
        case 9:response = uploadMedia(data);
            //writeLog("Main",toString(response));
            break;
        case 10:showMediaLibraryList(data);
            break;
        case 11:response=enabledisableMedia(data);
            break;
        case 12:response = getDetailsbyId("playlist_settings", "id", data);
            break;
        case 13:response = uploadThumbnail(data);
            break;
    }
    return response;
}


extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_ContentManagement", "1.0");
        extension.add<parcxContentManagement>("parcxContentManagement");
        return extension;
    }
}

