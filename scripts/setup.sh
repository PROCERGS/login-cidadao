#!/bin/bash
sudo su
cd ~

yum -y update
yum -y groupinstall "Development Tools"

yum install nodejs npm -y

yum -y install postgres-server
service postgresql initdb
chkconfig postgresql on

su - postgres
psql postgres postgres
alter user postgres with password 'postgres'
exit
exit

mysql -uroot -e 'create database login';

yum -y install httpd
chkconfig --levels 235 httpd on
rm /etc/httpd/conf.d/welcome.conf
/etc/init.d/httpd start

yum install -y memcached

yum -y install php-pgsql php-gd php-imap php-ldap php-pear php-xml php-xmlrpc php-mbstring php-mcrypt  php-snmp php-soap php-tidy curl curl-devel php-suhosin php-xcache php-pecl-memcache
cp /login-cidadao/scripts/login.conf /etc/httpd/conf.d/
cp /login-cidadao/scripts/php.ini /etc/php.ini
/etc/init.d/httpd restart

chown -R apache:apache /login-cidadao
chmod 755 /login-cidadao

dd if=/dev/zero of=/swapfile bs=1024 count=65536
mkswap /swapfile
swapon /swapfile

echo "/swapfile          swap            swap    defaults        0 0" >> /etc/fstab

cp /login-cidadao/app/config/parameters.yml.dist /login-cidadao/app/config/parameters.yml

cd /login-cidadao
curl -s https://getcomposer.org/installer | php
php composer.phar install

chmod 777 /login-cidadao/app/cache
chmod 777 /login-cidadao/app/logs

sudo -u apache php app/console doctrine:schema:create
sudo -u apache php app/console assets:install --symlink
sudo -u apache php app/console assetic:dump