#include<iostream>
#include<stdlib.h>
#include <fstream>
#include <time.h>
#include <ctime>
#include <string.h>
#include <phpcpp.h>
//#include<jsoncpp/json/json.h>
using namespace std;
class Validation{
    int myx;
    public:
    Validation()
    {
        myx= 0;
    }
    void setx(int newx);
    int getx();
    Php::Value DataValidation(string data,int minlen,int maxlen,int datatype,int mandatoryflag);
    //Json::Value checkValidation(Json::Value data,Json::Value rules);
    //Json::Value checkSpecialCharacters(Json::Value data,Json::Value rules);
    private:
    bool checkDigit(string data);
    bool checkAlpha(string data);
    bool checkAlphaNumeric(string data);
    bool checkDate(string data);
    bool checkEmail(string data);
    bool checkDateTime(string data);
    bool checkName(string data);
    bool checkPhone(string data);
    bool checkIP(string data);
    void writelog(string function,string txt);
    string CurrentDateTimeValidation();
    string CurrentDateValidation();
    
};