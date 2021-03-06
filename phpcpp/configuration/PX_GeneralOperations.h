#ifndef PX_GENERALOPERATIONS_H
#define PX_GENERALOPERATIONS_H

#include "mysql_connection.h"
#include <string.h>

using namespace std;
class GeneralOperations
{
    public:    
    string currentDateTime(string format);       
    void writeLog(string path,string function,string text);
    sql::Connection* mysqlConnect(string database);  
    int* differenceDateTime(string date1,string date2,string format);
};

#endif
