/* Standard C++ includes */
#include <stdlib.h>
#include <iostream>
#include<fstream>
#include<sstream>
#include<ctime>
#include<phpcpp.h>

#include<mysql.h>
#include "mysql_connection.h"
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>
#include <cppconn/resultset_metadata.h>

#define SERVER "localhost"
#define USER "parcxservice"
#define PASSWORD "1fromParcx!19514"
#define DATABASE_SERVER "parcx_server"
#define DATABASE_REPORTING "parcx_reporting"
#define DATABASE_DASHBOARD "parcx_dashboard"
#define dateFormat "%Y-%m-%d"
#define timeFormat "%H:%M:%S"
#define datetimeFormat "%d-%m-%Y %H:%M:%S"
#define mysqlFormat "%Y-%m-%d %H:%M:%S"
#define dayFormat "%A"
#define hourFormat "%H"

using namespace std;

sql::Connection *con,*con2,*con3;
sql::PreparedStatement *prep_stmt;	
sql::ResultSet *res,*res2;
sql::Statement *stmt;	
string query;
sql::ResultSet *product;
sql::ResultSet *validator;

//current dateTime
string currentDateTime(string format)
	{
	time_t now=time(NULL);	
	std::tm* timeinfo;
	char buffer[80];
	std::time(&now);
	timeinfo=std::localtime(&now);
	std::strftime(buffer,80,format.c_str(),timeinfo);		
	string datetime(buffer);
	return datetime;
	}//end of .currentDateTime

//write exception
void writeException(string function,string txt)
	{
	Php::out<<"Exception from:"<<function<<" "<<txt<<endl;
	ofstream fp;
	fp.open("/opt/parcx/Logs/WebApplication/ExceptionLogs/PX-Validation.log", ios::app);
	fp<<currentDateTime(datetimeFormat)<<" from:"<<function<<" "<<txt<<endl;
	fp.close();
	}//end of .writeException

//write Log
void writeLog(string function,string txt)
	{
	cout<<function<<" "<<txt<<endl;
	ofstream fp;
	fp.open("/opt/parcx/Logs/WebApplication/ApplicationLogs/PX-Validation-"+currentDateTime(dateFormat)+".log", ios::app);
	fp<<currentDateTime(datetimeFormat)<<" from:"<<function<<" "<<txt<<endl;
	fp.close();
	}//end of .writeLog

string ToString(Php::Value param)
	{
	string value=param;
	return value;
	}

//mysql connection
sql::Connection* mysqlConnect()
	{
	sql::Connection *con=NULL;
	try
		{
		sql::Driver *driver;				
		driver = get_driver_instance();				
		con = driver->connect(SERVER, USER,PASSWORD);	
		con->setSchema(DATABASE_SERVER);	
		}
	catch(exception &e)
		{				
		writeException("mysqlconnect",e.what());									
		}
	return con;
	}//end of .mysql_Connect

//mysql connection
sql::Connection* mysqlConnectReporting()
	{
	sql::Connection *con=NULL;
	try
		{
		sql::Driver *driver;				
		driver = get_driver_instance();				
		con = driver->connect(SERVER, USER,PASSWORD);	
		con->setSchema(DATABASE_REPORTING);	
		}
	catch(exception &e)
		{				
		writeException("mysqlconnect",e.what());									
		}
	return con;
	}//end of .mysql_Connect
	
//mysql connection
sql::Connection* mysqlConnectDashboard()
	{
	sql::Connection *con=NULL;
	try
		{
		sql::Driver *driver;				
		driver = get_driver_instance();				
		con = driver->connect(SERVER, USER,PASSWORD);	
		con->setSchema(DATABASE_DASHBOARD);	
		}
	catch(exception &e)
		{				
		writeException("mysqlconnect",e.what());									
		}
	return con;
	}//end of .mysql_Connect
	
