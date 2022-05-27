#include "PX_GeneralOperations.h"
#include "PX_Access.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>
#include<sstream>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"
#define dayFormat "%A"
#define hourFormat "%H"

using namespace std;
extern GeneralOperations General;
extern string query;

void writeAccessLog(string function,string message)
    {
    General.writeLog("Services/PXTicketCheck/ApplicationLog-PX-TicketCheck-"+General.currentDateTime("%Y-%m-%d"),function,message);    
    }

void writeAccessException(string function,string message)
    {
    General.writeLog("Services/PXTicketCheck/ExceptionLog-PX-TicketCheck-"+General.currentDateTime("%Y-%m-%d"),function,message); 
	writeAccessLog(function,"Exception: "+message);   
    }


bool presentInString(string str,string find)
    {
    if(find.length()==0)
        return true;    
    stringstream s_stream(str);
    string substr;
    while(s_stream.good()) 
        {       
        getline(s_stream, substr, ',');         
            if(substr==find)
                return true;
        }
    return false;
    } 

bool presentInTimeslot(string str,string find)
    {
    if(find.length()==0)
        return true;    
    stringstream s_stream(str);
    string slot;
    while(s_stream.good()) 
        {       
        getline(s_stream, slot, ',');         
            if(slot.substr(0, 2)==find)
                return true;
        }
    return false;
    } 

