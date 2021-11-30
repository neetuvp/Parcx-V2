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


/*bool Validation::checkAlphaNumeric(string data)
{
    int n = data.length();
    char c[n+1];
    strcpy(c,data.c_str());
    
    for(int i=0;i<n;i++)
    {
        if(!(isalnum((int)data[i])) && !(isspace((int)data[i])) && data!="")
        {
            return false;
        }
    }
    return true;
}*/

/*Json::Value Validation::checkValidation(Json::Value data,Json::Value rules)
{

    Json::FastWriter fw;
    string type;
    int datatype=0,length=0;
    Json::Value response,validation,row;
    int flag = 0;
    string field;
//$%:;>&*$
try{
    for (auto const& member : data.getMemberNames()) {
        length=0;
        datatype=0;
        field = string(member);
        type = rules[field+"_type"].asString();
        if(type=="int"||type=="bigint"||type=="tinyint")
            datatype=1;
        else if(type=="varchar"||type=="text"|| type=="tinytext")
        {
            datatype=3;
            length = rules[field+"_length"].asInt();
        }
        else if(type=="datetime"||type=="timestamp")
            datatype=4;
        response = DataValidation(data[field].asString(),length,datatype,rules[field+"_mandatory"].asInt());
        response["field"]=field;
        if(response["result"]==false)
        {
           flag = 1;
           validation["result"] = "failed";
           row.append(response);
        }
        
    }
    if(flag==1)
    {      
        validation["validation_details"] = row;
        writelog("Validation", "Validation Error:"+fw.write(validation));    
    }
    else
    {
        validation["result"] = "success";
    }
}
catch (const std::exception &e) 
{
    writelog("Validation", e.what());        
}
    return validation;


}

string SetDoublePrecision(double amt,int precision)
{
	std::string amt_string="0";
	std::ostringstream streamObj;
	// Set Fixed -Point Notation
	streamObj << std::fixed;
	
	// Set precision to 2 digits
	streamObj << std::setprecision(precision);
	
	//Add double to stream
	streamObj << amt;
	// Get string from output string stream
	amt_string = streamObj.str();
	std::cout << amt_string << std::endl;
	
	return amt_string;
	
}
string removeSpecialCharactersfromText(string str)
{
    for(int i=0;i<(signed)str.length();i++)
    {
       //Allowed chars - Uppercase and Lowercase alphabets,numbers,curly brackets,space,comma,dot
      if ((str[i]>=48 && str[i]<=57)||
          (str[i]>=65 && str[i]<=90)||
          (str[i]>=97 && str[i]<=122)||
          str[i]==32||str[i]==44||str[i]==46 || str[i]==123 || str[i]==125)
          {
            continue;
          }
      else
      {
        //cout<<"String contains special character.\n";
        //flag=1;
        char c = str[i];
        //str.erase(std::find(str.begin(), str.end(), c));
        //i=i-1;
        std::replace(str.begin(), str.end(), c, ' ');
      }
    }
    return str;
}

string removeSpecialCharactersfromDecimal(string str)
{
    //Allowed chars - numbers,dot
    for(int i=0;i<(signed)str.length();i++)
    {
      if ((str[i]>=48 && str[i]<=57)||str[i]==46)
          {
            continue;
          }
      else
      {
        char c = str[i];
        std::replace(str.begin(), str.end(), c, '\0'); 
      }
    }
    return str;
}


string removeSpecialCharactersfromDateTime(string str)
{
    //Allowed chars - Numbers,colon,space,comma,dot,hyphen
    for(int i=0;i<(signed)str.length();i++)
    {
      if ((str[i]>=48 && str[i]<=57)||str[i]==46||str[i]==58||str[i]==32||str[i]==45)
      {
        continue;
      }
      else
      {
        char c = str[i];
        std::replace(str.begin(), str.end(), c, '\0'); 
      }
    }
    return str;
}

string removeCharsFromString( string &str, char* charsToRemove ) {
   for ( unsigned int i = 0; i < strlen(charsToRemove); ++i ) {
      str.erase( remove(str.begin(), str.end(), charsToRemove[i]), str.end() );
   }
    return str;
}
string removeAlpha(string &str)
{
    str.erase(remove_if(str.begin(), str.end(), [](char c) { return isalpha(c); } ), str.end());
    return str;
}
string removePunct(string &str)
{
    str.erase(remove_if(str.begin(), str.end(), [](char c) { return ispunct(c); } ), str.end());
    return str;
}
Json::Value Validation::checkSpecialCharacters(Json::Value data,Json::Value rules)
{
    string field,fieldvalue,type,new_string;
   // string float_replace = "@~`!@#$%^&*()_=+\\\\';:\"\\/?><,-‘–";
   // string date_replace = "@~`!@#$%^&*()_=+\\\\';\"\\/?><,‘–";
   // string varchar_replace = "@~`!@#$%^&*()_=+\\\\';:\"\\/?><.-–‘";
    try{
        for (auto const& member : data.getMemberNames()) {
            field = string(member);
            type = rules[field+"_type"].asString();
            fieldvalue = data[field].asString();

            if(type=="int"||type=="bigint"||type=="tinyint")
            {
               fieldvalue = removeAlpha(fieldvalue);
               fieldvalue = removePunct(fieldvalue);
               data[field] = stoi(fieldvalue);
            }
            else if(type=="double"||type=="float"||type=="decimal")
            {
                fieldvalue = removeAlpha(fieldvalue);
               // new_string = removeCharsFromString(fieldvalue, const_cast<char*>(float_replace.c_str()));
                new_string = removeSpecialCharactersfromDecimal(fieldvalue);
                data[field] = new_string;
            }
            else if(type=="varchar"||type=="text" || type=="tinytext")
            {
                //new_string = removeCharsFromString(fieldvalue, const_cast<char*>(varchar_replace.c_str()));
                new_string = removeSpecialCharactersfromText(fieldvalue);
                data[field] = new_string;
            }   
            else if(type=="datetime"||type=="date" ||type=="timestamp")
            {
                new_string = removeAlpha(fieldvalue);
                //new_string = removeCharsFromString(new_string, const_cast<char*>(date_replace.c_str()));
                new_string = removeSpecialCharactersfromDateTime(fieldvalue);
                data[field] = new_string;
            }    
        }
    }
    catch (const std::exception &e) 
    {
        writelog("checkSpecialCharacters", e.what());        
    }
    return data;

}*/
