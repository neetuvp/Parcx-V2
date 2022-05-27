#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include <cppconn/resultset_metadata.h>
#include<phpcpp.h>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

using namespace std;
GeneralOperations General;
sql::Connection *con, *dcon;
sql::Statement *stmt;
sql::ResultSet *res;
sql::PreparedStatement *prep_stmt;
string query;
int decimal_places=0;
string step_value="1";

void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-Parking-Settings-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-Parking-Settings-" + General.currentDateTime("%Y-%m-%d"), function, message);
    Php::out << message << endl;
    writeLog(function, "Exception: " + message);
}

string ToString(Php::Value param) {
    string value = param;
    return value;
}

void setStepValue()
    {
    try
        {
        if(decimal_places==0)
            {
            res = stmt->executeQuery("SELECT setting_value from system_settings where setting_name='decimal_places'");            
            if(res->next())
                decimal_places=res->getInt("setting_value");            
            delete res;
            if(decimal_places==1)
                step_value=".1";
            else if(decimal_places==2)
                step_value=".01";
            else if(decimal_places==3)
                step_value=".001";
            
            }
        }
    catch (const std::exception& e) {
        writeException("setStepValue", e.what());
    }
    }


void showFixedParkingRates(Php::Value json) {
    try {
        string rate=json["parking_rate_name"];
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();        
        res = stmt->executeQuery("SELECT id,rate_labels,"+rate+",value_type from revenue_fixed_parking_rates");
                  
        Php::out << "<thead class='thead-light'>" << std::endl;
        Php::out << "<tr>" << endl;        
        Php::out << "<th>Fixed Fees</th>" << endl;
        Php::out << "<th>Rate</th>" << endl;
        Php::out << "<th></th>" << endl;
        Php::out << "</tr>" << endl;
        Php::out << "</thead>" << std::endl;        
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;            
            Php::out << "<td>" + res->getString("rate_labels") + "</td>" << endl;
            Php::out << "<td>" + res->getString(rate) + "</td>" << endl;                       
            Php::out << "<td>" << std::endl;                                   
            Php::out << "<button type='button' class='btn btn-info fixed-rate-edit' data-text='Edit'  data-type='"<<res->getString("value_type")<<"' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;            
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }       
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("showFixedParkingRates", e.what());
    }
}

void showParkingRates(Php::Value json) {
    try {
        string rate=json["parking_rate_name"];
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT id,time_unit_"+rate+",time_duration_"+rate+","+rate+" from revenue_parking_rates");
                  
        Php::out << "<thead class='thead-light'>" << std::endl;
        Php::out << "<tr>" << endl;
        //Php::out << "<th>No</th>" << endl;
        Php::out << "<th>Time Unit</th>" << endl;
        Php::out << "<th>Time Duration</th>" << endl;
        Php::out << "<th>Rate</th>" << endl;
        Php::out << "<th></th>" << endl;
        Php::out << "</tr>" << endl;
        Php::out << "</thead>" << std::endl;
        //int i=1;
        while (res->next()) {
            Php::out << "<tr data-id='" << res->getString("id") << "'>" << endl;
            //Php::out << "<td>" <<i++<< "</td>" << endl;
            if(res->getString("time_unit_"+rate)=="mins")
                Php::out << "<td>Minutes</td>" << endl;
            else
                Php::out << "<td>Hours</td>" << endl;
            Php::out << "<td>" + res->getString("time_duration_"+rate) + "</td>" << endl;
            Php::out << "<td>" + res->getString(rate) + "</td>" << endl;
           

            Php::out << "<td>" << std::endl;                       
            Php::out << "<button type='button' class='btn btn-info parking-rate-edit' data-text='Edit' title='Edit'><i class='fas fa-edit'></i></button>" << std::endl;
            Php::out << "</td>" << std::endl;
            Php::out << "</tr>" << endl;
        }        
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("showParkingRates", e.what());
    }
}


void showParkingRateLabelButtons() {
    try {        
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        setStepValue();
        Php::out<<"<input type='hidden' id='step_value' value='"<<step_value<<"'>"<<endl;     
        res = stmt->executeQuery("SELECT * from revenue_parking_rate_label");                          
        if (res->next()) {
            Php::out<<"<div class='col-auto'><input class='btn btn-success btn-parking-rate ' id ='parking_rates1' type='submit'  value='" <<res->getString("parking_rate1")<< "' onclick = ShowParkingRate('parking_rates1')></div>"<<endl;
            Php::out<<"<div class='col-auto'><input class='btn btn-info btn-parking-rate' id ='parking_rates2' type='submit'  value='" <<res->getString("parking_rate2")<< "' onclick = ShowParkingRate('parking_rates2')></div>"<<endl;
            Php::out<<"<div class='col-auto'><input class='btn btn-info btn-parking-rate' id ='parking_rates3' type='submit'  value='" <<res->getString("parking_rate3")<< "' onclick = ShowParkingRate('parking_rates3')></div>"<<endl;
            Php::out<<"<div class='col-auto'><input class='btn btn-info btn-parking-rate' id ='parking_rates4' type='submit'  value='" <<res->getString("parking_rate4")<< "' onclick = ShowParkingRate('parking_rates4')></div>"<<endl;
            Php::out<<"<div class='col-auto'><input class='btn btn-info btn-parking-rate' id ='parking_rates5' type='submit'  value='" <<res->getString("parking_rate5")<< "' onclick = ShowParkingRate('parking_rates5')></div>"<<endl;
        }
       
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("showParkingRateLabelButtons", e.what());
    }
}

Php::Value updateFixedParkingRates(Php::Value json) {
    string msg = "Failed";
    try {
        string rate=json["parking_rate_name"];
        con = General.mysqlConnect(ServerDB);       
        prep_stmt = con->prepareStatement("update revenue_fixed_parking_rates set "+rate+"=? where id=?");
        prep_stmt->setString(1, ToString(json["parking_rate_value"]));
        prep_stmt->setString(2, ToString(json["id"]));
               
        if (!prep_stmt->execute()) 
            msg = "Successfull";
                            
        delete prep_stmt;
        delete con;
        
    }    catch (const std::exception& e) {
        writeException("updateFixedParkingRates", e.what());
    }
    return msg;
}


Php::Value updateParkingRates(Php::Value json) {
    string msg = "Failed";
    try {
        string rate=json["parking_rate_name"];
        con = General.mysqlConnect(ServerDB);       
        prep_stmt = con->prepareStatement("update revenue_parking_rates set time_unit_"+rate+"=?,time_duration_"+rate+"=?,"+rate+"=? where id=?");
        prep_stmt->setString(1, ToString(json["time_unit"]));
        prep_stmt->setString(2, ToString(json["time_duration"]));
        prep_stmt->setString(3, ToString(json["parking_rate_value"]));
        prep_stmt->setString(4, ToString(json["id"]));
               
        if (!prep_stmt->execute()) 
            msg = "Successfull";
                            
        delete prep_stmt;
        delete con;
        
    }    catch (const std::exception& e) {
        writeException("updateParkingRates", e.what());
    }
    return msg;
}



Php::Value parcxParkingSettings(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) 
        {
        case 1:response = updateFixedParkingRates(data);
            break;
        case 2:showFixedParkingRates(data);
            break;
        case 3:showParkingRateLabelButtons();
            break;     
        case 4:showParkingRates(data);
            break;
        case 5:response = updateParkingRates(data);
            break;
        }
    return response;
}

extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_ParkingSettings", "1.0");
        extension.add<parcxParkingSettings>("parcxParkingSettings");
        return extension;
    }
}
