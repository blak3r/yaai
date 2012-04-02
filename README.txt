*********************************************************************************
STARFACE SugarCRM Connector is a computer telephony integration module for the
SugarCRM customer relationship managment program by SugarCRM, Inc.

Special Thanks go to to SugarCRM, Inc. who gave excellent support to get started
to provide this Connector as an upgrade-safe SugarCRM module.

You can contact vertico software GmbH at Amalienstr. 81-87, 76133 Karlsruhe,
GERMANY or at the e-mail address info@vertico-software.com
********************************************************************************

SYSTEM REQUIREMENTS:

 o SugarCRM 6.0+ (Community, Professional, Enterprise)
 o PHP 5.2+ ! Important for Ajax !
 o MySql
 
 
 *******************************************************************************
 * Parts of this code are (c) 2009 abcona e. K.
 * Angelo Malaguarnera www.abcona.de E-Mail admin@abcona.de
 * http://www.sugarforge.org/projects/yaai/
 *******************************************************************************

IMPORTANT !!!
If you run CentOs 5.2 with PHP 5.1.6 you have to enabled the json.so module.
At first look if it is the module include:
#ls -la /usr/lib64/php/modules/ | grep json.so
-rwxr-xr-x 1 root root   92750 Jun  1 19:41 json.so
(if not, try this 
#pecl install json
or google how to install php-json.

then add the json.ini file in /etc/php.d :
#echo -e "; Enable json extension module \nextension=json.so" > /etc/php.d/json.ini

or upgrade php 5.2.9
http://www.centos.org/modules/newbb/viewtopic.php?topic_id=20657&forum=41

/etc/init.d/httpd restart


Have Fun




		