Php::Value loginValidator(Php::Value json)
	{
	Php::Value response;
	response["status"]="400";
	try
		{						
		con = mysqlConnect();				
		prep_stmt=con->prepareStatement("select * from system_validators where email=? and password=md5(?)");				
		prep_stmt->setString(1,ToString(json["username"]));
		prep_stmt->setString(2,ToString(json["password"]));
		res=prep_stmt->executeQuery();
		delete prep_stmt;
		if(res->next())
			{
			bool flag=true;
			//status
			if(res->getInt("status")==0)
				{
				response["message"]="Username is disabled";	
				flag=false;			
				}
			if(res->getInt("date_validity")==1)				
				{
				//expiry date
				string current_date=currentDateTime(dateFormat);					
				string expiry_date=res->getString("expiry_date");																		
				if(expiry_date.compare(current_date)<0)
					{
					response["message"]="User validity expired";
					flag=false;								
					}

				//start date
				string start_date=res->getString("start_date");
				if(start_date.compare(current_date)>0)
					{
					response["message"]="User validity not started";
					flag=false;							
					}
				}
				
			if(flag)
				{				
				response["status"]="200";
				response["message"]="Login success";
				response["user_id"]=string(res->getString("user_id"));									
				response["user_name"]=string(res->getString("email"));
				response["display_name"]=string(res->getString("display_name"));																					
				response["logo"]=string(res->getString("logo"));										
				response["report_view"]=string(res->getString("validation_report_view"));										
				}
			}
		else
			response["message"]="Username/Password incorrect";

		delete res;				
		delete con;		
		}
	catch(exception &e)
		{
		writeException("loginValidator",e.what());
		}
	return response;
	}

Php::Value insertOperator(Php::Value json)
	{
	string msg = "Insertion Failed";
	try
		{		
		
		con = mysqlConnect();					
		query="select * from system_validators where email=?";
		prep_stmt=con->prepareStatement(query);
		prep_stmt->setString(1, ToString(json["email"]));
		res = prep_stmt->executeQuery();			
		if (res->next())
			msg="Email id already exists.Please try with another email id";
		else
			{			
			delete prep_stmt;						
			
			query="INSERT INTO system_validators (display_name, password, date_validity, start_date, expiry_date, email, mobile, logo, carparks, status,create_datetime,products,ticket_age,total_maximum_validation_hours,daily_maximum_validation_hours,weekly_maximum_validation_hours,monthly_maximum_validation_hours,validation_report_view) VALUES(?,md5(?),?,?,?,?,?,?,?,1,CURRENT_TIMESTAMP,?,?,?,?,?,?,?)";	
			prep_stmt = con->prepareStatement(query);												
			prep_stmt->setString(1, ToString(json["operator_name"]));			
			prep_stmt->setString(2, ToString(json["password"]));
			prep_stmt->setString(3, ToString(json["date_validity"]));
			
			if(stoi(json["date_validity"])==1)
				{
				prep_stmt->setString(4, ToString(json["account_activation_date"]));
				prep_stmt->setString(5, ToString(json["account_expiry_date"]));
				}
			else
				{
				prep_stmt->setNull(4,0);
				prep_stmt->setNull(5,0);
				}
			prep_stmt->setString(6, ToString(json["email"]));				
			prep_stmt->setString(7, ToString(json["mobile"]));
			prep_stmt->setString(8, ToString(json["logo"]));							
			prep_stmt->setString(9, ToString(json["carparks"]));																														
			prep_stmt->setString(10, ToString(json["validation_product"]));	
			prep_stmt->setString(11, ToString(json["validation_ticket_age"]));		
			prep_stmt->setString(12, ToString(json["validation_max_hours"]));		
			prep_stmt->setString(13, ToString(json["validation_daily_hours"]));		
			prep_stmt->setString(14, ToString(json["validation_weekly_hours"]));		
			prep_stmt->setString(15, ToString(json["validation_monthly_hours"]));						
			prep_stmt->setString(16, ToString(json["validation_view_report"]));
															
			if (!prep_stmt->execute())
				msg = "Insertion Successfull";
			}	
		delete prep_stmt;
		delete con;
		}
	catch (exception &e)
		{				
		writeException("insertOperator", e.what());				
		}
	return msg;	
	}

