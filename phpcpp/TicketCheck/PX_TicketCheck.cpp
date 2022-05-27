#include "PX_GeneralOperations.h"
#include "PX_Reservation.h"
#include "PX_Access.h"
#include "PX_Anpr.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <curl/curl.h>
#include<jsoncpp/json/json.h>
#include <sstream>
#include <iomanip>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateTimeFormat "%Y-%m-%d %H:%M:%S"

using namespace std;
GeneralOperations General;
Reservation ReservationObj;
Access AccessObj;
Anpr AnprObj;

int couponEnabled,paymentExit, deductMoneyFromWallet, carparkNumber, facilityNumber, deviceNumber, deviceType, task, entryGrace, exitGrace, reservationEnabled, accessEnabled, deviceFunction, seconds, anprEnabled, cameraId, plateCapturedInterval, plateCapturedId, validationEnabled, walletEnabled, entryType;
string current_coupon_id,deviceName, carpakName, facilityName, ticketId, plateNumber, parkingZone, query, entryDateTime, maxEntryGrace, maxExitGrace, currentDateTime, plateType, plateArea, plateCountry;
string timeValidationId, percentageValidationId, validationDuration, userId, accessExpiry, reservationExpiry, paymentDateTime, accessResult, accessResultDescription, reservationResult, reservationStart, reservationResultDescription;
int test,offlineTimeValidation, offlinePercentageValidation, timeValidation, percentageValidation, parkingDuration, validationSeconds, validationHours, rate_type = 0;
double startparkingFee, parkingFee, vat_percentage = 0, amountPaid = 0, grossAmount, vatAmount, reservationFee, walletBalance;
sql::Connection *reportCon, *serverCon;
sql::Statement *stmt, *serverStmt;
sql::ResultSet *res, *validation, *settings, *device, *rate,*coupon;
CURL *curl;
CURLcode curl_res;
string wallet_webservice, webservice_response, vat_type = "", parking_duration;
Json::FastWriter fw;
Json::Reader jsonReader;
Json::Value jsonResponse;
string currency,coupon_value,couponId,valid_coupon_id,validation_value, parking_rate = "", parking_rate_label = "", entry_grace_period, entry_grace_period_mins, entry_grace_period_hours, exit_grace_period_mins, exit_grace_period_hours, exit_grace_period;
int entry_grace_period_minuts, exit_grace_period_minuts;
int parkingFeeDuration = 0;
int anpr_mismatch_check = 0;
int cooperate_parker = 0;
int short_term_entry_after_contract_parking_space_exceeded = 0;
int no_entry_contract_parking_allow_exit=1;
int coupon_percentage,coupon_amount;
string coupon_array;

void writeLog(string function, string message) {
    General.writeLog("Services/PXTicketCheck/ApplicationLog-PX-TicketCheck-" + General.currentDateTime("%Y-%m-%d"), function, message);
}

void writeException(string function, string message) {
    General.writeLog("Services/PXTicketCheck/ExceptionLog-PX-TicketCheck-" + General.currentDateTime("%Y-%m-%d"), function, message);
    writeLog(function, "Exception: " + message);
}

string toString(Php::Value param) {
    string value = param;
    return value;
}

void getValidations() {
    try {
        timeValidation = 0;
        percentageValidation = 0;
        timeValidationId = "";
        percentageValidationId = "";

        query = "SELECT GROUP_CONCAT(DISTINCT id SEPARATOR ',') AS id,sum(validation_value) as validation_value,validation_type FROM parking_validation where ticket_id='" + ticketId + "' and date_time> '" + entryDateTime + "' GROUP by validation_type";
        validation = stmt->executeQuery(query);
        while (validation->next()) {
            if (validation->getString("validation_type") == "Time Value") {
                timeValidation = validation->getInt("validation_value");
                timeValidationId = validation->getString("id");
                writeLog("getValidations", "Validation Hours:" + to_string(timeValidation));
                writeLog("getValidations", "Time Validation id:" + timeValidationId);
            }
            if (validation->getString("validation_type") == "Percentage Value") {
                percentageValidation = validation->getInt("validation_value");
                percentageValidationId = validation->getString("id");
                writeLog("getValidations", "Validation Percentage:" + to_string(percentageValidation));
                writeLog("getValidations", "Percentage Validation id:" + percentageValidationId);
            }
        }
        delete validation;
    } catch (const std::exception& e) {
        writeException("getValidations", e.what());
    }
}

Php::Value couponCheck() {
    Php::Value response;
    try {                
        response["result"] = "invalid_coupon";
        response["result_description"] = "Invalid coupon";
        response["coupon_ticket"]="false";
        reportCon = General.mysqlConnect(ReportingDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();                        
            query="SELECT a.discount_id,a.status as coupon_status,coupon_usage,discount_option,discount_value,discount_type,b.status as discount_status,carpark_number FROM parcx_server.revenue_coupons_whitelist a,parcx_server.revenue_discounts b where a.discount_id=b.discount_id and coupon_id='"+ticketId+"'";
            res = stmt->executeQuery(query);
            if(res->next())
                {
                response["coupon_ticket"]="true";
                if(res->getInt("discount_status")==0 || res->getInt("coupon_status")==0)
                    {
                    response["result"] = "disabled_coupon";
                    response["result_description"] ="Inactive coupon";
                    }
                else if(res->getInt("coupon_usage")==1)
                    {
                    response["result"] = "used_coupon";
                    response["result_description"] ="Used coupon";
                    }
                else if (carparkNumber != res->getInt("carpark_number")) 
                    {
                    response["result"] = "coupon_invalid_carpark";
                    response["result_description"] = "Coupon from another carpark";
                    }
                else if (res->getString("discount_option")=="coupon") 
                    {                                  
                    response["result"] = "valid_coupon";
                    response["result_description"] = "Valid coupon";                        
                    response["coupon_id"]=ticketId;                                                    
                    }
                string result = response["result"];
                string result_description = response["result_description"];                
                writeLog("couponCheck", "Result: " + result + "\tDescription:" + result_description + "\n");                
                }                                                              
            delete res;
            delete stmt;
            delete reportCon;
            }
        }
        catch (const std::exception& e) {
        writeException("couponCheck", e.what());
    }
    return response;
}