Php::Value checkAccess2(string ticketId,string parkingZone,int carpark,int facility,int deviceType,string plateNumber,string plateType,string plateArea,string plateCountry,Php::Value anprSettings)
    {
    writeAccessLog("checkAccess","TicketID:"+ticketId+"\tPlateNumber: "+plateNumber);	    
    Php::Value response;
	response["whitelist_present"]="false";	
	response["access_allowed"]="false";	    
    try
        {
        sql::Connection *serverCon;
        sql::Statement *stmt;
        sql::ResultSet *res;                   
        serverCon=General.mysqlConnect(ServerDB);
        if(serverCon!=NULL)
            {
            if(plateNumber!="")
                query="select * from access_whitelist where plate_number='"+plateNumber+"'";		                
            else
                query="select * from access_whitelist where  ticket_id='"+ticketId+"'";
                                					
            stmt = serverCon->createStatement();
            res=stmt->executeQuery(query);
            if(res->next())
                {
                writeAccessLog("checkAccess","Present in access whitelist");
                plateNumber=res->getString("plate_number");
                ticketId=res->getString("ticket_id");
                response["plate_number"]=string(res->getString("plate_number"));	
                response["tag_serial"]=string(res->getString("tag_serial"));
                response["tag"]=string(res->getString("tag"));
                response["tag_tid"]=string(res->getString("tag_tid"));
                response["ticket_id"]=string(res->getString("ticket_id"));
                response["whitelist_present"]="true";
                int cooperate_parker=res->getInt("corporate_parker");
                response["cooperate_parker"]=cooperate_parker;
                response["entry_type_contract_parking_space_exceeded"]=2;
			
                
                string expiry_date=res->getString("validity_expiry_date");
                string start_date=res->getString("validity_start_date");
                string currentDate=General.currentDateTime(dateFormat);
                string zone=res->getString("access_zones");
                string carparkNumber=res->getString("carpark_number");
                response["expiry_date"]=expiry_date;
                if(res->getInt("status")==0)
                    {
                    response["result_description"]="Access disabled";    
                    response["result"]="access_disabled";
                    }
                else if(!presentInString(parkingZone,zone))                 
                    {
                    response["result_description"]="Invalid parking zone";    
                    response["result"]="invalid_access_parking_zone";
                    }
                else if(!presentInString(carparkNumber,to_string(carpark)))                  
                    {
                    response["result_description"]="Invalid access carpark";    
                    response["result"]="invalid_access_carpark";
                    }
                else if(facility!=res->getInt("facility_number"))
                    {
                    response["result_description"]="Invalid access facility";    
                    response["result"]="invalid_access_facility";
                    }
                else if(expiry_date.compare(currentDate)<0)
                    {
                    response["result_description"]="Access expired";    
                    response["result"]="access_expired";
                    }
                else if(start_date.compare(currentDate)>0)
                    {
                    response["result_description"]="Access not started";    
                    response["result"]="access_not_started";
                    }  
                else if(res->getInt("antipassback_enabled")==1 && deviceType<3)
                    {                    
                    query="select * from parcx_reporting.open_transactions where  ticket_id='"+ticketId+"'";
                    res=stmt->executeQuery(query);
		    if(res->next())
                        {
                        if(deviceType==1)
                            {
                            response["result_description"]="Entry exists";    
                            response["result"]="antipassback_entry_exist";   
                            }
                        else if(res->getInt("movement_type")==2)
                            {
                            response["result_description"]="Already Exited";    
                            response["result"]="antipassback_already_exited";      
                            }
                        else
                            {
                            response["access_allowed"]="true";		
                            response["result"]= "allow_access";		
                            response["result_description"]="Access allowed.whitelist available";
                            }                                                
                        }
                    else
                        {
                        if(deviceType==2)
                            {
                            response["result_description"]="Entry not exists";    
                            response["result"]="antipassback_entry_not_exist";   
                            }
                        else
                            {
                            response["access_allowed"]="true";		
                            response["result"]= "allow_access";		
                            response["result_description"]="Access allowed.whitelist available";
                            }                                                   
                        }
                    
                    }              
                else							
                    {
                    response["access_allowed"]="true";		
                    response["result"]= "allow_access";		
                    response["result_description"]="Access allowed.whitelist available";
                    }
                    
                writeAccessLog("checkAccess","cooperate_parker : "+to_string(cooperate_parker));
                response["short_term_entry_after_contract_parking_space_exceeded"]=0;
                
                string access_allowed=response["access_allowed"];
                                        
                if(access_allowed=="true" && cooperate_parker>1)
                    {
                    query="SELECT setting_value from system_settings where setting_name='short_term_entry_after_contract_parking_space_exceeded'";
                    res=stmt->executeQuery(query);
                    res->next();
                    response["short_term_entry_after_contract_parking_space_exceeded"]=int(res->getInt("setting_value"));
                    writeAccessLog("checkAccess","short_term_entry_after_contract_parking_space_exceeded : "+string(res->getString("setting_value")));

                    if(res->getInt("setting_value")==1 && deviceType==1)
                        {        
                        query="select * from cooperate_users where  user_id="+to_string(cooperate_parker);
                        res=stmt->executeQuery(query);
                        if(res->next())
                            {
                            int parking_spaces=res->getInt("parking_spaces");
                            writeAccessLog("checkAccess","Cooperate parker under "+to_string(cooperate_parker)+". Alloted spaces : "+to_string(parking_spaces));
                            query="SELECT count(*) as count FROM parcx_server.access_whitelist a,parcx_reporting.open_transactions b where a.ticket_id=b.ticket_id  and a.plate_number=b.plate_number and b.entry_type=2 and a.corporate_parker="+to_string(cooperate_parker);
                            res=stmt->executeQuery(query);
                            res->next();
                            int occupied=res->getInt("count");
                            writeAccessLog("checkAccess","Currently occupied for the cooperate user :"+to_string(occupied));
                            if(occupied>=parking_spaces)
                                {                                    
                                writeAccessLog("checkAccess","short term access after cooperate parking space occupied is enabled");
                                response["access_allowed"]="false";		
                                response["result"]= "contract_parking_spaces_occupied";		
                                response["result_description"]="Contract parking spaces occupied";                                        
                                }
                            }                        
                        }   
                    }                 			                                                    
                }
            else
                {			
                response["result"]="not_in_access_whitelist";
                response["result_description"]="Not in access whitelist";
                }
            string output=	response["result"];
            writeAccessLog("checkAccess",output+"\n");
            serverCon->close();       
            delete serverCon;   
            }
        }
    catch(const std::exception& e)
        {
            writeAccessException("checkAccess",e.what());
        }
    return response;        
    }


