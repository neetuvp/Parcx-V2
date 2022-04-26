#include "PX_GeneralOperations.h"
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <phpcpp.h>
#include <iostream>
#include <sstream>
#include <cppconn/prepared_statement.h>
#include "validation.h"

#define ServerDB "parcx_server"
#define ReportingDB "parcx_reporting"
#define DashboardDB "parcx_dashboard"
#define dateFormat "%Y-%m-%d"

#define MANDATORY "Mandatory"
#define MAXLENGTH "Maximum Length Exceeded"
#define CONSTRAINT "Invalid Character"

using namespace std;
GeneralOperations General;
Validation validation;
string validation_failed = "Validation Failed\n";
void writeLog(string function, string message) {
    General.writeLog("WebApplication/ApplicationLogs/PX-UserManagement-" + General.currentDateTime(dateFormat), function, message);
}

void writeException(string function, string message) {
    General.writeLog("WebApplication/ExceptionLogs/PX-UserManagement-" + General.currentDateTime(dateFormat), function, message);
    writeLog(function, "Exception: " + message);
}


string toString(Php::Value param) {
    string value = param;
    return value;
}

int generateUniqueId()
{    							    		    
   struct tm tm;
    string startDate = "2020-01-01 00:00:00";

    strptime(startDate.c_str(), "%Y-%m-%d %H:%M:%S", &tm);
    time_t t = mktime(&tm);

    time_t now = time(NULL);
    long seconds = difftime(now, t);
    return seconds;
}

std::string random_string( size_t length )
{
    auto randchar = []() -> char
    {
        const char charset[] =
        "0123456789"
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
        "abcdefghijklmnopqrstuvwxyz";
        const size_t max_index = (sizeof(charset) - 1);
        return charset[ rand() % max_index ];
    };
    std::string str(length,0);
    std::generate_n( str.begin(), length, randchar );
    return str;
}

string arrayToString(Php::Value json) {

    stringstream ss;
    ss << "{";
    for (auto &iter : json)
        ss << "\"" << iter.first << "\":\"" << iter.second << "\",";
    string response = ss.str();
    response = response.substr(0, response.size() - 1) + "}";
    return response;
}

void logUserActivity(string category, string description, int user_id, string user_name, string status, string ip, string request, string response) {
    try {
        sql::Connection *con;
        sql::PreparedStatement *pstmt;
        con = General.mysqlConnect(ReportingDB);
        pstmt = con->prepareStatement("insert into system_journal(category,description,user_id,user_name,status,ip_address,request,response)values(?,?,?,?,?,?,?,?)");
        pstmt->setString(1, category);
        pstmt->setString(2, description);
        pstmt->setInt(3, user_id);
        pstmt->setString(4, user_name);
        pstmt->setString(5, status);
        pstmt->setString(6, ip);
        pstmt->setString(7, request);
        pstmt->setString(8, response);
        pstmt->executeUpdate();
        delete pstmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("logUserActivity", e.what());
    }
}

void showSideBar(Php::Value data) {
    try {
        Php::Value label;
        string userrole_id = data["user_role_id"];
        string base_url = data["url"];
        string lang = data["lang"];
        string user_id = data["user_id"];

        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        string query;

        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        query = "SELECT menu_name," + lang + " FROM system_menu left join web_application_labels on menu_name=message";
        res = stmt->executeQuery(query);
        while (res->next()) {
            if (res->getString(lang) != "")
                label[res->getString("menu_name").asStdString()] = res->getString(lang).asStdString();
            else
                label[res->getString("menu_name").asStdString()] = res->getString("menu_name").asStdString();
        }

        query = "SELECT group_name," + lang + " FROM system_menu_group left join web_application_labels on group_name=message";
        res = stmt->executeQuery(query);
        while (res->next()) {
            if (res->getString(lang) != "")
                label[res->getString("group_name").asStdString()] = res->getString(lang).asStdString();
            else
                label[res->getString("group_name").asStdString()] = res->getString("group_name").asStdString();
        }

        delete res;

        query = "SELECT header_name," + lang + " FROM system_menu_header left join web_application_labels on header_name=message";
        res = stmt->executeQuery(query);
        while (res->next()) {
            if (res->getString(lang) != "")
                label[res->getString("header_name").asStdString()] = res->getString(lang).asStdString();
            else
                label[res->getString("header_name").asStdString()] = res->getString("header_name").asStdString();
        }

        delete res;

        query = "select password_reset_flag from system_users where user_id=" + user_id;
        res = stmt->executeQuery(query);
        int password_reset_flag = 0;
        if (res->next())
            password_reset_flag = res->getInt("password_reset_flag");
        delete res;
        if (password_reset_flag == 1)
            query = "SELECT c.*,header_expand,group_name,group_icon,group_order_index,group_expand,a.* FROM system_menu_header a,system_menu_group b,system_menu c where b.group_id=c.group_id and a.header_id=b.header_id and  menu_status=1 and group_status=1 and header_status=1 and menu_name='reset_password'  ORDER by header_order_index,group_order_index,menu_order_index";
        else if (data["user_role_id"] == 100)
            query = "SELECT c.*,header_expand,group_name,group_icon,group_order_index,group_expand,a.* FROM system_menu_header a,system_menu_group b,system_menu c where b.group_id=c.group_id and a.header_id=b.header_id and  menu_status!=0 and group_status=1 and header_status=1 ORDER by header_order_index,group_order_index,menu_order_index";
        else
            query = "SELECT c.*,header_expand,group_name,group_icon,group_order_index,group_expand,a.* FROM system_menu_header a,system_menu_group b,system_menu c,system_user_role_rights d where b.group_id=c.group_id and a.header_id=b.header_id and d.menu_id=c.menu_id and d.user_role_id=" + userrole_id + " and d.rr_view=1 and menu_status=1 and group_status=1 and header_status=1 ORDER by header_order_index,group_order_index,menu_order_index";
        res = stmt->executeQuery(query);
        int group_id = 0, header_id = 0, group_expand = 0;
        while (res->next()) {
            if (header_id != res->getInt("header_id")) {
                header_id = res->getInt("header_id");
                if (group_id != 0 && group_expand == 1) {
                    Php::out << "</ul>" << endl;
                    Php::out << "</li>" << endl;
                }
                if (res->getInt("header_expand") == 1)
                    Php::out << "<li class='nav-header'>" << label[res->getString("header_name")] << "</li>" << endl;
                group_id = 0;
            }
            if (group_id != res->getInt("group_order_index")) {
                if (group_id != 0 && group_expand == 1) {
                    Php::out << "</ul>" << endl;
                    Php::out << "</li>" << endl;
                }
                group_id = res->getInt("group_order_index");
                group_expand = res->getInt("group_expand");
                if (group_expand == 1) {
                    Php::out << "<li class='nav-item has-treeview'>" << endl;
                    Php::out << "<a href='#' class='nav-link'>" << endl;
                    Php::out << "<i class='nav-icon " << res->getString("group_icon") << "'></i>" << endl;
                    Php::out << "<p>" << label[res->getString("group_name")] << "<i class='right fa fa-angle-right'></i></p></a>" << endl;
                    Php::out << "<ul class='nav nav-treeview'>" << endl;
                }
            }
            Php::out << "<li class='nav-item has-treeview w-100'>" << endl;
            Php::out << "<a href='" << base_url << res->getString("menu_link") << "' class='nav-link'>" << endl;
            Php::out << "<i class='nav-icon " << res->getString("menu_icon") << "'></i>" << endl;
            Php::out << "<p>" << label[res->getString("menu_name").asStdString()] << "</p>" << endl;
            Php::out << "</a>" << endl;
            Php::out << "</li>" << endl;
        }
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("showSideBar", e.what());
    }
}