void getCouponDiscounts() {
    try 
        {
        coupon_percentage=0;
        coupon_amount=0;
        valid_coupon_id=""; 
        coupon_value="";    
        
        if(maxExitGrace!="")
            {
            query="SELECT group_concat(coupon_id) as coupon_id FROM revenue_coupons_whitelist where ticket_id='"+ticketId+"'";
            coupon = stmt->executeQuery(query);
            if(coupon->next())
                {
                if(coupon->getString("coupon_id")!="")
                    {
                    if(coupon_array=="")
                        coupon_array=coupon->getString("coupon_id");                    
                    else
                       coupon_array=coupon_array+","+ coupon->getString("coupon_id"); 
                    }
                }
            delete coupon;
            }
        
        if(current_coupon_id!="")
            {
            if(coupon_array=="")
                coupon_array=current_coupon_id;                    
            else
               coupon_array=coupon_array+","+ current_coupon_id;
            }
        
        std::stringstream ss(coupon_array);
        coupon_array="";
        while( ss.good() )
            {            
            getline( ss, couponId, ',' );                
            if(coupon_array=="")
                coupon_array="'"+couponId+"'";
            else
                coupon_array=coupon_array+",'"+couponId+"'";
            }
        writeLog("coupon_array",coupon_array);
        if(coupon_array!="")
            {            
            query="SELECT coupon_id,discount_value,discount_type FROM parcx_server.revenue_coupons_whitelist a,parcx_server.revenue_discounts b where a.discount_id=b.discount_id and carpark_number="+to_string(carparkNumber)+" and a.status=1 and b.status=1 and (coupon_usage=0 or ticket_id='"+ticketId+"') and discount_option='coupon' and coupon_id in("+coupon_array+")";            
            coupon = stmt->executeQuery(query);
            while(coupon->next())
                {
                if(valid_coupon_id=="")
                    valid_coupon_id=coupon->getString("coupon_id");
                else
                    valid_coupon_id=valid_coupon_id+","+coupon->getString("coupon_id");
                
                if(coupon->getString("discount_type")=="percentage")
                    coupon_percentage=coupon_percentage+coupon->getInt("discount_value");
                if(coupon->getString("discount_type")=="amount")
                    coupon_amount=coupon_amount+coupon->getInt("discount_value");                
                }
            delete coupon;
            }
        if(coupon_amount>0)
            coupon_value=to_string(coupon_amount)+" "+currency;
        if(coupon_percentage>0)
            coupon_value=coupon_value+to_string(coupon_percentage)+" %";
            
        writeLog("total coupon_amount",to_string(coupon_amount));
        writeLog("total coupon_percentage",to_string(coupon_percentage));
        } catch (const std::exception& e) {
        writeException("getCouponDiscounts", e.what());
    }
}

void calculateVat() {
    try {
        writeLog("calculateVat", "vat_percentage: " + to_string(vat_percentage));
        if (vat_percentage > 0) {
            if (vat_type == "inclusive") {
                grossAmount = parkingFee;
                parkingFee = grossAmount / (1 + (vat_percentage / 100));
                vatAmount = grossAmount - parkingFee;
            }
            if (vat_type == "exclusive") {
                grossAmount = parkingFee * (1 + (vat_percentage / 100));
                vatAmount = grossAmount - parkingFee;
            }
            writeLog("calculateVat", "parkingFee: " + to_string(parkingFee));
            writeLog("calculateVat", "vatAmount: " + to_string(vatAmount));
        } else
            grossAmount = parkingFee;

        writeLog("calculateVat", "grossAmount: " + to_string(grossAmount));
    } catch (const std::exception& e) {
        writeException("calculateVat", e.what());
    }

}

void setParkingRateConfiguration() {
    try {
        stmt = serverCon->createStatement();
        parking_rate = "", parking_rate_label = "";
        if (entryType == 3) {
            query = "select reservation_rate_plan,rate_type from system_devices where device_number=" + to_string(deviceNumber);
            rate = stmt->executeQuery(query);
            if (rate->next()) {
                rate_type = rate->getInt("rate_type");
                parking_rate = rate->getString("reservation_rate_plan");
                writeLog("setParkingRateConfiguration", "Reservation parking: Type:" + to_string(rate_type) + "\t" + parking_rate);
            }
        } else {
            query = "select rate_type,parking_rate,parking_rate_label from revenue_dynamic_parking_rates where datetime =date('" + entryDateTime + "') and status=1";
            rate = stmt->executeQuery(query);
            if (rate->first()) {
                rate_type = rate->getInt("rate_type");
                parking_rate = rate->getString("parking_rate");
                parking_rate_label = rate->getString("parking_rate_label");
                writeLog("setParkingRateConfiguration", "Dynamic parking rate For day: Type:" + to_string(rate_type) + "\t" + parking_rate + "\t" + parking_rate_label);
            } else {
                query = "select rate_type,parking_rate,parking_rate_label from revenue_dynamic_parking_rates where day=dayname('" + entryDateTime + "') and status=1";
                rate = stmt->executeQuery(query);
                if (rate->first()) {
                    rate_type = rate->getInt("rate_type");
                    parking_rate = rate->getString("parking_rate");
                    parking_rate_label = rate->getString("parking_rate_label");
                    writeLog("setParkingRateConfiguration", "Dynamic parking rate For date: Type:" + to_string(rate_type) + "\t" + parking_rate + "\t" + parking_rate_label);
                } else {
                    query = "select rate_plan,rate_type from system_devices where device_number=" + to_string(deviceNumber);
                    rate = stmt->executeQuery(query);
                    if (rate->next()) {
                        rate_type = rate->getInt("rate_type");
                        parking_rate = rate->getString("rate_plan");

                        query = "select parking_rate" + parking_rate.substr(13, 1) + " as parking_rate from revenue_parking_rate_label";
                        rate = stmt->executeQuery(query);
                        if (rate->next())
                            parking_rate_label = rate->getString("parking_rate");
                        writeLog("setParkingRateConfiguration", "Normal parking: Type:" + to_string(rate_type) + "\t" + parking_rate + "\t" + parking_rate_label);
                    }
                }
            }
        }
        entry_grace_period_hours = "0";
        entry_grace_period_mins = "0";
        exit_grace_period_hours = "0";
        exit_grace_period_mins = "0";
        if(parking_rate!="")
            {
            query = "select fixed_fees," + parking_rate + " from  revenue_fixed_parking_rates";
            rate = stmt->executeQuery(query);
            while (rate->next()) {
                if (rate->getString("fixed_fees") == "entry_grace_period_mins")
                    entry_grace_period_mins = rate->getString(parking_rate);
                if (rate->getString("fixed_fees") == "entry_grace_period_hours")
                    entry_grace_period_hours = rate->getString(parking_rate);
                if (rate->getString("fixed_fees") == "exit_grace_period_hours")
                    exit_grace_period_hours = rate->getString(parking_rate);
                if (rate->getString("fixed_fees") == "exit_grace_period_mins")
                    exit_grace_period_mins = rate->getString(parking_rate);

               
            }
            delete rate;
        }
        
        entry_grace_period = entry_grace_period_hours + "H " + entry_grace_period_mins + "M";
        exit_grace_period = exit_grace_period_hours + "H " + exit_grace_period_mins + "M";

        entry_grace_period_minuts = stoi(entry_grace_period_hours)*60 + stoi(entry_grace_period_mins);
        exit_grace_period_minuts = stoi(exit_grace_period_hours)*60 + stoi(exit_grace_period_mins);
                
        writeLog("setParkingRateConfiguration", "Entry grace period=" + entry_grace_period);
        writeLog("setParkingRateConfiguration", "Exit grace period=" + exit_grace_period);
        
    } catch (const std::exception& e) {
        writeException("setParkingRateConfiguration", e.what());
    }

}

