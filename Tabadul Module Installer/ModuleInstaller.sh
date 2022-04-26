#!/bin/bash
#sleep 45
#cd /opt/
#echo "Enter directory name"
#read dirname



#Start. Tabadul Module Installation

#Copy Web Application
chmod 777 -R Module
chmod 777 -R phpcpp
chmod 777 smartgate_request.sql
echo "************ Copying Tabadul Module to htdocs/parcx/modules folder"
echo " "

now=$(date +"%m_%d_%Y")
mkdir -p /opt/backup/webapp_module_backup_${now}
if [ ! -d "/opt/lampp/htdocs/parcx/modules/Tabadul" ]
then
    echo "Tabadul Module folder doesn't exist. Creating now"    
else
    echo "Tabadul Module folder Already Exits.Replacing existing files ."   
    #cp -r /opt/lampp/lib/php/extensions/no-debug-non-zts-20170718/PX_ContentManagement.so /opt/lampp/htdocs/parcx-V02-2021/modules/content_management
    chmod 777 -R /opt/lampp/htdocs/parcx/
    mv /opt/lampp/htdocs/parcx/modules/Tabadul /opt/backup/webapp_module_backup_${now}
fi


#Make dir for Media/SmartGate/images
cp -avr Module/Tabadul /opt/lampp/htdocs/parcx/modules

#Copy menu icons

#Copy PHP CPP Modules
: echo "************ Copying Web Application Phpcpp Modules"
echo " "
EXTENSION_DIR=$(/opt/lampp/bin/php-config --extension-dir)
echo $EXTENSION_DIR
echo " "
cd phpcpp
cp -avr *.so $EXTENSION_DIR

cd ..
echo " "
echo " "
#End. Parcx Application Installation




echo "*************"
echo "Server Config"
echo "*************"

#Update Php.ini
#echo 'extension="PX_ContentManagement.so"' >> /opt/lampp/etc/php.ini


#load lib
#sudo ldconfig -v
#restart xampp

echo "*************"
echo "Database Config"
echo "*************"
cd Tables
USER='parcxservice'
PASS='1fromParcx!19514'
PORT=3306
HOST='localhost'
DBASE='parcx_reporting'
export PATH=$PATH:/opt/lampp/bin
mysql -u$USER -p$PASS -P$PORT -h$HOST -D$DBASE -f <smartgate_request.sql

sudo /opt/lampp/xampp restart

echo "*************"
echo "Tabadul Module Installation Completed"
echo "*************"


#echo "end"
#exit 
