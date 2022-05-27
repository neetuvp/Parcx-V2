#ifndef ANPR_H
#define ANPR_H

#include <string.h>
#include <phpcpp.h>
using namespace std;
class Anpr
	{
    public:    
    Php::Value getPlateDetails(int cameraId,int delay,int id);   
    Php::Value getEntryPlateDetails(int id);   
    Php::Value getAnprSettings(int facility,int carpark);
	};

#endif    