float calculateParkingFee() {
    stmt = serverCon->createStatement();
    float fee = 0;
    float recurringFee = 0;
    parkingFeeDuration = 0;
    try {
        writeLog("calculateParkingFee", "Entry date: " + entryDateTime + "\tParking duration: " + to_string(parkingDuration) + "\tDevice:" + to_string(deviceNumber));
        //Calculate duration
        int hours = 0, minutes = 0, seconds = 0;
        hours = parkingDuration / 3600;
        minutes = (parkingDuration % 3600) / 60;
        seconds = (parkingDuration % 3600) % 60;
        writeLog("calculateParkingFee", to_string(hours) + " H " + to_string(minutes) + " M " + to_string(seconds) + " S ");
        //Calculate parking Fee

        if (rate_type == 1) {
            if (hours > 0) {
                query = "select " + parking_rate + ",time_duration_" + parking_rate + " from  revenue_parking_rates " + "where time_unit_" + parking_rate + "='hours'  " + "and time_duration_" + parking_rate + ">" + to_string(hours) + " " + "ORDER BY  time_duration_" + parking_rate + " limit 1";
                rate = stmt->executeQuery(query);

                if (rate->next()) {
                    fee = rate->getDouble(parking_rate);
                    parkingFeeDuration = rate->getInt(2)*60;
                } else {
                    query = "select time_duration_" + parking_rate + "," + parking_rate + " from  revenue_parking_rates " + "where time_unit_" + parking_rate + "='hours'  " + "ORDER BY  time_duration_" + parking_rate + " DESC limit 1";
                    rate = stmt->executeQuery(query);
                    if (rate->next()) {
                        parkingFeeDuration = (hours + 1)*60;
                        int maxhours = rate->getInt(1);
                        fee = rate->getDouble(parking_rate);
                        writeLog("calculateParkingFee", "Max hours:" + to_string(maxhours) + " Fee:" + to_string(fee));
                        ;
                        query = "select " + parking_rate + " from revenue_fixed_parking_rates where fixed_fees='recurring_fee'";
                        rate = stmt->executeQuery(query);

                        if (rate->next()) {
                            recurringFee = rate->getDouble(1);
                            writeLog("calculateParkingFee", "Recurring fee:" + to_string(recurringFee));
                            fee = fee + (hours + 1 - maxhours) * recurringFee;
                        }
                    }
                }

            } else {//minutes only
                query = "select " + parking_rate + ",time_duration_" + parking_rate + " from  revenue_parking_rates " + "where time_unit_" + parking_rate + "='mins'  " + "and time_duration_" + parking_rate + ">" + to_string(minutes) + " " + "ORDER BY  time_duration_" + parking_rate + " limit 1";
                rate = stmt->executeQuery(query);
                if (rate->next()) {
                    fee = rate->getDouble(parking_rate);
                    parkingFeeDuration = rate->getInt(2);
                } else {
                    query = "select " + parking_rate + ",time_duration_" + parking_rate + " from  revenue_parking_rates " + "where time_unit_" + parking_rate + "='hours'  " + "ORDER BY  time_duration_" + parking_rate + " limit 1";
                    rate = stmt->executeQuery(query);
                    rate->first();
                    fee = rate->getDouble(1);
                    parkingFeeDuration = rate->getInt(2)*60;
                }
            }
        } else if (rate_type == 2) {
            query = "select " + parking_rate + " from  revenue_fixed_parking_rates where fixed_fees='fixed_parking_rate'";
            rate = stmt->executeQuery(query);
            rate->first();
            fee = rate->getDouble(1);
        }
        writeLog("calculateParkingFee", "Fee: " + to_string(fee));
        writeLog("calculateParkingFee", "parkingFeeDuration: " + to_string(parkingFeeDuration));
        if (amountPaid > 0) {
            writeLog("calculateParkingFee", "amountPaid: " + to_string(amountPaid));
            fee = fee - amountPaid;
            writeLog("calculateParkingFee", "Fee: " + to_string(fee));
        }
        delete rate;
    } catch (exception &e) {
        writeException("calculateParkingFee", e.what());
        writeException("calculateParkingFee", query);
    }
    return fee;
}

void calculateAmountToPay() {
    try {
        parkingFee = startparkingFee;
        if (percentageValidation > 0) {
            parkingFee = parkingFee - (parkingFee * percentageValidation / 100);
            writeLog("calculateAmountToPay", "Parking fee after " + to_string(percentageValidation) + " validation : " + to_string(parkingFee));
        }
        if(coupon_amount>0)
            {
            parkingFee=parkingFee-coupon_amount;
            writeLog("calculateAmountToPay", "Parking fee after " + to_string(coupon_amount) + " amount coupon : " + to_string(parkingFee));
            }
        if (coupon_percentage > 0) {
            parkingFee = parkingFee - (parkingFee * coupon_percentage / 100);
            writeLog("calculateAmountToPay", "Parking fee after " + to_string(coupon_percentage) + "% coupon : " + to_string(parkingFee));
        }
        
        calculateVat();
    } catch (const std::exception& e) {
        writeException("calculateAmountToPay", e.what());
    }

}

static size_t WriteCallback(void *contents, size_t size, size_t nmemb, void *userp) {
    ((std::string*)userp)->append((char*) contents, size * nmemb);
    return size * nmemb;
}

string callWebService(string jsonstring) {
    string response;
    writeLog("callWebService", "Request:" + jsonstring);
    curl = curl_easy_init();
    if (curl) {
        curl_easy_setopt(curl, CURLOPT_URL, wallet_webservice.c_str());
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, jsonstring.c_str());
        curl_easy_setopt(curl, CURLOPT_TIMEOUT, 20L);
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteCallback);
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, &response);
        curl_res = curl_easy_perform(curl);
        if (curl_res != CURLE_OK) {
            response = "error";
        } else {
            curl_easy_cleanup(curl);
        }
    }
    writeLog("callWebService", "Response:" + response);
    return response;
}

Php::Value updateWalletPayment() {
    Php::Value response;
    try 
        {       
        Json::Value input;
        input["task"] = "1";
        input["user_id"] = userId;
        input["amount"] = grossAmount;
        input["device_number"] = deviceNumber;
        input["device_name"] = deviceName;
        input["carpark_number"] = carparkNumber;
        input["facility_number"] = facilityNumber;
        input["facility_name"] = facilityName;
        input["plate_number"] = plateNumber;
        input["ticket_id"] = ticketId;

        string jsonstring = fw.write(input);
        webservice_response = callWebService(jsonstring);
        if (webservice_response == "error") {
            response["result_description"] = "Cloud unreachable";
            response["result"] = "cloud_unreachable";
        } else if (jsonReader.parse(webservice_response, jsonResponse)) {
            for (auto const& id : jsonResponse.getMemberNames()) {
                response[id] = jsonResponse[id].asString();
            }
        } else
            writeLog("updateWalletPayment", "Could not parse HTTP data as JSON");

    } catch (const std::exception& e) {
        std::cerr << e.what() << '\n';
    }
    return response;
}

Php::Value walletCheck() {
    Php::Value response;
    try {
        response["wallet_present"] = "false";
        response["access_allowed"] = "false";
        stmt = serverCon->createStatement();
        if (plateNumber != "")
            query = "select * from plates_ewallet where plate_number='" + plateNumber + "'";
        else
            query = "select * from plates_ewallet where ticket_id='" + ticketId + "'";
        res = stmt->executeQuery(query);
        if (res->next()) {
            response["wallet_present"] = "true";
            writeLog("walletCheck", "Wallet Present :" + plateNumber);
            userId = res->getString("user_id");
            response["user_id"] = userId;
            if (grossAmount > 0) {
                Json::Value input;
                if (deviceType == 2) {
                    input["task"] = "1";
                    input["user_id"] = userId;
                    input["amount"] = grossAmount;
                    input["device_number"] = deviceNumber;
                    input["device_name"] = deviceName;
                    input["carpark_number"] = carparkNumber;
                    input["facility_number"] = facilityNumber;
                    input["facility_name"] = facilityName;
                    input["plate_number"] = plateNumber;
                    input["ticket_id"] = ticketId;
                } else {
                    input["task"] = "6";
                    input["user_id"] = userId;
                    input["amount"] = grossAmount;
                }


                string jsonstring = fw.write(input);
                webservice_response = callWebService(jsonstring);
                if (webservice_response == "error") {
                    response["result_description"] = "Cloud unreachable";
                    response["result"] = "cloud_unreachable";
                    response["wallet_present"] = "false";
                } else if (jsonReader.parse(webservice_response, jsonResponse)) {
                    for (auto const& id : jsonResponse.getMemberNames()) {
                        response[id] = jsonResponse[id].asString();
                    }
                } else
                    writeLog("walletCheck", "Could not parse HTTP data as JSON");
            } else {
                response["result"] = "access_allowed";
                response["access_allowed"] = "true";
                response["result_description"] = "Within exit grace period";
            }

        } else {
            response["result"] = "wallet_not_present";
            response["result_description"] = "No wallet for this user";
            writeLog("walletCheck", "Wallet not Present :" + plateNumber);
        }

    } catch (const std::exception& e) {
        writeException("walletCheck", e.what());
    }
    return response;
}