void showNavigationList() {
    sql::Connection *conn;
    sql::Statement *stmt;
    sql::ResultSet *res;
    string query;
    try {
        Php::Value label;
        string lang = "english";
        conn = General.mysqlConnect(ServerDB);
        stmt = conn->createStatement();

        query = "SELECT menu_name," + lang + " FROM system_menu left join web_application_labels on menu_name=message";
        res = stmt->executeQuery(query);
        while (res->next()) {
            if (res->getString(lang) != "")
                label[res->getString("menu_name").asStdString()] = res->getString(lang).asStdString();
            else
                label[res->getString("menu_name").asStdString()] = res->getString("menu_name").asStdString();
        }

        query = "SELECT group_name," + lang + " FROM system_menu_group left join web_application_labels on group_name=message";
        res = stmt->executeQuery(query);
        while (res->next()) {
            if (res->getString(lang) != "")
                label[res->getString("group_name").asStdString()] = res->getString(lang).asStdString();
            else
                label[res->getString("group_name").asStdString()] = res->getString("group_name").asStdString();
        }

        delete res;

        query = "SELECT menu_id,group_name,a.group_id,menu_name,menu_add,menu_delete,menu_edit FROM system_menu_group a INNER JOIN system_menu b ON a.group_id=b.group_id where menu_status=1 order by group_id desc";
        res = stmt->executeQuery(query);
        int group_id = 0;
        if (res->rowsCount() > 0) {
            Php::out << "<div class='row'>" << endl;
            Php::out << "<div class='col-4'>" << endl;
            Php::out << "</div>" << endl;
            Php::out << "<div class='col'>View" << endl;
            Php::out << "</div>" << endl;
            Php::out << "<div class='col'>Add" << endl;
            Php::out << "</div>" << endl;
            Php::out << "<div class='col'>Edit" << endl;
            Php::out << "</div>" << endl;
            Php::out << "<div class='col'>Delete" << endl;
            Php::out << "</div>" << endl;
            Php::out << "</div>" << endl;
        }
        while (res->next()) {

            if (group_id != res->getInt("group_id")) {
                group_id = res->getInt("group_id");
                Php::out << "<div class='row'><div class='col-4'><label class='label h5'>" << label[res->getString("group_name")] << "</label></div></div>" << endl;
            }



            // Php::out<<"<div class='form-group custom-control custom-checkbox'>"<<endl;
            Php::out << "<div class='row' id ='menuadd-" + res->getString("menu_id") + "'>" << endl;
            Php::out << "<div class='col-4'>" << endl;
            Php::out << "<label class='label'>" << label[res->getString("menu_name")] << "</label>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='col'>" << endl;
            Php::out << "<input type='checkbox' class='custom-checkbox' title='View' id='" << res->getInt("menu_id") << "-view'>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='col'>" << endl;
            if (res->getInt("menu_add") == 1)
                Php::out << "<input type='checkbox' class='custom-checkbox' title='Add' id='" << res->getInt("menu_id") << "-add'>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='col'>" << endl;
            if (res->getInt("menu_edit") == 1)
                Php::out << "<input type='checkbox' class='custom-checkbox' title='Edit' id='" << res->getInt("menu_id") << "-edit'>" << endl;
            Php::out << "</div>" << endl;

            Php::out << "<div class='col'>" << endl;
            if (res->getInt("menu_delete") == 1)
                Php::out << "<input type='checkbox' class='custom-checkbox' title='Delete' id='" << res->getInt("menu_id") << "-delete'>" << endl;
            Php::out << "</div>" << endl;



            Php::out << "</div>" << endl;
        }

        delete res;
        delete stmt;
        delete conn;
    }    catch (const std::exception& e) {
        writeException("showNavigationList", e.what());
    }
}

