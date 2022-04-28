//sudo g++ -std=c++11 -Wall -o ModuleInstaller ModuleInstaller.cpp
//Edit   sudo nano /etc/sudoers to allow sudo access to www-data  Add at the end:-       www-data ALL=(ALL)NOPASSWD:ALL(ubuntu)
//daemon ALL=(ALL)NOPASSWD:ALL for  centos

#include <phpcpp.h>
#include<iostream>
#include<stdlib.h>
#include<fstream>
#include<time.h>
#include<ctime>
#include <dirent.h>
#include <sys/types.h>
#include <signal.h>
#include<unistd.h>
#include <chrono>
#include <thread>
#include <math.h>
using namespace std;
#define HOST "DBServer"
#define USER "parcxservice"
#define PASS "1fromParcx!19514"
#define DATABASE "parcx_server"
#define EXTENSION_DIR "/opt/lampp/bin/php-config --extension-dir"
#define PORT "3306"

double percent;
string tmp;
int step = 1;
int displayNext = step;
int i;
int errorCode =0;
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
}


void progressBar(int step,int total)
{
    std::string progress_bar;
    int progress_level = (100 * step) / total;

   

    for (double percentage = 0; percentage <= 100; percentage += progress_level)
    {
        progress_bar.insert(0, 1, '#');
        std::cout << "\r [" << round(progress_level) << '%' << "] " << progress_bar;
        std::this_thread::sleep_for(std::chrono::milliseconds(500));       
    }
    if(step==total)
        std::cout << "\n\n";
}


int main()
{

    //Start. Parcx Tabadul Module Installation
    
    //copy Web Application
   // printf("\033[31mred text\n");
  //printf("\033[44;37m\n");
  //printf("\033[0mdefault colors\n");
    system("chmod 777 -R Module");
    system("chmod 777 -R phpcpp");
    system("chmod 777 -R Tables");
    system("chmod 777 -R Webservice");
    printf("\033[0m\n");
    //"************ Copying Tabadul Module to htdocs/parcx/modules folder"<<endl;
    
    //cout<<" "<<endl;

    string current_date =currentDateTime("%Y-%m-%d");
    printf("\033[44;37m\n");
    cout<<"************ Installing Tabadul Module ************"<<endl;
    DIR* dir = opendir("/opt/lampp/htdocs/parcx/modules/Tabadul");
    if (dir)
    {
        //********Tabadul folder already exists.Replacing existing files .
        
        cout<<"#    Backup existing folder\t\t"<<"Completed !!"<<endl; 
            
            
        tmp = "mkdir -p /opt/backup/webapp_module_backup_"+current_date;
        system(tmp.c_str());
        //progressBar(1,4);
        //std::this_thread::sleep_for( std::chrono::microseconds(300) );
   
    
    
        system("cp -r /opt/lampp/lib/php/extensions/no-debug-non-zts-20170718/Tabadul.so /opt/lampp/htdocs/parcx/modules/Tabadul");
        //bar.update();
        //progressBar(2,4);
        //std::this_thread::sleep_for( std::chrono::microseconds(300) );
    

    
        system("chmod 777 -R /opt/lampp/htdocs/parcx/");      
        //tmp = "mv /opt/lampp/htdocs/parcx/modules/Tabadul /opt/backup/webapp_module_backup_"+current_date;
        //bar.update();
        //progressBar(3,4);
        //std::this_thread::sleep_for( std::chrono::microseconds(300) );
    

    
        tmp = "cp -rlf /opt/lampp/htdocs/parcx/modules/Tabadul /opt/backup/webapp_module_backup_"+current_date;
        tmp = tmp +"&& rm -r /opt/lampp/htdocs/parcx/modules/Tabadul";
        system(tmp.c_str()); 
       // bar.update();
       //progressBar(4,4);
        //std::this_thread::sleep_for( std::chrono::microseconds(300) );
    

        
       
        
    }
    else
    {
        cout<<"#    Creating module for Tabadul\t\t"<<"Completed !!"<<endl;
    }
    
  
    //cout<<"  "<<endl;

    
    system("cp -ar Module/Tabadul /opt/lampp/htdocs/parcx/modules");
    system("cp -ar Webservice/SmartGate /opt/lampp/htdocs");
    cout<<"#    Copying required files\t\t"<<"Completed !!"<<endl;
    //Copy menu icons

    //Copy PHP CPP Modules
    //cout<<"************ Copying Web Application Phpcpp Modules"<<endl;
    //cout<<" "<<endl;
    
    //cout<<EXTENSION_DIR<<endl;
    //tmp = "cd phpcpp && cp -ar *.so /opt/lampp/lib/php/extensions/no-debug-non-zts-20170718"; 
    tmp = "cd phpcpp && cp -ar *.so "+string(EXTENSION_DIR);
    system(tmp.c_str());

    system("cd ..");


    cout<<"#    Copying extension modules\t\t"<<"Completed !!"<<endl;
    //End. Tabadul Module Installation




    //cout<<"*************"<<endl;
    //cout<<"Server Config"<<endl;
    //cout<<"*************"<<endl;

    //Update Php.ini
    //system("echo 'extension=\"PX_Tabadul.so\"' >> /opt/lampp/etc/php.ini");
    //system("sudo ldconfig >nul 2>nul");
    //cout<<"#    Server Configuration\t\t"<<"Completed !!"<<endl;
    //load lib
    
    

    //cout<<"*************"<<endl;
    //cout<<"Database Config"<<endl;
    //cout<<"*************"<<endl;

    
    //system("export PATH=$PATH:/opt/lampp/bin");
    tmp = "export PATH=$PATH:/opt/lampp/bin && mysql -u"+string(USER)+" -p"+string(PASS)+" -P"+string(PORT)+" -h"+string(HOST)+" -D"+string(DATABASE)+" -f <Tables/smartgate_request.sql 2> /dev/null";
    errorCode =system(tmp.c_str());
    if(errorCode)
        cout<<"#    Database Configuration\t\t"<<"\033[31mFailed !!\033[44;37m"<<endl;
    else
        cout<<"#    Database Configuration\t\t"<<"Completed !!"<<endl;

    errorCode = 0;
    errorCode = system("sudo /opt/lampp/xampp restart");
    if(errorCode)
        cout<<"#    Restarting XAMPP\t\t\t"<<"Failed !!"<<endl;
    else
        cout<<"#    Restarting XAMPP\t\t\t"<<"Completed !!"<<endl;

    //cout<<"*************"<<endl;
    cout<<"************ Tabadul Module Installation Completed ************"<<endl;
    //cout<<"*************"<<endl;


    printf("\033[0m\n");



    //echo "end"
    //exit 
    return 0;
}
