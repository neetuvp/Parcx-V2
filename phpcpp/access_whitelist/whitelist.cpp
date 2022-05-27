#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/driver.h>
#include <cppconn/prepared_statement.h>
#include <cppconn/resultset_metadata.h>
#include<phpcpp.h>

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"
#define ticketFormat "%Y%m%d%H%M%S"

using namespace std;
GeneralOperations General;
sql::Connection *con,*dcon;;
sql::Statement *stmt;
sql::ResultSet *res;
sql::PreparedStatement *prep_stmt;
string query;
string previousData;
bool facilityFlag,carparkFlag,deviceFlag;
int carpark_number,facility_number,device_number,device_type;

void writeLog(string function,string message)
    {
    General.writeLog("WebApplication/ApplicationLogs/PX-WhitelistSettings-"+General.currentDateTime("%Y-%m-%d"),function,message);    
    }

void writeException(string function,string message)
    {
    General.writeLog("WebApplication/ExceptionLogs/PX-WhitelistSettings-"+General.currentDateTime("%Y-%m-%d"),function,message);     
    writeLog(function,"Exception: "+message);   
    }

string ToString(Php::Value param)
	{
	string value=param;
	return value;
	}


long accessId()
	{
	struct tm tm;
	string startDate="2020-01-01 00:00:00";			
			
	strptime(startDate.c_str(),"%Y-%m-%d %H:%M:%S",&tm);		
	time_t t=mktime(&tm);
						
	time_t now=time(NULL);
	long seconds=difftime(now,t);
	return seconds;					
	}

Php::Value insertUpdateWhitelistPolicy(Php::Value json)
    {
    string message = "Failed";    
    try
        {        
        con= General.mysqlConnect(ServerDB);   
        stmt=con->createStatement();        
        string id=json["id"];  
        string start_date=json["start_date"];
        string expiry_date=json["expiry_date"];
        string validity_days=json["validity_days"];
        string validity_timeslot=json["validity_timeslot"];
        string facility_number=json["facility_number"];
        string carpark_number=json["carpark_number"];  
        string parking_zone=json["parking_zone"];  
        string policy_name=json["policy_name"];        
        string description=json["description"];   
        string users=json["users"];
        if(id=="")
            query="insert into access_whitelist_group_policy(policy_name,description,start_date,expiry_date,validity_days,validity_timeslot,facility_number,carpark_number,parking_zone,status)values('"+policy_name+"','"+description+"','"+start_date+"','"+expiry_date+"','"+validity_days+"','"+validity_timeslot+"',"+facility_number+",'"+carpark_number+"','"+parking_zone+"',1)";
        else
            query="update access_whitelist_group_policy set policy_name='"+policy_name+"',description='"+description+"',start_date='"+start_date+"',expiry_date='"+expiry_date+"',validity_days='"+validity_days+"',validity_timeslot='"+validity_timeslot+"',facility_number="+facility_number+",carpark_number='"+carpark_number+"',parking_zone='"+parking_zone+"' where id="+id;
        stmt->executeUpdate(query);
        
        
        if(id=="")
            {
            query="select id from access_whitelist_group_policy ORDER BY id DESC LIMIT 1 ";
            res=stmt->executeQuery(query);
            if(res->next())                
                id=res->getString("id");
            delete res;                                
            
            query="update access_whitelist_customers set whitelist_group = CONCAT_WS(',', whitelist_group, '"+id+"') where id in("+users+")";
            stmt->executeUpdate(query);
            }
        else
            {
            query="update  access_whitelist_customers set whitelist_group= TRIM(BOTH ',' FROM REPLACE(REPLACE(whitelist_group, '"+id+"', ''), ',,', ','))";
            stmt->executeUpdate(query);
            query="update access_whitelist_customers set whitelist_group = CONCAT_WS(',', whitelist_group, '"+id+"') where id in("+users+")";
            stmt->executeUpdate(query);
            }
        
        
        delete stmt;
        delete con;   
        message="Successfull";
        }     
    catch(const std::exception& e)
        {
        writeException("insertUpdateWhitelistPolicy",e.what());
        writeException("insertUpdateWhitelistPolicy",query);
        }
    return message;
    }

