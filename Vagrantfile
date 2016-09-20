# -*- mode: ruby -*-
# vi: set ft=ruby :
$dependencies = <<SCRIPT
    # Instalando pacotes do php.
    # Observe que aqui vamos optar por usar o postgres, mas e possivel usar mysql sem problemas
    sudo apt-get install -y php5 php5-curl php5-intl php5-pgsql php5-memcache

    sudo apt-get install -y memcached php5-memcached

    sudo apt-get install -y postgresql git

    # Instalando Composer
    sudo apt-get install -y curl
    sudo curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

    # Instalando nodejs
    sudo curl -sL https://deb.nodesource.com/setup_5.x | bash -
    sudo apt-get install -y nodejs

    sudo -u postgres createuser -d vagrant

    # edita /etc/php5/cli/php.ini
    # se for executar no bash na mao, use:
    # sudo sed -i 's/;date.timezone/date.timezone = America\/Sao_Paulo/' /etc/php5/cli/php.ini
    sudo sed -i "s/;date.timezone =/date.timezone = America\\\/Sao_Paulo/" /etc/php5/cli/php.ini
    sudo dd if=/dev/zero of=/swapfile bs=1024 count=256k
    sudo mkswap /swapfile
    sudo chown root:root /swapfile
    sudo chmod 0600 /swapfile
    echo 10 | sudo tee /proc/sys/vm/swappiness
    echo vm.swappiness = 10 | sudo tee -a /etc/sysctl.conf
    sudo sed -i -e '$a/swapfile       none    swap    sw      0       0' /etc/fstab
    sudo sed -i -e '$alisten_addresses = \'*\'' /etc/postgresql/9.3/main/pg_ident.conf
    sudo sed -i -e '0,/peer/s//md5/' /etc/postgresql/9.3/main/pg_hba.conf
    sudo service postgresql restart
    sudo -u postgres createdb lc
    sudo -u postgres createuser lc
    sudo -u postgres psql -c "ALTER USER lc with encrypted password 'lc';"

SCRIPT

$setup = <<SCRIPT
    cd /vagrant
    if [ ! -f app/config/parameters.yml ]; then
      cp app/config/parameters.yml.vagrant app/config/parameters.yml
    fi
    source install.sh
SCRIPT

$runserver = <<SCRIPT
    php /vagrant/app/console --env=dev server:start 0.0.0.0:8000
SCRIPT

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "ubuntu/trusty64"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"
  config.vm.synced_folder "./", "/vagrant", :nfs => true
  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  # config.vm.provision "shell", inline: <<-SHELL
  #   sudo apt-get update
  #   sudo apt-get install -y apache2
  # SHELL
  config.vm.network "forwarded_port", guest: 8000, host: 8000
  config.vm.provision "shell", inline: $dependencies
  config.vm.provision "shell",
        inline: $setup,
        privileged: false
  config.vm.provision "shell",
        inline: $runserver,
        privileged: false,
        run: "always"

end
