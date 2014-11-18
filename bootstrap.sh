#!/usr/bin/env bash

yum -y install deltarpm
yum -y update
yum -y install policycoreutils-python
yum -y install gcc httpd postgresql-server epel-release memcached
yum -y install nodejs
yum -y install --disablerepo=epel --skip-broken php*

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

# Application setup
cd /vagrant
/vagrant/install.sh --skip-acl
