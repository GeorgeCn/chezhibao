#!/bin/bash
if [ $# != 1  ];then
    echo "usage: $0 {tag/branch}" 
    exit
fi

git fetch -p
git checkout $1 -f
composer install --prefer-dist   #更新composer
php7 app/console cache:clear --env=dev
php7 app/console doctrine:schema:update --force --env=dev
php7 app/console assetic:dump --env=dev
chmod -R 777 -R ./
