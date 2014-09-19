#!/bin/bash
sudo su
cd ~

yum -y update
yum -y groupinstall "Development Tools"
#yum -y install git

git clone https://github.com/joyent/node.git
cd node
./configure
make
make install

yum -y install mysql mysql-server
chkconfig --levels 235 mysqld on
/etc/init.d/mysqld start

yum -y install httpd
chkconfig --levels 235 httpd on
/etc/init.d/httpd start

yum -y install php php-gd php-imap php-ldap php-pear php-xml php-xmlrpc php-mbstring php-mcrypt  php-snmp php-soap php-tidy curl curl-devel php-mysql php-suhosin php-xcache
cp /login-cidadao/scripts/login.conf /etc/httpd/conf.d/
cp /login-cidadao/scripts/php.ini /etc/php.ini
/etc/init.d/httpd restart

chown -R www:www /login-cidadao
chmod 755 /login-cidadao

cd /login-cidadao
curl -s https://getcomposer.org/installer | php
php composer.phar install