string secondsToParkingDuration(int n) {
    int day = 0, hour = 0, minutes = 0, seconds = 0;
    if (n > 0) {
        day = n / (24 * 3600);
        n = n % (24 * 3600);
        hour = n / 3600;
        n %= 3600;
        minutes = n / 60;
        n %= 60;
        seconds = n;
    }
    string duration;
    if (day > 0)
        duration = to_string(day) + "D " + to_string(hour) + "H " + to_string(minutes) + "M";
    else if (hour > 0)
        duration = to_string(hour) + "H " + to_string(minutes) + "M";
    else
        duration = to_string(minutes) + "M " + to_string(seconds) + "S";
    return duration;
}

Php::Value openTransactionCheck(int getDetails) {
    Php::Value response;
    response["access_allowed"] = "false";
    response["open_transaction_check"] = "false";
    try {
        if (ticketId != "") {
            if (ticketId.length() == 20)
                query = "SELECT * from open_transactions where ticket_id = '" + ticketId + "' and movement_type=1 order by id desc";
            else
                query = "SELECT * from open_transactions where ticket_id like '%" + ticketId + "' and movement_type=1 order by id desc";
        } else
            query = "SELECT * from open_transactions where plate_number = '" + plateNumber + "' and movement_type=1 order by id desc";

        res = stmt->executeQuery(query);
        if (res->next()) {
            response["entry_plate_number"] = "###";            

            if (res->getString("plate_captured_id") != "")
                response = AnprObj.getEntryPlateDetails(res->getInt("plate_captured_id"));

            response["open_transaction_check"] = "true";                

            ticketId = res->getString("ticket_id");
            plateNumber = res->getString("plate_number");
            entryType = res->getInt("entry_type");
            entryDateTime = res->getString("entry_date_time");
            maxEntryGrace = res->getString("entry_grace_period");
            writeLog("openTransactionCheck", "Present in open transaction, id:" + res->getString("id"));
            writeLog("openTransactionCheck", "ticketId: " + ticketId);
            writeLog("openTransactionCheck", "plateNumber: " + plateNumber);
            writeLog("openTransactionCheck", "entryDateTime: " + entryDateTime);
            writeLog("openTransactionCheck", "maxEntryGrace: " + maxEntryGrace);

            response["ticket_id"] = ticketId;
            response["plate_number"] = plateNumber;            
            response["entry_grace_period"] = maxEntryGrace;
            response["entry_date_time"] = entryDateTime;
            response["entry_type"] = entryType;
            response["carpark_number"] = string(res->getString("carpark_number"));
            response["facility_number"] = string(res->getString("facility_number"));
            if (getDetails == 1)
                return response;
            response["access_allowed"] = "false";

            if (carparkNumber != res->getInt("carpark_number")) {
                response["result"] = "invalid_carpark";
                response["result_description"] = "Invalid carpark";
            } else if (facilityNumber != res->getInt("facility_number")) {
                response["result"] = "invalid_facility";
                response["result_description"] = "Invalid facility";
            } else {
                if (entryType == 1 && accessResult == "allow_access") {
                    if (cooperate_parker > 1 && short_term_entry_after_contract_parking_space_exceeded == 1) {
                        response["whitelist_result"] = "access_denied";
                        response["whitelist_result_description"] = "Corporate user enterd as shortterm user";
                    } else {
                        response["whitelist_result"] = "access_denied";
                        response["whitelist_result_description"] = "Antipassback.Access user entered as shortterm";
                    }
                }

                if (entryType == 2 && accessResult == "allow_access") {
                    if (res->getString("pay_upto") != "") {
                        currentDateTime = res->getString("pay_upto");
                        response["entry_type"] = 1;
                    } else {
                        response["access_allowed"] = "true";
                        return response;
                    }
                } else
                    currentDateTime = General.currentDateTime(dateTimeFormat);
                writeLog("openTransactionCheck", "paymentUpto: " + currentDateTime);
                setParkingRateConfiguration();
                if (deviceType > 2 || walletEnabled == 1 || paymentExit == 1) 
                    {                    
                    response["parking_rate"] = parking_rate;
                    response["parking_rate_label"] = parking_rate_label;
                    response["entry_grace_period"] = entry_grace_period;
                    response["exit_grace_period"] = exit_grace_period;
                    response["exit_grace_period_minutes"] = exit_grace_period_minuts;
                    response["entry_grace_period_minutes"] = entry_grace_period_minuts;
                    response["parking_fee"] = 0;
                    response["net_amount"] = 0;
                    response["vat_amount"] = 0;
                    response["gross_amount"] = 0;
                    response["parking_fee_duration_minutes"] = 0;
                    }

                stmt = reportCon->createStatement();

                int *duration = General.differenceDateTime(currentDateTime, entryDateTime, dateTimeFormat);
                parkingDuration = duration[0];
                writeLog("openTransactionCheck", "Parking duration: " + to_string(parkingDuration) + " S");
                parking_duration = secondsToParkingDuration(parkingDuration);
                writeLog("openTransactionCheck", "Parking duration: " + parking_duration);
                response["parking_duration"] = parking_duration;                
                response["parking_duration_seconds"] = to_string(parkingDuration);                
                
                timeValidation = 0;
                percentageValidation =0;                       
                if (validationEnabled == 1) {
                    getValidations();
                    validation_value = "";
                    response["validation_id"] = "";
                    response["validation_hours"] = "0";
                    response["validation_percentage"] = "0";
                    timeValidation = timeValidation + offlineTimeValidation;
                    percentageValidation = percentageValidation + offlinePercentageValidation;
                    writeLog("openTransactionCheck", "Offline Validation Hours:" + to_string(offlineTimeValidation));
                    writeLog("openTransactionCheck", "Offline Validation Percentage:" + to_string(offlinePercentageValidation));

                    writeLog("openTransactionCheck", "Total Validation Hours:" + to_string(timeValidation));
                    writeLog("openTransactionCheck", "Total Validation Percentage:" + to_string(percentageValidation));
                    if (timeValidation > 0) {
                        response["validation_id"] = timeValidationId;
                        response["validation_type"] = "Time";
                        response["validation_hours"] = to_string(timeValidation);
                        validation_value = to_string(timeValidation) + " H";
                    }
                    if (percentageValidation > 0) {
                        response["validation_percentage"] = to_string(percentageValidation);
                        response["validation_id"] = (timeValidationId!="") ? timeValidationId + "," + percentageValidationId : percentageValidationId;
                        response["validation_type"] = (timeValidation > 0) ? "Time,Percentage" : "Percentage";
                        validation_value = validation_value + to_string(percentageValidation) + " %";
                    }

                    validationSeconds = timeValidation * 3600;
                }
                
                response["entry_grace_time_remaining"] = "0";
                response["exit_grace_time_remaining"] = "0";

                int *timeDiff = General.differenceDateTime(maxEntryGrace, currentDateTime, dateTimeFormat);
                writeLog("openTransactionCheck", "entry_grace_time_remaining: " + to_string(timeDiff[0]));
                if (timeDiff[0] > 0) {
                    response["entry_grace_time_remaining"] = to_string(timeDiff[0]);
                    response["ticketcheck_result"] = "within_entry_grace_period";
                    response["result"] = "ticketcheck_access_allowed";
                    response["access_allowed"] = "true";
                    response["result_description"] = "Within entry grace period";
                    return response;
                } else {
                    maxExitGrace = res->getString("max_exit_date_time");
                    amountPaid = res->getDouble("total_amount_paid");
                    if (entryType == 1) {
                        paymentDateTime = res->getString("last_payment_date_time");
                        response["payment_date_time"] = paymentDateTime;
                    }
                    if (entryType == 2) {
                        writeLog("openTransactionCheck", "Access Entry: accessResult: " + accessResult);
                        response["result"] = accessResult;
                        response["result_description"] = accessResultDescription;
                        if (accessResult == "access_expired") {
                            entryDateTime = accessExpiry + " 23:59:59";
                            if (maxExitGrace == "")
                                maxExitGrace = accessExpiry + " 23:59:59";

                            if (amountPaid > 0) {
                                response["result"] = "access_exit_grace_period_exceeded";
                                response["result_description"] = "Access exit grace period exceeded";
                            }
                        } else if (accessResult == "allow_access") {
                            if (maxExitGrace != "")
                                maxExitGrace = accessExpiry + " 23:59:59";

                        } else
                            return response;
                    }
                    if (entryType == 3) {
                        writeLog("openTransactionCheck", "Reservation Entry: reservationResult: " + reservationResult);
                        if (reservationResult == "reservation_exit_grace_period_exceeded") {
                            entryDateTime = reservationStart;
                            if (amountPaid == 0) {
                                maxExitGrace = reservationExpiry;
                                amountPaid = reservationFee;
                            } else
                                amountPaid = amountPaid + reservationFee;
                        }
                        else
                            return response;

                    }
                    if (maxExitGrace != "") {
                        writeLog("openTransactionCheck", "maxExitGrace: " + maxExitGrace);
                        response["max_exit_date_time"] = maxExitGrace;
                        response["total_amount_paid"] = amountPaid;
                        writeLog("openTransactionCheck", "amountPaid: " + to_string(amountPaid));
                        int *timeDiff = General.differenceDateTime(maxExitGrace, currentDateTime, dateTimeFormat);
                        writeLog("openTransactionCheck", "exit_grace_time_remaining: " + to_string(timeDiff[0]));
                        if (timeDiff[0] > 0) {
                            response["exit_grace_time_remaining"] = to_string(timeDiff[0]);
                            response["ticketcheck_result"] = "within_exit_grace_period";
                            response["result"] = "ticketcheck_access_allowed";
                            response["access_allowed"] = "true";
                            response["result_description"] = "Within exit grace period";
                            return response;
                        } else {
                            if (entryType == 1) {
                                response["result_description"] = "Exit grace period exceeded";
                                response["result"] = "exit_grace_period_exceeded";
                            }
                            if (entryType == 2 && deviceType == 2) {
                                response["result_description"] = "Access expired";
                                response["result"] = "access_expired";
                                return response;
                            }
                        }
                    } else {
                        if (accessResult == "allow_access") {
                            if (cooperate_parker > 1 && short_term_entry_after_contract_parking_space_exceeded == 1)
                                response["result_description"] = "Unpaid.Corporate user enterd as shortterm user";
                            else
                                response["result_description"] = "Unpaid.Antipassback.Access user entered as shortterm";
                        } else
                            response["result_description"] = "Unpaid";
                        response["result"] = "unpaid";
                    }

                    startparkingFee = calculateParkingFee();
                    response["parking_fee"] = startparkingFee;
                    response["parking_fee_duration_minutes"] = parkingFeeDuration;
                    exitGrace = parkingFeeDuration * 60 - parkingDuration;


                    if (validationSeconds > 0) {
                        parkingDuration = parkingDuration - validationSeconds;
                        writeLog("openTransactionCheck", "Parking duration after validation: " + to_string(parkingDuration) + " S");
                        writeLog("openTransactionCheck", "Parking duration after validation: " + to_string(parkingDuration / 3600) + " H " + to_string((parkingDuration % 3600) / 60) + " M");
                    }
                    if ((validationSeconds > 0 && parkingDuration <= 0) || percentageValidation >= 100) {
                        response["ticketcheck_result"] = "within_validation_hours";
                        response["result"] = "ticketcheck_access_allowed";
                        response["access_allowed"] = "true";
                        response["result_description"] = "Validation Applied";
                        return response;
                    } else {
                        writeLog("openTransactionCheck", "calculate parking fee after validation hours");
                        startparkingFee = calculateParkingFee();
                    }
                    
                    coupon_percentage=0;
                    coupon_amount=0;
                    if(couponEnabled==1)
                        {
                        getCouponDiscounts();
                        response["coupon_percentage"]=coupon_percentage;
                        response["coupon_amount"]=coupon_amount;
                        response["valid_coupons"]=valid_coupon_id;
                        response["coupon_value"]=coupon_value;
                        }
                    
                    if(coupon_percentage>=100 ||(coupon_amount>0 && startparkingFee<=coupon_amount))
                        {
                        response["ticketcheck_result"] = "valid_coupon_applied";
                        response["result"] = "ticketcheck_access_allowed";
                        response["access_allowed"] = "true";
                        response["result_description"] = "coupon Applied";
                        return response;
                        }                   

                    if (deviceType >= 2 || walletEnabled == 1 || paymentExit == 1) {
                        calculateAmountToPay();
                        response["net_amount"] = parkingFee;
                        response["vat_amount"] = vatAmount;
                        response["gross_amount"] = grossAmount;
                        
                        if (grossAmount <= 0) {
                        response["exit_grace_time_remaining"] = to_string(exitGrace);
                        response["ticketcheck_result"] = "within_exit_grace_period";
                        response["result"] = "ticketcheck_access_allowed";
                        response["access_allowed"] = "true";
                        response["result_description"] = "Within exit grace period.Amount to pay =0";
                        return response;
                        }
                    }
                   
                    if (toString(response["access_allowed"]) == "false" && walletEnabled == 1) {
                        if (grossAmount > 0) {
                            Php::Value wallet = walletCheck();
                            if (deviceType == 2) {
                                response["access_allowed"] = toString(wallet["access_allowed"]);
                                if (toString(response["access_allowed"]) == "true") {
                                    response["result"] = "ticketcheck_access_allowed";
                                    string amount_in_points = wallet["amount_in_points"];
                                    response["gross_amount_in_points"] = amount_in_points;
                                    response["wallet_balance"] = toString(wallet["wallet_balance"]);
                                    response["result_description"] = toString(wallet["result_description"]);
                                    stmt = reportCon->createStatement();
                                    string validation_id = response["validation_id"];
                                    string category;
                                    if (entryType == 1)
                                        category = "StandardParkingFee";
                                    if (entryType == 3)
                                        category = "Reservation";
                                    query = "INSERT into revenue_payments(device_number,device_name,carpark_number,carpark_name,facility_number,parking_rate_label,parking_rate_name,entry_grace_period,exit_grace_period,vat_type,vat_percentage,ticket_id,entry_date_time,payment_date_time,max_exit_date_time,parking_duration,payment_category,parking_fee,vat_amount,gross_amount,amount_received,payment_type,validation_value,validation_id,entry_plate_number,exit_plate_number,wallet_points)VALUES('" + to_string(deviceNumber) + "','" + deviceName + "'," + to_string(carparkNumber) + ",'" + carpakName + "','" + to_string(facilityNumber) + "','" + parking_rate_label + "','" + parking_rate + "','" + entry_grace_period + "','" + exit_grace_period + "','" + vat_type + "','" + to_string(vat_percentage) + "','" + ticketId + "','" + entryDateTime + "',CURRENT_TIMESTAMP ,(now() + INTERVAL " + exit_grace_period_mins + " MINUTE),'" + parking_duration + "','" + category + "','" + to_string(parkingFee) + "','" + to_string(vatAmount) + "','" + to_string(grossAmount) + "','" + to_string(grossAmount) + "','E WALLET','" + validation_value + "','" + validation_id + "','" + plateNumber + "','" + plateNumber + "'," + amount_in_points + ")";
                                    stmt->executeUpdate(query);
                                } else {
                                    response["wallet_result"] = toString(wallet["result"]);
                                    response["wallet_result_description"] = toString(wallet["result_description"]);
                                    response["wallet_balance"] = toString(wallet["wallet_balance"]);
                                    response["wallet_expiry_date"] = toString(wallet["wallet_expiry_date"]);
                                }
                            } else {
                                if (toString(wallet["wallet_present"]) == "true") {
                                    response["user_id"] = toString(wallet["user_id"]);
                                    response["user_name"] = toString(wallet["user_name"]);
                                    response["wallet_expiry_date"] = toString(wallet["wallet_expiry_date"]);
                                    response["wallet_present"] = "true";
                                    response["wallet_usable"] = toString(wallet["wallet_usable"]);
                                    response["gross_amount_in_points"] = stod(toString(wallet["gross_amount_in_points"]));
                                    response["wallet_balance"] = stod(toString(wallet["wallet_balance"]));
                                    response["conversion_rate"] = stod(toString(wallet["conversion_rate"]));
                                } else {
                                    response["wallet_present"] = "false";
                                    response["wallet_usable"] = "false";
                                }
                                response["wallet_result_description"] = toString(wallet["result_description"]);
                                response["wallet_result"] = toString(wallet["result"]);
                            }
                        } else {
                            response["result_description"] = "Amount to pay=0";
                        }
                    }
                }
            }
        } else {
            if(accessResult!="" && accessResult!="not_in_access_whitelist")
                {
                if(accessResult=="allow_access" && no_entry_contract_parking_allow_exit==1)
                    response["access_allowed"]="true"; 
                else
                    {
                    response["result"]=accessResult;
                    response["result_description"]=accessResultDescription;
                    }
                }
            else
                {
                response["result"] = "already_exited";
                response["result_description"] = "Already exited";
                }                                                                                                     
        }
       
    }    catch (const std::exception& e) {
        writeException("openTransactionCheck", e.what());
        writeException("openTransactionCheck", query);
    }
    return response;
}



