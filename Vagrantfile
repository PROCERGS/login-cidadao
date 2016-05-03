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

SCRIPT

$setup = <<SCRIPT
    #createdb lc
    cd /vagrant
    if [ ! -f app/config/parameters.yml ]; then
    	cp app/config/parameters.yml.vagrant app/config/parameters.yml
    fi
    source install.sh
SCRIPT

$runserver = <<SCRIPT
    php /vagrant/app/console --env=dev server:start 0.0.0.0:8000
SCRIPT

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # All Vagrant configuration is done here. The most common configuration
  # options are documented and commented below. For a complete reference,
  # please see the online documentation at vagrantup.com.

  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = "debian/jessie64"
  # config.vm.network :forwarded_port, guest: 80, host: 3131
  config.vm.network "forwarded_port", guest: 8000, host: 8000
  config.vm.provision "shell", inline: $dependencies
  config.vm.provision "shell",
        inline: $setup,
        privileged: false
  config.vm.provision "shell",
        inline: $runserver,
        privileged: false,
        run: "always"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder "./", "/vagrant"
  # owner: "mapas", group: "mapas"
end
