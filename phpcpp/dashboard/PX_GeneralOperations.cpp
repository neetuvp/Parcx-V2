#include "PX_GeneralOperations.h"
#include <fstream>
#include <ctime>
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>
#include <sstream>
#define Server "DBServer"
#define Username "parcxservice"
#define Password "1fromParcx!19514"
#define timeFormat "%H:%M:%S"
#define dateFormat "%Y-%m-%d"
#define ServerDB "parcx_server"
using namespace std;

string logpath="/opt/parcx/Logs/";

string GeneralOperations::currentDateTime(string format)
{
    time_t now=time(NULL);	
    std::tm* timeinfo;
    char buffer[80];
    std::time(&now);
    timeinfo=std::localtime(&now);
    std::strftime(buffer,80,format.c_str(),timeinfo);		
    string datetime(buffer);
    return datetime;
}


void GeneralOperations::writeLog(string path,string function,string txt)
{
    cout<<function<<" : "<<txt<<endl;
    ofstream fp;
    fp.open(logpath+path+".log", ios::app);
    fp<<currentDateTime(timeFormat)<<" "<<function<<":: "<<txt<<endl;
    fp.close();
}//end of .writeLog

sql::Connection* GeneralOperations::mysqlConnect(string database)
{
    sql::Connection *con=NULL;
    try
	{
        sql::Driver *driver;				
        driver = get_driver_instance();				
        con = driver->connect(Server,Username,Password);	
        con->setSchema(database);	       
	}
    catch(exception &e)
	{
        writeLog("DBException/Dashboard-"+currentDateTime(dateFormat),"mysqlConnect",e.what());  				      
	}
    return con;
}//end of  mysqlConnect

int* GeneralOperations::differenceDateTime(string date1,string date2,string format)
{    
	static int arr[5];
	int n,totalseconds;  
    int day=0,hour=0,minutes=0,seconds=0;  
	struct tm tm;
	strptime(date1.c_str(),format.c_str(),&tm);
	time_t t1 =mktime(&tm);
	strptime(date2.c_str(),format.c_str(),&tm);
	time_t t2 =mktime(&tm);
	n =difftime(t1,t2);
    totalseconds = n;   
    if(n>0)    
        {
        day = n / (24 * 3600); 
        n = n % (24 * 3600); 	
        hour = n / 3600; 
        n %= 3600; 	
        minutes = n / 60 ; 
        n %= 60; 	
        seconds = n;
        }
	arr[0] = totalseconds ;
	arr[1] = day;
	arr[2] = hour;
	arr[3] = minutes;
	arr[4] = seconds;	
    return arr;
} 
Php::Value GeneralOperations::getLabels(string lang,string label)
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
        sql::ResultSet *res; 
        GeneralOperations g;
        con = g.mysqlConnect(ServerDB);
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


Php::Value GeneralOperations::getApplicationLabels(Php::Value data)
    {
    string lang=data["language"];  
    int page=data["page"];                
    string label=data["label"];    
    if(page>0)
        {
        string reportlabels="logout,export,export_to_excel,export_to_pdf,";
        switch(page)
            {
            case 1://device_status_facility,device_status_carpark
                    label=reportlabels+"device_status,alarms,";
                    break;
            case 2 ://device_status_device
             		label=reportlabels+"view_all,payment_machines,entry_exit_columns,controllers,camera,valet,device_status,notifications,cash_levels,manual_operation,reason,ok,cancel,device_status,alarms,online,offline,device_name,device_ip,open_barrier,close_barrier,restart,lane_closed_mode,free_passage_mode,standard_operation_mode,standard_operation_mode_from_server,standard_operation_mode_reason,barrier_open_from_server,free_passage_mode_from_server,free_passage_mode_reason,lane_closed_mode_from_server,lane_closed_mode_reason,restart_machine_from_server,barrier_close_from_server,close_barrier_reason,open_barrier_reason,enter_valid_reason";
                    break;
            case 3 ://finance_facility,finance_carpark,finance_device
                label = reportlabels+"finance,reservation_amount,parking_fee,lost_fee,product_sale_amount,view_all,manual_cashiers,pay_on_foot_machines,handheld_pos,revenue_7_days,revenue_sources";
                    break;
            case 4://occupancy_facility,occupancy_carpark,occupancy_device
                label = reportlabels + "occupancy,hourly_occupancy,this_day_last_week,last_week_average,last_week_average,all_time_average,maximum,minimum";
                    break;
            case 5://anpr_review
                label = reportlabels + "plate_correction_required,plate_mismatch,plates_corrected,update_plate_number,current_plate_number,plate_number,enter_corrected_plate_number,entry_plate_number,exit_plate_number,anpr_review,save_changes,cancel,allow,plate_review_text,click_update_text,ok,plates_require_attention,manual_plate_correction,plates_not_matching";
                    break;
            
            

}
        }
    return  getLabels(lang,label);
    }