Php::Value exitTicketCheck() {
    Php::Value response;
    try {
        response["access_allowed"] = "false";
        response["coupon_ticket"]="false";
        reportCon = General.mysqlConnect(ReportingDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();
                    
            //blacklist
            if (ticketId.length() == 20)
                query = "SELECT * from parking_blacklist where ticket_id = '" + ticketId + "'";
            else
                query = "SELECT * from parking_blacklist where ticket_id like '%" + ticketId + "'";
            res = stmt->executeQuery(query);
            if (res->next()) 
                {
                response["result"] = "blacklisted";
                response["result_description"] = string(res->getString("blacklisting_description"));
                writeLog("exitTicketCheck", "Blacklisted");
                } 
            else 
                {
                writeLog("exitTicketCheck", "Not blacklisted");
                serverCon = General.mysqlConnect(ServerDB);
                response = openTransactionCheck(0);
                delete serverCon;
                }
                
            delete res;
            delete stmt;
            delete reportCon;
        }
    }    catch (const std::exception& e) {
        writeException("exitTicketCheck", e.what());
    }
    return response;
}

Php::Value exitPlateCheck() {
    Php::Value response;
    try {
        response["access_allowed"] = "false";
        reportCon = General.mysqlConnect(ReportingDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();
            serverCon = General.mysqlConnect(ServerDB);
            response = openTransactionCheck(0);
            delete serverCon;
        }
        delete res;
        delete stmt;
        delete reportCon;
    } catch (const std::exception& e) {
        writeException("exitPlateCheck", e.what());
    }
    return response;
}