void showUserRoleNavigationList()
    {
    try
        { 
        Php::Value label;
        string lang="english";
        sql::ResultSet *role,*rights;
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();   
        
        string query="SELECT menu_name,"+lang+" FROM system_menu left join web_application_labels on menu_name=message where menu_status<2";
        res=stmt->executeQuery(query);
        while(res->next())            
            {
            if(res->getString(lang)!="")
                label[res->getString("menu_name").asStdString()]=res->getString(lang).asStdString();                
            else
                label[res->getString("menu_name").asStdString()]=res->getString("menu_name").asStdString();                
            }
        
        query="SELECT group_name,"+lang+" FROM system_menu_group left join web_application_labels on group_name=message";
        res=stmt->executeQuery(query);
        while(res->next())            
            {
            if(res->getString(lang)!="")
                label[res->getString("group_name").asStdString()]=res->getString(lang).asStdString();                
            else
                label[res->getString("group_name").asStdString()]=res->getString("group_name").asStdString();                
            }
            
        delete res;
        
        query="SELECT menu_id,group_name,a.group_id,menu_name,menu_add,menu_delete,menu_edit FROM system_menu_group a INNER JOIN system_menu b ON a.group_id=b.group_id where menu_status=1 order by header_id desc,group_id desc";
        res=stmt->executeQuery(query);
        int group_id=0;
        
        Php::Value menu;
        while(res->next())  
        {
            menu[res->getString("menu_id")+"-view"]=0;   
            menu[res->getString("menu_id")+"-add"]=0;   
            menu[res->getString("menu_id")+"-edit"]=0;
            menu[res->getString("menu_id")+"-delete"]=0;
        }
        
        Php::Value start_menu=menu;                        
        
        query="select * from system_user_role where Lower(user_role_name) not like ('%admin%')";
        role=stmt->executeQuery(query);
        string view_checked,add_checked,edit_checked,delete_checked;
        while(role->next())
        {    
            Php::out<<"<div id='parent'>"<<endl;
            Php::out<<"<div id='roleholder' class='accordion'>"<<role->getString("user_role_name")<<endl;
            Php::out<<"<div class='panel' style='display: none;'><form>"<<endl;
            //query="select menu_id,rr_view,rr_add,rr_edit,rr_delete from system_user_role_rights where rr_view=1 and user_role_id="+role->getString("user_role_id");
            query="select menu_id,rr_view,rr_add,rr_edit,rr_delete from system_user_role_rights where user_role_id="+role->getString("user_role_id");
            //Php::out<<query<<endl;
            rights=stmt->executeQuery(query);
            menu=start_menu;
            while(rights->next())
            {
                menu[rights->getString("menu_id")+"-view"]=rights->getInt("rr_view"); 
                menu[rights->getString("menu_id")+"-add"]=rights->getInt("rr_add"); 
                menu[rights->getString("menu_id")+"-edit"]=rights->getInt("rr_edit"); 
                menu[rights->getString("menu_id")+"-delete"]=rights->getInt("rr_delete"); 

            }
            res->beforeFirst();
            
            Php::out<< "<div class='row'>"<<endl;
            Php::out<< "<div class='col form-group'>"<<endl;
            Php::out<< "<label for=''>User Role Name</label>"<<endl;
            Php::out<< "<input type='text' disabled class='form-control' id='user_role_name_"<<role->getString("user_role_id")<<"' value='"<<role->getString("user_role_name")<<"' required="">"<<endl;
            Php::out<< "</div></div>"<<endl;
            if(res->rowsCount()>0)
            {
                Php::out<< "<div class='row'>"<<endl;
                Php::out<< "<div class='col-4'>"<<endl;
                Php::out<< "</div>"<<endl;
                Php::out<< "<div class='col'>View"<<endl;
                Php::out<< "</div>"<<endl;
                Php::out<< "<div class='col'>Add"<<endl;
                Php::out<< "</div>"<<endl;
                Php::out<< "<div class='col'>Edit"<<endl;
                Php::out<< "</div>"<<endl;
                Php::out<< "<div class='col'>Delete"<<endl;
                Php::out<< "</div>"<<endl;
                Php::out<< "</div>"<<endl;
            }
            while(res->next())
            {
                
                if(group_id!=res->getInt("group_id"))
                {
                    group_id=res->getInt("group_id");
                    Php::out<<"<div class='row'><div class='col-4'><label class='label h5'>"<<label[res->getString("group_name")]<<"</label></div></div>"<<endl;
                }
                
                view_checked="";
                add_checked="";
                edit_checked="";
                delete_checked="";
                if(menu[res->getString("menu_id")+"-view"]==1)
                {
                    view_checked="checked='checked'";
                }
                if(menu[res->getString("menu_id")+"-add"]==1)
                {
                    add_checked="checked='checked'";
                }
                if(menu[res->getString("menu_id")+"-edit"]==1)
                {
                    edit_checked="checked='checked'";
                }
                if(menu[res->getString("menu_id")+"-delete"]==1)
                {
                    delete_checked="checked='checked'";
                }
                
                // Php::out<<"<div class='form-group custom-control custom-checkbox'>"<<endl;
                Php::out<< "<div class='row' id ='menu-"+res->getString("menu_id")+"'>"<<endl;
                Php::out<< "<div class='col-4'>"<<endl;
                Php::out<<"<label class='label' for='"<<role->getString("user_role_id")<<"-"<<res->getString("menu_id")<<"'>"<<label[res->getString("menu_name")]<<"</label>"<<endl;
                Php::out<<"</div>"<<endl;

                Php::out<<"<div class='col'>"<<endl;
                Php::out<<"<input type='checkbox' class='custom-checkbox' title='View' disabled "<<view_checked<<" id='"<<role->getString("user_role_id")<<"-"<<res->getInt("menu_id")<<"-view'>"<<endl;
                Php::out<<"</div>"<<endl;
                
                Php::out<<"<div class='col'>"<<endl;
                if(res->getInt("menu_add")==1)
                    Php::out<<"<input type='checkbox' class='custom-checkbox' title='Add' disabled "<<add_checked<<" id='"<<role->getString("user_role_id")<<"-"<<res->getInt("menu_id")<<"-add'>"<<endl;
                Php::out<<"</div>"<<endl;

                Php::out<<"<div class='col'>"<<endl;
                if(res->getInt("menu_edit")==1)
                    Php::out<<"<input type='checkbox' class='custom-checkbox' title='Edit' disabled "<<edit_checked<<" id='"<<role->getString("user_role_id")<<"-"<<res->getInt("menu_id")<<"-edit'>"<<endl;
                Php::out<<"</div>"<<endl;

                Php::out<<"<div class='col'>"<<endl;
                if(res->getInt("menu_delete")==1)
                    Php::out<<"<input type='checkbox' class='custom-checkbox' title='Delete' disabled "<<delete_checked<<" id='"<<role->getString("user_role_id")<<"-"<<res->getInt("menu_id")<<"-delete'>"<<endl;
                Php::out<<"</div>"<<endl;



                Php::out<<"</div>"<<endl;
            }
            Php::out<<"<div class='row'>"<<endl;
           // Php::out<<"<div class='col'><input type='button' data-id='"<<role->getString("user_role_id")<<"' class='edit-user-role signUp btn btn-block btn-info mt-2 btn-lg mb-2' value='Edit'></div>";
            Php::out << "<div class='col'><button type='button' data-id='"<<role->getString("user_role_id")<<"' class='btn btn-info user-edit edit-user-role float-right mb-2' value=' Edit ' title='Edit'><i class='fas fa-edit'></i><span id='editrole"<<role->getString("user_role_id")<<"'>Edit</span></button></div>"<< std::endl;  
            if(role->getInt("status")==1)
                Php::out << "<div class='col'><button type='button' data-id='"<<role->getString("user_role_id")<<"' class='btn btn-danger role-enable-disable-btn mr-2 mb-2' title='Disable' value='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i><span id='disableenablerole'>Disable</span></button></div>"<< std::endl;
            else
                Php::out << "<div class='col'><button type='button' data-id='"<<role->getString("user_role_id")<<"' class='btn btn-success role-enable-disable-btn mr-2 mb-2' title='Enable' value='Enable' data-text='Enable'><i class='fas fa-play-circle'></i><span id='disableenablerole'>Enable</span></button></div>"<< std::endl;
            
            Php::out<<"</div"<<endl;
            Php::out<<"</form></div></div></div>"<<endl;
        }  
        delete role;
        delete res;
        delete stmt;
        delete con;        
        }
    catch(const std::exception& e)
        {
        writeException("showUserRoleNavigationList",e.what());
        } 
    }

