#ifndef PARKINGREPORT_H
#define PARKINGREPORT_H

#include <phpcpp.h>
using namespace std;
class ParkingReport
    {
    public:    
    void openTransactionReport(Php::Value json);    
    void parkingMovementsReport(Php::Value json);    
    void validationReport(Php::Value json);    
    void accessReport(Php::Value json);  
    void trackTicket(Php::Value json);   
    void visitorFrequencyReport(Php::Value json);
    void visitorFrequencyRealTimeReport(Php::Value json);     
    void blacklistReport(Php::Value json);
    void manualmovementReport(Php::Value json);   
    void platescapturedReport(Php::Value json);      
    void platesCorrectedReport(Php::Value json);      
    void getanprImage(Php::Value json);
    void trafficReport(Php::Value json);
    void watchDogDeviceAlarms(Php::Value json);
    void watchdogNetworkLogs(Php::Value json);     
    void parkingDuration(Php::Value json);
    void liveParking(Php::Value json);
    void getPlatesfromTicket(Php::Value json);
    void userActivity(Php::Value json);
    };

#endif    

