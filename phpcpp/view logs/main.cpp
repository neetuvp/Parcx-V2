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
#include <dirent.h>
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


sql::Connection* mySQLConnect()
    {
    driver = get_driver_instance();
    conn = driver->connect(DBServer,DBUsername,DBPassword);
    conn->setSchema(DATABASE);
    return conn;
    }
string currentDateTime()
	{
	time_t now=time(NULL);	
	std::tm* timeinfo;
	char buffer[80];
	std::time(&now);
	timeinfo=std::localtime(&now);
	std::strftime(buffer,80,"%d-%m-%Y %H:%M:%S",timeinfo);		
	string datetime(buffer);
	return datetime;
	}

void writeException(string function,string txt)
    {
    Php::out<<function<<" : "<<txt<<endl;    
    ofstream fp;
    fp.open("/opt/parcx/Logs/WebApplication/ApplicationLogs/PX-ViewLogs.log", ios::app);
    fp<<currentDateTime()<<" "<<function<<":: "<<txt<<endl;
    fp.close();
    }//end of .writeException


void writeLog(string function,string txt)
    {
    ofstream fp;
    fp.open("/opt/parcx/Logs/WebApplication/ExceptionLogs/PX-ViewLogs.log", ios::app);
    fp<<currentDateTime()<<" "<<function<<":: "<<txt<<endl;
    fp.close();
    }//end of .writeException



Php::Value getFileContent(Php::Value data)
    {   
    string result = "Could not open file";
    try
        {
		string main_folder = data["main_folder"];
	    string sub_folder = data["sub_folder"];
        string filename = data["file_name"];
        		
		ifstream in("/opt/parcx/Logs/"+main_folder+"/"+sub_folder+"/"+filename);
        if(in)
            {
            in.seekg(0,std::ios::end);
            size_t len = in.tellg();
            in.seekg(0);
            string contents(len+1,'\0');
            in.read(&contents[0],len);
            result = contents;
            }        
        }
    catch(const std::exception& e)
        {
        writeException("getFileContent",e.what());
        }
    return result;
    }

void getLogList(Php::Value data)
    {
    string main_folder = data["main_folder"];
	string sub_folder = data["sub_folder"];
    try
        {        		
		std::string path = "/opt/parcx/Logs/"+main_folder+"/"+sub_folder;
        DIR *dir;
        struct dirent *ent;
        string url="";
        if ((dir = opendir (path.c_str())) != NULL) 
            {  
            Php::out<<"<table id='RecordsTable' class='table table-blue table-bordered table-striped'>"<<endl;              
            Php::out << "<thead>" << std::endl;
			Php::out<<"<tr>"<<endl;
            Php::out<<"<th>No</th>"<<endl;
            Php::out<<"<th>File</th>"<<endl;                        
            Php::out<<"<th></th>"<<endl;
            Php::out<<"<th></th>"<<endl;		           
			Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;			
            int n=1;            
            while ((ent = readdir (dir)) != NULL) 
                {                
				if(ent->d_type!=DT_DIR)
				    {
                    Php::out<<"<tr>"<<endl;                       
                    Php::out<<"<td>"<<n<<"</td>"<<endl;
                    Php::out<<"<td>"<<ent->d_name<<"</td>"<<endl;                                                                   
                    Php::out << "<td><button type='button' class='col btn btn-info btn-view'>View</button></td>"<< std::endl;                    
                    Php::out << "<td><button type='button' class='col btn btn-info btn-download'>Download</button></td>"<< std::endl;                    
                    Php::out<<"</tr>"<<endl;						
					n++;
				    }                
                }
            Php::out<<"</table>"<<endl;
           
            closedir (dir);
            } 
        else 
            {        
            writeLog("getLogList","could not open directory");
            }
        }
    catch(const std::exception& e)
        {
        writeException("getLogList",e.what());
        }       
    }

void getLogFolders(Php::Value data)
    {
    string main_folder = data["main_folder"];
    string button_class="btn-main";
    if(main_folder!="")
        button_class="btn-sub";
    try
        {        		
		std::string path = "/opt/parcx/Logs/"+main_folder;
        DIR *dir;
        struct dirent *ent;  
        int i=0;      
        if ((dir = opendir (path.c_str())) != NULL) {			                      
            while ((ent = readdir (dir)) != NULL) 
                {               
				if(ent->d_type==DT_DIR)
				    {
                    if((strcmp(ent->d_name, ".") != 0) && (strcmp(ent->d_name, "..") != 0))    
                        {
                        if(i==0)    
                            Php::out<< "<div class='col-auto'><input class='btn btn-success "+button_class+"' id ='"<<ent->d_name<<"' type='submit'  value='"<<ent->d_name<<"' ></div>"<<endl;
                        else
                            Php::out<< "<div class='col-auto'><input class='btn btn-info "+button_class+"' id ='"<<ent->d_name<<"' type='submit'  value='"<<ent->d_name<<"' ></div>"<<endl;
                        i++;
                        }                    
				    }                
                }
           
            closedir (dir);
            } 
        else 
            {        
            writeLog("getLogFolders","could not open directory");
            }
        }
    catch(const std::exception& e)
        {
        writeException("getLogFolders",e.what());
        }       
    }


Php::Value parcxViewLogs(Php::Parameters &params)
    {
    Php::Value response;    
    try
        {
        Php::Value data=params[0];      
        int task=data["task"];   
                
        switch (task)
            {
            case 1: 
                    getLogFolders(data);
                    break;    
            case 2: 
                    getLogList(data);
                    break; 
            case 3:                    
                    response=getFileContent(data);  
                    break;            
            }
        }
    catch(const std::exception& e)
        {
        writeException("parcxViewLogs",e.what());
        }
    return response;
        
    }



extern "C" 
    {        
    PHPCPP_EXPORT void *get_module() 
        {        
        static Php::Extension extension("PX_ViewLogs", "1.0");
        extension.add<parcxViewLogs>("parcxViewLogs");        
        return extension;
        }
    }
