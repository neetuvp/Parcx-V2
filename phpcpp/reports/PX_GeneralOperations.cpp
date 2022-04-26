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
        writeLog("DBException/Reports-"+currentDateTime(dateFormat),"mysqlConnect",e.what());  				      
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
    if(difftime(t1,t2)>0)    
        {
        day = difftime(t1,t2) / (24 * 3600);         
        n=difftime(t1,t2)-(day*24*3600);
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
        string reportlabels="choose_datetime_range,view_report,export,export_to_excel,export_to_pdf,logout,search,entries_label,info_label,previous,next,";
        switch(page)
            {
            case 1://"watchdog_device_alarms"
                    label=reportlabels+"watchdog_device_alarms,select_severity,low,high,medium,all_devices,select_devices";
                    break;
            case 2 ://payment_transactions
                label=reportlabels+"all_validation,select_validation,payment_transactions,detailed_payment,receipt_details,close,print,tax_invoice,cash,credit_card,parking_fee,lost_ticket,discount,grace_period,product_sales,all_devices,select_devices,all_carparks,select_carparks,all_category,select_category,all_payment,select_payment,all_discount,select_discount,duplicate";
                    break;
            case 3 ://revenue_summary
                label = reportlabels+"earnings_by_device,vat_amount,reservation_amount,earnings,total_revenue,parking_fee,discount_amount,product_sale_amount,lost_fee,sunday,monday,tuesday,wednesday,thursday,friday,saturday,revenue_report,all_carparks,select_carparks,select_days,all_days";
                    break;
            case 4://shift_report
                label = reportlabels + "detailed_shift,shift_report,all_carparks,select_carparks,all_devices,select_devices,all_operators,select_operators";
                    break;
            case 5://vat_report
                label = reportlabels + "vat_report,cash,credit_card,detailed_payment,receipt_details,close,print,tax_invoice,all_devices,parking_fee,lost_ticket,discount,grace_period,product_sales,select_devices,all_carparks,select_carparks,all_category,select_category,all_payment,select_payment,all_discount,select_discount";
                    break;
            case 6://creditcard_payment_transactions
                label = reportlabels + "creditcard_transactions,all_carparks,select_carparks,all_devices,select_devices,";
                    break;
            case 7://payment_transaction_exceptions
                label = reportlabels + "payment_transaction_exception,all_carparks,select_carparks,all_devices,select_devices,select_exception,blacklisted,already_paid_chipcoin,invalid_carpark,invalid_chipcoin,cancelled_transaction";
                    break;
            case 8://cash_levels
                label = reportlabels + "cash_levels,all_devices,select_devices";
                    break;
            case 9://apm_refill_report
                label = reportlabels + "apm_refill_report,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            case 10://apm_payout_report
                label = reportlabels + "apm_payout_report,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            case 11://parking_movements
                label = reportlabels + "parking_movement,plate_ticket,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            case 12://blacklist_report
                label = reportlabels + "blacklist_report,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            case 13://traffic_report
                label = reportlabels + "traffic_report,entries,exits,all_carparks,select_carparks,all_days,select_days,sunday,monday,tuesday,wednesday,thursday,friday,saturday";
                    break;
            case 14://ticket_details
                label = reportlabels + "ticket_details,ticket_id,view_details,plate_number";
                    break;
            case 15://visitor_frequency_realtime
                label = reportlabels + "visitor_frequency_realtime,short_term,contract,select,ANPR,tag,search,ticket_id,view_details";
                    break;
            case 16://visitor_frequency
                label = reportlabels + "visitor_frequency,all_category,short_term,contract";
                    break;
            case 17://access_reports
                label = reportlabels + "select_status,access_movements,plate_ticket,all_carparks,select_carparks,all_devices,select_devices,access_allowed,access_denied,tag_unknown,plate_ticket_tag,add_to_whitelist,customer_name,country,select_country,emirate,uae,bahrain,kuwait,oman,qatar,saudi,abudhabi,dubai,ajman,sharjah,rasalkhaimah,fujairah,ummalquwwain,qrcode,activation_date,expiry_date,facility_name,personal_message_1,personal_message_2,anti_passback,submit,tag,prefix,carpark";
                    break;
            case 18://manual_movements
                label = reportlabels + "manual_movement,barrier_open_from_server,barrier_close,free_passage_mode,lane_closed_mode,standard_operation_mode,all_operation_methods,manual_open_from_cashier,remote_pushbutton_open,all_carparks,select_carparks,all_devices,select_devices,access_allowed,access_denied,tag_unknown,plate_ticket_tag,add_to_whitelist,customer_name,country,select_country";
                    break;
            case 19://open_transactions
                label = reportlabels + "open_transactions,anpr_image,sel_duration,less_1_day,greater_1_day,greater_2_day,greater_3_day,greater_4_day,greater_5_day,greater_6_day,greater_7_day,plate_ticket,all_operation_methods,manual_open_from_cashier,remote_pushbutton_open,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            case 20://plates_captured
                label = reportlabels + "confidence_rate,plates_captured,plate_number,plate_area,plate_type,plate_type,police,transport,commercial,private,learning,cddiplomat,ccdiplomat,protocol,government,export,taxi,testing,transfer,truck,bus,publictransport,privatetransport,all_carparks,select_carparks,all_devices,select_devices,uae,bahrain,kuwait,oman,qatar,saudi,select_country";
                    break;
            case 21://validation_report
                label = reportlabels + "validation_report,all_carparks,select_carparks,all_devices,select_devices,select_validator,all_validators,select_product,all_products,plate_ticket";
                    break;
            case 22://parking_duration
                label = reportlabels + "parking_duration,shortterm_parkers,contract_parkers,all_devices,select_devices,all_carparks,all_days,select_days,select_carparks,sunday,monday,tuesday,wednesday,thursday,friday,saturday";
                    break;
            case 23://watchdog_network_logs
                label = reportlabels + "watchdog_network_logs,all_carparks,select_carparks,all_devices,select_devices";
                    break;
            
            case 24://anpr_entry_plate_correction
                label = reportlabels + "anpr_plate_correction,all_devices,select_devices";
                    break;
            case 25://live_parking
                label = reportlabels + "live_parking";
                    break;
            case 26://manual_operations page
                label=reportlabels+"device_name,device_ip,manual_operation,reason,ok,cancel,standard_operation_mode_from_server,enter_valid_reason,latest_manual_operations";
                    break;
            case 27 ://discount summary
                label=reportlabels+"discount_summary,all_devices,select_devices,all_carparks,select_carparks,all_discount,select_discount";
                    break;
            case 28 ://user activity
                label=reportlabels+"user_activity,all_operators,select_operators";
                    break;

}
        }
    return  getLabels(lang,label);
    }