Php::Value createUserRole(Php::Value data)
{
    sql::Connection *conn;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    string msg = "Failed"; 
    string status = "Failed";
    string menu_id;
    int view_access,add_access,edit_access,delete_access;
    try
        {
        string name=data["name"];
        Php::Value menu=data["menu"];
        
        //Validation
        Php::Value validation_response = validation.DataValidation(name,3,30,3,1);
        if(validation_response["result"]==false)
        {
            msg = validation_failed+"User Role Name:"+ toString(validation_response["reason"]);
            logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);       
            return msg;
        }
        
        for(int i=0;i<menu.size();i++)
        {
            validation_response = validation.DataValidation(menu[i]["id"],0,11,1,1);
            msg = validation_failed;
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Menu id:"+ toString(validation_response["reason"]);
                logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);     
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["view"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for View for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);     
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["add"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Add for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);     
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["edit"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Edit for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);     
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["delete"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Delete for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg);     
                return msg;
            }
            
        }

        msg = "Failed";
        //Validation

        conn = General.mysqlConnect(ServerDB);

        prep_stmt = conn->prepareStatement("insert into system_user_role(user_role_name)values(?)");
        prep_stmt->setString(1, name);
        prep_stmt->execute();

        prep_stmt = conn->prepareStatement("select user_role_id from system_user_role where user_role_name=?");
        prep_stmt->setString(1, name);
        res=prep_stmt->executeQuery();
        if(res->next())
        {
            string user_role_id=res->getString("user_role_id");
            for(int i=0;i<menu.size();i++)
            {
                menu_id=toString(menu[i]["id"]);
                view_access = menu[i]["view"];
                add_access = menu[i]["add"];
                edit_access = menu[i]["edit"];
                delete_access = menu[i]["delete"];
                prep_stmt = conn->prepareStatement("insert into system_user_role_rights(user_role_id,menu_id,rr_view,rr_add,rr_edit,rr_delete)values(?,?,?,?,?,?)");
                prep_stmt->setString(1, user_role_id);
                prep_stmt->setString(2, menu_id);
                prep_stmt->setInt(3, view_access);
                prep_stmt->setInt(4, add_access);
                prep_stmt->setInt(5, edit_access);
                prep_stmt->setInt(6, delete_access);
                prep_stmt->execute();
            }
        }
        delete res;
        delete prep_stmt;
        delete conn;
        
        msg="Successfull";
        status = "Success";
        logUserActivity("User Role", "Create Role, Role name: " + name + " Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), status, toString(data["request_ip"]), arrayToString(data), msg);       

        }
    catch(const std::exception& e)
        {
        writeException("createUserRole",e.what());
        }
    return msg;
}

Php::Value updateUserRoleRights(Php::Value data)
{
    sql::Connection *conn;
    sql::PreparedStatement *prep_stmt;
    sql::ResultSet *res;
    string msg = "Failed"; 
    string status = "Failed";
    string menu_id;
    int view_access,add_access,edit_access,delete_access;
    try
    {
        string user_role_id=data["user_role_id"];
        string user_role_name=data["user_role_name"];
        Php::Value menu=data["menu"];
        
        //Validation
        Php::Value validation_response = validation.DataValidation(user_role_name,3,30,3,1);
        if(validation_response["result"]==false)
        {
            msg = validation_failed+"User Role Name:"+ toString(validation_response["reason"]);
            return msg;
        }
        
        for(int i=0;i<menu.size();i++)
        {
            validation_response = validation.DataValidation(menu[i]["id"],0,11,1,1);
            msg = validation_failed;
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Menu id:"+ toString(validation_response["reason"]);
                logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg); 
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["view"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for View for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg); 
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["add"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Add for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg); 
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["edit"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Edit for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg); 
                return msg;
            }
            validation_response = validation.DataValidation(menu[i]["delete"],0,11,1,1);
            if(validation_response["result"]==false)
            {
                msg = msg+"Invalid Input for Delete for Menu id:"+toString(menu[i]["id"]);
                logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), "Failed", toString(data["request_ip"]), arrayToString(data), msg); 
                return msg;
            }
            
        }
        //Validation

        msg = "Failed";


        conn = General.mysqlConnect(ServerDB);  
        
        if(user_role_name!="")
        {
            prep_stmt = conn->prepareStatement("update system_user_role set user_role_name=? where user_role_id=?");
            prep_stmt->setString(1, user_role_name);
            prep_stmt->setString(2, user_role_id);
            prep_stmt->execute();
            delete prep_stmt;
        }
        //Php::out<<user_role_id<<endl;
        prep_stmt = conn->prepareStatement("update system_user_role_rights set rr_view=0,rr_edit=0,rr_delete=0 where user_role_id=?");
        prep_stmt->setString(1, user_role_id);
        prep_stmt->execute();
        delete prep_stmt;    
        string id;
        for(int i=0;i<menu.size();i++)
        {
            menu_id=toString(menu[i]["id"]);   
            view_access = menu[i]["view"];
            add_access = menu[i]["add"];
            edit_access = menu[i]["edit"];
            delete_access = menu[i]["delete"];
            prep_stmt = conn->prepareStatement("select user_role_rights_id from system_user_role_rights where menu_id=? and user_role_id=?");
            prep_stmt->setString(1, menu_id);
            prep_stmt->setString(2, user_role_id);
            res = prep_stmt->executeQuery();
            delete prep_stmt;
            if(res->next())
            {
                
                id=res->getString("user_role_rights_id");
                prep_stmt = conn->prepareStatement("update system_user_role_rights set rr_view=?,rr_add=?,rr_edit=?,rr_delete=? where user_role_rights_id=?");
               
                prep_stmt->setInt(1, view_access);
                prep_stmt->setInt(2, add_access);
                prep_stmt->setInt(3, edit_access);
                prep_stmt->setInt(4, delete_access);
                prep_stmt->setString(5, id);

                prep_stmt->executeUpdate();
                delete prep_stmt;
                delete res;
            }
            else 
            {
                prep_stmt = conn->prepareStatement("insert into system_user_role_rights(user_role_id,menu_id,rr_view,rr_add,rr_edit ,rr_delete)values(?,?,?,?,?,?)");
                prep_stmt->setString(1, user_role_id);
                prep_stmt->setString(2, menu_id);
                prep_stmt->setInt(3, view_access);
                prep_stmt->setInt(4, add_access);
                prep_stmt->setInt(5, edit_access);
                prep_stmt->setInt(6, delete_access);
                prep_stmt->executeUpdate();
                delete prep_stmt;
            }
            
        }

        delete conn;

           
        msg="Successfull";
        status = "Success";
        logUserActivity("User Role", "Update Role Rights, Result: " + msg, data["login_user_id"], toString(data["login_user_name"]), status, toString(data["request_ip"]), arrayToString(data), msg);  
    }
    catch(const std::exception& e)
    {
        writeException("updateUserRoleRights",e.what());
    }
    return msg;
}