Php::Value insertUpdateWhitelist(Php::Value json)
    {
    string msg = "Failed";    
    try
        {        
        con= General.mysqlConnect(ServerDB);   
        string id=json["id"];
        string report_id=json["report_id"];
        if(id=="")    
            {            
            prep_stmt = con->prepareStatement("insert into access_whitelist_tickets(plate_number,tag,validity_start_date,validity_expiry_date,antipassback_enabled,ticket_id,access_id,status) values(?,?,?,?,?,?,'"+to_string(accessId())+"',1)");                        
            }
        else
            {
            prep_stmt = con->prepareStatement("update access_whitelist_tickets set cloud_upload_status=0,plate_number=?,tag=?,validity_start_date=?,validity_expiry_date=?,antipassback_enabled=?,ticket_id=? where access_whitelist_id=?");    
            prep_stmt->setString(7, id);       
            }
        
        prep_stmt->setString(1, ToString(json["plate_number"]));
        prep_stmt->setString(2, ToString(json["tag"]));        
        prep_stmt->setString(3, ToString(json["start_date"]));
        prep_stmt->setString(4, ToString(json["expiry_date"]));        
        prep_stmt->setString(5, ToString(json["anti_passback"]));
        prep_stmt->setString(6, ToString(json["ticket_id"]));
       
        
                
        if (!prep_stmt->execute())            
            {
            msg = "Successfull"; 
            delete prep_stmt;                                                                      
            delete con;               	                
            if(report_id!="0")
                {
                con= General.mysqlConnect(ReportingDB);       
                prep_stmt = con->prepareStatement("update parking_movements_access set whitelist_added=1 where tag =?");    
                prep_stmt->setString(1, ToString(json["tag"]));    
                prep_stmt->execute(); 
                delete prep_stmt;                                                                      
		        delete con;
                }
            }
              
        }
    catch(const std::exception& e)
        {
        writeException("insertUpdateWhitelist",e.what());
        }
    return msg;
    }

Php::Value insertUpdateCustomers(Php::Value json)
    {
    string msg = "Failed";    
    try
        {        
        con= General.mysqlConnect(ServerDB);   
        string ticket_id=json["ticket_id"];        
        if(ticket_id=="")    
            {   
            ticket_id=General.currentDateTime(ticketFormat);
            prep_stmt = con->prepareStatement("insert into access_whitelist_customers(customer_name,country,personalized_message_line1,personalized_message_line2,company_name,active,ticket_id) values(?,?,?,?,?,1,?)");            
            
            }
        else
            {
            prep_stmt = con->prepareStatement("update access_whitelist_customers set cloud_upload_status=0,customer_name=?,country=?,personalized_message_line1=?,personalized_message_line2=?,company_name=? where ticket_id=?");                
            }
                                    
        prep_stmt->setString(1, ToString(json["customer_name"]));        
        prep_stmt->setString(2, ToString(json["country"]));        
        prep_stmt->setString(3, ToString(json["message1"]));
        prep_stmt->setString(4, ToString(json["message2"]));
        prep_stmt->setString(5, ToString(json["company_name"]));
        prep_stmt->setString(6, ticket_id);
        
                
        if (!prep_stmt->execute())            
            {
            msg = "Successfull"; 
            delete prep_stmt;                                                                      
            delete con;               	                            
            }              
        }
    catch(const std::exception& e)
        {
        writeException("insertUpdateCustomers",e.what());
        }
    return msg;
    }

Php::Value enableDisable(string table,string id_field,string status_field,Php::Value json)
    {
    string msg = "Failed";    
    try
        {
        con= General.mysqlConnect(ServerDB); 
        prep_stmt = con->prepareStatement("update "+table+" set "+status_field+"=? where "+id_field+"=?");
        prep_stmt->setString(1, ToString(json["status"]));
        prep_stmt->setString(2, ToString(json["id"]));       
        if (!prep_stmt->execute())
				msg = "Successfull";	
		delete prep_stmt;
		delete con;       
        }
    catch(const std::exception& e)
        {
        writeException("enableDisable",e.what());
        }
    return msg;
    }

void accessCustomerOptions()
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from access_whitelist_customers");
        while(res->next())
            {
            Php::out << "<div class='form-group custom-control custom-checkbox'>"<<endl;                                
            Php::out << "<input type='checkbox' class='custom-control-input' id='"<<res->getInt("id")<<"'>"<<endl;
            Php::out << "<label class='custom-control-label' for='"<<res->getInt("id")<<"'>"<<res->getString("customer_name")<<"</label>"<<endl;
            Php::out << "</div>"<<endl;
            }
        delete res;    
        delete stmt;
        delete con;                            
        }
    catch(const std::exception& e)
        {
        writeException("accessCustomerOptions",e.what());
        }
    }