Php::Value updateOperator(Php::Value json)
	{
	string msg = "Update Failed";
	try
		{				
		con = mysqlConnect();				
		query="update system_validators set display_name=?, date_validity=?, start_date=?, expiry_date=?, mobile=?, carparks=?,products=?,ticket_age=?,total_maximum_validation_hours=?,daily_maximum_validation_hours=?,weekly_maximum_validation_hours=?,monthly_maximum_validation_hours=?,validation_report_view=? where user_id=?";
		prep_stmt = con->prepareStatement(query);												
		prep_stmt->setString(1, ToString(json["operator_name"]));					
		prep_stmt->setString(2, ToString(json["date_validity"]));
		
		if(stoi(json["date_validity"])==1)
			{
			prep_stmt->setString(3, ToString(json["account_activation_date"]));
			prep_stmt->setString(4, ToString(json["account_expiry_date"]));
			}
		else
			{
			prep_stmt->setNull(3,0);
			prep_stmt->setNull(4,0);
			}		
		prep_stmt->setString(5, ToString(json["mobile"]));							
		prep_stmt->setString(6, ToString(json["carparks"]));																														
		prep_stmt->setString(7, ToString(json["validation_product"]));	
		prep_stmt->setString(8, ToString(json["validation_ticket_age"]));		
		prep_stmt->setString(9, ToString(json["validation_max_hours"]));		
		prep_stmt->setString(10, ToString(json["validation_daily_hours"]));		
		prep_stmt->setString(11, ToString(json["validation_weekly_hours"]));		
		prep_stmt->setString(12, ToString(json["validation_monthly_hours"]));						
		prep_stmt->setString(13, ToString(json["validation_view_report"]));		
		prep_stmt->setString(14, ToString(json["user_id"]));

		if (!prep_stmt->execute())
			msg = "Update Successfull";

		if(ToString(json["logo"])!="user.jpg")
			{
			query="update system_validators set logo=? where user_id=?";
			prep_stmt = con->prepareStatement(query);	
			prep_stmt->setString(1, ToString(json["logo"]));
			prep_stmt->setString(2, ToString(json["user_id"]));	
			if (!prep_stmt->execute())
				msg = "Update Successfull";
			}

		if(ToString(json["password"])!="")
			{
			query="update system_validators set password=md5(?) where user_id=?";
			prep_stmt = con->prepareStatement(query);	
			prep_stmt->setString(1, ToString(json["password"]));
			prep_stmt->setString(2, ToString(json["user_id"]));	
			if (!prep_stmt->execute())
				msg = "Update Successfull";
			}
		
														
		
				
		delete prep_stmt;
		delete con;
		}
	catch (exception &e)
		{				
		writeException("insertOperator", e.what());				
		}
	return msg;	
	}

void showValidatorList(int status)
	{
	try
		{		
		con = mysqlConnect();				
		prep_stmt = con->prepareStatement("select * from system_validators where status="+to_string(status)+" order by user_id desc");
		res = prep_stmt->executeQuery();	
		if(res->rowsCount()>0)
			{
			Php::out << "<div class='card-header'>" << std::endl;
          	Php::out << "<div class='d-flex justify-content-between align-items-center'>" << std::endl;
            Php::out << "<div class='col'>Account Name</div>" << std::endl;
            Php::out << "<div class='col'>Email</div>" << std::endl;
			 Php::out << "<div class='col'>Carpark</div>" << std::endl; 
            Php::out << "<div class='col'>Valid From</div>" << std::endl;            
            Php::out << "<div class='col'>Valid To</div> " << std::endl;                                 
            Php::out << "<div class='col'>Products</div>" << std::endl;                 
            Php::out << "<div class='col'></div> " << std::endl;           
            Php::out << "<div class='col'></div>" << std::endl;
          	Php::out << "</div>" << std::endl;
        	Php::out << "</div>	" << std::endl;
			}			
		
		while (res->next())
			{			
			Php::out << "<div class='table-view'>" << std::endl;
			Php::out << "<div class='card-text'>" << std::endl;
			Php::out << "<div class='d-flex justify-content-between align-items-center'>" << std::endl;
			Php::out << "<div class='col'>" << res->getString("display_name") << "</div>" << std::endl;
			Php::out << "<div class='col'>" << res->getString("email") << "</div>" << std::endl;

			prep_stmt = con->prepareStatement("select group_concat(carpark_name) as carparks from system_carparks where carpark_number in("+res->getString("carparks")+")");
			res2 = prep_stmt->executeQuery();
			if(res2->next())
				Php::out << "<div class='col'>" << res2->getString("carparks") << "</div>" << std::endl;
			else
				Php::out << "<div class='col'>" << res->getString("carparks") << "</div>" << std::endl;

			delete prep_stmt;
			delete res2;
			
			Php::out << "<div class='col'>" << res->getString("start_date") << "</div>" << std::endl;				
			Php::out << "<div class='col'>" << res->getString("expiry_date") << "</div>" << std::endl;
			
			prep_stmt = con->prepareStatement("select group_concat(product_name) as products from system_products where product_id in("+res->getString("products")+")");
			res2 = prep_stmt->executeQuery();
			if(res2->next())
				Php::out << "<div class='col'>" << res2->getString("products") << "</div>" << std::endl;
			else
				Php::out << "<div class='col'>" << res->getString("products") << "</div>" << std::endl;

			delete prep_stmt;
			delete res2;							
									
			Php::out << "<div class='col'>";
			if(status==1)				
				Php::out << "<input class='btn btn-danger btn-enable-disable-validator btn-block' type='submit'  value='Disable' id='" << res->getString("user_id") << "'>";
			else
				Php::out << "<input class='btn btn-success btn-enable-disable-validator btn-block' type='submit'  value='Enable' id='" << res->getString("user_id") << "'>";
			
			Php::out << "</div>" << std::endl;
			Php::out << "<div class='col'>";
			Php::out << "<input class='btn btn-info btn-edit-validator btn-block' type='submit'  value='Edit' id='edit" << res->getString("user_id") << "'>";
			Php::out << "</div>" << std::endl;
			Php::out << "</div>" << std::endl;
			Php::out << "</div>" << std::endl;
			Php::out << "</div>" << std::endl;
		}

		con->close();		
		delete con;
		delete res;
	}
	catch (exception &e)
		{		
		writeException("showValidatorList", e.what());		
		}
	}

