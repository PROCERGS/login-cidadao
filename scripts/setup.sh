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
rm /etc/httpd/conf.d/welcome.conf
/etc/init.d/httpd start

yum -y install php php-gd php-imap php-ldap php-pear php-xml php-xmlrpc php-mbstring php-mcrypt  php-snmp php-soap php-tidy curl curl-devel php-mysql php-suhosin php-xcache
cp /login-cidadao/scripts/login.conf /etc/httpd/conf.d/
cp /login-cidadao/scripts/php.ini /etc/php.ini
/etc/init.d/httpd restart

chown -R apache:apache /login-cidadao
chmod 755 /login-cidadao


dd if=/dev/zero of=/swapfile bs=1024 count=65536
mkswap /swapfile
swapon /swapfile

echo "/swapfile          swap            swap    defaults        0 0" >> /etc/fstab

cd /login-cidadao
curl -s https://getcomposer.org/installer | php
php composer.phar install
