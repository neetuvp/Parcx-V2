#ifndef ACCESS_H
#define ACCESS_H

#include <string.h>
#include <phpcpp.h>
using namespace std;
class Access
	{
    public:    
    Php::Value checkAccess(string ticketId,string parkingZone,int carpark,int facility,int deviceType,string plateNumber,string plateType,string plateArea,string plateCountry,Php::Value anprSettings);    
	};

#endif    