void showAccessWhitelistList(int type,string ticket_id)
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        if(type==0)
            res=stmt->executeQuery("select a.*,customer_name from access_whitelist_tickets a,access_whitelist_customers b where a.ticket_id=b.ticket_id order by access_whitelist_id desc");
        else if(type==1)
            res=stmt->executeQuery("select a.*,customer_name from access_whitelist_tickets a,access_whitelist_customers b where a.ticket_id=b.ticket_id and validity_expiry_date  BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) order by validity_expiry_date asc");
        else if(type==2)
            res=stmt->executeQuery("select a.*,customer_name from access_whitelist_tickets a,access_whitelist_customers b where a.ticket_id=b.ticket_id and validity_expiry_date  < CURDATE() order by validity_expiry_date asc");
        else
            res=stmt->executeQuery("select * from access_whitelist_tickets where ticket_id='"+ticket_id+"'");
        if(res->rowsCount()>0)
            {            
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            if(type!=3)
                Php::out<<"<th>Customer Name</th>"<<endl;
            Php::out<<"<th>Plate Number</th>"<<endl;      
            if(type!=3)
                Php::out<<"<th>Ticket Id</th>"<<endl;
            Php::out<<"<th>Tag</th>"<<endl;
            Php::out<<"<th>AntiPassBack</th>"<<endl;
            Php::out<<"<th>Valid From</th>"<<endl;
            Php::out<<"<th>Valid To</th>"<<endl;
            Php::out<<"<th></th>"<<endl;            
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
            {
            Php::out<<"<tr data-id='"<<res->getString("access_whitelist_id")<<"'>"<<endl;  
            if(type!=3)
                Php::out<<"<td>"+res->getString("customer_name")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("plate_number")+"</td>"<<endl;            
            if(type!=3)
                Php::out<<"<td>"+res->getString("ticket_id")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("tag")+"</td>"<<endl;
            if(res->getInt("antipassback_enabled")==1)
                Php::out<<"<td>Enabled</td>"<<endl;
            else            
                Php::out<<"<td>Disabled</td>"<<endl;
                                   
            Php::out<<"<td>"+res->getString("validity_start_date")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("validity_expiry_date")+"</td>"<<endl;
            Php::out << "<td>"<< std::endl;
            if(res->getInt("status")==1)
                Php::out << "<button type='button' class='btn btn-danger whitelist-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success whitelist-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;            
            Php::out << "<button type='button' class='btn btn-info whitelist-edit' title='Edit'><i class='fas fa-edit'></i></button>"<< std::endl;           
            Php::out << "</td>"<< std::endl;
            Php::out<<"</tr>"<<endl;	
            }        
        delete res;    
        delete stmt;
		delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showAccessWhitelistList",e.what());
        }
    
    }

void showAccessWhitelistPolicies()
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from access_whitelist_group_policy order by id desc");
        if(res->rowsCount()>0)
            {            
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th>Policy Name</th>"<<endl;
            Php::out<<"<th>Description</th>"<<endl;                       
            Php::out<<"<th>Valid From</th>"<<endl;
            Php::out<<"<th>Valid To</th>"<<endl;
            Php::out<<"<th></th>"<<endl;
            
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
            {
            Php::out<<"<tr data-id='"<<res->getString("id")<<"'>"<<endl;                       
            Php::out<<"<td>"+res->getString("policy_name")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("description")+"</td>"<<endl;            
            Php::out<<"<td>"+res->getString("start_date")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("expiry_date")+"</td>"<<endl;            
            Php::out << "<td>"<< std::endl;
            if(res->getInt("status")==1)
                Php::out << "<button type='button' class='btn btn-danger policy-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success policy-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;           
            Php::out << "<button type='button' class='btn btn-info policy-edit' title='Edit'><i class='fas fa-edit'></i></button>"<< std::endl;           
            Php::out << "</td>"<< std::endl;
            Php::out<<"</tr>"<<endl;	
            }        
        delete res;    
        delete stmt;
		delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showAccessWhitelistPolicies",e.what());
        }
    
    }

void showDeviceListForAccessSynch()
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from system_devices");
        if(res->rowsCount()>0)
            {             
            Php::out << "<thead class='thead-light'>" << std::endl;
			Php::out<<"<tr>"<<endl;
            Php::out<<"<th>Device Number</th>"<<endl;
            Php::out<<"<th>Device Name</th>"<<endl;            
            Php::out<<"<th>Device IP</th>"<<endl;
            Php::out<<"<th>Synch Whitelist</th>"<<endl;                                  
			Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
            {
            Php::out<<"<tr data-id='"<<res->getString("id")<<"'>"<<endl; 
            Php::out<<"<td>"+res->getString("device_number")+"</td>"<<endl;                      
            Php::out<<"<td>"+res->getString("device_name")+"</td>"<<endl;            
            Php::out<<"<td>"+res->getString("device_ip")+"</td>"<<endl;                        
            Php::out << "<td>"<< std::endl;
            if(res->getInt("synch_whitelist_flag")==1)
                Php::out << "<button type='button' class='btn btn-danger synch-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
            else
                Php::out << "<button type='button' class='btn btn-success synch-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;
            Php::out << "</td>"<< std::endl;            
            Php::out<<"</tr>"<<endl;	
            }           
        delete res;    
        delete stmt;
		delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showDeviceListForAccessSynch",e.what());
        }
    
    }
  