Php::Value enableDisableValidator(Php::Value json) 
	{
	string msg = "Failed";
	try
		{		
		con = mysqlConnect();		
		prep_stmt = con->prepareStatement("update system_validators set status=? where user_id=?");		
		prep_stmt->setString(1, ToString(json["status"]));
		prep_stmt->setString(2, ToString(json["user_id"]));		

		if (!prep_stmt->execute())
			msg = "Updated successfully";

		delete prep_stmt;
		delete con;
		}
	catch (exception &e)
		{			
		writeException("enableDisableValidator", e.what());			
		}
	return msg;
	} //end of .enableDisableUsers

void carparkDropdown()
	{
	try
		{		
		con = mysqlConnect();		
		prep_stmt = con->prepareStatement("select carpark_number,carpark_name from system_carparks");
		res = prep_stmt->executeQuery();
		while (res->next())
			{
			Php::out << "<option value='" << res->getString("carpark_number") << "'>" << res->getString("carpark_name") << "</option>" << std::endl;
			}

		con->close();		
		delete prep_stmt;
		delete con;
		delete res;
	}
	catch (exception &e)
		{		
		writeException("carparkDropdown", e.what());		
		}
	}

void productDropdown()
	{
	try
		{		
		con = mysqlConnect();		
		prep_stmt = con->prepareStatement("select product_id,product_name from system_products where product_type='Validation' and status=1");
		res = prep_stmt->executeQuery();
		while (res->next())
			{
			Php::out << "<option value='" << res->getString("product_id") << "'>" << res->getString("product_name") << "</option>" << std::endl;
			}

		con->close();		
		delete prep_stmt;
		delete con;
		delete res;
	}
	catch (exception &e)
		{		
		writeException("productDropdown", e.what());		
		}
	}

void showValidationProductButtons()
	{
	try
		{				
		con = mysqlConnect();		
		prep_stmt = con->prepareStatement("select product_id,product_name from system_products where product_type='Validation' and status=1");
		res = prep_stmt->executeQuery();				
		int i=1;
		while (res->next())
			{
			if(i%2==1)
				Php::out<<"<div class='row mt-3'>"<<endl;                                
			Php::out <<"<div class='col'><button type='button' value='" << res->getString("product_id") << "' class='calc-btn btn btn-lg btn-block btn-secondary'>" << res->getString("product_name") << "</button></div>"<<endl;
            if(i%2==0)                    
                Php::out<<"</div>"<<endl;	
			i++;		
			}
		if(i%2==0)                    
            Php::out<<"</div>"<<endl;		
		delete prep_stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("showValidationProductButtons",e.what());
		}		
	}

