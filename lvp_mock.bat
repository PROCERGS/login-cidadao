@echo off
rmdir "app/cache" /s /q
git pull && git fetch --all && git reset --hard origin/master && composer install --prefer-dist --optimize-autoloader && php app/console assetic:dump --env=prod --no-debug && php app/console cache:clear --env=prod --no-warmup