Php::Value Access::checkAccess(string ticketId,string parkingZone,int carpark,int facility,int deviceType,string plateNumber,string plateType,string plateArea,string plateCountry,Php::Value anprSettings)
    {
    writeAccessLog("checkAccess","TicketID:"+ticketId+"\tPlateNumber: "+plateNumber);	    
    Php::Value response;
    response["whitelist_present"]="false";	
    response["access_allowed"]="false";	    
    try
        {
        sql::Connection *serverCon;
        sql::Statement *stmt;
        sql::ResultSet *res;                   
        serverCon=General.mysqlConnect(ServerDB);
        if(serverCon!=NULL)
            {
            if(plateNumber!="")
                query="SELECT a.*,corporate_parker,active,whitelist_group FROM access_whitelist_tickets a,access_whitelist_customers b where a.ticket_id=b.ticket_id and plate_number='"+plateNumber+"'";		                
            else
                query="SELECT a.*,corporate_parker,active,whitelist_group FROM access_whitelist_tickets a,access_whitelist_customers b where a.ticket_id=b.ticket_id and  a.ticket_id='"+ticketId+"'";
                                					
            stmt = serverCon->createStatement();
            res=stmt->executeQuery(query);
            if(res->next())
                {
                writeAccessLog("checkAccess","Present in access whitelist");
                plateNumber=res->getString("plate_number");
                ticketId=res->getString("ticket_id");
                response["plate_number"]=string(res->getString("plate_number"));	                
                response["tag"]=string(res->getString("tag"));                
                response["ticket_id"]=string(res->getString("ticket_id"));
                response["whitelist_present"]="true";
                int cooperate_parker=res->getInt("corporate_parker");
                response["cooperate_parker"]=cooperate_parker;
                response["entry_type_contract_parking_space_exceeded"]=2;
			
                
                string expiry_date=res->getString("validity_expiry_date");
                string start_date=res->getString("validity_start_date");
                string currentDate=General.currentDateTime(dateFormat);                
                response["expiry_date"]=expiry_date;
                string whitelist_group=res->getString("whitelist_group");
                if(res->getInt("status")==0 || res->getInt("active")==0)
                    {
                    response["result_description"]="Access disabled";    
                    response["result"]="access_disabled";
                    }
                else if(expiry_date.compare(currentDate)<0)
                    {
                    response["result_description"]="Access expired";    
                    response["result"]="access_expired";
                    }
                else if(start_date.compare(currentDate)>0)
                    {
                    response["result_description"]="Access not started";    
                    response["result"]="access_not_started";
                    }
                else if(whitelist_group!="")
                    {
                    //check whitelist group policy
                    writeAccessLog("checkAccess","whitelist_group : "+whitelist_group);
                    string zone,carparkNumber,valid_days,valid_time_slot;
                    string currentDay=General.currentDateTime(dayFormat);
                    string currentHour=General.currentDateTime(hourFormat);
                    query="select * from access_whitelist_group_policy where id in("+whitelist_group+")";
                    res=stmt->executeQuery(query);
                    while(res->next())
                        {
                        zone=res->getString("parking_zone");
                        carparkNumber=res->getString("carpark_number");
                        valid_days=res->getString("validity_days");
                        whitelist_group=res->getString("policy_name");
                        valid_time_slot=res->getString("validity_timeslot");                                                                        
                        
                        if(res->getInt("status")==0)
                            {
                            response["result_description"]="Access disabled";    
                            response["result"]="access_disabled";
                            }
                        else if(facility!=res->getInt("facility_number"))
                            {
                            response["result_description"]="Invalid access facility";    
                            response["result"]="invalid_access_facility";
                            }
                        else if(!presentInString(carparkNumber,to_string(carpark)))                  
                            {
                            response["result_description"]="Invalid access carpark";    
                            response["result"]="invalid_access_carpark";
                            } 
                        else if(!presentInString(parkingZone,zone))                 
                            {
                            response["result_description"]="Invalid parking zone";    
                            response["result"]="invalid_access_parking_zone";
                            }
                        else if(!presentInString(valid_days,currentDay))                 
                            {
                            response["result_description"]="Invalid day";    
                            response["result"]="invalid_access_day";
                            }
                        else if(!presentInTimeslot(valid_time_slot,currentHour))                 
                            {
                            response["result_description"]="Invalid time slot";    
                            response["result"]="invalid_access_timeslot";
                            }
                        else if(res->getInt("antipassback_enabled")==1 && deviceType<3)
                            {                    
                            query="select * from parcx_reporting.open_transactions where  ticket_id='"+ticketId+"'";
                            res=stmt->executeQuery(query);
                            if(res->next())
                                {
                                if(deviceType==1)
                                    {
                                    response["result_description"]="Entry exists";    
                                    response["result"]="antipassback_entry_exist";   
                                    }
                                else if(res->getInt("movement_type")==2)
                                    {
                                    response["result_description"]="Already Exited";    
                                    response["result"]="antipassback_already_exited";      
                                    }
                                else
                                    {
                                    response["access_allowed"]="true";		
                                    response["result"]= "allow_access";		
                                    response["result_description"]="Access allowed.whitelist available";
                                    }                                                
                                }
                            else
                                {
                                if(deviceType==2)
                                    {
                                    response["result_description"]="Entry not exists";    
                                    response["result"]="antipassback_entry_not_exist";   
                                    }
                                else
                                    {
                                    response["access_allowed"]="true";		
                                    response["result"]= "allow_access";		
                                    response["result_description"]="Access allowed.whitelist available";
                                    }                                                   
                                }

                            }
                        else							
                            {
                            response["access_allowed"]="true";		
                            response["result"]= "allow_access";		
                            response["result_description"]="Access allowed.whitelist available";
                            break;
                            }
                        string result=response["result"];
                        writeAccessLog("checkAccess","whitelist_group:"+whitelist_group+" result : "+result);
                        }                                                         
                    //End whitelist group policy
                    }
                else
                    {
                    response["result"]= "no_vald_group";		
                    response["result_description"]="Not included in any group";
                    }
                
            

                    
                writeAccessLog("checkAccess","cooperate_parker : "+to_string(cooperate_parker));
                response["short_term_entry_after_contract_parking_space_exceeded"]=0;
                
                string access_allowed=response["access_allowed"];
                                        
                if(access_allowed=="true" && cooperate_parker>1)
                    {
                    query="SELECT setting_value from system_settings where setting_name='short_term_entry_after_contract_parking_space_exceeded'";
                    res=stmt->executeQuery(query);
                    res->next();
                    response["short_term_entry_after_contract_parking_space_exceeded"]=int(res->getInt("setting_value"));
                    writeAccessLog("checkAccess","short_term_entry_after_contract_parking_space_exceeded : "+string(res->getString("setting_value")));

                    if(res->getInt("setting_value")==1 && deviceType==1)
                        {        
                        query="select * from cooperate_users where  user_id="+to_string(cooperate_parker);
                        res=stmt->executeQuery(query);
                        if(res->next())
                            {
                            int parking_spaces=res->getInt("parking_spaces");
                            writeAccessLog("checkAccess","Cooperate parker under "+to_string(cooperate_parker)+". Alloted spaces : "+to_string(parking_spaces));
                            query="SELECT count(*) as count FROM parcx_server.access_whitelist_tickets a,parcx_reporting.open_transactions b,parcx_server.access_whitelist_customers c where a.ticket_id=b.ticket_id and b.ticket_id=c.ticket_id  and a.plate_number=b.plate_number and b.entry_type=2 and c.corporate_parker="+to_string(cooperate_parker);
                            res=stmt->executeQuery(query);
                            res->next();
                            int occupied=res->getInt("count");
                            writeAccessLog("checkAccess","Currently occupied for the cooperate user :"+to_string(occupied));
                            if(occupied>=parking_spaces)
                                {                                    
                                writeAccessLog("checkAccess","short term access after cooperate parking space occupied is enabled");
                                response["access_allowed"]="false";		
                                response["result"]= "contract_parking_spaces_occupied";		
                                response["result_description"]="Contract parking spaces occupied";                                        
                                }
                            }                        
                        }   
                    }                 			                                                    
                }
            else
                {			
                response["result"]="not_in_access_whitelist";
                response["result_description"]="Not in access whitelist";
                }
            string output=	response["result"];
            writeAccessLog("checkAccess",output+"\n");
            serverCon->close();       
            delete serverCon;   
            }
        }
    catch(const std::exception& e)
        {
            writeAccessException("checkAccess",e.what());
        }
    return response;        
    }
