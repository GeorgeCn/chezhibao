#!/bin/bash
composer install --prefer-dist -v
php app/console cache:clear
php app/console assets:install --symlink
chmod 777 -R ./
php app/console assetic:dump