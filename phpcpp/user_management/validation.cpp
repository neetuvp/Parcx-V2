#include "validation.h"
#include<iostream>
#include<stdlib.h>
#include <fstream>
#include <time.h>
#include <ctime>
#include <string.h>
#include <regex>
#include<iomanip>
#include <phpcpp.h>
#include <algorithm>
#include "PX_GeneralOperations.h"
using namespace std;
GeneralOperations gen;

#define MANDATORY "Mandatory"
#define MAXLENGTH "Maximum Length Exceeded"
#define MINLENGTH "Minimum Length Required"
#define CONSTRAINT "Invalid Character"

void Validation::setx(int newx)
{
   myx = newx;
}

int Validation::getx()
{
    return myx;
}
string Validation::CurrentDateTimeValidation()
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
string Validation::CurrentDateValidation()
{
    time_t now=time(NULL);	
    std::tm* timeinfo;
    char buffer[80];
    std::time(&now);
    timeinfo=std::localtime(&now);
    std::strftime(buffer,80,"%d-%m-%Y",timeinfo);		
    string datetime(buffer);
    return datetime;
}

void Validation::writelog(string function, string txt) {    
    string path = "WebApplication/ExceptionLogs/PX_UserManagement_Validation_" + gen.currentDateTime("%Y-%m-%d");
    gen.writeLog(path, function, txt);
}

bool Validation::checkDigit(string data)
{
    int n = data.length();
    for(int i=0;i<n;i++)
    {
        if(!(isdigit((int)data[i])) && data!="")
        {
            return false;
        }
    }
    return true;
}
bool Validation::checkAlpha(string data)
{
    int n = data.length();
    for(int i=0;i<n;i++)
    {
        if(!(isalpha((int)data[i])) && !(isspace((int)data[i])) && data!="")
        {
            return false;
        }
    }
    return true;
}


bool Validation::checkAlphaNumeric(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
       //Allowed chars - Uppercase and Lowercase alphabets,numbers,space,hyphen,underscore
        if ((str[i]>=48 && str[i]<=57)||(str[i]>=65 && str[i]<=90)||(str[i]>=97 && str[i]<=122)||str[i]==32||str[i]==45||str[i]==95||str[i]==46)
        {
            continue;
        }
        else
        {
            return false;
        }
    }
    return true;
}

bool Validation::checkDateTime(string data)
{
    struct tm tm;
    if(!strptime(data.c_str(),"%Y-%m-%d %H:%M:%S",&tm) && data!="")
    {
        return false;   //||strptime(data.c_str(),"%FT%TZ",&tm)
    }
    else
    {
        return true;;
    }
}

bool Validation::checkDate(string data)
{
    struct tm tm;
    if(!strptime(data.c_str(),"%Y-%m-%d",&tm) && data!="")
    {
        return false;   //||strptime(data.c_str(),"%FT%TZ",&tm)
    }
    else
    {
        return true;;
    }
}

bool Validation::checkEmail(string data)
{
    if(data != "")
    {
        auto b=data.begin(), e=data.end();

        if ((b=std::find(b, e, '@')) != e && std::find(b, e, '.') != e )
        {
                return true;
        }
        else
        {
                return false;
        }
    }
    else{
        return true;//Not Mandatory
    }
	
}

bool Validation::checkName(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
       //Allowed chars - Uppercase and Lowercase alphabets,space,dot
        if ((str[i]>=65 && str[i]<=90)||(str[i]>=97 && str[i]<=122)||str[i]==32||str[i]==46)
        {
            continue;
        }
        else
        {
            return false;
        }
    }
    return true;
}

bool Validation::checkPhone(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
       //Allowed chars - Numbers and +
        if ((str[i]>=48 && str[i]<=57)||str[i]==32||str[i]==43)
        {
            continue;
        }
        else
        {
            return false;
        }
    }
    return true;
}

Php::Value Validation::DataValidation(string data,int minlen,int maxlen,int datatype,int mandatoryflag) //Datatype: 1 - Integer, 2 - Alphabets, 3 - AlphaNumeric, 4 - SpecialCharacter,5-email,6-Names
{
    Php::Value response;
    response["result"]=true;
    response["data"] = data;
    if(mandatoryflag==1 && data=="")  //Check if data is null
    {
	writelog("DataValidation","Missing Mandatory field:"+data);
        response["result"]=false;
        response["reason"] = MANDATORY;
    }
    if((datatype==3||datatype==2||datatype==6||datatype==7) && data.length()>(unsigned)maxlen)  //Check if length of the data is within the assigned limit
    {
	writelog("DataValidation","Data length exceeds allowed limit:"+data);
        response["result"]=false;
        response["reason"] = MAXLENGTH;
    }
    if((datatype==3||datatype==2||datatype==6||datatype==7) && data.length()<(unsigned)minlen && mandatoryflag==1)  //Check if length of the data is within the assigned limit
    {
	writelog("DataValidation","Data length exceeds allowed limit:"+data);
        response["result"]=false;
        response["reason"] = MINLENGTH;
    }
    switch(datatype)                 //Check the datatype validity
    {
        case 1:
            if(!checkDigit(data))
            {
		writelog("DataValidation","Invalid Digit:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 2:
            if(!checkAlpha(data))
            {
		writelog("DataValidation","Invalid Alpha Data:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 3:
            if(!checkAlphaNumeric(data))
            {
                writelog("DataValidation","Invalid AlphaNumeric Data:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 4:
            if(!checkDate(data))
            {
		writelog("DataValidation","Invalid Date:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 5:
            if(!checkEmail(data))
            {
		writelog("DataValidation","Invalid Email:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 6:
            if(!checkName(data))
            {
                writelog("DataValidation","Invalid Name:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 7:
            if(!checkPhone(data))
            {
                writelog("DataValidation","Invalid Phone:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;
        case 8:
            if(!checkDateTime(data))
            {
		writelog("DataValidation","Invalid DateTime:"+data);
                response["result"]=false;
                response["reason"] = CONSTRAINT;
            }
            break;

    }
    return response;
}


