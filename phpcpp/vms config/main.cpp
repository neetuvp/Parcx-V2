//make -B
//sudo make install
//service apache2 restart
//Edit   sudo nano /etc/sudoers to allow sudo access to www-data  Add at the end:-       www-data ALL=(ALL)NOPASSWD:ALL

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
#include <mysql.h>
#include <cppconn/driver.h>
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




void writeLog(string function,string msg)
{
    general.writeLog("WebApplication/ApplicationLogs/PX-VMSConfiguration-"+general.currentDateTime("%Y-%m-%d"),function,msg);
}//end of .writeLog


void writeException(string function,string msg)
{
    general.writeLog("WebApplication/ExceptionLogs/PX-VMSConfiguration-"+general.currentDateTime("%Y-%m-%d"),function,msg);
}//end of .writeException

int getProcIdByName(string procName)
{
    int pid = -1;

    // Open the /proc directory
    DIR *dp = opendir("/proc");
    if (dp != NULL)
    {
        // Enumerate all entries in directory until process found
        struct dirent *dirp;
        while (pid < 0 && (dirp = readdir(dp)))
        {
            // Skip non-numeric entries
            int id = atoi(dirp->d_name);
            if (id > 0)
            {
                // Read contents of virtual /proc/{pid}/cmdline file
                string cmdPath = string("/proc/") + dirp->d_name + "/cmdline";
                ifstream cmdFile(cmdPath.c_str());
                string cmdLine;
                getline(cmdFile, cmdLine);
                if (!cmdLine.empty())
                {
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
void GetVMSSettings(Php::Parameters &params)
{
    try
    {
        int n=0;
        string id = "0";
        conn = general.mysqlConnect(DATABASE);
        stmt = conn->createStatement();
        res = stmt->executeQuery("Select * from vms_communication_config where status =1");
        if(res->rowsCount()>0)			
		{	
            //Php::out<< "<input type='hidden' name = 'device_type' id = 'device_type' value='"+to_string(device_type)+"'>"<<std::endl;
            Php::out<<"<div class='card card-primary mb-4 col-md-12 p-0' id='active_daemon' data-status='active_daemon'>"<<std::endl;
			Php::out<<"<div class='card-header'>"<< std::endl;	
            Php::out<<"<div class='d-flex justify-content-between align-items-center'>"<<std::endl;
            Php::out<<"<div class='col'>No</div>"<< std::endl;	
            Php::out<<"<div class='col-2'>VMS Name</div>"<< std::endl;
            Php::out<<"<div class='col-2'>Port</div>"<< std::endl;
            Php::out<<"<div class='col-2'>Last Communication</div>"<< std::endl;
            //Php::out<<"<div class='col-2'>English Line 2</div>"<< std::endl;
            //Php::out<<"<div class='col-2'>English Line 3</div>"<< std::endl;
            Php::out<<"<div class='col-2'></div>"<< std::endl;
            Php::out<<"<div class='col-2'></div></div></div>" << std::endl;	
			n=1;
			
			while (res->next())
			{		
                id = res->getString("id");
				Php::out<<"<div class='table-view'>"<< std::endl;
                Php::out<< "<div class='card-text'>"<< std::endl;
                Php::out<< "<div class='d-flex justify-content-between align-items-center'>"<< std::endl;
                Php::out<< "<div class='col'>"+to_string(n)+"</div>"<< std::endl;
                Php::out<< "<div class='col-2' >"+res->getString("vms_name")+"</div>"<< std::endl;
                Php::out<< "<div class='col-2' >"+res->getString("serial_port")+"</div>"<< std::endl;
                Php::out<< "<div class='col-2' >"+res->getString("last_communication_datetime")+"</div>"<< std::endl;
                Php::out<< "<div class='col-2' ></div>"<<std::endl;
                Php::out<< "<div class='col-2'>"<< std::endl;
                Php::out<< "<input class='btn btn-success btn-block btn-edit-vms' id ='editbtn"+id+"' type='submit'  value='Edit' onclick=EditVMS('"+res->getString("id")+"') />"<< std::endl;
                Php::out<< "</div>"<< std::endl;
                Php::out<< "</div>"<< std::endl;
                Php::out<< "</div>"<< std::endl;
                Php::out<< "</div>"<< std::endl;
                n++;		
			}
            Php::out <<" </div>"<<std::endl; 
		}
		
		delete res;
		delete stmt;					
		delete conn;
    }
    catch(const std::exception& e)
    {
        writeException("GetVMSSettings",e.what());
    }
    
   
}


Php::Value EditVMSSettings(Php::Parameters &params)
{
    Php::Value rows;
    Php::Value data = params[0];
    int id;
    try
    {
        MYSQL *conn;
        MYSQL_RES *res;
        MYSQL_ROW row;
        MYSQL_FIELD *field;
        id = data["id"];
        conn = mysql_init (NULL);	  
        if(!conn)
        {
            Php::out<<"Mysql Init Failed"<<endl;
            writeLog("EditVMSSettings","Mysql Init Failed");
            writeException("EditVMSSettings","Mysql Init Failed");
        }  
        
        conn = mysql_real_connect(conn,DBServer,DBUsername,DBPassword,DATABASE,0,NULL,0); 
        if(conn)
        {
        // Php::out<<"Connection Succeeded"<<endl;
        }
        else{
            Php::out<<"Connection Failed"<<endl;
        }
        string sql = "SELECT * FROM vms_communication_config where id = "+to_string(id);
        //Php::out<<"SQL:"<<sql<<endl;
        mysql_query(conn,(sql.c_str()));

    
    
        res = mysql_store_result(conn);
        int num_fields = mysql_num_fields(res);
        Php::Array array;

        int i = 0;

        while((field = mysql_fetch_field(res))){
            array[i] = field->name;
            i++;
        }

        int x = 0;
        while ((row = mysql_fetch_row(res)))
        {

            for (int z=0;z<num_fields;z++) 
            {
                std::string fieldname = array[z];
                array[fieldname] = row[z];
                rows[x]=array;
            }

            x++;
        }
        if(res != NULL)
        mysql_free_result(res);
        mysql_close (conn);
    }
    catch (exception &e)
    {
        //write to log file
        writeLog("EditVMSSettings", e.what());
        writeException("EditVMSSettings", e.what());
    }


    return rows;
}


Php::Value PostVMSSettings(Php::Parameters &params)
{
	sql::Connection *con;
    int result=0;
    int sign_type,vms_width,vms_height,left_font_size,left_counter_color,left_character_count,center_font_size,center_counter_color,center_character_count,right_font_size,right_counter_color,right_character_count,brightness_time1,brightness_value1,brightness_time2,brightness_value2,brightness_time3,brightness_value3,brightness_time4,brightness_value4,brightness_time5,brightness_value5;
    //std::string vms_name,port_no,left_icon_text,left_counter_id,left_icon,center_icon_text,center_counter_id,center_icon,right_icon_text,right_counter_id,right_icon;
    Php::Value data= params[0];
    string id = data["id"];
    string vms_name  = data["vms_name"];
    string port_no = data["port_no"];
    sign_type = data["sign_type"];
    vms_width = data["vms_width"];
    vms_height = data["vms_height"];
    left_font_size = data["left_font_size"];
    string left_icon_text = data["left_icon_text"];
    left_counter_color = data["left_counter_color"] ;
    left_character_count = data["left_character_count"];
    string left_counter_id = data["left_counter_id"] ;
    string left_icon = data["left_icon"];
    center_font_size = data["center_font_size"];
    string center_icon_text = data["center_icon_text"];
    center_counter_color = data["center_counter_color"] ;
    center_character_count = data["center_character_count"];
    string center_counter_id = data["center_counter_id"];
    string center_icon = data["center_icon"];
    right_font_size = data["right_font_size"];
    string right_icon_text = data["right_icon_text"];
    right_counter_color = data["right_counter_color"];
    right_character_count = data["right_character_count"];
    string right_counter_id = data["right_counter_id"] ;
    string right_icon = data["right_icon"];
    brightness_time1 = data["brightness_time1"];
    brightness_value1 = data["brightness_value1"] ;
    brightness_time2 = data["brightness_time2"];
    brightness_value2 = data["brightness_value2"] ;
    brightness_time3 = data["brightness_time3"] ;
    brightness_value3 = data["brightness_value3"] ;
    brightness_time4 = data["brightness_time4"];
    brightness_value4 = data["brightness_value4"] ;
    brightness_time5 = data["brightness_time5"] ;
    brightness_value5 = data["brightness_value5"] ;
    Php::out<<"here:"<<brightness_value5<<endl;
	try{
    con = general.mysqlConnect(DATABASE);

    if(id == "")
    {
        prep = con->prepareStatement("INSERT INTO vms_communication_config (serial_port, vms_name, vms_width, vms_height, left_icon, left_icon_text, left_color, left_font_size, left_character_count, left_counter, center_icon, center_icon_text, center_color, center_font_size, center_character_count, center_counter, right_icon, right_icon_text, right_character_count, right_counter, right_color, right_font_size, brightness_time1, brightness_time2, brightness_time3, brightness_time4, brightness_time5, brightness_value1, brightness_value2, brightness_value3, brightness_value4, brightness_value5, sign_type,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
    }
    else
    {
        prep = con->prepareStatement("Update vms_communication_config set serial_port=?, vms_name=?, vms_width=?, vms_height=?, left_icon=?, left_icon_text=?, left_color=?, left_font_size=?, left_character_count=?, left_counter=?, center_icon=?, center_icon_text=?, center_color=?, center_font_size=?, center_character_count=?, center_counter=?, right_icon=?, right_icon_text=?, right_character_count=?, right_counter=?, right_color=?, right_font_size=?, brightness_time1=?, brightness_time2=?, brightness_time3=?, brightness_time4=?, brightness_time5=?, brightness_value1=?, brightness_value2=?, brightness_value3=?, brightness_value4=?, brightness_value5=?, sign_type =? where id = ?");
        prep->setInt(34,stoi(id));
    }

    prep->setString(1,port_no);
    prep->setString(2,vms_name);
    prep->setInt(3,vms_width);
    prep->setInt(4,vms_height);
    prep->setString(5,left_icon);
    prep->setString(6,left_icon_text);
    prep->setInt(7,left_counter_color);
    prep->setInt(8,left_font_size);
    prep->setInt(9,left_character_count);
    prep->setString(10,left_counter_id);
    prep->setString(11,center_icon);
    prep->setString(12,center_icon_text);
    prep->setInt(13,center_counter_color);
    prep->setInt(14,center_font_size);
    prep->setInt(15,center_character_count);
    prep->setString(16,center_counter_id);
    prep->setString(17,right_icon);
    prep->setString(18,right_icon_text);
    prep->setInt(19,right_character_count);
    prep->setString(20,right_counter_id);
    prep->setInt(21,right_counter_color);
    prep->setInt(22,right_font_size);
    prep->setInt(23,brightness_time1);
    prep->setInt(24,brightness_time2);
    prep->setInt(25,brightness_time3);
    prep->setInt(26,brightness_time4);
    prep->setInt(27,brightness_time5);
    prep->setInt(28,brightness_value1);
    prep->setInt(29,brightness_value2);
    prep->setInt(30,brightness_value3);
    prep->setInt(31,brightness_value4);
	prep->setInt(32,brightness_value5);
	prep->setInt(33,sign_type);
    

    if(prep->executeUpdate())
    {
        result = 1;
    }
	//Log("test","result:"+to_string(result));
    prep->close();
    con->close();
	}
    catch (sql::SQLException &e)
    {
        //write to log file
        Php::out<<"Exception"<<e.what()<<endl;
        writeLog("PostVMSSettings", e.what());
        writeException("PostVMSSettings", e.what());
    }
    return result;

}


extern "C" {
    
    /**
     *  Function that is called by PHP right after the PHP process
     *  has started, and that returns an address of an internal PHP
     *  strucure with all the details and features of your extension
     *
     *  @return void*   a pointer to an address that is understood by PHP
     */
    PHPCPP_EXPORT void *get_module() 
    {
        // static(!) Php::Extension object that should stay in memory
        // for the entire duration of the process (that's why it's static)
        static Php::Extension extension("PX_VMSConfiguration", "1.0");
        extension.add<GetVMSSettings>("GetVMSSettings");
        extension.add<EditVMSSettings>("EditVMSSettings");
        extension.add<PostVMSSettings>("PostVMSSettings");
        //extension.add<StartDaemon>("StartDaemon");
        //extension.add<RestartDaemon>("RestartDaemon");

               // @todo    add your own functions, classes, namespaces to the extension
        
        // return the extension
        return extension;
    }
}