void insertIntoPlateMismatch(string entry_plate_number,string exit_plate_number,string id1,string id2,string result,string description,string dismiss) {
    try {
        reportCon = General.mysqlConnect(ReportingDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();
            query="insert into plates_mismatch(date_time,device_number,device_name,entry_plate_number,exit_plate_number,ticket_id,entry_plate_captured_id,exit_plate_captured_id,result,result_description,dismiss)values(CURRENT_TIMESTAMP," + to_string(deviceNumber) + ",'" + deviceName + "','" +entry_plate_number + "','" + exit_plate_number + "','" + ticketId + "',"+id1+","+id2+",'"+result+"','"+description+"','"+dismiss+"')";
            stmt->executeUpdate(query);
            delete stmt;
            delete reportCon;
        }
    } catch (const std::exception& e) {
        writeException("insertIntoPlateMismatch", e.what());
        //writeException("insertIntoPlateMismatch",query);
    }
}


void insertIntoTicketCheck(string result, string description) {
    try {
        reportCon = General.mysqlConnect(DashboardDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();
            stmt->executeUpdate("insert into ticket_check(date_time,device_number,device_name,carpark_number,carpark_name,device_type,ticket_id,plate_number,result,facility_number,result_description)values(CURRENT_TIMESTAMP," + to_string(deviceNumber) + ",'" + deviceName + "'," + to_string(carparkNumber) + ",'" + carpakName + "'," + to_string(deviceType) + ",'" + ticketId + "','" + plateNumber + "','" + result + "','" + to_string(facilityNumber) + "','" + description + "')");
            delete stmt;
            delete reportCon;
        }
    } catch (const std::exception& e) {
        writeException("insertIntoTicketCheck", e.what());
    }
}

void insertIntoOpenTransaction(string result) {
    try {                        
        if(result=="allow_access")
            entryType=2;
        else if(result=="reservation_allow_access")
            entryType=3;
        else
            entryType=1;
            
        serverCon = General.mysqlConnect(ServerDB);
        setParkingRateConfiguration();
        delete serverCon;
                
        reportCon = General.mysqlConnect(ReportingDB);
        if (reportCon != NULL) {
            stmt = reportCon->createStatement();                                    
            query = "INSERT into open_transactions(device_name,device_number,ticket_id,carpark_number,facility_number,entry_date_time,plate_number,chip_utid,operation_mode,entry_grace_period,entry_type,plate_captured_id,carpark_name,movement_type) VALUES('" + deviceName + "'," + to_string(deviceNumber) + ",'" + ticketId + "'," + to_string(carparkNumber) + "," + to_string(facilityNumber) + ",CURRENT_TIMESTAMP,'" + plateNumber + "','','',  DATE_ADD(CURRENT_TIMESTAMP,INTERVAL " + to_string(entry_grace_period_minuts) + " MINUTE) ,"+to_string(entryType)+"," + to_string(plateCapturedId) + ",'" + carpakName + "',1)";
            
            stmt->executeUpdate(query);
            delete stmt;
            delete reportCon;
        }
    } catch (const std::exception& e) {
        writeException("insertIntoOpenTransaction", e.what());
    }
}

