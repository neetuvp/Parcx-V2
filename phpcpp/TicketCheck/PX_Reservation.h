#ifndef RESERVATION_H
#define RESERVATION_H

#include <string.h>
#include <phpcpp.h>
using namespace std;
class Reservation
	{
    public:    
    Php::Value checkReservation(string ticketId,int entryGrace,int exitGrace,int carpark,int facility,int deviceType,string plateNumber,string plateType,string plateArea,string plateCountry,Php::Value anprSettings);
	};

#endif    

