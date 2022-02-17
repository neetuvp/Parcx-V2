#include "PX_GeneralOperations.h"
#include "PX_ParkingReport.h"
#include "PX_RevenueReport.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>

using namespace std;
GeneralOperations General;
ParkingReport pObj;
RevenueReport rObj;


void writeLog(string function,string message)
    {
    General.writeLog("WebApplication/ApplicationLogs/PX-Report-"+General.currentDateTime("%Y-%m-%d"),function,message);    
    }

void writeException(string function,string message)
    {
    General.writeLog("WebApplication/ExceptionLogs/PX-Report-"+General.currentDateTime("%Y-%m-%d"),function,message); 
	writeLog(function,"Exception: "+message);   
    }




Php::Value parcxV2Report(Php::Parameters &params)
    {
    Php::Value data=params[0];      
    int task=data["task"];   
    Php::Value response;
    switch (task)
        {
        case 1:  pObj.openTransactionReport(data);
		break;
        case 2:  pObj.parkingMovementsReport(data);
		break;
        case 3:  pObj.validationReport(data);
		break;
        case 4:pObj.accessReport(data);
                break;
        case 5:pObj.trackTicket(data);
                break;
        case 6:pObj.visitorFrequencyReport(data);
                break;
        case 7:pObj.visitorFrequencyRealTimeReport(data);
                break;
        case 8:rObj.paymentTransactions(data);
                break;
        
        case 10:pObj.blacklistReport(data);
		break;
        case 11:pObj.manualmovementReport(data);
                break;
        case 12:pObj.platescapturedReport(data);
                break;        
//        case 13:response = General.getApplicationLabels(data);
//                break;
        case 13:pObj.platesCorrectedReport(data);
                break;
        case 14:pObj.getanprImage(data);
                break;
        case 15:rObj.shiftReport(data);
                break;
        case 16:rObj.getshiftDetails(data);
                        break;
        case 17:rObj.apmrefillReport(data);
                        break;
        case 18:rObj.apmcashlevelsReport(data);
                        break;
        case 19:rObj.apmcashpayoutReport(data);
                        break;
        case 20:rObj.paymentexceptionReport(data);
                        break;
        case 21:rObj.creditcardReport(data);
                        break;
        case 22:rObj.revenueReport(data);
                        break;
        case 23:pObj.trafficReport(data);
                        break;
        case 24:rObj.getPaymentDetails(data);
                        break;
        case 25:rObj.getPdfReceipt(data);
                        break;
        case 26:rObj.vatReport(data);
                        break;
        case 27:pObj.watchDogDeviceAlarms(data);
                        break;
        case 28:pObj.watchdogNetworkLogs(data);
                        break;        
        case  29:response=General.getApplicationLabels(data);
                        break;
        case 30:pObj.parkingDuration(data);
                        break;
        case 31:pObj.liveParking(data);
                break;
        case 32:pObj.getPlatesfromTicket(data);
                break;
        case 33:pObj.userActivity(data);
                break;
	}
	return response;
	}


extern "C" 
	{    
    PHPCPP_EXPORT void *get_module()
		{        
		static Php::Extension extension("PX_V2_Reports", "1.0");
        extension.add<parcxV2Report>("parcxV2Report");               
        return extension;
		}
	}