void getDeviceDetails() {
    try {
        serverCon = General.mysqlConnect(ServerDB);
        if (serverCon != NULL) {
            stmt = serverCon->createStatement();
            res = stmt->executeQuery("select * from system_devices where device_number=" + to_string(deviceNumber));
            if (res->next()) {
                deviceType = res->getInt("device_category");
                writeLog("deviceType", to_string(deviceType));
                carparkNumber = res->getInt("carpark_number");
                writeLog("carpark", to_string(carparkNumber));
                parkingZone = res->getString("parking_zone");
                writeLog("parkingZone", parkingZone);
                reservationEnabled = res->getInt("reservation_enabled");
                writeLog("reservationEnabled", to_string(reservationEnabled));
                accessEnabled = res->getInt("access_enabled");
                writeLog("accessEnabled", to_string(accessEnabled));
                
                deviceName = res->getString("device_name");
                writeLog("deviceName", deviceName);
                carpakName = res->getString("carpark_name");
                writeLog("carpakName", carpakName);
                cameraId = res->getInt("camera_id");
                writeLog("cameraId", to_string(cameraId));
                paymentExit = res->getInt("payment_enabled_exit");
                writeLog("paymentExit", to_string(paymentExit));
                anpr_mismatch_check = res->getInt("anpr_mismatch_check");
                writeLog("anpr_mismatch_check", to_string(anpr_mismatch_check));
                couponEnabled=res->getInt("coupon_enabled");
                writeLog("couponEnabled", to_string(couponEnabled));
               
            }
            delete res;            
            query = "select setting_name,setting_value from system_settings";        
            settings = stmt->executeQuery(query);
            while (settings->next()) {                
                if (settings->getString("setting_name") == "cloud_wallet_webservice")
                    wallet_webservice = settings->getString("setting_value");
                if (settings->getString("setting_name") == "validation_enabled")
                    validationEnabled = settings->getInt("setting_value");
                if (settings->getString("setting_name") == "wallet_enabled")
                    walletEnabled = settings->getInt("setting_value");
                if (settings->getString("setting_name") == "vat_type")
                    vat_type = settings->getString("setting_value");
                if (settings->getString("setting_name") == "vat_percentage")
                    vat_percentage = settings->getDouble("setting_value");                
                if (settings->getString("setting_name") == "facility_number")
                    facilityNumber = settings->getInt("setting_value");
                if (settings->getString("setting_name") == "facility_name")
                    facilityName = settings->getString("setting_value");                   
                if (settings->getString("setting_name") == "no_entry_contract_parking_allow_exit")
                    no_entry_contract_parking_allow_exit = settings->getInt("setting_value"); 
                if (settings->getString("setting_name") == "currency")
                    currency = settings->getString("setting_value");                                 
            }
        delete settings;

        writeLog( "walletEnabled", to_string(walletEnabled));
        writeLog( "wallet_webservice", wallet_webservice);
        writeLog( "validationEnabled", to_string(validationEnabled));
        writeLog( "vat_type", vat_type);
        writeLog( "vat_percentage", to_string(vat_percentage));        
        writeLog("facility",to_string(facilityNumber));
        writeLog("facilityName",facilityName);      
        writeLog("currency",currency);      
        writeLog("no_entry_contract_parking_allow_exit",to_string(no_entry_contract_parking_allow_exit));
        delete stmt;
        delete serverCon;
        }
    } catch (const std::exception& e) {
        writeException("getDeviceDetails", e.what());
    }
}

string convertToString(int number,int length,int hex)
    {
    stringstream ss;
    if(hex==1)        
		ss << setw(length) << setfill('0') <<std::hex << number;		
    else
        ss << setw(length) << setfill('0') << number;
    return ss.str();
    }

void generateTicketNumber()
    {
    string facility=convertToString(facilityNumber,6,0);							    		
    string hexCarpark= convertToString(carparkNumber,2,1);
    string hexDevice= convertToString(deviceNumber,2,1);
    string hexTicketType= "00";
    																				
    //number of seconds from 2018 jan 01 00:00:00 to now+1415926535
    struct tm tm;
    string startDate="2018-01-01 00:00:00";    

    strptime(startDate.c_str(),"%Y-%m-%d %H:%M:%S",&tm);		
    time_t t=mktime(&tm);

    time_t now=time(NULL);
    long seconds=difftime(now,t);

    seconds=seconds+1415926535;			
			
    std::stringstream ss;				
    ss << std::hex << seconds;
    std::string ticket(ss.str());									
    
    ticketId=facility+hexCarpark+hexDevice+hexTicketType+ticket;
    writeLog("ticketId",ticketId);     
    }

