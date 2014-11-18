#!/usr/bin/env bash

yum -y install deltarpm
yum -y update
yum -y install policycoreutils-python
yum -y install gcc httpd postgresql-server epel-release memcached
yum -y install nodejs
yum -y install --disablerepo=epel --skip-broken php*
yum -y install git
yum -y install samba samba-client samba-common

# enable ACL
sed -i 's/\(defaults\)\([ ]\+1 1\)/\1,acl\2/' /etc/fstab
mount -a

# network setup
firewall-cmd --zone=public --add-port=80/tcp --permanent

# php-fpm setup
systemctl enable php-fpm.service
systemctl restart php-fpm.service

# httpd setup
systemctl enable httpd.service
rm /etc/httpd/conf.d/welcome.conf
cp /vagrant/provisioning/httpd.vhost.conf /etc/httpd/conf.d/login-cidadao.conf
cp /vagrant/provisioning/php.ini /etc/php.ini
systemctl restart httpd.service

# PostgreSQL setup
postgresql-setup initdb

sed -ri 's/^(host[ ]+[a-z]+[ ]+[a-z]+[ ]+[a-z0-9:./]+[ ]+)(ident)/\1md5/' /var/lib/pgsql/data/pg_hba.conf

echo "local all all              md5" >> /var/lib/pgsql/data/pg_hba.conf
echo "host  all all 127.0.0.1/32 md5" >> /var/lib/pgsql/data/pg_hba.conf
systemctl enable postgresql.service
systemctl start postgresql.service
sudo -u postgres psql -d postgres -c "CREATE USER login_cidadao WITH PASSWORD 'login_cidadao';"
sudo -u postgres createdb --owner login_cidadao login_cidadao

# Application download
cd /var/www
git clone https://github.com/PROCERGS/login-cidadao.git
cd /var/www/login-cidadao
git checkout dev

# SMB Share setup
mv /etc/samba/smb.conf /etc/samba/smb.conf.bak
cp /vagrant/provisioning/smb.conf /etc/samba/smb.conf
systemctl enable smb.service
systemctl enable nmb.service
systemctl restart smb.service
systemctl restart nmb.service
firewall-cmd --permanent --zone=public --add-service=samba
firewall-cmd --reload

# Application setup
cd /var/www/login-cidadao
/var/www/login-cidadao/install.sh

# Finish
LOCAL_IP=$(ip addr | grep 'state UP' -A2 | grep 'eth1' | tail -n1 | awk '{print $2}' | cut -f1  -d'/')
echo "A Samba share is available at smb://$LOCAL_IP/login-cidadao"
echo "Use any username and an empty password."
