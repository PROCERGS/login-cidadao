#!/bin/bash

# Define permissions to write for www-data user
chown -R www-data:www-data /var/www/symfony/app/cache
chown -R www-data:www-data /var/www/symfony/app/logs

php app/console cache:clear -e prod --no-warmup
php app/console assets:install
php app/console assetic:dump --env=prod --no-debug
php app/console doctrine:schema:update --force
php app/console login-cidadao:database:populate batch
/bin/bash -l -c "$*"