Php::Value parcxTicketCheck(Php::Parameters &params) {
    Php::Value response;
    Php::Value anprSettings;    
    try {
        Php::Value json = params[0];
        
        test=0;
        writeLog("parcxTicketCheck","--------------START-----------------");
        test=json["test"];
        writeLog("test", to_string(test));

        deviceNumber = json["device_number"];                
        writeLog("deviceNumber", to_string(deviceNumber));
        
        plateCapturedId = json["plate_captured_id"];
        writeLog("plateCapturedId", to_string(plateCapturedId));

        if (plateCapturedId > 0 || json["anpr_check_only"] == 1 || json["anpr_enable"] == 1)
            anprEnabled = 1;
        else
            anprEnabled = 0;
        writeLog("anprEnabled", to_string(anprEnabled));

        deductMoneyFromWallet = json["deduct_money_from_wallet"];
        if (json["qrcode_access_enabled"] == 1 || json["anpr_access_enabled"] == 1 || json["access_enabled"] == 1)
            accessEnabled = 1;
        writeLog("accessEnabled", to_string(accessEnabled));
        
        getDeviceDetails();        

        if (deductMoneyFromWallet == 1) {
            grossAmount = json["amount_to_pay"];
            userId = toString(json["user_id"]);
            response = updateWalletPayment();
        } else {
            
            plateCapturedInterval = json["plate_captured_interval"];
            writeLog("plateCapturedInterval", to_string(plateCapturedInterval));
            anprSettings = "";
            ticketId = toString(json["ticket_id"]);
            writeLog("ticketId", ticketId);
            plateNumber = toString(json["plate_number"]);
            writeLog("plateNumber", plateNumber);            
            plateType = "";
            plateCountry = "";
            plateArea = "";

            if (anprEnabled == 1) {                  
                Php::Value plate_details = AnprObj.getPlateDetails(cameraId, plateCapturedInterval, plateCapturedId);
                response = plate_details;
                if (toString(plate_details["result"]) == "plate_available") {
                    plateNumber = toString(plate_details["plate_number"]);
                    plateArea = toString(plate_details["plate_area"]);                    
                    plateCountry = toString(plate_details["plate_country"]);
                    plateType = toString(plate_details["plate_type"]);
                    plateCapturedId=plate_details["plate_captured_id"];
                } 
            } 

            response["access_allowed"] = "false";
            response["ticket_id"] = ticketId;
            response["plate_number"] = plateNumber;
            response["open_transaction_check"] = "false";
            response["whitelist_present"]="false";
            accessExpiry="";
            accessResult="";     
            if(couponEnabled==1 && deviceType!=1)
                {
                coupon_array=toString(json["coupon_id"]);   
                writeLog("coupon_array", coupon_array);
                
                current_coupon_id="";                
                Php::Value coupon =couponCheck();
                for (auto &iter : coupon)
                    response[iter.first] = iter.second;    
                string coupon_ticket=response["coupon_ticket"];                
                if(coupon_ticket=="true")
                    {
                    string result=response["result"];
                    if(result=="valid_coupon")
                        {                        
                        if(toString(json["coupon_ticket_id"])!="")
                            {
                            current_coupon_id=ticketId;                                
                            ticketId=toString(json["coupon_ticket_id"]);
                            writeLog("current_coupon_id",current_coupon_id);
                            writeLog("ticketId",ticketId);
                            }
                        else 
                            return response;
                        }
                    else
                        return response;
                    }
                                
                }
            if (accessEnabled == 1) {
                cooperate_parker = 0;
                short_term_entry_after_contract_parking_space_exceeded = 0;
                writeLog("qrcodeAccessEnabled", "Access check");
                Php::Value whitelist_details = AccessObj.checkAccess(ticketId, parkingZone, carparkNumber, facilityNumber, deviceType, plateNumber, plateType, plateArea, plateCountry, anprSettings);
                for (auto &iter : whitelist_details)
                    response[iter.first] = iter.second;
                accessExpiry = toString(whitelist_details["expiry_date"]);
                accessResult = toString(whitelist_details["result"]);
                accessResultDescription = toString(whitelist_details["result_description"]);

                response["whitelist_result"] = accessResult;
                response["whitelist_result_description"] = accessResultDescription;

                cooperate_parker = response["cooperate_parker"];
                short_term_entry_after_contract_parking_space_exceeded = response["short_term_entry_after_contract_parking_space_exceeded"];
                //if(toString(response["access_allowed"])=="true" && cooperate_parker>1 && short_term_entry_after_contract_parking_space_exceeded==1 && deviceType!=1)

                if (toString(response["access_allowed"]) == "true" && deviceType != 1 ) {                                        
                    Php::Value ticket_details = exitPlateCheck();
                    for (auto &iter : ticket_details)
                        response[iter.first] = iter.second;

                    string result = response["result"];
                    string result_description = response["result_description"];
                    insertIntoTicketCheck(result, result_description);
                    writeLog("parcxTicketCheck", "Result: " + result + "\tDescription:" + result_description + "\n");
                    writeLog("parcxTicketCheck","--------------END-----------------"); 
                    return response;
                }

            }


            if (toString(response["access_allowed"]) == "false" && reservationEnabled == 1) {                
                entryType=3;
                serverCon = General.mysqlConnect(ServerDB);
                setParkingRateConfiguration();
                delete serverCon;
                writeLog("reservationEnabled", "Reservation check");
               
                Php::Value reservation_details = ReservationObj.checkReservation(ticketId, entry_grace_period_minuts, exit_grace_period_minuts, carparkNumber, facilityNumber, deviceType, plateNumber, plateType, plateArea, plateCountry, anprSettings);
                for (auto &iter : reservation_details)
                    response[iter.first] = iter.second;

                reservationExpiry = toString(reservation_details["max_exit_grace_period"]);
                reservationFee = reservation_details["reservation_fee"];
                reservationResult = toString(reservation_details["result"]);
                reservationResultDescription = toString(reservation_details["result_description"]);
                reservationStart = toString(reservation_details["parking_from"]);

                response["reservation_result"] = reservationResult;
                response["reservation_result_description"] = reservationResultDescription;
            }

            if (toString(response["access_allowed"]) == "false" && deviceType == 1) {
                response["result"] = "shortterm_entry";
                response["result_description"] = "Shortterm Entry";
            }
            if (toString(response["access_allowed"]) == "true" && deviceType == 3) {
                reportCon = General.mysqlConnect(ReportingDB);
                if (reportCon != NULL) {
                    stmt = reportCon->createStatement();
                    Php::Value entry_details = openTransactionCheck(1);
                    for (auto &iter : entry_details)
                        response[iter.first] = iter.second;
                }
                delete res;
                delete stmt;
                delete reportCon;
            }

            if (toString(response["access_allowed"]) == "false" && deviceType != 1) {
                
                offlinePercentageValidation = json["offline_validation_percentage"];
                offlineTimeValidation = json["offline_validation_hours"];

                writeLog("offlinePercentageValidation", to_string(offlinePercentageValidation));
                writeLog("offlineTimeValidation", to_string(offlineTimeValidation));

                if (ticketId != "")//ticket check
                {
                    writeLog("exitTicketCheck", "Exit ticket check: " + ticketId);
                    Php::Value ticket_details = exitTicketCheck();
                    for (auto &iter : ticket_details)
                        response[iter.first] = iter.second;
                } else if (plateNumber != "") //plate check
                {
                    writeLog("exitPlateCheck", "Exit plate check: " + plateNumber);
                    Php::Value ticket_details = exitPlateCheck();
                    for (auto &iter : ticket_details)
                        response[iter.first] = iter.second;
                }

                if (toString(response["result"]) == "ticketcheck_access_allowed" && deviceType > 2)
                    response["result"] = toString(response["ticketcheck_result"]);                
            } else
                writeLog("parcxTicketCheck", "No exit ticket check");
        }

        string result = response["result"];
        string result_description = response["result_description"];
        if(test==0)
            insertIntoTicketCheck(result, result_description);
        else
            {            
            if(deviceType==1)
                {
                int create_entry=json["add_test_entry"];                
                if(create_entry==1)
                    {
                    ticketId=toString(response["ticket_id"]);
                    plateNumber = toString(response["plate_number"]);
                    if(ticketId=="")
                        {
                        generateTicketNumber();
                        response["ticket_id"]=ticketId;
                        }
                    insertIntoOpenTransaction(result);
                    }
                }
            insertIntoTicketCheck(result, "Test: "+result_description);                        
            }

        writeLog("parcxTicketCheck", "Result: " + result + "\tDescription:" + result_description);
        
        string open_transaction_check=response["open_transaction_check"];
        
        if (toString(response["access_allowed"]) == "true" && deviceType !=1 && anprEnabled == 1 && test==0 && open_transaction_check=="true") //anpr plate mismatch
            {
                string entry_plate = response["entry_plate_number"];
                string current_plate = response["current_plate_number"];
                
                writeLog("parcxTicketCheck", "Entry plate: " + entry_plate + " Current Plate:" + current_plate);

                if ( (entry_plate.length() >0 && current_plate.length() >0 && entry_plate != current_plate)||(plateCapturedId>0 ))
                    {
                    insertIntoTicketCheck("plate_mismatch", "Plate mismatch");
                    string id1=response["entry_plate_captured_id"];
                    string id2=response["plate_captured_id"];
                    if(id1=="")
                        id1="0";
                    string dismiss="1";
                    if(anpr_mismatch_check==1 && deviceType==2)
                        {
                        dismiss="0";  
                        response["result"]="plate_mismatch";
                        response["result_description"]="Plate mismatch.Entry Plate: "+entry_plate+" Current Plate:"+current_plate; 
                        writeLog("parcxTicketCheck", "Result:plate_mismatch\tDescription:Plate mismatch.Entry Plate: "+entry_plate+" Current Plate:"+current_plate); 
                        }
                    insertIntoPlateMismatch(entry_plate,current_plate,id1,id2,result,result_description,dismiss);
                    
                    }                    
            }

        
        
     writeLog("parcxTicketCheck","--------------END-----------------");   
    } catch (exception &e) {
        writeException("parcxTicketCheck", e.what());
    }
    return response;
}

extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_TicketCheck", "1.0");
        extension.add<parcxTicketCheck>("parcxTicketCheck");
        return extension;
    }
}
