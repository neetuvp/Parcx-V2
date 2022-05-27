#include "PX_GeneralOperations.h"
#include <fstream>
#include <ctime>
#include "mysql_connection.h"
#include <cppconn/driver.h>
#include <cppconn/exception.h>

#define Server "DBServer"
#define Username "parcxservice"
#define Password "1fromParcx!19514"
#define dateTimeFormat "%Y-%m-%d %H:%M:%S"
#define dateFormat "%Y-%m-%d"

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
    ofstream fp;
    fp.open(logpath+path+".log", ios::app);
    fp<<currentDateTime(dateTimeFormat)<<" "<<function<<":: "<<txt<<endl;
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
        writeLog("DBException/DaemonStatus-"+currentDateTime(dateFormat),"mysqlConnect",e.what());  				      
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
        day = n / (24 *3600); 
        n = n % (24 *3600); 
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