void userRoleDropdown() {
    try {
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        string query = "SELECT * from system_user_role";
        res = stmt->executeQuery(query);
        while (res->next())
            Php::out << "<option value='" << res->getString("user_role_id") << "'>" << res->getString("user_role_name") << "</option>" << endl;
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("userRoleDropdown", e.what());
    }
}

Php::Value insertUpdateUsers(Php::Value json)
{
    sql::Connection *conn;
    sql::PreparedStatement *prep_stmt;    
    sql::ResultSet *res;
    string msg = "Failed",auto_password;  
    string status="Failed";
    int generate_pwd=0;
    try
    {
        string id=json["id"];
        string full_name=json["full_name"];
        string user_name=json["user_name"];
        string email=json["email"];
        string company_name=json["company_name"];
        string phone=json["phone"];
        string start_date=json["start_date"];
        string expiry_date=json["expiry_date"];
        string user_role=json["user_role"];
        string language=json["language"];
        
        
        //Validation
        Php::Value validation_response = validation.DataValidation(full_name,3,100,6,1);
        if(validation_response["result"]==false)
        {
            msg = "Full Name : "+ toString(validation_response["reason"]);
            return msg;
        }
        
        validation_response = validation.DataValidation(user_name,3,100,3,1);
        if(validation_response["result"]==false)
        {
            msg = "User Name : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(company_name,3,100,3,0);
        if(validation_response["result"]==false)
        {
            msg = "Company name : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(email,3,100,5,0);
        if(validation_response["result"]==false)
        {
            msg = "Email : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(phone,6,20,7,0);
        if(validation_response["result"]==false)
        {
            msg = "Phone : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(start_date,10,10,4,1);
        if(validation_response["result"]==false)
        {
            msg = "Account Activation Date : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(expiry_date,10,10,4,1);
        if(validation_response["result"]==false)
        {
            msg = "Account Expiry Date : "+ toString(validation_response["reason"]);
            return msg;
        }

        validation_response = validation.DataValidation(language,3,50,2,0);
        if(validation_response["result"]==false)
        {
            msg = "Language : "+ toString(validation_response["reason"]);
            return msg;
        }







        conn = General.mysqlConnect(ServerDB);        
        if(id=="")
            {
            prep_stmt = conn->prepareStatement("select user_id from system_users where user_name=?");
            prep_stmt->setString(1,user_name);
            res = prep_stmt->executeQuery();
            if(res->next())
                {
                msg="Username already exists.Please try with another username";
                logUserActivity("User Management", "Add User, User name: " + user_name + " Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), status, toString(json["request_ip"]), arrayToString(json), msg);
                delete res;
                delete prep_stmt;
                delete conn;
                return msg;
                }
            delete res;
            delete prep_stmt;
            }
        
        if(id=="")
        {
            string uuid;
            generate_pwd = json["generate_pwd"];
            int reset_pwd = json["reset_pwd"];
            string password=json["password"];

            validation_response = validation.DataValidation(to_string(generate_pwd),1,1,1,0);
            if(validation_response["result"]==false)
            {
                msg = "Invalid Input for Generate Automatic Password";
                return msg;
            }
            
            validation_response = validation.DataValidation(to_string(reset_pwd),1,1,1,0);
            if(validation_response["result"]==false)
            {
                msg = "Invalid Input for Password";
                return msg;
            }

            prep_stmt = conn->prepareStatement("select uuid() as uuid");
            res = prep_stmt->executeQuery();
            if(res->next())                
                uuid = res->getString("uuid");
                
            delete res;
            delete prep_stmt;

            if(generate_pwd==1)
            {
                auto_password = random_string(8);
                password = auto_password;
            }
            
            validation_response = validation.DataValidation(password,6,20,0,1);
            if(validation_response["result"]==false)
            {
                msg = "Password : "+toString(validation_response["reason"]);
                return msg;
            }


            prep_stmt = conn->prepareStatement("insert into system_users(user_id,display_name,user_name,company_name,email,phone,password,user_role_id,language,validity_from_date,validity_to_date,account_status,password_key,password_reset_flag)values(?,?,?,?,?,?,SHA2(CONCAT('"+password+"','"+uuid+"'),256),?,?,?,?,?,?,?)");
            prep_stmt->setInt(1,generateUniqueId());
            prep_stmt->setString(2,full_name);
            prep_stmt->setString(3,user_name);
            prep_stmt->setString(4,company_name);
            prep_stmt->setString(5,email);
            prep_stmt->setString(6,phone);
            prep_stmt->setString(7,user_role);
            prep_stmt->setString(8,language);
            prep_stmt->setString(9,start_date);
            prep_stmt->setString(10,expiry_date);
            prep_stmt->setInt(11,1);
            prep_stmt->setString(12,uuid);
            prep_stmt->setInt(13,reset_pwd);
            prep_stmt->executeUpdate();
          
            delete prep_stmt;
            
            prep_stmt = conn->prepareStatement("select user_id from system_users where user_name=?");
            prep_stmt->setString(1,user_name);
            res=prep_stmt->executeQuery();
            int id=0;
            if(res->next())
                id=res->getInt("user_id");
            
            prep_stmt = conn->prepareStatement("insert into  system_users_password(password,password_key,user_id)values(SHA2(CONCAT(?,?), 256),?,?)");
            prep_stmt->setString(1, password);
            prep_stmt->setString(2, uuid);
            prep_stmt->setString(3, uuid);
            prep_stmt->setInt(4, id);
            prep_stmt->executeUpdate();                        

        }
        else
        {
            //Validation
            Php::Value validation_response = validation.DataValidation(id,1,10,1,1);
            if(validation_response["result"]==false)
            {
                msg = validation_failed+"No User Found:";
                return msg;
            }
            prep_stmt = conn->prepareStatement("update system_users set display_name=?,user_name=?,company_name=?,email=?,phone=?,user_role_id=?,language=?,validity_from_date=?,validity_to_date=? where id=?");
            prep_stmt->setString(1,full_name);
            prep_stmt->setString(2,user_name);
            prep_stmt->setString(3,company_name);
            prep_stmt->setString(4,email);
            prep_stmt->setString(5,phone);
            prep_stmt->setString(6,user_role);
            prep_stmt->setString(7,language);
            prep_stmt->setString(8,start_date);
            prep_stmt->setString(9,expiry_date);
            prep_stmt->setString(10,id);  
            prep_stmt->executeUpdate();
        }
        
        
        delete prep_stmt;;
        delete conn;
        msg = "Successfull";
        status="Success";
        
        if(id=="")            
            logUserActivity("User Management", "Add User, User name: " + user_name + " Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), status, toString(json["request_ip"]), arrayToString(json), msg);            
        else
            logUserActivity("User Management", "Edit User, User name: " + user_name + " Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), status, toString(json["request_ip"]), arrayToString(json), msg);
        
        if(generate_pwd==1)            
            msg = "Auto generated password: "+auto_password;            
        }
    catch(const std::exception& e)
        {
        writeException("insertUpdateUsers",e.what());
        }    
    return msg;
    }

void showUsersList(Php::Value data)
    {
    try
        {
        Php::Value role;
        sql::Connection *con;
        sql::Statement *stmt;            
        sql::ResultSet *res;
        int edit_access = data["edit"];
        int delete_access = data["delete"];
        int user_role_id = data["user_role_id"];
        con= General.mysqlConnect(ServerDB); 
        stmt=con->createStatement();
        res=stmt->executeQuery("select * from system_user_role");
        while(res->next())
            {
            role[res->getInt("user_role_id")]=string(res->getString("user_role_name"));
            }
        delete res;
        
        if(user_role_id==100)
            res=stmt->executeQuery("select * from system_users");
        else
            res=stmt->executeQuery("select * from system_users where user_role_id!=100");
        if(res->rowsCount()>0)
            {
            //Php::out<<"<table class='table table-blue'>"<<endl;
            Php::out << "<thead class='thead-light'>" << std::endl;
            Php::out<<"<tr>"<<endl;
            Php::out<<"<th>User name</th>"<<endl;
            Php::out<<"<th>Name</th>"<<endl; 
            Php::out<<"<th>User Role</th>"<<endl;
            Php::out<<"<th>Language</th>"<<endl;       
            Php::out<<"<th>From</th>"<<endl;           
            Php::out<<"<th>To</th>"<<endl; 
            if(edit_access==1 ||delete_access==1)
                Php::out<<"<th></th>"<<endl;		           
           
            Php::out<<"</tr>"<<endl;	
            Php::out << "</thead>" << std::endl;		
            }
        while(res->next())
            {
            Php::out<<"<tr data-id='"<<res->getString("user_id")<<"'>"<<endl;                       
            Php::out<<"<td>"+res->getString("user_name")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("display_name")+"</td>"<<endl;            
            Php::out<<"<td>"<<role[res->getInt("user_role_id")]<<"</td>"<<endl;
            Php::out<<"<td>"+res->getString("language")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("validity_from_date")+"</td>"<<endl;
            Php::out<<"<td>"+res->getString("validity_to_date")+"</td>"<<endl;            
            if(edit_access==1 ||delete_access==1){
                Php::out << "<td>"<< std::endl;
                if(delete_access==1)
                {
                    if(res->getInt("account_status")==1)
                        Php::out << "<button type='button' class='btn btn-danger user-enable-disable-btn' title='Disable' data-text='Disable'><i class='fas fa-stop-circle'></i></button>"<< std::endl;
                    else
                        Php::out << "<button type='button' class='btn btn-success user-enable-disable-btn' title='Enable' data-text='Enable'><i class='fas fa-play-circle'></i></button>"<< std::endl;
                }
                if(edit_access==1)
                {
                    Php::out << "<button type='button' class='btn btn-info user-edit' title='Edit'><i class='fas fa-edit'></i></button>"<< std::endl;            

                    Php::out << "<button type='button' class='btn btn-info user-change-password' title='Change password'><i class='fas fa-key'></i></button>"<< std::endl;    
                }
                Php::out << "</td>"<< std::endl;
            }
            Php::out<<"</tr>"<<endl;	
            }
        //Php::out<<"</table>"<<endl;	
        delete res;    
        delete stmt;
		delete con;  
        }
    catch(const std::exception& e)
        {
        writeException("showUsersList",e.what());
        }
    
    }

Php::Value enableDisableUser(Php::Value json) {
    string msg = "Failed";
    string response_status="Failed";
    try {
        string status = json["status"];
        string id = json["id"];
        sql::Connection *con;
        sql::Statement *stmt;                    
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        string query = "update system_users set account_status=" + status + " where user_id=" + id;
        stmt->executeUpdate(query);
        delete stmt;
        delete con;
        msg = "Successfull";
        response_status="Success"   ;     
    }    catch (const std::exception& e) {
        writeException("enableDisableUser", e.what());
    }
    logUserActivity("User Management",  toString(json["activity_message"] )+" Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), response_status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}

Php::Value changePassword(Php::Value json) {
    string msg = "";
    string status = "Failed";
    string user_name = "";
    try {
        int id = json["id"];
        string current_password = json["current_password"];
        string new_password = json["new_password"];
        

        int current_password_length = current_password.length();
        int new_password_length = new_password.length();

        int password_min = 6;
        int password_max = 20;
        if(id==0)
           msg="User id not available";     
        else if (new_password_length == 0 || current_password_length == 0)
            msg = "Please enter password";
        else if (current_password_length < password_min || new_password_length < password_min)
            msg = "Password must have at least " + to_string(password_min) + " characters";
        else if (current_password_length > password_max || new_password_length > password_max)
            msg = "Password is too long";
        else {
            msg = "Failed";
            sql::Connection *con;
            sql::PreparedStatement *pstmt;
            string query;
            sql::ResultSet *res;
            con = General.mysqlConnect(ServerDB);

            query = "SELECT uuid() as uuid,user_name from system_users where user_id=? and password=SHA2(CONCAT(?,password_key), 256)";
            pstmt = con->prepareStatement(query);
            pstmt->setInt(1, id);
            pstmt->setString(2, current_password);
            res = pstmt->executeQuery();
            if (res->next()) {
                string uuid = res->getString("uuid");
                user_name=res->getString("user_name");
                delete res;
                delete pstmt;

                query = "SELECT id from system_users_password  where user_id=? and password=SHA2(CONCAT(?,password_key), 256)";
                pstmt = con->prepareStatement(query);
                pstmt->setInt(1, id);
                pstmt->setString(2, new_password);
                res = pstmt->executeQuery();
                if (res->next()) {
                    msg = "Already used password,Please use a new password";
                } else {
                    query = "update system_users set password_reset_flag=0,password=SHA2(CONCAT(?,?), 256),password_key=? where user_id=?";
                    pstmt = con->prepareStatement(query);
                    pstmt->setString(1, new_password);
                    pstmt->setString(2, uuid);
                    pstmt->setString(3, uuid);
                    pstmt->setInt(4, id);
                    pstmt->executeUpdate();
                    delete pstmt;

                    query = "insert into  system_users_password(password,password_key,user_id)values(SHA2(CONCAT(?,?), 256),?,?)";
                    pstmt = con->prepareStatement(query);
                    pstmt->setString(1, new_password);
                    pstmt->setString(2, uuid);
                    pstmt->setString(3, uuid);
                    pstmt->setInt(4, id);
                    pstmt->executeUpdate();

                    msg = "Successfull";
                    status = "Success";
                }
            } else
                msg = "current password is wrong";

            delete res;
            delete pstmt;
            delete con;
        }
                        
            
    }    catch (const std::exception& e) {
        writeException("changePassword", e.what());
    }
    
    logUserActivity("Reset Password",  "User name: " + user_name +" Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), status, toString(json["request_ip"]), arrayToString(json), msg);
    return msg;
}

Php::Value getUserDetails(Php::Value json) {
    Php::Value response;
    int day_block_count = 0, block_count = 0,failed_login_count=9;
    
    try {
        string id = json["id"];
        sql::Connection *con;
        sql::Statement *stmt;
        sql::ResultSet *res;
        con = General.mysqlConnect(ServerDB);
        stmt = con->createStatement();
        string query = "select setting_name,setting_value from system_settings where setting_name in('failed_login_attempt_per_day','max_allowed_login_attempt')";
        res = stmt->executeQuery(query);
        if(res->rowsCount()>0){
            while(res->next()){
                if (res->getString("setting_name") == "failed_login_attempt_per_day")
                    day_block_count = res->getInt("setting_value");
                else if (res->getString("setting_name") == "max_allowed_login_attempt")
                    block_count = res->getInt("setting_value"); 
            }
            failed_login_count = day_block_count * block_count;
        }
        

        query = "select * from system_users where user_id=" + id;
        res = stmt->executeQuery(query);
        if (res->next()) {
            sql::ResultSetMetaData *res_meta = res -> getMetaData();
            int columns = res_meta -> getColumnCount();
            for (int i = 1; i <= columns; i++)
                response[res_meta -> getColumnLabel(i)] = string(res->getString(i));

            if(res->getString("validity_to_date")<General.currentDateTime("%Y-%m-%d"))
            {
                response["alert_message"] = "User account expired";
            }
            else if(res->getInt("account_status")==0 && res->getInt("failed_login_count")>=failed_login_count)
            {
                response["alert_message"] = "User account disabled due to multiple failed login attempts";
            }
            else if(res->getInt("status")==0)
            {
                response["alert_message"] = "User role has been disabled by administrator";
            }
        }
        delete res;
        delete stmt;
        delete con;
    }    catch (const std::exception& e) {
        writeException("getUserDetails", e.what());
    }
    return response;
}

Php::Value loginUser(Php::Value json) {
    Php::Value response;
    response["status"] = 400;
    string message = "Failed";
    string result = "";
    string status = "Failed";
    int user_id = 0;
    try {
        string ip = json["request_ip"];
        string user_name = json["user_name"];
        string password = json["password"];

        int password_min = 6;
        int password_max = 20;
        int username_min = 3;
        int username_max = 100;
        int password_length = password.length();
        int user_name_length = user_name.length();

        if (user_name_length == 0)
            result = "Please enter username";
        else if (password_length == 0)
            result = "Please enter password";
        else if (user_name_length < username_min)
            result = "Username must have at least " + to_string(username_min) + " characters";
        else if (user_name_length > username_max)
            result = "Username is too long";
        else if (password_length < password_min)
            result = "Password must have at least " + to_string(password_min) + " characters";
        else if (password_length > password_max)
            result = "Password is too long";
        else {
            result = "Invalid username/password";
            bool valid = false;
            int day_block_count = 0, block_count = 0, blocking_delay = 0,password_expiry_days=0;
            sql::Connection *con;
            sql::PreparedStatement *pstmt;
            string query;
            sql::ResultSet *res;
            con = General.mysqlConnect(ServerDB);


            query = "select setting_name,setting_value from system_settings where setting_name in('password_expiry_days','failed_login_attempt_per_day','max_allowed_login_attempt','blocking_delay_login_failed')";
            pstmt = con->prepareStatement(query);
            res = pstmt->executeQuery();
            while (res->next()) {
                if (res->getString("setting_name") == "failed_login_attempt_per_day")
                    day_block_count = res->getInt("setting_value");
                else if (res->getString("setting_name") == "max_allowed_login_attempt")
                    block_count = res->getInt("setting_value");
                else if (res->getString("setting_name") == "blocking_delay_login_failed")
                    blocking_delay = res->getInt("setting_value");
                if (res->getString("setting_name") == "password_expiry_days")
                    password_expiry_days = res->getInt("setting_value");
            }
            delete pstmt;
            delete res;

            query = "SELECT SHA2(CONCAT(?,password_key), 256) as user_password,password,password_reset_flag,validity_from_date,validity_to_date,display_name,system_users.user_role_id,user_role_name,account_status,user_id,language,status,TIMESTAMPDIFF(MINUTE, last_login_attempt,now()) as last_login_delay,failed_login_count  FROM system_users INNER JOIN system_user_role ON system_users.user_role_id=system_user_role.user_role_id where user_name= ? LIMIT 1";
            pstmt = con->prepareStatement(query);
            pstmt->setString(1, password);
            pstmt->setString(2, user_name);
            res = pstmt->executeQuery();
            if (res->next()) {
                user_id = res->getInt("user_id");
                string current_date = General.currentDateTime(dateFormat);
                string expiry_date = res->getString("validity_to_date");
                string start_date = res->getString("validity_from_date");
                string user_password = res->getString("user_password");
                int last_login_delay = res->getInt("last_login_delay");
                int failed_login_count = res->getInt("failed_login_count");

                password = res->getString("password");

                if (res->getInt("account_status") == 0) {
                    message = "Account disabled";
                    result = "Login failed,Please contact admin";
                } else if (failed_login_count > 0 && failed_login_count % block_count == 0 && last_login_delay < blocking_delay) {
                    message = "Login blocked due to consecutive invalid login";
                    result = "Login blocked,Please try after " + to_string(blocking_delay - last_login_delay) + " minutes";
                    valid = true;
                } else if (res->getInt("status") == 0) {
                    message = "Userole " + res->getString("user_role_name") + " disabled";
                    result = "Login failed,Please contact admin";
                } else if (password != user_password) {
                    message = "Wrong password";
                } else if (expiry_date.compare(current_date) < 0) {
                    message = "User validity expired";
                    result = "Login failed,Please contact admin";
                } else if (start_date.compare(current_date) > 0) {
                    message = "User validity not started";
                    result = "Login failed,Please contact admin";
                }
                else {
                    valid = true;
                    status = "Success";
                    message = "Valid login";
                    result = message;
                    response["status"] = 200;
                    response["operator_name"] = string(res->getString("display_name"));
                    response["user_role_id"] = string(res->getString("user_role_id"));
                    response["user_role_name"] = string(res->getString("user_role_name"));
                    response["user_id"] = string(res->getString("user_id"));
                    response["language"] = string(res->getString("language"));
                    response["home"] = "";
                    int password_reset = res->getInt("password_reset_flag");
                    string user_id = res->getString("user_id");
                    delete res;
                    delete pstmt;

                    if (password_reset == 0) {
                        pstmt = con->prepareStatement("SELECT TIMESTAMPDIFF(DAY, date_time,now()) as days FROM system_users_password where user_id=" + user_id + " order by id desc limit 1");
                        res = pstmt->executeQuery();
                        if (res->next()) {
                            if (res->getInt("days") >= password_expiry_days)
                                password_reset = 1;
                        }
                        delete res;
                        delete pstmt;
                    }

                    if (password_reset == 1)
                        pstmt = con->prepareStatement("select menu_link from system_menu where menu_name='reset_password'");
                    else
                        pstmt = con->prepareStatement("select menu_link from system_menu where menu_name='home'");
                    res = pstmt->executeQuery();
                    if (res->next())
                        response["home"] = string(res->getString("menu_link"));

                    pstmt = con->prepareStatement("update system_users set last_login_attempt=CURRENT_TIMESTAMP,failed_login_count=0,password_reset_flag=? where user_name=?");
                    pstmt->setInt(1, password_reset);
                    pstmt->setString(2, user_name);
                    pstmt->executeUpdate();

                }
                delete res;

                if (valid == false) {
                    if (failed_login_count == (block_count * day_block_count - 1))
                        {
                        pstmt = con->prepareStatement("update system_users set last_login_attempt=CURRENT_TIMESTAMP,failed_login_count=failed_login_count+1,account_status=0 where user_name=?");
                        message="Account blocked due to consecutive invalid login";
                        }
                    else
                        pstmt = con->prepareStatement("update system_users set last_login_attempt=CURRENT_TIMESTAMP,failed_login_count=failed_login_count+1 where user_name=?");

                    pstmt->setString(1, user_name);
                    pstmt->executeUpdate();
                }
            } else
                message = "Wrong Username";
            delete pstmt;
            delete con;
        }

        response["message"] = result;
        json["password"] = "******";
        logUserActivity("Login", "User name: " + user_name + " Result: " + message, user_id, user_name, status, ip, arrayToString(json), arrayToString(response));
    }    catch (const std::exception& e) {
        writeException("loginUser", e.what());
    }
    return response;
}

void logParcxUserActivity(Php::Parameters &params) {
    string category = params[0];
    string description = params[1];
    int user_id = params[2];
    string user_name = params[3];
    string status = params[4];
    string ip = params[5];
    string request = params[6];
    string response = params[7];
    logUserActivity(category, description, user_id, user_name, status, ip, request, response);
}


Php::Value enableDisableRole(Php::Value json)
    {
    string msg = "Failed"; 
    string op_status = "Failed";
    sql::Connection *conn;
    sql::PreparedStatement *prep_stmt;
    try
        {
        string status=json["status"];
        string id=json["id"];
        conn= General.mysqlConnect(ServerDB); 
        prep_stmt=conn->prepareStatement("update system_user_role set status=? where user_role_id=?");
        prep_stmt->setString(1, status);
        prep_stmt->setString(2, id);
               
         prep_stmt->executeUpdate();
        delete prep_stmt;
        delete conn;       
        msg = "Successfull";
        op_status="Success";
        

        if(status=="1")
            logUserActivity("User Roles", "User Role Enabled, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
        else if(status=="0")
            logUserActivity("User Roles", "User Role Disabled, Result: " + msg, json["login_user_id"], toString(json["login_user_name"]), op_status, toString(json["request_ip"]), arrayToString(json), msg);
        }
    catch(const std::exception& e)
        {
        writeException("enableDisableRole",e.what());
        }
        
    return msg;
    }

Php::Value checkPageAccess(Php::Value json) {
    Php::Value access;
    access["view"] = 0;
    access["add"] = 0;
    access["edit"] = 0;
    access["delete"] = 0;
    try {
        string page = json["page"];
        int user_role_id = json["user_role_id"];

        if (user_role_id == 100) {
            access["view"] = 1;
            access["add"] = 1;
            access["edit"] = 1;
            access["delete"] = 1;
            return access;
        }

        int menu_add=0,menu_delete=0,menu_edit=0;
        string menu_id;
        sql::Connection *con = General.mysqlConnect(ServerDB); 
        sql::Statement *stmt;
        sql::ResultSet *res;
        stmt = con->createStatement();
        res = stmt->executeQuery("SELECT menu_id,menu_add,menu_edit,menu_delete from  system_menu where menu_name='" + page + "' and menu_status>0");
        if (res->next()) 
        {
            menu_id = res->getString("menu_id");
            menu_add = res->getInt("menu_add");
            menu_edit = res->getInt("menu_edit");
            menu_delete = res->getInt("menu_delete");
          
            
            delete res;


            res = stmt->executeQuery("SELECT rr_view,rr_add,rr_edit,rr_delete FROM system_user_role_rights where menu_id=" + menu_id + " and user_role_id=" + to_string(user_role_id));
            if (res->next()) {
                access["view"] = res->getInt("rr_view");

                if(menu_add==1)
                    access["add"] = res->getInt("rr_add");
                if(menu_edit==1)
                    access["edit"] = res->getInt("rr_edit");
                if(menu_delete==1)
                    access["delete"] = res->getInt("rr_delete");
            }
            delete res;
        }

        
        delete stmt;
        delete con;
    }    
    catch (exception &e) {
        writeException("checkPageAccess", e.what());
    }
    return access;
}

Php::Value parcxV2UserManagement(Php::Parameters &params) {
    Php::Value data = params[0];
    int task = data["task"];
    Php::Value response;
    switch (task) {
        case 1:showSideBar(data);
            break;
        case 2:showNavigationList();
            break;
        case 3:response = createUserRole(data);
            break;
        case 4:showUserRoleNavigationList();
            break;
        case 5:response = updateUserRoleRights(data);
            break;
        case 6:userRoleDropdown();
            break;
        case 7:response = insertUpdateUsers(data);
            break;
        case 8:showUsersList(data);
            break;
        case 9:response = enableDisableUser(data);
            break;
        case 10:response = changePassword(data);
            break;
        case 11:response = getUserDetails(data);
            break;
        case 12:response = loginUser(data);
            break;
        case 13:response=enableDisableRole(data);
            break;
        case 14:response=checkPageAccess(data);
            break;
    }
    return response;
}


extern "C" {

    PHPCPP_EXPORT void *get_module() {
        static Php::Extension extension("PX_V2_UserManagement", "1.0");
        extension.add<parcxV2UserManagement>("parcxV2UserManagement");
        extension.add<logParcxUserActivity>("logParcxUserActivity");
        return extension;
    }
}