Php::Value getDetails(string table,string id,Php::Value json)
    {
    Php::Value response;    
    try
        {
        string row_id=json["id"];
        con= General.mysqlConnect(ServerDB);
        prep_stmt = con->prepareStatement("select * from "+table+" where "+id+"=?");
        prep_stmt->setString(1, row_id);
        res=prep_stmt->executeQuery();
        if(res->next())
            {
            sql::ResultSetMetaData *res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();   
            for (int i = 1; i <= columns; i++) 							
                    response[res_meta -> getColumnLabel(i)]=string(res->getString(i));				
            }
        if(table=="access_whitelist_group_policy")
            {            
            query="SELECT group_concat(id) as users FROM access_whitelist_customers where find_in_set('"+row_id+"',whitelist_group)";            
            prep_stmt = con->prepareStatement(query);
            res=prep_stmt->executeQuery();
            if(res->next())                   
                response["users"]=string(res->getString("users"));
            }
        delete res;    
        delete prep_stmt;
	delete con;
        }
    catch(const std::exception& e)
        {
        writeException("getDetails",e.what());        
        } 
    return response;       
    }

Php::Value syncWhitelist()
	{
	string message="";
	try
	    {	
        sql::ResultSet *device;	
        sql::Driver *driver;
        sql::Connection *con_local=NULL;	
        sql::Statement *stmt_local;	
		con= General.mysqlConnect(ServerDB);
		stmt = con->createStatement();							
		driver = get_driver_instance();
		string ip,devicename,carpark;	

        writeLog("syncWhitelist","Connected to db");
		device = stmt->executeQuery("Select device_ip,device_name from system_devices where synch_whitelist_flag = 1");
		while (device->next())
		    {            
			try
                {	
                ip = device->getString("device_ip");                
                devicename = device->getString("device_name");	
                writeLog("syncWhitelist","IP: "+ip+"\tDevice:"+devicename);				
                con_local = driver->connect(ip, "parcxservice", "1fromParcx!19514");			
                con_local->setSchema("ParcxTerminal");	
                writeLog("syncWhitelist","Connected to "+ip);		
                stmt_local = con_local->createStatement();				
                stmt_local->executeUpdate("Truncate table access_whitelist");				
                stmt_local->executeUpdate("Set SQL_Mode='ALLOW_INVALID_DATES'");
                res = stmt->executeQuery("Select * from access_whitelist");               
                while (res->next())
                    {
                    prep_stmt = con_local->prepareStatement("Insert into access_whitelist(id,customer_name,ticket_id,plate_number,tag,tag_serial,start_date,expiry_date,access_zones,carpark_number,facility_number,tag_tid,antipassback,status,personalized_message_line1,personalized_message_line2) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    prep_stmt->setString(1, res->getString("access_whitelist_id"));
                    prep_stmt->setString(2, res->getString("customer_name"));
                    prep_stmt->setString(3, res->getString("ticket_id"));
                    prep_stmt->setString(4, res->getString("plate_number"));
                    prep_stmt->setString(5, res->getString("tag"));
                    prep_stmt->setString(6, res->getString("tag_serial"));
                    prep_stmt->setString(7, res->getString("validity_start_date"));
                    prep_stmt->setString(8, res->getString("validity_expiry_date"));				
                    prep_stmt->setString(9, res->getString("access_zones"));
                    prep_stmt->setString(10, res->getString("carpark_number"));
                    prep_stmt->setString(11, res->getString("facility_number"));
                    prep_stmt->setString(12, res->getString("tag_tid"));
                    prep_stmt->setString(13, res->getString("antipassback_enabled"));
                    prep_stmt->setString(14, res->getString("status"));
                    prep_stmt->setString(15, res->getString("personalized_message_line1"));
                    prep_stmt->setString(16, res->getString("personalized_message_line2"));
                    prep_stmt->executeUpdate();

				    delete prep_stmt;                
				    }
                delete stmt_local;	
								
                stmt->executeUpdate("Update system_devices set last_updated_whitelist = NOW() where device_ip = '" + ip + "'");
                message+=devicename+"("+ip+"):Success\n";                
                writeLog("syncWhitelist","Success "+ip);	
                delete stmt;			
                delete con_local;			
                delete res;			
			    }   
			catch (exception &e)
			    {
				writeException(ip+":syncWhitelist", e.what());
				message += devicename + "(" + ip +  ") : Failed\n ";
			    }								
		    }
		delete device;				
		delete con;		
		
		return message;
		}
	catch (exception &e)
		{			
        writeException("syncWhitelist", e.what());			
        return message;
		}	
	}


void CustomerDropdown()
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from access_whitelist_customers");
        while(res->next())
        {
            Php::out << "<option value='"<<res->getString("ticket_id")<<"'>"<<res->getString("customer_name")<<"</option>" << std::endl;   
        }
       
        delete res;    
        delete stmt;
	delete con; 
        }
    catch(const std::exception& e)
        {
        writeException("CustomerDropdown",e.what());
        }
    }

