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
    Php::Value DataValidation(string data,int minlen,int maxlen,int datatype,int mandatoryflag);
   
};