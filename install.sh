#!/bin/bash

SKIP_ACL=0
while test $# -gt 0
do
    case "$1" in
        --skip-acl)
            SKIP_ACL=1
            ;;
        --*) echo "bad option $1"
            ;;
        *) echo "argument $1"
            ;;
    esac
    shift
done

sudo echo "Installing login-cidadao..."

OK="[$(tput setaf 2)$(tput bold) OK $(tput sgr0)$(tput sgr0)]"
FAIL="[$(tput setaf 1)$(tput bold)FAIL$(tput sgr0)$(tput sgr0)]"
WARN="[$(tput setaf 3)$(tput bold)WARN$(tput sgr0)$(tput sgr0)]"
YELLOW_WARNING="$(tput setaf 3)$(tput bold)WARNING$(tput sgr0)$(tput sgr0)"
PARAMETERS_FILE="app/config/parameters.yml"

function die {
  echo -e $1
  exit 1
}

function setTimezone {
  echo "date.timezone = $2" | sudo tee --append $1
}

function askTimezone {
  if [ -n "$PHP_TIMEZONE" ]; then
    return
  fi
  read -p "What's your timezone? " -e -i "America/Sao_Paulo" PHP_TIMEZONE
}

###############################
# Setting up Permissions
###############################

if [ $SKIP_ACL -ne 1 ]; then
  echo -ne "Setting up Permissions...\\t\\t"
  rm -rf app/cache/*
  rm -rf app/logs/*

  HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
  if type "setfacl" &>/dev/null; then
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/uploads
  else
    sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs web/uploads &>/dev/null
    sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs web/uploads &>/dev/null
    if [ "$?" -ne 0 ]; then
      echo $FAIL
      die "\\nThere was a problem setting the directories permissions.\\nFor more info check: http://symfony.com/doc/current/book/installation.html"
    fi
  fi
  echo $OK
fi

###############################
# Configuring PHP timezones
###############################
PHP_INI_CLI="/etc/php5/cli/php.ini"
PHP_INI_FPM="/etc/php5/fpm/php.ini"
PHP_TIMEZONE=""

if [ -f $PHP_INI_CLI ]; then
  CLI_TIMEZONE=`php -c $PHP_INI_CLI -i | grep date.timezone | grep 'no value' | wc -l`
  if [ $CLI_TIMEZONE = "1" ]; then
    askTimezone
    setTimezone $PHP_INI_CLI $PHP_TIMEZONE
  fi
fi

if [ -f $PHP_INI_FPM ]; then
  FPM_TIMEZONE=`php -c $PHP_INI_FPM -i | grep date.timezone | grep 'no value' | wc -l`
  if [ $FPM_TIMEZONE = "1" ]; then
    askTimezone
    setTimezone $PHP_INI_FPM $PHP_TIMEZONE
  fi
fi

###############################
# Composer Check
###############################
if hash composer 2>/dev/null; then
  COMPOSER="composer"
else
  COMPOSER="php composer.phar"
  if [ ! -f "composer.phar" ]; then
    echo -e "Composer not found... Installing it as composer.phar"
    php -r "readfile('https://getcomposer.org/installer');" | php
  fi
fi

###############################
# composer install
###############################
echo -ne "Checking parameters.yml...\\t\\t"
if [ ! -f $PARAMETERS_FILE ]; then
  echo $WARN
  cp "$PARAMETERS_FILE.dist" $PARAMETERS_FILE
  echo "$YELLOW_WARNING: parameters.yml initialized with default values!"
  echo " This is likely to be a major problem when installing the database!"
  echo -e " This is likely to be a major problem running the application!\\n"
else
  echo $OK
fi
echo -ne "Installing dependencies...\\t\\t"
COMPOSER_RESULT=`$COMPOSER install -n 2>&1`
if [ "$?" -ne 0 ]; then
  echo $FAIL
  die "\\nThere was a problem running composer install procedure. Here is the output returned:\\n$COMPOSER_RESULT"
else
  echo $OK
fi

###############################
# Symfony Check
###############################
SF_CHECK="php app/check.php"
function sf_env_check {
  if type "php" &>/dev/null; then
    PHP_INFO=$($SF_CHECK 2>&1)
    if [ "$?" -ne 0 ]; then
      SF_OK=0
    else
      if [[ $PHP_INFO == *ERROR* ]]; then
        SF_OK=0
      fi
    fi
  else
    SF_OK=0
  fi
}

echo -ne "Checking Symfony2 requirements...\\t"
SF_OK=1
sf_env_check
if [ "$SF_OK" -ne 1 ]; then
  echo $FAIL
  die "Your environment didn't pass the test. Check the problems found by running:\\n$ $SF_CHECK"
else
  echo $OK
fi

###############################
# Database Setup
###############################
echo -ne "Installing the database...\\t\\t"
# Let's check if the schema is ok first...
php app/console doctrine:schema:validate -q

if [ "$?" -ne 0 ]; then
  # Ok... We'll have to do something...
  php app/console doctrine:database:create -q
  SCHEMA_CREATE=$(php app/console doctrine:schema:create 2>&1)

  if [ "$?" -ne 0 ]; then
    echo $FAIL
    die "\\nThere was a problem installing the database. Here is the error returned:\\n\\n$SCHEMA_CREATE"
  else
    POPULATE=$(php app/console lc:database:populate batch/ 2>&1)
  fi
fi
echo $OK

echo -e "\\nInstall is done."