void showAccessWhitelistCustomers()
    {
    try
        {
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from access_whitelist_customers order by id desc");
                    
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th>Customer Name</th>"<<endl;
            Php::out<<"<th>Company Name</th>"<<endl;    
            Php::out<<"<th>Country</th>"<<endl;                                	
            Php::out<<"<th><button type='button' class='btn btn-info' id='customer-add' title='Add Customer'><i class='fas fa-user-plus'></i></button></th>"<<endl;	
            Php::out<<"</tr>"<<endl;	
            Php::out <<"</thead><tbody>" << std::endl;		
        if(res->rowsCount()>0)
            {
            while(res->next())
                {
                Php::out<<"<tr data-id='"<<res->getString("ticket_id")<<"'>"<<endl;                       
                Php::out<<"<td>"+res->getString("customer_name")+"</td>"<<endl;
                Php::out<<"<td>"+res->getString("company_name")+"</td>"<<endl;
                Php::out<<"<td>"+res->getString("country")+"</td>"<<endl;                            
                Php::out << "<td>"<< std::endl;

                Php::out << "<button type='button' class='btn btn-info whitelist-add' title='Add Whitelist'><i class='fas fa-plus'></i></button>"<< std::endl;           

                Php::out << "<button type='button' class='btn btn-info whitelist-customer-view' title='View Whitelist'><i class='fas fa-eye'></i></button>"<< std::endl;          

                Php::out << "<button type='button' class='btn btn-info whitelist-customer-edit' title='Edit User'><i class='fas fa-edit'></i></button>"<< std::endl;                                   
                Php::out << "</td>"<< std::endl;          

                Php::out<<"</tr>"<<endl;	
                }    
                Php::out<<"</tbody>"<<endl;
            }
        delete res;    
        delete stmt;
	delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showAccessWhitelistCustomers",e.what());
        }
    
    }

Php::Value parcxWhitelistSettings(Php::Parameters &params)
	{
	Php::Value data=params[0];      
    int task=data["task"];   
    Php::Value response;
    switch (task)
        {        
        case 1:showAccessWhitelistList(0,"");
            break;
        case 2:response=enableDisable("access_whitelist_tickets","access_whitelist_id","status",data); 
		    break;            
        case 3:response=insertUpdateWhitelist(data); 
		    break;
        case 4:response=getDetails("access_whitelist_tickets","access_whitelist_id",data); 
		    break; 
        case 5:response=syncWhitelist();
		    break;  
        case 6:showDeviceListForAccessSynch();
            break;  
        case 7:response=enableDisable("system_devices","id","synch_whitelist_flag",data); 
		    break;                     
        case 8:response=insertUpdateWhitelistPolicy(data); 
		    break;
        case 9:showAccessWhitelistPolicies();
            break; 
        case 10:response=enableDisable("access_whitelist_group_policy","id","status",data); 
            break; 
        case 11:response=getDetails("access_whitelist_group_policy","id",data); 
            break;
        case 12:accessCustomerOptions();
            break;
        case 13:showAccessWhitelistList(1,"");
            break;
        case 14:showAccessWhitelistList(2,"");
            break;
        case 15:CustomerDropdown();
            break;            
        case 16:showAccessWhitelistCustomers();
            break;
        case 17:response=insertUpdateCustomers(data); 
            break;
        case 18:response=getDetails("access_whitelist_customers","ticket_id",data); 
		break;
        case 19:showAccessWhitelistList(3,data["ticket_id"]);
            break;
            
	}
	return response;
	}
    
extern "C" 
	{    
    PHPCPP_EXPORT void *get_module()
		{        
		static Php::Extension extension("PX_WhitelistSettings", "1.0");
        extension.add<parcxWhitelistSettings>("parcxWhitelistSettings");               
        return extension;
		}
	}
