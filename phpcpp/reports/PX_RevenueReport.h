#ifndef REVENUEREPORT_H
#define REVENUEREPORT_H

#include <string.h>
#include <phpcpp.h>
using namespace std;
class RevenueReport
	{
    public:    
    void paymentTransactions(Php::Value json);
    void shiftReport(Php::Value json);
    void getshiftDetails(Php::Value json);
    void apmrefillReport(Php::Value json);
    void apmcashlevelsReport(Php::Value json);
    void apmcashpayoutReport(Php::Value json);
    void paymentexceptionReport(Php::Value json);
    void creditcardReport(Php::Value json);
    void revenueReport(Php::Value json);
    void getPaymentDetails(Php::Value json);
    void getPdfReceipt(Php::Value json);
    void vatReport(Php::Value json);
    
    };

#endif    