Php::Value getvalidatorDetails(Php::Value json) 
	{
	Php::Value response;
	try
		{		
		con = mysqlConnect();				
		prep_stmt = con->prepareStatement("select * from system_validators where user_id=?");				
		prep_stmt->setString(1, ToString(json["user_id"]));				
		res = prep_stmt->executeQuery();			
		if (res->next())
			{				
			sql::ResultSetMetaData *res_meta = res -> getMetaData();
			int columns = res_meta -> getColumnCount();   
			for (int i = 1; i <= columns; i++) 							
				response[res_meta -> getColumnLabel(i)]=string(res->getString(i));				
			}				
		delete res;
		delete prep_stmt;
		delete con;
		}
	catch (exception &e)
		{			
		writeException("getvalidatorDetails", e.what());			
		}
	return response;
	} 

void showValidatedTimeForValidatorForLogin(Php::Value json)
	{
	try
		{	
		con = mysqlConnectReporting();				
		string validator=json["validator_id"];
		string login_time=json["login_time"];		
		stmt=con->createStatement();
		res = stmt->executeQuery("select time(date_time) as time from parking_validation where validator_id='"+validator+"' and date_time>'"+login_time+"'");			
		while (res->next())
			{
			Php::out<<res->getString("time")<<" Ticket validated"<<"<br>"<<endl;
			}
			
		delete stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("showValidatedTimeForValidatorForLogin",e.what());
		}		
	}

void showlatestValidationForValidator(Php::Value json)
	{
	try
		{
		con = mysqlConnectReporting();
		string validator=json["validator_id"];		
		stmt=con->createStatement();
		res = stmt->executeQuery("select date_time,chip_serial_number,plate_number,ticket_id,product_name from parking_validation  where validator_id='"+validator+"' order by date_time desc limit 3");	
		
		Php::out<<"<thead><tr>"<<endl;
        Php::out<<"<td>Date and Time</td>"<<endl;		
		Php::out<<"<td>Ticket Number</td>"<<endl;

        Php::out<<"<td>Validation Product</td>"<<endl;
        Php::out<<"</tr></thead>"<<endl;
		if(res->rowsCount()==0)				
			Php::out<<"<tr><td colspan='3'>No validations today</td></tr>"<<endl;	
		while (res->next())
			{
			Php::out<<"<tr>"<<endl;
            Php::out<<"<td>"<<res->getString("date_time")<<"</td>"<<endl;			
			Php::out<<"<td>"<<res->getString("ticket_id")<<"</td>"<<endl;			
            Php::out<<"<td>"<<res->getString("product_name")<<"</td>"<<endl;
            Php::out<<"</tr> "<<endl;
			}
			
			
		delete stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("showlatestValidationForValidator",e.what());
		}		
	}

Php::Value getHourlyValidationSummaryToday(Php::Value json)
	{
	Php::Value count;
	try
		{		
		con = mysqlConnectReporting();		
		prep_stmt = con->prepareStatement("select * from hourly_validation_summary where validator_id=? and report_date=CURRENT_DATE");
		prep_stmt->setString(1, ToString(json["validator_id"]));
		res = prep_stmt->executeQuery();
		int i=0;
		if(res->next())
			{			
			while(i<24)
            	{
                count[i] = res->getInt("h" +std::to_string(i)+ "to" +std::to_string((i + 1)));
                i++;                   
            	}
			count[i]=res->getInt("total");
			 
			}
		else
			{			
            while (i < 25) 
				{
                count[i] = 0;
                i++;
            	}
			}		
		delete prep_stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("getHourlyValidationSummaryToday",e.what());
		}	
	return count;	
	}

Php::Value getHourlyValidationSummaryAverageLastweek(Php::Value json)
	{
	Php::Value count;
	try
		{
		con = mysqlConnectReporting();
		prep_stmt = con->prepareStatement("select * from hourly_validation_summary where validator_id=? and report_date between CURRENT_DATE-7 and CURRENT_DATE-1");
		prep_stmt->setString(1, ToString(json["validator_id"]));
		res = prep_stmt->executeQuery();
		int i=0;
		 while (i < 24) 
				{
                count[i] = 0;
                i++;
            	}

		while(res->next())
			{
			i=0;			
			while(i<24)
            	{
                count[i] = count[i]+res->getInt("h" +std::to_string(i)+ "to" +std::to_string((i + 1)));
                i++;                   
            	}			
			 
			}
		i=0;			
		while(i<24)
            {
            count[i] = count[i]/7;
            i++;                   
            }		
		delete prep_stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("getHourlyValidationSummaryAverageLastweek",e.what());
		}	
	return count;	
	}

bool findInList(string list,string find)
	{
	stringstream ss(list); 
	string element; 
	while(getline(ss,element,','))
		{
		if(find==element)
			return true;
		} 
	return false;	
	}

Php::Value validateTicket(Php::Value json)
	{
	Php::Value response;
	response["status"]="400";
	try
		{		
		bool flag =true;		
		con = mysqlConnect();
		con2 = mysqlConnectReporting();		
		if(con!=NULL)	
			{
			string validator_id=json["validator_id"];
			string product_id=json["product_id"];
						
			string entry_date_time,product_name,validator_name,carpark_name,facility_name,plate_number,ticket_id,product_type,chip_utid,device_number;
			int product_value,carpark_number,facility_number;					
					
			ticket_id=ToString(json["ticket_id"]);	
			
			writeLog("validateTicket","ticket_id: "+ticket_id);						
			if(ticket_id.length()==20)	
				{
				prep_stmt=con2->prepareStatement("select * from open_transactions where ticket_id=? order by id desc");	
				prep_stmt->setString(1, ticket_id);
				}
			else
				{
				prep_stmt=con2->prepareStatement("select * from open_transactions where ticket_id like ? order by id desc");	
				prep_stmt->setString(1, "%"+ticket_id);
				}																		
			res = prep_stmt->executeQuery();
			delete prep_stmt;

			if(res->next())
				{
				writeLog("validateTicket","Present in open transaction");					
				ticket_id=res->getString("ticket_id");
				plate_number=res->getString("plate_number");
				entry_date_time=res->getString("entry_date_time");	
				writeLog("validateTicket","entry_date_time: "+entry_date_time);

				carpark_number=res->getInt("carpark_number");	
				facility_number=res->getInt("facility_number");	
				device_number=res->getString("device_number");				
					
				//get validator details
				prep_stmt=con->prepareStatement("select * from system_validators where user_id=? and status=1");
				prep_stmt->setString(1, validator_id);									
				validator = prep_stmt->executeQuery();
				delete prep_stmt;
					
				if(validator->next())
					{
					validator_name=validator->getString("display_name");
					writeLog("validateTicket","validator_name: "+validator_name);
					//ticket age								
					string current_time=currentDateTime(mysqlFormat);
					writeLog("validateTicket","current_time: "+current_time);
					char out[30];
					int ticket_age=validator->getInt("ticket_age");	
					writeLog("validateTicket","ticket_age: "+to_string(ticket_age));					
					struct tm tm;
					strptime(entry_date_time.c_str(),"%Y-%m-%d %H:%M:%S",&tm);
					tm.tm_hour+=ticket_age;
					mktime(&tm); 
					strftime(out,30,"%Y-%m-%d %H:%M:%S",&tm);
					string expiry_date(out);
					writeLog("validateTicket","expiry: "+expiry_date);
					if(expiry_date.compare(current_time)<0)
						{
						response["message"]="Validation ticket age exceeded";							
						flag=false;														
						}

					//carpark-facility											
					string carparks=validator->getString("carparks");
					writeLog("validateTicket","Validator carpark: "+carparks);						
					
					if(findInList(carparks,to_string(carpark_number))==false)
						{
						response["message"]="Ticket from another carpark,validator dont have access to this carpark ";
						flag=false;	
						}					
					}
				else
					{
					response["message"]="Validator disabled";
					flag=false;	
					}
				delete validator;
						
				if(flag)
					{
					//get product details
					prep_stmt=con->prepareStatement("select * from system_products where product_id=? and status=1");
					prep_stmt->setString(1, product_id);						
					product = prep_stmt->executeQuery();
					delete prep_stmt;
					if(product->next())
						{
						product_name=product->getString("product_name");
						product_value=product->getInt("product_value");
						product_type=product->getString("validation_type");							
						//carpark-facility
						string carparks=product->getString("carparks");	
						
						if(findInList(carparks,to_string(carpark_number))==false)
							{
							response["message"]="Ticket from another carpark,Product is not applicable";
							flag=false;	
							}
						
						
						if(product->getInt("date_validity")==1)
							{
							//expiry date
							string current_date=currentDateTime(dateFormat);					
							string expiry_date=product->getString("expiry_date");																		
							if(expiry_date.compare(current_date)<0)
								{
								response["message"]="Validation Product expired";
								flag=false;							
								}

							//start date
							string start_date=product->getString("start_date");
							if(start_date.compare(current_date)>0)
								{
								response["message"]="Validation Product not started";	
								flag=false;						
								}
							}
						//current day
						string current_day=currentDateTime(dayFormat);
						string days=product->getString("validity_days");
						if(days.size()>0)
							{
							if(findInList(days,current_day)==false)
								{
								response["message"]="Validation Product not active for today";
								flag=false;	
								}								
							}							
						//time slot
						string current_hour=currentDateTime(hourFormat);
						string timeslot=product->getString("validity_time_slots");							
						if(timeslot.size()>0)
							{
							bool match=false;
							stringstream ss(timeslot);
							string slot;
							while(getline(ss,slot,','))
								{										
								if(slot.substr(0,2).compare(current_hour)==0)
									{
									match=true;
									break;	
									}
								}
							if(match==false)
								{
								response["message"]="Validation Product not active for current time slot";
								flag=false;
								}
							}
						
						//success
						if(flag)
							{
							prep_stmt=con->prepareStatement("select carpark_name,facility_name from system_devices where device_number="+device_number);
							res = prep_stmt->executeQuery();
							if(res->next())
								{
								carpark_name=res->getString("carpark_name");
								facility_name=res->getString("facility_name");
								}
							

							prep_stmt=con2->prepareStatement("insert into parking_validation(date_time,ticket_id,chip_utid,chip_serial_number,validator_id,product_id,carpark_number,facility_number,product_name,validator_name,carpark_name,validation_type,validation_value,facility_name,plate_number)Values(CURRENT_TIMESTAMP,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
							prep_stmt->setString(1,ticket_id);
							prep_stmt->setString(2,"");
							prep_stmt->setString(3,"");
							prep_stmt->setString(4, validator_id);
							prep_stmt->setString(5, product_id);
							prep_stmt->setInt(6, carpark_number);
							prep_stmt->setInt(7, facility_number);
							prep_stmt->setString(8,product_name);
							prep_stmt->setString(9,validator_name);
							prep_stmt->setString(10,carpark_name);					
							prep_stmt->setString(11, product_type);
							prep_stmt->setInt(12,product_value);
							prep_stmt->setString(13,facility_name);
							prep_stmt->setString(14,plate_number);
							if (!prep_stmt->execute())
								{
								response["status"]="200";
								response["message"]="Validated successfully";
								}
							
							//update hourly validation summary								
							string column="h"+to_string(stoi(current_hour))+"to"+to_string(stoi(current_hour)+1);

							prep_stmt=con2->prepareStatement("select * from hourly_validation_summary where report_date=CURRENT_DATE and validator_id=?");
							prep_stmt->setString(1, validator_id);
							res=prep_stmt->executeQuery();
							if(res->rowsCount()>0)
								{
								//update 									
								prep_stmt=con2->prepareStatement("update hourly_validation_summary set "+column+"="+column+"+1,total=total+1 where validator_id=? and report_date=CURRENT_DATE");
								prep_stmt->setString(1, validator_id);
								prep_stmt->executeUpdate();									
								}
							else
								{
								//insert									
								prep_stmt=con2->prepareStatement("insert into hourly_validation_summary(report_date,validator_id,total,"+column+")values(CURRENT_DATE,?,1,1)");
								prep_stmt->setString(1, validator_id);
								prep_stmt->executeUpdate();
								}
							}					

						}
					else						
						{
						response["message"]="Validation Product not active";
						flag=false;	
						}
					delete product;	
					}					
				}
			else
				{
				response["message"]="Not present in open transactions";		
				flag=false;
				}
			delete res;																	
			delete con;			
			delete con2;															
			}
		else
			response["message"]="No database connection";
		writeLog("validateTicket","Result: "+ToString(response["message"])+"\n");					
		}
	catch (exception &e)
		{			
		writeException("validateTicket", e.what());			
		}
	return response;
	}

void validationTransactionReport(Php::Value json)
	{
	try
		{
		con = mysqlConnectReporting();						
		query="select * from parking_validation where date_time between '"+ToString(json["from"])+"' and  '"+ToString(json["to"])+"' and validator_id='"+ToString(json["validator_id"])+"'";				
		if(ToString(json["products"]).length()>0)
			query=query+" and product_id in("+ToString(json["products"])+")";
		if(ToString(json["carparks"]).length()>0)
			query=query+" and carpark_number in("+ToString(json["carparks"])+")";
		if(ToString(json["days"]).length()>0)
			query=query+"and dayname(date_time) in("+ToString(json["days"])+")";	
				
		prep_stmt = con->prepareStatement(query);				
		res = prep_stmt->executeQuery();											

		
		if(res->rowsCount()!=0)				
			{
			Php::out<<"<div class='table-responsive card card-table-blue'>"<<endl;
			Php::out<<"<table class='table table-blue jspdf-table'>"<<endl;        						
			Php::out<<"<thead><tr>"<<endl;
			Php::out<<"<td>Date and Time</td>"<<endl;		        		
			Php::out<<"<td>Plate Number</td>"<<endl;
			Php::out<<"<td>Ticket Number</td>"<<endl;
			Php::out<<"<td>Account Name</td>"<<endl;
			Php::out<<"<td>Validation Product</td>"<<endl;
			Php::out<<"<td>Carpark Name</td>"<<endl;
			Php::out<<"<td>Facility Name</td>"<<endl;
			Php::out<<"</tr></thead>"<<endl;	
			while (res->next())
				{
				Php::out<<"<tr>"<<endl;
				Php::out<<"<td>"<<res->getString("date_time")<<"</td>"<<endl;				
				Php::out<<"<td>"<<res->getString("plate_number")<<"</td>"<<endl;
				Php::out<<"<td>"<<res->getString("ticket_id")<<"</td>"<<endl;
				Php::out<<"<td>"<<res->getString("validator_name")<<"</td>"<<endl;                      
				Php::out<<"<td>"<<res->getString("product_name")<<"</td>"<<endl;
				Php::out<<"<td>"<<res->getString("carpark_name")<<"</td>"<<endl;
				Php::out<<"<td>"<<res->getString("facility_name")<<"</td>"<<endl;
				Php::out<<"</tr> "<<endl;
				}
			Php::out<<"</table></div>"<<endl;
			}
		else
			{
			Php::out<<"<div class='card p-3'>No records/transactions available for the current search criteria</div>"<<endl;
			}
											
		delete prep_stmt;
		delete res;
		delete con;	
		}
	catch(exception &e)
		{
		writeException("validationTransactionReport",e.what());
		}		
	}


Php::Value parcxValidation(Php::Parameters &params)
    {
    Php::Value data=params[0];      
    int task=data["option-type"];   
    Php::Value response;
    switch (task)
        {
		case 1:	response=loginValidator(data);
				break;
		case 2:	
				{
				int status=data["status"];
				showValidatorList(status);
				break;	
				}
		case 3:	response=insertOperator(data);
				break;
		case 4:	response=enableDisableValidator(data);
				break;
		case 5:	carparkDropdown();	
				break;
		case 6:	response=getvalidatorDetails(data);
				break;
		case 7:	response=updateOperator(data);
				break;
		case 8:	productDropdown();	
				break;	
		case 9:	showValidatedTimeForValidatorForLogin(data);	
				break;
		case 10:showValidationProductButtons();	
				break;
		case 11:showlatestValidationForValidator(data);	
				break;
		case 12:response=getHourlyValidationSummaryToday(data);	
				break;	
		case 13:response=getHourlyValidationSummaryAverageLastweek(data);	
				break;
		case 14:response=validateTicket(data);	
				break;
		case 15:validationTransactionReport(data);	
				break;		
		}
	return response;
	}

extern "C"
	{
	PHPCPP_EXPORT void *get_module()
		{
		static Php::Extension extension("PX_Validation", "1.0");							
		extension.add<parcxValidation>("parcxValidation");				
		return extension;
		}
	}